<?php

/**
 * Company model; Used to store companines for the module.
 */
class CompanyModel extends OBFModel
{

  public function get_one($id)
  {
    $this->db->where('id', $id);
    $data = $this->db->get_one('module_ad_system_companies');
    if ($this->db->error != '') {
      echo $this->db->error();
      return null;
    }
    return $data;
  }

  public function get($sort_col='name', $sort_dir='asc')
  {
    $this->db->orderby($sort_col, $sort_dir);
    $data = $this->db->get('module_ad_system_companies');
    echo $this->db->error();
    return $data;
  }

  public function save($data)
  {
    $this->db->insert('module_ad_system_companies', $data);
    if ($this->db->error() != '') {
      echo $this->db->error();
      return false;
    }
    return true;
  }

  // Method to update a company entry.

  public function update($id, $data)
  {
    $this->db->where('id', $id);
    $this->db->update('module_ad_system_companies', $data);
    if ($this->db->error() != '') {
      echo $this->db->error();
      return false;
    }
    return true;
  }

  public function verify($data)
  {
    $errors = [];
    if (preg_match('/^[A-Za-z0-9]+/', $data['name']) == false) {
      array_push($errors, "Company name is has invalid characters! Only A-Z, and 0-9 is allowed.");
    } else {
      if (strlen($data['name']) > 255) {
        array_push($errors, "Company name is to long! Max length is 255 characters.");
      }
    }
    if (check_phone_number($data['phone']) == false) {
      array_push($errors, "The phone number is invalid! Numbers must be formated as 800-555-5555.");
    }
    if (check_street_addr($data['street_addr']) == false) {
      array_push($errors, "Invalid street address!");
    } else {
      if (strlen($data['street_addr']) > 255) {
        array_push($errors, "The street address is to long! Max length is 255 characters.");
      }
    }
    if (preg_match('/^[A-Za-z ]+$/', $data['city']) == false) {
      array_push($errors, "Invalid city! Only a-z, and 0-9 characters is allowed.");
    } else {
      if (strlen($data['city']) > 255) {
        array_push($errors, "The city name is to long! Max length is 255 characters.");
      }
    }

    $status_msg = check_zip_code($data['zip_code']);
    if ($status_msg != '') {
      array_push($errors, $status_msg);
    }
    return $errors;
  }

  public function remove($id)
  {
    $this->db->where('id', $id);
    $data = $this->db->delete('module_ad_system_companies');
    if ($this->db->error() != '') {
      echo $this->db->error();
      return false;
    }
    return true;
  }

}


?>
