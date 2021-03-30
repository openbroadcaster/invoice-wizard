<?php

/**
 * Settings model; Used to store settings for the module.
 */
class SettingsModel extends OBFModel
{

  public function update_setting($setting_name, $setting_value)
  {
    $full_setting_name = 'ad_psa_system_settings_'. $setting_name;
    $data = ['name' => $full_setting_name, 'value' => $setting_value];
    $this->db->where('name', $full_setting_name);
    $this->db->update('settings', $data);
    if ($this->db->error() == '') {
      return true;
    }
    return false;
  }

  public function get_setting($setting_name)
  {
    $this->db->where('name', 'ad_psa_system_settings_'. $setting_name);
    $data = $this->db->get_one('settings');
    echo $this->db->error();
    return $data['value'];
  }
}


?>
