<?php

/*
    Copyright 2021 OpenBroadcaster, Inc.
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
*/

class OBAdPSASystemModule extends OBFModule
{

	public $name = 'Campaign Wizard v1.0';
	public $description = 'AD/PSA system allows media buyers to create and submit POLLY Ads or PSAs.';

	public function callbacks()
	{

	}

	public function install()
	{
		// Check for the background music folder. If it's not found, then we will create it below.
		$bg_folder = "modules/obadpsasystem/bed_music";
		if (is_dir($bg_folder) === false) {
			exec("mkdir -p ". $bg_folder. " 2>&1", $output, $return_val);
		}

		$this->db->query('CREATE TABLE IF NOT EXISTS `module_ad_system_messages` (
		`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	  `file_id` text NOT NULL,
	  `invoices` text NOT NULL,
	  `time_slots` text NOT NULL,
		`devices` text NOT NULL,
	  PRIMARY KEY (`id`)
	) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;');
		$this->db->query('CREATE TABLE IF NOT EXISTS `module_ad_system_invoices` (
		`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		`invoice_id` text NOT NULL,
		`attention_to` text NOT NULL,
		`advertiser` text NOT NULL,
		`buyer` text NOT NULL,
		`billing_mailing_address` text NOT NULL,
		`billing_mailing_city` text NOT NULL,
		`billing_mailing_state_prov` text NOT NULL,
		`billing_mailing_zip_code` int NOT NULL,
		`billing_email` text NOT NULL,
		`billing_mailing_country` text NOT NULL,
		`device_names` text NOT NULL,
		`device_ids` text NOT NULL,
		`creative` text NOT NULL,
		`ad_id` text NOT NULL,
		`time_slots` text NOT NULL,
		`media_type` text NOT NULL,
		`media_file_id` text NOT NULL,
		`contract_number` text NOT NULL,
		`revenue_type` text NOT NULL,
		`invoice_date` text NOT NULL,
		`start_date` text NOT NULL,
		`stop_date` text NOT NULL,
		`campaign_name` text NOT NULL,
		`companie_id` int NOT NULL,
		`buyer_id` int NOT NULL,
		`invoice_number` TEXT NOT NULL,
		`campaign_notes` TEXT NOT NULL,
		`sales_rep` TEXT NOT NULL,
		PRIMARY KEY (`id`)
	) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;');
	$this->db->query('CREATE TABLE IF NOT EXISTS `module_ad_system_companies` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`name` text NOT NULL,
	`phone` text NOT NULL,
	`street_addr` text NOT NULL,
	`city` text NOT NULL,
	`state_prov` text NOT NULL,
	`zip_code` text NOT NULL,
	`country` text NOT NULL,
	`player_id` int(4) unsigned NOT NULL,
	`player_name` text NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;');
$this->db->query('CREATE TABLE IF NOT EXISTS `module_ad_system_buyers` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`name` text NOT NULL,
`phone` text NOT NULL,
`street_addr` text NOT NULL,
`city` text NOT NULL,
`state_prov` text NOT NULL,
`zip_code` text NOT NULL,
`attention_to` text NOT NULL,
`advertiser` text NOT NULL,
`email` text NOT NULL,
`country` text NOT NULL,
`last_selected_advertiser` text NOT NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;');
$this->db->query('CREATE TABLE IF NOT EXISTS `module_ad_system_advertisers` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`name` text NOT NULL,
`trade_name` text NOT NULL,
`website` text NOT NULL,
`local_location` text NOT NULL,
`country` varchar(2) NOT NULL,
`type` text NOT NULL,
`notes` text NOT NULL,
`industry` text NOT NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;');
$this->db->query('CREATE TABLE IF NOT EXISTS `module_ad_system_logs` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`method` text NOT NULL,
`message` text NOT NULL,
`time` text NOT NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;');

	// Setting default values for some settings.

	$this->db->where('name','ad_psa_system_settings_aws_region_name');

	if(!$this->db->get_one('settings')) {
		$this->db->insert('settings', ['name'=> 'ad_psa_system_settings_aws_region_name', 'value' => 'us-east-2']);
	}

	$this->db->where('name','ad_psa_system_settings_aws_access_key_id');

	if(!$this->db->get_one('settings')) {
		$this->db->insert('settings', ['name'=>'ad_psa_system_settings_aws_access_key_id', 'value' => '']);
	}

	$this->db->where('name','ad_psa_system_settings_aws_secret_access_key');

	if(!$this->db->get_one('settings')) {
		$this->db->insert('settings', ['name'=>'ad_psa_system_settings_aws_secret_access_key', 'value' => '']);
	}

	$this->db->where('name','ad_psa_system_settings_stripe_publishable_key');

	if(!$this->db->get_one('settings')) {
		$this->db->insert('settings', ['name'=>'ad_psa_system_settings_stripe_publishable_key', 'value' => '']);
	}

	$this->db->where('name','ad_psa_system_settings_stripe_secret_key');

	if(!$this->db->get_one('settings')) {
		$this->db->insert('settings', ['name'=>'ad_psa_system_settings_stripe_secret_key', 'value' => '']);
	}

	$this->db->where('name','ad_psa_system_settings_company_name');

	if(!$this->db->get_one('settings')) {
		$this->db->insert('settings', ['name'=>'ad_psa_system_settings_company_name', 'value' => 'company name not set']);
	}

	$this->db->where('name','ad_psa_system_settings_company_street_address');

	if(!$this->db->get_one('settings')) {
		$this->db->insert('settings', ['name'=>'ad_psa_system_settings_company_street_address', 'value' => 'company street address not set']);
	}

	$this->db->where('name','ad_psa_system_settings_company_city');

	if(!$this->db->get_one('settings')) {
		$this->db->insert('settings', ['name'=>'ad_psa_system_settings_company_city', 'value' => 'company city not set']);
	}

	$this->db->where('name','ad_psa_system_settings_company_state_prov');

	if(!$this->db->get_one('settings')) {
		$this->db->insert('settings', ['name'=>'ad_psa_system_settings_company_state_prov', 'value' => 'company state/province not set']);
	}

	$this->db->where('name','ad_psa_system_settings_company_zip_code');

	if(!$this->db->get_one('settings')) {
		$this->db->insert('settings', ['name'=>'ad_psa_system_settings_company_zip_code', 'value' => 'company zip/postal code not set']);
	}

	$this->db->where('name','ad_psa_system_settings_company_country');

	if(!$this->db->get_one('settings')) {
		$this->db->insert('settings', ['name'=>'ad_psa_system_settings_company_country', 'value' => 'company country not set']);
	}

	$this->db->where('name','ad_psa_system_settings_company_phone');

	if(!$this->db->get_one('settings')) {
		$this->db->insert('settings', ['name'=>'ad_psa_system_settings_company_phone', 'value' => 'company phone number not set']);
	}

	$this->db->where('name','ad_psa_system_settings_currency');

	if(!$this->db->get_one('settings')) {
		$this->db->insert('settings', ['name'=>'ad_psa_system_settings_currency', 'value' => 'cad']);
	}

	$this->db->where('name','ad_psa_system_settings_morning_hourly_rate');

	if(!$this->db->get_one('settings')) {
		$this->db->insert('settings', ['name'=>'ad_psa_system_settings_morning_hourly_rate', 'value' => '0.00']);
	}

	$this->db->where('name','ad_psa_system_settings_midday_hourly_rate');

	if(!$this->db->get_one('settings')) {
		$this->db->insert('settings', ['name'=>'ad_psa_system_settings_midday_hourly_rate', 'value' => '0.00']);
	}

	$this->db->where('name','ad_psa_system_settings_drive_hourly_rate');

	if(!$this->db->get_one('settings')) {
		$this->db->insert('settings', ['name'=>'ad_psa_system_settings_drive_hourly_rate', 'value' => '0.00']);
	}

	$this->db->where('name','ad_psa_system_settings_ros_hourly_rate');

	if(!$this->db->get_one('settings')) {
		$this->db->insert('settings', ['name'=>'ad_psa_system_settings_ros_hourly_rate', 'value' => '0.00']);
	}

	$this->db->where('name','ad_psa_system_settings_default_country');

	if(!$this->db->get_one('settings')) {
		$this->db->insert('settings', ['name'=>'ad_psa_system_settings_default_country', 'value' => '']);
	}

	$this->db->where('name','ad_psa_system_settings_default_language');

	if(!$this->db->get_one('settings')) {
		$this->db->insert('settings', ['name'=>'ad_psa_system_settings_default_language', 'value' => '']);
	}

	$this->db->where('name','ad_psa_system_settings_default_language_sets');

	if(!$this->db->get_one('settings')) {
		$this->db->insert('settings', ['name'=>'ad_psa_system_settings_default_language_sets', 'value' => '']);
	}

	$this->db->where('name','ad_psa_system_settings_enabled_languages');

	if(!$this->db->get_one('settings')) {
		$this->db->insert('settings', ['name'=>'ad_psa_system_settings_enabled_languages', 'value' => '[{"english":false},{"chinese_mandarin":false},{"dutch":false},{"french":false},{"german":false},{"italian":false},{"portuguese":false},{"spanish":false}]']);
	}

	$this->db->where('name','ad_psa_system_settings_gst_number');

	if(!$this->db->get_one('settings')) {
		$this->db->insert('settings', ['name'=>'ad_psa_system_settings_hst_number', 'value' => '']);
	}

	// Add needed permissons.

	$data = array();
	$data['name'] = 'ad_psa_system_view_invoices';
	$data['description'] = 'view invoices created by AD/PSA System module';
	$data['category'] = 'ad/psa system';

	$this->db->insert('users_permissions', $data);

	$data = array();
	$data['name'] = 'ad_psa_system_create_campaign';
	$data['description'] = 'create Campaign in the AD/PSA System module';
	$data['category'] = 'ad/psa system';

	$this->db->insert('users_permissions', $data);

	$data = array();
	$data['name'] = 'ad_psa_system_change_system_settings';
	$data['description'] = 'Allows user to change settings for the AD/PSA System module';
	$data['category'] = 'ad/psa system';

	$this->db->insert('users_permissions', $data);

	$data = array();
	$data['name'] = 'ad_psa_system_change_add_edit_remove_buyers';
	$data['description'] = 'Allows user to add, edit, or remove buyers from the AD/PSA System module';
	$data['category'] = 'ad/psa system';

	$this->db->insert('users_permissions', $data);

	$data = array();
	$data['name'] = 'ad_psa_system_change_add_edit_remove_company';
	$data['description'] = 'Allows user to add, edit, or remove a company from the AD/PSA System module';
	$data['category'] = 'ad/psa system';

	$this->db->insert('users_permissions', $data);

	$data = array();
	$data['name'] = 'ad_psa_system_view_edit_remove_campaigns';
	$data['description'] = 'Allows user to view, edit, or remove a campaign from the AD/PSA System module';
	$data['category'] = 'ad/psa system';

	$this->db->insert('users_permissions', $data);

	// Adds the permission for building tts messages.

	$data = array();
	$data['name'] = 'ad_psa_system_create_tts_messages';
	$data['description'] = 'Allows a user to a create tts message.';
	$data['category'] = 'ad/psa system';

	$this->db->insert('users_permissions', $data);

	// Adds permisson for viewing, adding, editing, or removing
	// advertisers.

	$data = array();
	$data['name'] = 'ad_psa_system_view_edit_remove_advertisers';
	$data['description'] = 'Allows a user to create a advertiser record.';
	$data['category'] = 'ad/psa system';

	$this->db->insert('users_permissions', $data);

	// Add buttons to the playlist area.
	$data = array();
	$data['name'] = 'ad_slot_30';
	$data['description'] = 'Ad Slot :30';
	$data['duration'] = '30';
	$data['callback_model'] = 'AdsModel';
	$data['callback_method'] = 'ad_slot_30';
	$this->db->insert('playlists_items_types', $data);

	$data = array();
	$data['name'] = 'psa_slot_30';
	$data['description'] = 'PSA Slot :30';
	$data['duration'] = '30';
	$data['callback_model'] = 'AdsModel';
	$data['callback_method'] = 'psa_slot_30';
	$this->db->insert('playlists_items_types', $data);

	// Run the code to add the default buyers.
	$file_path = 'modules/obadpsasystem/data/default_ad_buyers.json';
	if (file_exists($file_path)) {
		$file = fopen($file_path, 'r');
		$data = fread($file, filesize($file_path));
		fclose($file);
		$json = json_decode($data);
		foreach ($json->buyers as $buyer) {
			$data = ['name' => $buyer->name, 'phone' => $buyer->phone,
							'street_addr' => $buyer->street,
							'city' => $buyer->city, 'state_prov' => $buyer->state_prov,
							'zip_code' => $buyer->zip_code, 'advertiser' => '',
							'email' => $buyer->emails[0], 'country' => $buyer->country,
							'last_selected_advertiser' => ''];

			$this->db->where('name', $buyer->name);
			if (!$this->db->get_one('module_ad_system_buyers')) {
				$this->db->insert('module_ad_system_buyers', $data);
				if ($this->db->error() != '') {
					error_log('There was a error saving a buyer entry for the ad system. '. $this->db->error());
				}
			}
		}
	}

	return true;
	}

	public function uninstall()
	{
		//$this->db->delete('module_ad_system_messages');
		//$this->db->query('DROP TABLE `module_ad_system_invoices`;');
		//$this->db->delete('module_ad_system_settings');
		//$this->db->query('DROP TABLE \'module_ad_system_settings\';');
		// Removes permissons dsaveuring the uninstall.
		$this->db->where('name','ad_psa_system_view_invoices');
		$this->db->delete('users_permissions');
		$this->db->where('name','ad_psa_system_create_invoices');
		$this->db->delete('users_permissions');
		$this->db->where('name','ad_psa_system_create_tts_messages');
		$this->db->delete('users_permissions');
		$this->db->where('name','ad_psa_system_view_edit_remove_campaigns');
		$this->db->delete('users_permissions');
		$this->db->where('name','ad_psa_system_change_add_edit_remove_company');
		$this->db->delete('users_permissions');
		$this->db->where('name','ad_psa_system_change_add_edit_remove_buyers');
		$this->db->delete('users_permissions');
		$this->db->where('name','ad_psa_system_change_system_settings');
		$this->db->delete('users_permissions');
		$this->db->where('name','ad_psa_system_create_campaign');
		$this->db->delete('users_permissions');

		// Remove buttons for playlists.
		$this->db->where('name','ad_slot_30');
		$this->db->delete('playlists_items_types');
		$this->db->where('name','psa_slot_30');
		$this->db->delete('playlists_items_types');

		//Remove default buyers. Non defualt buyers won't be removed.
		$file_path = 'modules/obadpsasystem/data/default_ad_clients.json';
		if (file_exists($file_path)) {
			$file = fopen($file_path, 'r');
			$data = fread($file, filesize($file_path));
			fclose($file);
			$json = json_decode($data);
			foreach ($json->buyers as $buyer) {
				$this->db->where('name', $buyer->name);
				$status = $this->db->delete('module_ad_system_buyers');
				if (!$status) {
					error_log('There was a error removing a buyer entry for the ad system. '. $this->db->error());
				}
			}
		}
		return true;
	}
}
