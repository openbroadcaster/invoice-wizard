<?php

/**
 * Advertiser model; Used to store advertisers for the module.
 */
class AdvertiserModel extends OBFModel
{

  public function get_one($id)
  {
    $this->db->where('id', $id);
    $data = $this->db->get_one('module_ad_system_advertisers');
    if ($this->db->error != '') {
      echo $this->db->error();
      return null;
    }
    return $data;
  }

  public function get($sort_col='name', $sort_dir='asc')
  {
    $this->db->orderby($sort_col, $sort_dir);
    $data = $this->db->get('module_ad_system_advertisers');
    echo $this->db->error();
    return $data;
  }

  public function save($data)
  {
    $this->db->insert('module_ad_system_advertisers', $data);
    if ($this->db->error() != '') {
      echo $this->db->error();
      return false;
    }
    return true;
  }

  // Method to update a advertiser entry.

  public function update($id, $data)
  {
    $this->db->where('id', $id);
    $this->db->update('module_ad_system_advertisers', $data);
    if ($this->db->error() != '') {
      echo $this->db->error();
      return false;
    }
    return true;
  }

  public function verify($data)
  {
    $errors = [];
    if (preg_match('/^[A-Za-z0-9\s\-]+/', $data['name']) == false) {
      array_push($errors, "Advertiser name is has invalid characters! Only A-Z, dashes, spaces, and 0-9 is allowed.");
    } else {
      if (strlen($data['name']) > 255) {
        array_push($errors, "Advertiser name is to long! Max length is 255 characters.");
      }
    }
    if (preg_match('/^[A-Za-z0-9\s\-]+/', $data['trade_name']) == false) {
      array_push($errors, "The Trade name is has invalid characters! Only A-Z, dashes, spaces, and 0-9 is allowed.");
    } else {
      if (strlen($data['trade_name']) > 255) {
        array_push($errors, "The Trade name is to long! Max length is 255 characters.");
      }
    }
    if (preg_match('/^[A-Za-z0-9]+/', $data['local_location']) == false) {
      array_push($errors, "The local location is has invalid characters! Only A-Z, and 0-9 is allowed.");
    } else {
      if (strlen($data['local_location']) > 255) {
        array_push($errors, "The local location is to long! Max length is 255 characters.");
      }
    }
    if (preg_match('/^[A-Za-z0-9]+/', $data['website']) == false) {
      array_push($errors, "The website url is has invalid characters! Only A-Z, and 0-9 is allowed.");
    } else {
      if (strlen($data['website']) > 255) {
        array_push($errors, "The website url is to long! Max length is 255 characters.");
      }
    }
    return $errors;
  }

  public function remove($id)
  {
    $this->db->where('id', $id);
    $data = $this->db->delete('module_ad_system_advertisers');
    if ($this->db->error() != '') {
      echo $this->db->error();
      return false;
    }
    return true;
  }

}


?>
