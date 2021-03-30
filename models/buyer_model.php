<?php

/**
 * Buyers model; Used to store buyers for the module.
 */
class BuyerModel extends OBFModel
{

  public function get_one($id)
  {
    $this->db->where('id', $id);
    $data = $this->db->get_one('module_ad_system_buyers');
    if (count($data) > 0) {
      return $data;
    }
    return null;
  }

  public function get($sort_col='name', $sort_dir='asc')
  {
    $this->db->orderby($sort_col, $sort_dir);
    $data = $this->db->get('module_ad_system_buyers');
    echo $this->db->error();
    return $data;
    // try {
    //   $name = array_column($data, 'name');
    //   return array_multisort($name, SORT_DESC, $data);
    // } catch (\Exception $e) {
    //   return $e;
    // }
  }

  public function save($data)
  {
    $this->db->insert('module_ad_system_buyers', $data);
    if ($this->db->error() != '') {
      return false;
    }
    return true;
  }

  /* Updates the buyer data, with any changes from the web UI. */
  public function update($id, $data)
  {
    $this->db->where('id', $id);
    if (!$this->db->update('module_ad_system_buyers', $data)) {
      echo "error ". $this->db->error();
      return false;
    }
    return true;
  }

   // Checks if all values are vaild format for the system.

  public function verify($data)
  {
    $errors = [];
    if (preg_match('/^[A-Za-z ]+/', $data['name']) == false) {
      array_push($errors, "buyer name is has invalid characters! Only A-Z, and spaces is allowed.");
    } else {
      if (strlen($data['name']) > 255) {
        array_push($errors, "buyer name is to long! Max length is 255 characters.");
      }
    }

    if (check_phone_number($data['phone']) == false) {
      array_push($errors, "The phone number is invalid! Numbers must be formated as 800-555-5555.");
    }

    // Because advertiser isn't required if its empty thats ok.
    if (strlen($data['advertiser']) > 0) {
      if (preg_match('/^[A-Za-z0-9 ]+$/', $data['advertiser']) == false) {
        array_push($errors, "Invalid advertiser! Only a-z, spaces, and 0-9 characters allowed in name.");
      } else {
        if (strlen($data['advertiser']) > 255) {
          array_push($errors, "The advertiser name is to long! Max length is 255 characters.");
        }
      }
    }
    // if (preg_match('/^[a-zA-Z0-9]+@[a-zA-Z0-9\.]+[a-zA-Z0-9]+$/', $data['email']) == false) {
    //   array_push($errors, "Invalid email!");
    // } else {
    //   if (strlen($data['email']) > 255) {
    //     array_push($errors, "The email is to long! Max length is 255 characters.");
    //   }
    // }

    if (check_zip_code($data['zip_code'])) {
      array_push($errors, "Invalid zip code!");
    }

    if (check_street_addr($data['street_addr'])) {
      if (strlen($data['street_addr']) > 255) {
          array_push($errors, "The street address is to long! Max length is 255 characters.");
        }
      } else {
        array_push($errors, "The street address is invalid!");
    }

    // switch ($data['country']) {
    //   case 'US':
    //     if (preg_match('/^[0-9][0-9][0-9][0-9][0-9]$/', $data['zip_code']) == false) {
    //       array_push($errors, "Invalid zip code!");
    //     }
    //     if (preg_match('/^\d+\s[A-z]+\s[A-z]+$/', $data['street_addr']) == false) {
    //       array_push($errors, "Invalid street address!");
    //     } else {
    //       if (strlen($data['street_addr']) > 255) {
    //         array_push($errors, "The street address is to long! Max length is 255 characters.");
    //       }
    //     }
    //     break;
    //   case 'CA':
    //     if (preg_match('/^[a-zA-Z]{1}[0-9]{1}[a-zA-Z]{1}[- ]{0,1}[0-9]{1}[a-zA-Z]{1}[0-9]{1}$/', $data['zip_code']) == false) {
    //       array_push($errors, "Invalid zip code!");
    //     }
    //     if (preg_match('/^[A-Za-z]\d[A-Za-z][ -]?\d[A-Za-z]\d$/', $data['street_addr']) == false) {
    //       array_push($errors, "Invalid street address!");
    //     } else {
    //       if (strlen($data['street_addr']) > 255) {
    //         array_push($errors, "The street address is to long! Max length is 255 characters.");
    //       }
    //     }
    //     break;
    //
    //   default:
    //     // code...
    //     break;
    // }

    return $errors;
  }

  public function remove($id)
  {
    $this->db->where('id', $id);
    $this->db->delete('module_ad_system_buyers');
    if ($this->db->error() != '') {
      return false;
    }
    return true;
  }

}


?>
