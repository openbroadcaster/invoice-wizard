<?php

include 'modules/obadpsasystem/includes/checks.php';

/*
    Copyright 2012-2020 OpenBroadcaster, Inc.
    This file is part of OpenBroadcaster Server.
    OpenBroadcaster Server is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.
    OpenBroadcaster Server is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.
    You should have received a copy of the GNU Affero General Public License
    along with OpenBroadcaster Server.  If not, see <http://www.gnu.org/licenses/>.

    This file includes code from Valums File Uploader. © 2010 Andrew Valums.
    Licensed under GPL v2 or later.
    See license information in extras/valums-file-uploader.
*/

/**
 * Uploader
 */

class OBAdPSADSystemModule extends OBFController
{

  function __construct()
  {
    parent::__construct();
    $this->AdsModel = $this->load->model('Ads');
    $this->SettingsModel = $this->load->model('Settings');
    $this->PlayersModel = $this->load->model('Players');
    $this->media_model = $this->load->model('media');
    $this->uploads_model = $this->load->model('uploads');
    $this->buyers_model = $this->load->model('Buyer');
    $this->company_model = $this->load->model('Company');
    $this->advertiser_model = $this->load->model('Advertiser');
    $this->logs_model = $this->load->model('Logs');
    //$this->user->require_permission('ad_psa_system_create_invoices');
  }

  public function save_media()
  {
    $this->user->require_permission('ad_psa_system_create_campaign');
    $file_id = $this->data('file_id');
    $file_key = $this->data('file_key');
    $creative = $this->data('spot_name');
    $message_type = $this->data('message_type');
    $ad_id = $this->data('ad_id');
    $has_ad_id = $this->data('has_ad_id');
    $country = $this->data('country');
    $language = $this->data('language');
    if (empty($country) && $country == '') {
      $this->log('submit_tts_audio', 'Failed to save the tts audio! Please select a country first.');
      return [false, "Failed to save the tts audio! Please select a country first.", null];
    }
    if ($creative  == '' || $creative == null) {
      $this->log('save_media', 'Failed to upload audio! Please enter a creative name first.');
      return [false, "Failed to upload audio! Please enter a creative name first.", null];
    }
    if ($has_ad_id) {
      if (empty($ad_id)) {
        $this->log('save_media', 'Failed to upload the audio! Please enter a Ad-ID first.');
        return [false, "Failed to upload the audio! Please enter a Ad-ID first.", null];
      }
    } else {
      $ad_id = 'N/A';
    }
    if ($message_type == 'ad') {
      // For now as a workaround to allow for the media to be in ad/psa category the ad message will
      // use the PSA disabled code. This should be changed.
      $genre_id = 994;
      $category_id = 2;
    } elseif ($message_type == 'psa') {
      $genre_id = 995;
      $category_id = 2;
    } elseif ($message_type == 'other') {
      $genre_id = 999;
      $category_id = 10;
    } else {
      // This is a failsafe though we always get one of the values above,
      // if for some reason we don't reject the request.
      $this->log('save_media', 'Failed to upload the audio!');
      return [false, "Failed to upload the audio!", null];
    }

    $file_info = $this->uploads_model->file_info($file_id, $file_key);
    $item = array('file_id' => $file_id, 'file_key' => $file_key, 'artist' => 'AD System', 'title' => $creative, 'file_info' => $file_info, 'is_approved' => 1, 'is_copyright_owner' => 0, 'dynamic_select' => 0,'status' => 'public',
    'category_id' => $category_id, 'genre_id' => $genre_id, 'year' => date('Y'), 'comments' => 'Ad-ID: '. $ad_id, 'album' => 'AD/PSA Messages', 'local_id' => 1, 'country_id' => $country, 'language_id' => $language);
    $data = $this->media_model->validate(['item'=>$item], false);
    if ($data[0]) {
        $this->media_model->save(['item'=>$item]);
        $this->log('save_media', 'The media was uploaded!');
        return [true, 'The media was uploaded!', null];
      } else {
        $this->log('save_media', 'The media wasn\'t uploaded!');
        return [false, 'The media wasn\'t uploaded!', $data];
      }
  }

  // Store the invoice's station logo image data into a file.

  private function store_image($file, $convert) {
    $save_status = false;
    $status_msg = 'Your image has been submitted.';
    $filename = $file['name'];
    //$uploadPath = 'modules/obadpsasystem/temp_media/';
    $uploadPath = '/tmp/temp_media/';
    $status = move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadPath);
    //echo var_dump($file);
    if ($status) {
      if ($convert) {
        echo 'ffmpeg -i "'. $uploadPath. $file_name. '" '. $uploadPath. 'station_logo.jpeg';
        exec('ffmpeg -i "'. $uploadPath. $file_name. '" '. $uploadPath. 'station_logo.jpeg', $ffmpeg_output, $ffmepg_status);
        echo $ffmpeg_output;
        //echo "ffmepg_status". $ffmepg_status;
        if ($ffmepg_status == false) {
          $save_status = true;
        } else {
          $save_status = false;
          $status_msg = 'The image file failed to convert to the production format! Please contact a server admin.';
        }
      }
    } else {
      $save_status = false;
      $status_msg = 'Couldn\'t save the logo image to the server! Please contact a server admin.';
    }
    $this->log('store_image', $status_msg);
    return [$save_status, $status_msg];
  }

  // Saves the invoice's station logo image.

  public function save_station_logo()
  {
    $input_file = $this->data('data');
    $convert = true;
    // Check if file is a suported format.
    switch ($input_file['type']) {
      case 'image/jpeg':
        $convert = false;
        break;

      case 'image/png':
        $convert = true;
        break;

      default:
        return [false, 'You submited a invaild file format! Please use a png, or jpeg file.', null];
        break;
    }
    $status_data = $this->store_image($input_file, $convert);
    $status = $status_data[0];
    $status_msg = $status_data[1];
    // $file = fopen('modules/obadpsasystem/temp_media/'. $file_name, "w");
    // if ($file == false) {
    //   return [false, 'Couln\'t submit your image! Please try again later.', ''];
    // }
    // echo $file_data[''];
    // fwrite($file, $file_data);
    // fclose($file);
    // exec('ffmpeg -i modules/obadpsasystem/temp_media/'. $file_name. ' modules/obadpsasystem/temp_media/station_logo.jpeg', $output, $status);
    // //echo "status: ". $status;

    //return [$status, 'Your image has been submitted.', $status_msg];
    $this->log('save_station_logo', 'The logo has been saved!');
    return [$status, $status_msg, null];
  }

    private function randKey()
    {
      $chars = 'qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM0123456789';
      $key = '';
      for($i=0;$i<16;$i++)  $key.=$chars[rand(0,(strlen($chars)-1))];
      return $key;
    }

  // Finds next invoice number, and returns it.

  public function get_next_invoice_number()
  {
    $invoices = $this->AdsModel->get_invoices();
    // Return 1 if there isn't any invoices. Otherwise return the last invoice_number plus 1.
    if ($invoices == false) {
        return 1;
    } else {
      return end($invoices)['invoice_number'] + 1;
    }
  }

  public function create_invoice()
  {
    $this->user->require_permission('ad_psa_system_create_campaign');
    /*

      gets attention to name, advertiser, the billing data, sales rep, c number, and devices to play on.

    */

    try {
      $invoice_id = $this->randKey();
      $attention_to = $this->data('attention_to');
      $advertiser = $this->data('advertiser');
      $billing_mailing_address = $this->data('billing_mailing_address');
      $billing_mailing_city = $this->data('billing_mailing_city');
      $billing_mailing_state_prov = $this->data('billing_mailing_state_prov');
      $billing_mailing_zip_code = $this->data('billing_mailing_zip_code');
      $billing_email = $this->data('billing_email');
      $billing_mailing_country = $this->data('billing_mailing_country');
      $sales_rep = $this->data('sales_rep');
      $start_date = $this->data('start_date');
      $stop_date = $this->data('stop_date');
      $players = explode(',', $this->data('devices'));
      $player_names = [];
      foreach ($players as $player_id) {
        $player = $this->PlayersModel->get_one($player_id);
        array_push($player_names, $player['name']);
      }
      $creative = $this->data('creative');
      $ad_id = $this->data('ad_id');
      $time_slots = $this->data('time_slots');
      $media_type = $this->data('media_type');
      $media_file_id = $this->data('media_file_id');
      $campaign_name = $this->data('campaign_name');
      $buyer = $this->data('buyer');
      $revenue_type = $this->data('revenue_type');
      $contract_number = $this->data('contract_number');
      $campaign_notes = $this->data('campaign_notes');
      $media_data = ['file_id' => $media_file_id, 'invoices' => $invoice_id, 'time_slots' => $time_slots, 'devices' => $players];
      $invoice_data = ['invoice_id' => $invoice_id, 'attention_to' => $attention_to, 'advertiser' => $advertiser,
                      'billing_mailing_address' => $billing_mailing_address, 'billing_mailing_city' => $billing_mailing_city,
                      'billing_mailing_state_prov' => $billing_mailing_state_prov, 'billing_mailing_zip_code' => $billing_mailing_zip_code,
                      'billing_email' => $billing_email, 'sales_rep' => $sales_rep,
                      'device_names' => implode(',', $player_names), 'device_ids' => implode(',', $players), 'creative' => $creative,
                      'ad_id' => $ad_id, 'time_slots' => $time_slots,
                      'media_type' => $media_type, 'media_file_id' => $media_file_id, 'billing_mailing_country' => $billing_mailing_country,
                      'start_date' => $start_date, 'stop_date' => $stop_date, 'campaign_name' => $campaign_name,
                      'buyer' => $buyer, 'revenue_type' => $revenue_type, 'contract_number' => $contract_number,
                      'invoice_date' => date('M. d, Y'), 'invoice_number' => $this->get_next_invoice_number(), 'campaign_notes' => $campaign_notes];
      $this->AdsModel->add_new_ad($media_data, $invoice_data);
      $this->log('create_invoice', 'Invoice created!');
      return [true, 'Invoice created!'];
    } catch (\Exception $e) {
      $this->log('create_invoice', 'The invoice couldn\'t be saved!');
      return [false, 'The invoice couldn\'t be saved!', $e];
    }
  }

  public function get_invoices($sort_col='id', $sort_dir='asc')
  {
    $sort_col = $this->data('sort_col');
    if ($sort_col == null) {
      $sort_col = '';
    }
    $sort_dir = $this->data('sort_dir');
    if ($sort_dir == null) {
      $sort_dir = 'asc';
    }
    $this->user->require_permission('ad_psa_system_view_invoices');

    $invoices = $this->AdsModel->get_invoices($sort_col, $sort_dir);
    if ($invoices != false) {
      $json_data = json_encode($invoices);
      $this->log('get_invoices', 'All invoices returned');
      return [true, 'All invoices returned', $json_data];
    } else {
      $this->log('get_invoices', 'Couldn\'t return invoices!');
      return [false, 'Couldn\'t return invoices!', null];
    }
  }

  // Returns a list of the voices based on the language, and
  // language subsets selected in the settings.
  public function get_tts_voices()
  {
    $this->user->require_permission('ad_psa_system_create_tts_messages');
    $language = $this->data('language');
    if ($language == null) {
      $default_language = $this->SettingsModel->get_setting('default_language');
    } else {
      $default_language = $language;
    }
    $voices = [];
    $default_language_sets = explode(',', $this->SettingsModel->get_setting('default_language_sets'));
    if ($default_language == 'English') {
      foreach ($default_language_sets as $default_language_set) {
        switch ($default_language_set) {
            case 'Australian':
              array_push($voices, array('voice_name' => 'Nicole', 'gender' => 'Female', 'subset' => 'Australian',
              'language' => 'English'));
              array_push($voices, array('voice_name' => 'Russell', 'gender' => 'Male', 'subset' => 'Australian', '',
              'language' => 'English'));
              break;
            case 'British':
              array_push($voices, array('voice_name' => 'Amy', 'gender' => 'Female', 'subset' => 'British',
              'language' => 'English'));
              array_push($voices, array('voice_name' => 'Emma', 'gender' => 'Female', 'subset' => 'British',
              'language' => 'English'));
              array_push($voices, array('voice_name' => 'Brian', 'gender' => 'Male', 'subset' => 'British',
              'language' => 'English'));
              break;
            case 'Indian':
              array_push($voices, array('voice_name' => 'Aditi', 'gender' => 'Female', 'subset' => 'Indian',
              'language' => 'English'));
              array_push($voices, array('voice_name' => 'Raveena', 'gender' => 'Female', 'subset' => 'Indian',
              'language' => 'English'));
              break;
            case 'US':
              array_push($voices, array('voice_name' => 'Joanna', 'gender' => 'Female', 'subset' => 'US',
              'language' => 'English'));
              array_push($voices, array('voice_name' => 'Kendra', 'gender' => 'Female', 'subset' => 'US',
              'language' => 'English'));
              array_push($voices, array('voice_name' => 'Kimberly', 'gender' => 'Female', 'subset' => 'US',
              'language' => 'English'));
              array_push($voices, array('voice_name' => 'Salli', 'gender' => 'Female', 'subset' => 'US',
              'language' => 'English')) ;
              array_push($voices, array('voice_name' => 'Joey', 'gender' => 'Male', 'subset' => 'US',
              'language' => 'English'));
              array_push($voices, array('voice_name' => 'Matthew', 'gender' => 'Male', 'subset' => 'US',
              'language' => 'English'));
              break;
            case 'Welsh':
              array_push($voices, array('voice_name' => 'Geraint', 'gender' => 'Male', 'subset' => 'Welsh',
              'language' => 'English'));
              break;
        }
      }
      } elseif ($default_language == 'Spanish') {
        foreach ($default_language_sets as $default_language_set) {
          switch ($default_language_set) {
              case 'European':
                array_push($voices, array('voice_name' => 'Conchita', 'gender' => 'Female', 'subset' => 'European',
                'language' => 'European'));
                array_push($voices, array('voice_name' => 'Lucia', 'gender' => 'Female', 'subset' => 'European',
                'language' => 'European'));
                array_push($voices, array('voice_name' => 'Enrique', 'gender' => 'Male', 'subset' => 'European',
                'language' => 'European'));
                break;
              case 'Mexican':
                array_push($voices, array('voice_name' => 'Mia', 'gender' => 'Female', 'subset' => 'Mexican',
                'language' => 'Mexican'));
                break;
              case 'US Spanish':
                array_push($voices, array('voice_name' => 'Lupe', 'gender' => 'Female', 'subset' => 'US Spanish',
                'language' => 'Spanish'));
                array_push($voices, array('voice_name' => 'Penélope/Penelope', 'gender' => 'Female', 'subset' => 'US Spanish',
                'language' => 'Spanish'));
                array_push($voices, array('voice_name' => 'Miguel', 'gender' => 'Male', 'subset' => 'US Spanish',
                'language' => 'Spanish'));
                break;
          }
      }
    } elseif ($default_language == 'French') {
      foreach ($default_language_sets as $default_language_set) {
        switch ($default_language_set) {
            case 'None':
              array_push($voices, array('voice_name' => 'Céline/Celine', 'gender' => 'Female', 'subset' => 'None',
              'language' => 'French'));
              array_push($voices, array('voice_name' => 'Léa', 'gender' => 'Female', 'subset' => 'None',
              'language' => 'French'));
              array_push($voices, array('voice_name' => 'Mathieu', 'gender' => 'Male', 'subset' => 'None',
              'language' => 'French'));
              break;
            case 'Canadian':
              array_push($voices, array('voice_name' => 'Chantal', 'gender' => 'Female', 'subset' => 'Canadian',
              'language' => 'French'));
              break;
        }
      }
    } elseif ($default_language == 'Portuguese') {
      foreach ($default_language_sets as $default_language_set) {
        switch ($default_language_sets) {
            case 'Brazilian':
              array_push($voices, array('voice_name' => 'Camila', 'gender' => 'Female', 'subset' => 'Brazilian',
              'language' => 'Portuguese'));
              array_push($voices, array('voice_name' => 'Vitória/Vitoria', 'gender' => 'Female', 'subset' => 'Brazilian',
              'language' => 'Portuguese'));
              array_push($voices, array('voice_name' => 'Ricardo', 'gender' => 'Male', 'subset' => 'Brazilian',
              'language' => 'Portuguese'));
              break;
            case 'European':
              array_push($voices, array('voice_name' => 'Inês/Ines', 'gender' => 'Female', 'subset' => 'European',
              'language' => 'Portuguese'));
              break;
        }
      }
    }
    if (count($voices) > 0) {
      $this->log('get_tts_voices', 'The list of tts voices as returned.');
      return [true, '', $voices];
    } else {
      $this->log('get_tts_voices', 'Failed to get the currently active voices from the server! Please ensure the tts settings in the admin is set.');
      return [false, 'Failed to get the currently active voices from the server! Please ensure the tts settings in the admin is set.', null];
    }
  }

  public function request_tts_audio()
  {
    $this->user->require_permission('ad_psa_system_create_tts_messages');
    $output = [];
    $voice = $this->data('voice');
    $speed = $this->data('speed');
    $text = $this->data('text');
    $bed_music = $this->data('bed_music');
    $bed_music_vol = $this->data('bed_music_vol');
    // Handle invaild options, or no option selected errors.
    if (empty($voice) || $voice == '') return [false, "Please select a TTS langauge, lagauage set, and voice!", null];
    if (empty($speed) || $speed == '') return [false, "Please select a TTS speed!", null];
    if (empty($text) || $text == '') return [false, "Please type a message to speak!", null];
    $aws_access_key_id = $this->SettingsModel->get_setting('aws_access_key_id');
    //echo $aws_access_key_id;
    $aws_secret_access_key = $this->SettingsModel->get_setting('aws_secret_access_key');
    //echo $aws_secret_access_key;
    //$aws_region_name = $this->data('aws_region_name');
    $aws_region_name = $this->SettingsModel->get_setting('aws_region_name');
    //$aws_region_name = 'us-east-1';
    if (empty($aws_access_key_id) || empty($aws_secret_access_key)) {
      $this->log('request_tts_audio', 'Your tts request has failed! Please add your AWS API Keys for Polly TTS creation.');
      return [false, "Your tts request has failed! Please add your AWS API Keys for Polly TTS creation.", null];
    }
    $cmd = 'PATH=/home/obsuser/.local/bin:/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin:/usr/games:/usr/local/games:/snap/bin modules/obadpsasystem/tts_backend.sh'. ' --voice '. $voice. ' --speed '. $speed. ' --text "'. $text. '" --aws_access_key_id "'. $aws_access_key_id. '" --aws_secret_access_key "'. $aws_secret_access_key. '" --aws_region_name "'. $aws_region_name. '" --music_bed_track "'. $bed_music. '" --music_bed_track_volume '. $bed_music_vol;
    //exec(implode(" ", $cmd). " 2>&1", $output, $return_val);
    exec($cmd. " 2>&1", $output, $return_val);
    if ($return_val == 0) {
      $this->log('request_tts_audio', 'Sent tts request!');
      return [true, "Sent tts request!", implode(" ", $output)];
    } else {
      $this->log('request_tts_audio', 'Your tts request has failed!');
      return [false, "Your tts request has failed!", null];
    }
  }

  public function submit_tts_audio()
  {
    $this->user->require_permission('ad_psa_system_create_campaign');
    $audio_file = $this->data('audio_file');
    $file_path = "/tmp/temp_media/". $audio_file;
    $creative = $this->data('creative');
    $message_type = $this->data('message_type');
    $has_ad_id = $this->data('has_ad_id');
    $ad_id = $this->data('ad_id');
    $standalone = $this->data('standalone');
    $county = $this->data('county');
    if (empty($county) && $county == '') {
      $this->log('submit_tts_audio', 'Failed to save the tts audio! Please select a county first.');
      return [false, "Failed to save the tts audio! Please select a county first.", null];
    }
    if (empty($ad_id) && $has_ad_id == '') {
      $this->log('submit_tts_audio', 'Failed to save the tts audio! Please enter a Ad-ID first.');
      return [false, "Failed to save the tts audio! Please enter a Ad-ID first.", null];
    }
    if ($message_type == 'ad') {
      // For now as a workaround to allow for the media to be in ad/psa category the ad message will
      // use the PSA disabled code. This should be changed.
      $genre_id = 994;
      $category_id = 2;
      $album = 'AD/PSA Messages';
    } elseif ($message_type == 'psa') {
      $genre_id = 995;
      $category_id = 2;
      $album = 'AD/PSA Messages';
    } elseif ($message_type == 'other') {
      $genre_id = 999;
      $category_id = 10;
      $album = 'AD/PSA/Other TTS Messages';
    } else {
      // This is a failsafe though we always get one of the values above,
      // if for some reason we don't reject the request.
      $this->log('submit_tts_audio', 'Failed to save the tts audio!');
      return [false, "Failed to save the tts audio!", null];
    }
    $ch = curl_init("localhost/upload.php");

    curl_setopt($ch, CURLOPT_POST, true);
    $file = fopen($file_path, "r");
    $data = fread($file, filesize($file_path));
    fclose($file);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/octet-stream',
    'Content-Length: ' . strlen($data))
    );

    $returned_data = curl_exec($ch);
    //echo "returned_data ". $returned_data;
    $json_data = json_decode($returned_data);

    //$id = $json_data->id;
    $file_id = $json_data->file_id;
    $file_key = $json_data->file_key;

    if(curl_error($ch)) {
        $this->log('submit_tts_audio', 'Failed to save the tts audio!');
        return [false, "Failed to save the tts audio! upload 1", curl_error($ch)];
    } else {
      //$file_info = $json_data->info->media_info;
      $file_info = $this->uploads_model->file_info($file_id, $file_key);
      // file info should be from media info in json from upload.php request data.
      $item = array('file_id' => $file_id, 'file_key' => $file_key, 'artist' => 'AD System', 'title' => $creative, 'file_info' => $file_info, 'is_approved' => 1, 'is_copyright_owner' => 0, 'dynamic_select' => 0,'status' => 'public',
      'category_id' => $category_id, 'genre_id' => $genre_id, 'year' => date('Y'), 'comments' => 'Ad-ID: '. $ad_id, 'album' => $album, 'country_id' => $county, 'language_id' => 54, 'local_id' => 2);
      $data = $this->media_model->validate(array('item' => $item));
      if ($data[0]) {
        $this->media_model->save(array('item' => $item));
        // If the media is saved in the server, then remove it from /tmp/temp_media/.
        //$ch2 = curl_init('localhost/api.php');
        //curl_setopt($ch2, CURLOPT_POSTFIELDS, "c=media&a=save&d=". json_encode($item). "appkey=". "Nw==:vzPEmI9uI4hk5awZFeeLQiLx4i1ApRAE7ywKc+rgkHw=");
        //curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
        //print_r(curl_exec($ch2));
        unlink($file_path);
        if ($standalone) {
          $this->log('submit_tts_audio', 'Your audio has been uploaded. Do not exit the wizard!');
          return [true, "Your audio has been uploaded.", $returned_data];
        } else {
          $this->log('submit_tts_audio', 'Your audio has been uploaded. Do not exit the wizard!');
          return [true, "Your audio has been uploaded. Do not exit the wizard!", $returned_data];
        }
      } else {
        $this->log('submit_tts_audio', 'Failed to save the tts audio!');
        return [false, "Failed to save the tts audio! upload 2", $data[2]];
      }
    }
    curl_close($ch);
  }

  /* get setting's value from the database, and return it. */

  public function get_setting()
  {
    $this->user->require_permission('ad_psa_system_change_system_settings');
    $setting_name = $this->data('setting_name');
    // check they sent the setting name. If not return false.
    if ($setting_name == null) {
      return [false, "Erorr! invaild setting name, or no setting name sent.", null];
    }

    /* get setting value, and return it. */

    $value = $this->SettingsModel->get_setting($setting_name);
    if ($value != null) {
      $this->log('get_setting', $setting_name. ', value: '. $value .' returned.');
      return [true, "Settings value returned.", $value];
    } else {
      $this->log('get_setting', $setting_name. ', value: '. 'null' .' returned.');
      return [false, "Settings value null.", $value];
    }
  }

  /* update a setting's value in the database. */

  public function update_setting()
  {
    $this->user->require_permission('ad_psa_system_change_system_settings');
    $setting_name = $this->data('setting_name');
    $setting_value = $this->data('setting_value');
    // check they sent the setting name. If not return false.
    if ($setting_name == null) {
      $this->log('update_setting', 'Erorr! invaild setting name, or no setting name sent.');
      return [false, "Erorr! invaild setting name, or no setting name sent.", $setting_name];
    }

    /* update the setting value. */
    $this->SettingsModel->update_setting($setting_name, $setting_value);
    $this->log('update_setting', 'setting: '. $setting_name, ' was updated with value: '. $setting_value);
    return [true, "Settings updated.", null];
  }

  /* gets all players in the server's DB. */

  public function get_all_devices()
  {
    $output = [];
    $players = $this->PlayersModel->get_all();
    if ($players == False) {
      return [false, 'Your server needs atleast one player device before you can use this module!', null];
    }
    foreach ($players as $player) {
      array_push($output, ['name' => $player['name'], 'id' => $player['id']]);
    }
    $json_data = json_encode($output);
    $this->log('get_all_devices', 'All devices returned');
    return [true, 'All devices returned', $json_data];
  }

  /* Verifys data for the invoice. */

  public function verify_invoice_data()
  {
    $this->user->require_permission('ad_psa_system_create_campaign');
    $status_messages = [];
    $data = $this->data('data');
    $fields = json_decode($data);
    foreach ($fields as $field) {
      $name = $field->name;
      $value = $field->value;
      switch ($name) {
        /* Page 1 values check */
        // Checking text is only A-Z chars, spaces, and commas.
        case 'attention_to':
          if (! preg_match('/^[A-Z|a-z| |,]+$/', $value)) {
            array_push($status_messages, 'Attention to field has invaild character(s)! Only A-Z, commas, and spaces is allowed.');
          }
          break;
        // Checking text is only A-Z chars, spaces, and commas.
        case 'advertiser':
          if (! preg_match('/^[A-Z|a-z| |,]+$/', $value)) {
            array_push($status_messages, 'advertiser has invaild character(s)! Only A-Z, commas, and spaces is allowed.');
          }
          break;
          // Checking text is only A-Z chars, spaces, commas, dashes, or ().
          case 'buyer':
            if (! preg_match('/^[A-z|a-z|0-9|\–|\(|\)|,|\'| ]+$/', $value)) {
              array_push($status_messages, 'buyer has invaild character(s)! Only A-Z, commas, spaces, and "()" is allowed.');
            }
            break;
        case 'bill_to_email':
          if (! preg_match('/^[A-Za-z0-9._%+-]+\@[A-Za-z0-9.-_]+\.[A-Za-z]{2,}$/', $value)) {
            array_push($status_messages, 'The email provided is invalid!');
          }
          break;
        // Checking text is a valid US, or Canada formated address.
        // The first check is for the US format, and the second is the Canadian format.
        // TODO: Fix address checking.
        // case 'address':
        //   if (! preg_match('/^\d+\s[A-z]+\s[A-z]+$/', $value)) {
        //     if (! preg_match('/^[A-Z][0-9][A-Z] ?[0-9][A-Z][0-9]$/', $value))
        //     array_push($status_messages, 'the street address has invaild character(s)! Only A-Z, and spaces is allowed.');
        //   }
        //  break;
        if (! check_street_addr($value)) {
          array_push($status_messages, 'the street address has invaild character(s)! Only A-Z, 0-9, dashes. and spaces is allowed.');
        }
        // Checking if city is A-Z, and spaces only.
        case 'city':
          if (! check_a_z($value)) {
            array_push($status_messages, 'city has invaild character(s)! Only A-Z, and spaces is allowed.');
          }
          break;
        case 'zip_code':
          $message = check_zip_code($value);
          if ($message != '') {
            array_push($status_messages, $message);
          }
          break;

          /* page 2 values check */
          case 'creative':
            if (! preg_match('/^[A-Za-z |0-9]+$/', $value)) {
              array_push($status_messages, 'The creative name should be A-Z, spaces, and numbers only.');
            }
          break;
          case 'ad_id':
            // Checks if the user isn't requiring a Ad-ID.
            if ($this->data('no_ad_id') == true) {
              if ($value != "NA") {
                // Checks if the Ad-ID is valid.
                if (! preg_match('/^[1-9|A-Za-z][0-9A-Za-z]{3}[0-9]{4}[0-9]{3}[|H|D]$/', $value)) {
                  if(! preg_match('/^[1-9|A-Za-z][0-9A-Za-z]{3}[0-9]{4}[0-9]{3}$/', $value)) {
                    array_push($status_messages, 'The Ad-ID is invaild!');
                  }
                }
              }
            }
          break;
          case 'contract_number':
              // Checks if the contract number is valid.
              if (! check_num($value)) {
                array_push($status_messages, 'The contract number has invaild character(s)! Only numbers is allowed.');
              }
          break;
        case 'campaign_name':
          if (! check_a_z_0_9_dashes($value)) {
            array_push($status_messages, 'The Campaign name field has invaild character(s)! Only A-Z, commas, and spaces is allowed.');
          }
          if (empty($value)) {
            array_push($status_messages, 'The Campaign name field is empty! Please fill it using only A-Z, commas, and spaces.');
          }
          break;
      }
    }

    if (count($status_messages) > 0) {
      $this->log('verify_invoice_data', 'The invoice data wasn\'t able to be verifyed!');
      return [false, implode("\n", $status_messages), null];
    }

    $this->log('verify_invoice_data', 'The invoice data was able to be verifyed!');
    return [true, null, null];
  }

  // Verifys buyer data is valid.

  public function verify_buyer()
  {
    $this->user->require_permission('ad_psa_system_change_add_edit_remove_buyers');
    $attention_to = $this->data('attention_to');
    $advertiser = $this->data('advertiser');
    $name = $this->data('name');
    $phone = $this->data('phone');
    $street_addr = $this->data('street_addr');
    $city = $this->data('city');
    $advertiser = $this->data('advertiser');
    $email = $this->data('email');
    $zip_code = $this->data('zip_code');
    $country = $this->data('country');
    $values = ['advertiser' => $advertiser, 'name' => $name, 'phone' => $phone, 'email' => $email, 'city' => $city, 'zip_code' => $zip_code, 'street_addr' => $street_addr,
              'country' => $country];
    $status_messages = $this->buyers_model->verify($values);
    if (count($status_messages) > 0) {
        $this->log('verify_buyer', 'The buyer info wasn\'t able to be verifyed!');
        return [false, implode('<br>', $status_messages), null];
    }
    $this->log('verify_buyer', 'The buyer info was able to be verifyed!');
    return [true, null, null];
  }

  // Saves buyer data.

  public function save_buyer()
  {
    $this->user->require_permission('ad_psa_system_change_add_edit_remove_buyers');

    $from_company = $this->data('from_company');
    if ($from_company) {
      $attention_to = '';
      $advertiser = '';
      $name = $this->data('company_name');
      $phone = $this->data('company_phone');
      $phone = format_phone_number($phone);
      $street_addr = $this->data('company_street_address');
      $city = $this->data('company_city');
      $email = '';
      $zip_code = $this->data('company_zip_code');
      $state_prov = $this->data('company_state_prov');
      $country = $this->data('company_country');
      $sales_rep = '';
    } else {
      $advertiser = $this->data('advertiser');
      $name = $this->data('name');
      $phone = $this->data('phone');
      $phone = format_phone_number($phone);
      $street_addr = $this->data('street_addr');
      $city = $this->data('city');
      $email = $this->data('email');
      $zip_code = $this->data('zip_code');
      $state_prov = $this->data('state_prov');
      $country = $this->data('country');
      $sales_rep = $this->data('sales_rep');
    }
    $values = ['advertiser' => $advertiser, 'name' => $name, 'phone' => $phone, 'email' => $email, 'city' => $city, 'zip_code' => $zip_code, 'street_addr' => $street_addr,
              'state_prov' => $state_prov, 'country' => $country];
    $status = $this->buyers_model->save($values);
    if ($status) {
        $this->log('save_buyer', 'The buyer was saved!');
        return [true, 'buyer Saved!', null];
    }
    $this->log('save_buyer', 'The buyer wasn\'t saved!');
    return [false, 'The buyer couldn\'t be saved! Please contact a server admin.', null];
  }
  /* Updates buyer data. */
  public function update_buyer()
  {
    $this->user->require_permission('ad_psa_system_change_add_edit_remove_buyers');
    $id = $this->data('id');
    $attention_to = $this->data('attention_to');
    $advertiser = $this->data('advertiser');
    $name = $this->data('name');
    $phone = $this->data('phone');
    $phone = format_phone_number($phone);
    $street_addr = $this->data('street_addr');
    $city = $this->data('city');
    $advertiser = $this->data('advertiser');
    $email = $this->data('email');
    $zip_code = $this->data('zip_code');
    $values = ['attention_to' => $attention_to, 'advertiser' => $advertiser, 'name' => $name, 'phone' => $phone, 'email' => $email, 'city' => $city, 'zip_code' => $zip_code, 'street_addr' => $street_addr];
    $status = $this->buyers_model->update($id, $values);
    if ($status) {
        $this->log('update_buyer', 'The buyer with id: '. $id. ' was updated!');
        return [true, 'buyer Updated!', null];
    }
    $this->log('update_buyer', 'The buyer with id: '. $id. ' wasn\'t updated!');
    return [false, 'The buyer couldn\'t be Updated! Please contact a server admin.', null];
  }

  /* Gets a list of buyers */
  public function get_buyers()
  {
    $sort_col = $this->data('sort_col');
    if ($sort_col == null) {
      $sort_col = 'name';
    }
    $sort_dir = $this->data('sort_dir');
    if ($sort_dir == null) {
      $sort_dir = 'asc';
    }
    $buyers = $this->buyers_model->get($sort_col, $sort_dir);
    if (count($buyers) > 0) {
        $this->log('get_buyers', 'The buyers was not returned!');
        return [true, null, $buyers];
    }
    $this->log('get_buyers', 'The buyers was returned!');
    return [false, null, null];
  }

  /* Gets all data for the equested buyer */
  public function get_buyer()
  {
    $id = $this->data('id');
    $buyer = $this->buyers_model->get_one($id);
    if ($buyer != null) {
        $this->log('get_buyer', 'The selected buyer with id: '. $id .' was returned!');
        return [true, null, $buyer];
    }
    $this->log('get_buyer', 'The selected buyer with id: '. $id .' was not returned!');
    return [false, null, null];
  }

  /* Remove buyer */
  public function remove_buyer()
  {
    $this->user->require_permission('ad_psa_system_change_add_edit_remove_buyers');
    $error = false;
    $error_message = 'There was a issue requesting removal of the selected buyer. Please try agian later.';
    $id = $this->data('id');
    if ($id == null) {
      $error = true;
    }
    if ($error) {
      return [false, $error_message, null];
    }
    $status = $this->buyers_model->remove($id);
    if ($status) {
      $this->log('remove_buyer', 'The selected buyer with id: '. $id .' was removed!');
      return [true, 'The selected buyer has been removed!', null];
    } else {
      array_push($status_msg, '');
      $this->log('remove_buyer', 'The selected buyer with id: '. $id .' couldn\'t be removed!');
      return [false, $error_message, null];
    }
  }

  public function remove_company()
  {
    $this->user->require_permission('ad_psa_system_change_add_edit_remove_companines');
    $error = false;
    $error_message = 'There was a issue requesting removal of the selected company. Please try agian later.';
    $id = $this->data('id');
    if ($id == null) {
      $error = true;
    }
    if ($error) {
      return [false, $error_message, null];
    }
    $status = $this->company_model->remove($id);
    if ($status) {
      $this->log('remove_company', 'The selected company with id: '. $id .' has been removed!');
      return [true, 'The selected company has been removed!', null];
    } else {
      $this->log('remove_company', 'The selected company with id: '. $id .' couldn\'t be removed!');
      return [false, $error_message, null];
    }
  }

  /* Verifys all company is valid data. */

  public function verify_company()
  {
    $this->user->require_permission('ad_psa_system_change_add_edit_remove_companines');
    $name = $this->data('company_name');
    $phone = $this->data('company_phone');
    $street_address = $this->data('company_street_address');
    $city = $this->data('company_city');
    $state_prov = $this->data('company_state_prov');
    $zip_code = $this->data('company_zip_code');
    $country = $this->data('company_country');
    $values = ['name' => $name, 'phone' => $phone,
            'street_addr' => $street_address, 'city' => $city,
            'state_prov' => $state_prov, 'zip_code' => $zip_code,
            'country' => $country];

    $status_messages = $this->company_model->verify($values);
    if (count($status_messages) > 0) {
        $this->log('verify_company', 'The company couldn\'t be verifyed.');
        return [false, implode('<br>', $status_messages), null];
    }
    $this->log('verify_company', 'The company was verifyed.');
    return [true, null, null];
  }

  /* Saves company to the database. verify_company should be run first to check
     that all the data is valid. */

  public function save_company()
  {
    $this->user->require_permission('ad_psa_system_change_add_edit_remove_company');
    $name = $this->data('company_name');
    $phone = $this->data('company_phone');
    $street_address = $this->data('company_street_address');
    $city = $this->data('company_city');
    $state_prov = $this->data('company_state_prov');
    $zip_code = $this->data('company_zip_code');
    $country = $this->data('company_country');
    $values = ['name' => $name, 'phone' => $phone,
            'street_addr' => $street_address, 'city' => $city,
            'state_prov' => $state_prov, 'zip_code' => $zip_code,
            'country' => $country];

    $status = $this->company_model->save($values);
    if ($status == false) {
        $this->log('save_company', 'Couldn\'t save the company without a id');
        return [false, null, null];
    }
    $this->log('save_company', 'the company was saved.');
    return [true, null, null];
  }

  /* Saves changes the selected company to the database. verify_company should be run first to check
     that all the data is valid. */

  public function update_company()
  {
    $this->user->require_permission('ad_psa_system_change_add_edit_remove_company');
    $id = $this->data('current_company_id');
    if ($id == null) {
      $this->log('update_company', 'Couldn\'t update the company without a id');
      return [false, 'The system couldn\'t save your changes!', 'ID: '. $id];
    }
    $name = $this->data('company_name');
    $phone = $this->data('company_phone');
    $phone = format_phone_number($phone);
    $street_address = $this->data('company_street_address');
    $city = $this->data('company_city');
    $state_prov = $this->data('company_state_prov');
    $zip_code = $this->data('company_zip_code');
    $country = $this->data('company_country');
    $values = ['name' => $name, 'phone' => $phone,
            'street_addr' => $street_address, 'city' => $city,
            'state_prov' => $state_prov, 'zip_code' => $zip_code,
            'country' => $country];

    $status = $this->company_model->update($id, $values);
    if ($status == false) {
        $this->log('update_company', 'The company with id: '. $id. ' has been updated.');
        return [false, null, null];
    }
    $this->log('update_company', 'Couldn\'t update the company with id: '. $id);
    return [true, null, null];
  }

  public function get_companines()
  {
    $sort_col = $this->data('sort_col');
    if ($sort_col == null) {
      $sort_col = 'name';
    }
    $sort_dir = $this->data('sort_dir');
    if ($sort_dir == null) {
      $sort_dir = 'asc';
    }
    $companines = $this->company_model->get($sort_col, $sort_dir);
    if (count($companines) > 0) {
      $this->log('get_companines', 'Returned all companines');
      return [true, null, $companines];
    }
    $this->log('get_companines', 'Couldn\'t return the companines');
    return [false, null, null];
  }

  public function get_company()
  {
    $id = $this->data('id');
    $company = $this->company_model->get($id);
    if ($company != null) {
      $this->log('get_company', 'Company with id: '. $id. ' returned');
      return [true, null, $company];
    }
    $this->log('get_company', ' Couldn\'t find the company with id: '. $id);
    return [false, null, null];
  }

  // Checks if user has the given permission.

  public function permissons_check()
  {
    $permission_name = $this->data('name');
    $status = $this->user->check_permission($permission_name);
    if ($status == false) {
      return [false, null, $status];
    }

    return [true, null, $status];
  }

  // Returns all campaigns.
  // // TODO: Prevent needing to repeat this code with few changes.
  // NOTE: All invoices are basicly just a campaign, thus we return the same data but more is displayed.
  // It would most likly make sense to change this data to be stored in a new table in the future.

  public function get_campaigns()
  {
    $this->user->require_permission('ad_psa_system_view_edit_remove_campaigns');
    $sort_col = $this->data('sort_col');
    if ($sort_col == null) {
      $sort_col = 'name';
    }
    $sort_dir = $this->data('sort_dir');
    if ($sort_dir == null) {
      $sort_dir = 'asc';
    }
    $invoices = $this->AdsModel->get_invoices($sort_col, $sort_dir);
    if ($invoices != false) {
      $this->log('get_campaigns', 'All campaigns returned');
      return [true, 'All campaigns returned', $invoices];
    } else {
      $this->log('get_campaigns', 'Couldn\'t return campaigns!');
      return [false, 'Couldn\'t return campaigns!', null];
    }
  }

  // Returns the current advertisers in the system.

  public function get_advertisers() {
    $this->user->require_permission('ad_psa_system_view_edit_remove_advertisers');
    $sort_col = $this->data('sort_col');
    if ($sort_col == null) {
      $sort_col = 'name';
    }
    $sort_dir = $this->data('sort_dir');
    if ($sort_dir == null) {
      $sort_dir = 'asc';
    }
    $advertisers = $this->advertiser_model->get($sort_col, $sort_dir);
    if ($advertisers != false) {
        $this->log('get_advertisers', 'All advertisers returned');
        return [true, 'All advertisers returned', $advertisers];
    } else {
      $this->log('get_advertisers', 'Couldn\'t return advertisers!');
      return [false, 'Couldn\'t return advertisers!', null];
    }
  }

  // Returns the requested advertiser via the provided id.

  public function get_advertiser() {
    $this->user->require_permission('ad_psa_system_view_edit_remove_advertisers');
    $id = $this->data('id');
    $advertiser = $this->advertiser_model->get_one($id);
    if ($advertiser != null) {
        $this->log('get_advertiser', 'advertiser returned from id: '. $id);
        return [true, 'advertiser returned', $advertiser];
    } else {
      $this->log('get_advertiser', 'Couldn\'t return the requested advertiser with id: '. $id);
      return [false, 'Couldn\'t return the requested advertiser!', null];
    }
  }

  // Saves the current advertiser data's into the system.

  public function save_advertiser() {
    $this->user->require_permission('ad_psa_system_view_edit_remove_advertisers');

    $company_name = $this->data('company_name');
    $trade_name = $this->data('trade_name');
    $website = $this->data('website');
    $local_location = $this->data('local_location');
    $country = $this->data('country');
    $type = $this->data('type');
    $industry = $this->data('industry');

    $values = ['name' => $company_name, 'trade_name' => $trade_name,
              'website' => $website, 'local_location' => $local_location,
              'country' => $country, 'type' => $type,
              'industry' => $industry];
    $errors = $this->advertiser_model->verify($values);
    if (count($errors) > 0) {
      $this->log('save_advertiser', 'The Advertiser has one, or more errors', implode('<br>', $errors));
      return [false, 'The Advertiser has one, or more errors', implode('<br>', $errors)];
    } else {
      $advertisers = $this->advertiser_model->save($values);
      if ($advertisers != false) {
          $this->log('save_advertiser', 'Advertiser saved');
          return [true, 'Advertiser saved', $advertisers];
      } else {
        $this->log('save_advertiser', 'Couldn\'t save advertiser!');
        return [false, 'Couldn\'t save advertiser!', null];
      }
    }
  }

  // Updates a advertiser with the provided id.

  public function update_advertiser() {
    $this->user->require_permission('ad_psa_system_view_edit_remove_advertisers');

    $id = $this->data('id');
    $company_name = $this->data('company_name');
    $trade_name = $this->data('trade_name');
    $website = $this->data('website');
    $local_location = $this->data('local_location');
    $country = $this->data('country');
    $type = $this->data('type');
    $industry = $this->data('industry');

    $values = ['name' => $company_name, 'trade_name' => $trade_name,
              'website' => $website, 'local_location' => $local_location,
              'country' => $country, 'type' => $type,
              'industry' => $industry];
    $errors = $this->advertiser_model->verify($values);
    if (count($errors) > 0) {
      //return [false, 'The Advertiser has one, or more errors', implode('<br>', $errors)];
      return [false, implode('<br>', $errors), null];
    } else {
      $advertisers = $this->advertiser_model->update($id, $values);
      if ($advertisers != false) {
          $this->log('update_advertiser', 'Advertiser updated');
          return [true, 'Advertiser updated', $advertisers];
      } else {
        $this->log('update_advertiser', 'Couldn\'t update the advertiser!');
        return [false, 'Couldn\'t update the advertiser!', null];
      }
    }
  }

  // Removes the advertiser with the provided id.

  public function remove_advertiser() {
    $this->user->require_permission('ad_psa_system_view_edit_remove_advertisers');

    $id = $this->data('id');
      $status = $this->advertiser_model->remove($id);
      if ($status) {
          $this->log('remove_advertiser', 'Advertiser removed');
          return [true, 'Advertiser removed', null];
      } else {
        $this->log('remove_advertiser', 'Couldn\'t remove the advertiser!');
        return [false, 'Couldn\'t remove the advertiser!', null];
      }
    }

    // Logs module messages into a db table.
    // Logs everything to the database.

    private function log($method, $message)
    {
      $this->logs_model->save($method, $message);
    }


    public function get_logs()
    {
      $sort_col = $this->data('sort_col');
      if ($sort_col == null) {
        $sort_col = 'method';
      }
      $sort_dir = $this->data('sort_dir');
      if ($sort_dir == null) {
        $sort_dir = 'asc';
      }
      $logs = $this->logs_model->get($sort_col, $sort_dir);
      if (count($logs) > 0) {
        return [true, 'Logs retuned!', $logs];
      } else {
        return [true, 'The logs couldn\'t be returned!', null];
      }
    }

}

?>
