import boto3
import uuid
import wave
import subprocess
import os
import os.path
import argparse
import re
import json
from pydub import AudioSegment

# checks if cli arguments are valid.

def args_veify(args):
    args_list = ['voice', 'speed', 'aws_access_key_id', 'aws_secret_access_key', 'aws_region_name', 'text', '']
    aws_regions = ['us-east-1', 'us-east-2', 'us-west-1', 'us-west-2', 'ca-central-1', 'us-gov-east-1', 'us-gov-west-1']
    for arg_list_item in args_list:
        if arg_list_item == 'voice':
            if args.voice != None:
                if args.voice == 'Ivy' or args.voice == 'Joanna' or args.voice == 'Kendra'\
                or args.voice == 'Kimberly' or args.voice == 'Salli' or args.voice == 'Joey' \
                or args.voice == 'Justin' or args.voice == 'Matthew' or args.voice == 'Chantal' :
                    pass
                else:
                    return false
        elif arg_list_item == 'speed':
            if args.speed != None:
                if args.speed > 19 and args.speed < 201:
                    pass
                else:
                    print('Invaild speed!')
                    return False
        elif arg_list_item == 'aws_access_key_id':
            if args.aws_access_key_id != None or args.aws_access_key_id != '':
                pass
            else:
                print('Invaild aws access key id!')
                return False
        elif arg_list_item == 'aws_secret_access_key':
            if args.aws_secret_access_key != None or args.aws_secret_access_key != '':
                pass
            else:
                print('Invaild aws secret access key!')
                return False
        elif arg_list_item == 'aws_region_name':
            if args.aws_region_name != None and args.aws_region_name != '':
                for aws_region in aws_regions:
                    if args.aws_region_name == aws_region:
                        break
            else:
                print('Invaild aws region name!')
                return False
        elif arg_list_item == 'text':
            if args.text != None and args.text != '':
                pass
            else:
                print('Text is required to build audio!')
                return False
    return True

# class to create a tts message for playback, and/or airing.

class TTS:
    def __init__(self, voice='Joanna', speed=None, aws_access_key_id=None, aws_secret_access_key=None, aws_region_name='us-east-1'):
        self.voice = voice
        self.speed = speed
        self.bg_supported = True
        self.polly_client = boto3.Session(
                aws_access_key_id=aws_access_key_id,
                aws_secret_access_key=aws_secret_access_key,
                region_name=aws_region_name).client('polly')

        if os.path.exists('/tmp/temp_media/') == False:
            os.mkdir('/tmp/temp_media/')
        try:
            if os.path.exists('modules/obadpsasystem/bed_music') == False:
                os.mkdir('modules/obadpsasystem/bed_music')
        except Exception as e:
            # Failover to no bed music if we can find it.
            self.bg_supported = False
            pass

        

    # Method to call the aws Polly service, get audio, and save it using ffmpeg.

    def gen(self, text, music_bed_track=None, music_bed_track_volume=12):
        if music_bed_track == None or music_bed_track == "None":
            music = False
        else:
            music = True

        if self.bg_supported== False:
            music = False

        # spliting text into mutiplie requests as needed.

        # text_parts = []
        #
        # if len(text) > 500:
        #     for text_part in text.split('\n'):
        #         if len(text_part) < 501:
        #             text_parts.append(text_part)
        #         else:
        #             text_parts.append(text_part.split('\n')[0])
        #             text_parts.append(text_part.split('\n')[1])
        # print(text_parts)
        # exit()

        ssml_text = '<speak><prosody rate="{0}%">{1}</prosody></speak>'.format(self.speed, text)
        try:
            response = self.polly_client.synthesize_speech(VoiceId=self.voice,
                    OutputFormat='pcm',
                    TextType = 'ssml',
                    Text = ssml_text)

            pcm_audio = response['AudioStream'].read()
            file_name = '{0}.pcm'.format(uuid.uuid4().hex)
            file_path = '/tmp/temp_media/{0}'.format(file_name)
            with open(file_path, 'wb') as file:
                file.write(pcm_audio)
            process = subprocess.Popen(['ffmpeg', '-f', 's16le', '-ar', '16k', '-ac', '1',
                                        '-i', file_path, file_path.replace('.pcm', '.wav')], stdout=subprocess.PIPE, stderr=subprocess.PIPE)
            process.wait()

            if music:
                file_name = self.make_audio_mix(file_path.replace('.pcm', '.wav'), music_bed_track, music_bed_track_volume)

            if os.path.isfile(file_path.replace('.pcm', '.wav')) == False:
                self.return_output(msg='encoding audio failed!', file_path=None)
            else:
                self.return_output(msg='Audio encoded!', file_path=file_name.replace('.pcm', '.wav'))

            #self.cleanup(file_path)


        except Exception as e:
            #self.cleanup(file_path)
            print(e)

    # mixes bed music, and voice audio together.

    def make_audio_mix(self, tts_audio_file, music_bed_file, music_bed_track_volume):
        tts_audio = AudioSegment.silent(duration=2000) + AudioSegment.from_file(tts_audio_file, format="wav")
        music_audio = AudioSegment.from_file("modules/obadpsasystem/" + music_bed_file, format="mp3")

        # get tts audio seconds and add 2 seconds to the duration.
        # Tailing two seconds is for fading the music down.

        tts_audio_len = len(tts_audio) + 2000

        # Removes any music after the tts audio message duration.

        music_audio = music_audio[:tts_audio_len]

        # Adds a fade to the end, and start of the music track.

        music_audio = music_audio.fade_in(500)

        music_audio = music_audio.fade_out(2000)

        # lowers the volume for the music track.

        music_audio = music_audio - music_bed_track_volume

        sound3 = music_audio.overlay(tts_audio)
        output_file_name = '{0}.wav'.format(uuid.uuid4().hex)
        output_file_path = '/tmp/temp_media/{0}'.format(output_file_name)
        sound3.export(output_file_path, format="wav")

        return output_file_name

    def return_output(self, msg, file_path):
        json_output = json.dumps({'msg': msg, 'file_path': file_path})
        print(json_output)

    def cleanup(self, file_path):
        try:
            os.remove(file_path)
        except Exception as e:
            print(e)


if __name__ == '__main__':
    args_parser = argparse.ArgumentParser(description='Cli app to be called from the ad system as needed.')
    args_parser.add_argument('--voice', type=str, help='AWS Polly voice to use')
    args_parser.add_argument('--speed', type=int, help='Speed to read the text.')
    args_parser.add_argument('--text', type=str, help='The message to be read by the TTS system.')
    args_parser.add_argument('--aws_access_key_id', type=str, help='AWS key id')
    args_parser.add_argument('--aws_secret_access_key', type=str, help='AWS acess key')
    args_parser.add_argument('--aws_region_name', type=str, help='The AWS region to send the reqest to.')
    args_parser.add_argument('--music_bed_track', type=str, help='The background music track.')
    args_parser.add_argument('--music_bed_track_volume', type=int, help='Sets the background music track.')
    args = args_parser.parse_args()
    if args_veify(args):
        tts = TTS(speed=args.speed, aws_access_key_id=args.aws_access_key_id, aws_secret_access_key=args.aws_secret_access_key, voice=args.voice)
        if args.music_bed_track != "":
            tts.gen(args.text, args.music_bed_track, args.music_bed_track_volume)
        else:
            tts.gen(args.text, None, args.music_bed_track_volume)
