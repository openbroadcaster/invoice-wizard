<?php

/**
 * TTS model; Builds audio messages with AWS Polly. $aws holds its key, secret, and region.
 */
class TtsModel extends OBFModel
{

  // Returns [true, '', mp3 data], or [false, the reason].
  public function generate($aws, $voice, $speed, $text, $bed_track, $bed_volume)
  {
    $bed_file = null;
    if (!empty($bed_track)) {
      $bed_file = OB_LOCAL. '/modules/obadpsasystem/bed_music/'. $bed_track;
      // Only files directly in the bed music folder can be used.
      if (basename($bed_track) !== $bed_track || !is_file($bed_file)) {
        return [false, 'The bed music track was not found.'];
      }
    }
    $audio = $this->synthesize($aws, $voice, max(20, min(200, (int) $speed)), $text);
    if (!$audio[0] || !$bed_file) {
      return $audio;
    }
    return $this->mix($audio[2], $bed_file, max(12, min(24, (int) $bed_volume)));
  }

  public function bed_tracks()
  {
    return array_map('basename', glob(OB_LOCAL. '/modules/obadpsasystem/bed_music/*.mp3') ?: []);
  }

  private function synthesize($aws, $voice, $speed, $text)
  {
    $ssml = '<speak><prosody rate="'. $speed. '%">'. htmlspecialchars($text, ENT_XML1 | ENT_NOQUOTES). '</prosody></speak>';
    $body = json_encode(['VoiceId' => $voice, 'OutputFormat' => 'mp3', 'TextType' => 'ssml', 'Text' => $ssml]);
    $host = 'polly.'. $aws['region']. '.amazonaws.com';
    $date = gmdate('Ymd\THis\Z');
    $headers = ['Content-Type: application/json', 'X-Amz-Date: '. $date, 'Authorization: '. $this->authorization($aws, $host, $date, $body)];
    [$status, $reply, $error] = $this->send('https://'. $host. '/v1/speech', $headers, $body);

    if ($error !== '') {
      return [false, $error];
    }
    if ($status != 200) {
      $details = json_decode($reply, true);
      return [false, $details['message'] ?? $details['Message'] ?? 'Polly replied with status '. $status. '.'];
    }
    if ($reply === '') {
      return [false, 'Polly returned no audio.'];
    }
    return [true, '', $reply];
  }

  // AWS Signature Version 4, for the one request made here (fixed path, no query string).
  private function authorization($aws, $host, $date, $body)
  {
    $day = substr($date, 0, 8);
    $scope = $day. '/'. $aws['region']. '/polly/aws4_request';
    $signed_headers = 'content-type;host;x-amz-date';
    $canonical_request = "POST\n/v1/speech\n\ncontent-type:application/json\nhost:". $host. "\nx-amz-date:". $date. "\n\n". $signed_headers. "\n". hash('sha256', $body);
    $string_to_sign = "AWS4-HMAC-SHA256\n". $date. "\n". $scope. "\n". hash('sha256', $canonical_request);
    $key = 'AWS4'. $aws['secret'];
    foreach ([$day, $aws['region'], 'polly', 'aws4_request'] as $part) {
      $key = hash_hmac('sha256', $part, $key, true);
    }
    return 'AWS4-HMAC-SHA256 Credential='. $aws['key']. '/'. $scope. ', SignedHeaders='. $signed_headers. ', Signature='. hash_hmac('sha256', $string_to_sign, $key);
  }

  // Returns [http status, reply, curl error].
  protected function send($url, $headers, $body)
  {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    $reply = curl_exec($ch);
    return [curl_getinfo($ch, CURLINFO_RESPONSE_CODE), (string) $reply, curl_error($ch)];
  }

  // The voice starts two seconds in, and the music plays two seconds past it, fading down.
  private function mix($voice_audio, $bed_file, $volume)
  {
    $voice_file = tempnam(sys_get_temp_dir(), 'tts');
    file_put_contents($voice_file, $voice_audio);
    try {
      $duration = $this->run(['ffprobe', '-v', 'error', '-show_entries', 'format=duration', '-of', 'csv=p=0', $voice_file]);
      if (!$duration[0]) {
        return $duration;
      }
      $voice_end = 2 + (float) $duration[2];
      $filters = '[0:a]adelay=2000,apad=pad_dur=2[voice];'
               . '[1:a]atrim=0:'. ($voice_end + 2). ',afade=t=in:d=0.5,afade=t=out:st='. $voice_end. ':d=2,volume=-'. $volume. 'dB[music];'
               . '[voice][music]amix=inputs=2:duration=first:normalize=0[mix]';
      // Without an explicit format the mix drops to the voice's 22 kHz mono, and a loud voice clips.
      return $this->run(['ffmpeg', '-nostdin', '-loglevel', 'error', '-i', $voice_file, '-i', $bed_file, '-filter_complex', $filters,
                         '-map', '[mix]', '-ar', '44100', '-ac', '2', '-b:a', '128k', '-f', 'mp3', '-']);
    } finally {
      unlink($voice_file);
    }
  }

  // Runs a tool without a shell. Returns [true, '', its output], or [false, its last error line].
  private function run($command)
  {
    $process = proc_open($command, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
    if ($process === false) {
      return [false, $command[0]. ' could not be run on this server.'];
    }
    $output = stream_get_contents($pipes[1]);
    $errors = explode("\n", trim(stream_get_contents($pipes[2])));
    return proc_close($process) == 0 ? [true, '', $output] : [false, end($errors)];
  }
}
