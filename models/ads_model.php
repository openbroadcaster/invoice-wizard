<?php

/**
 * Ads model; Used to store ad/psas for air.
 */
class AdsModel extends OBFModel
{

  public function add_new_ad($media_data, $invoice_data)
  {
    $this->db->insert('module_ad_system_messages', $media_data);
    echo $this->db->error();
    $this->db->insert('module_ad_system_invoices', $invoice_data);
    echo $this->db->error();
  }

  public function get_invoices($sort_col='name', $sort_dir='asc')
  {
    $this->db->orderby($sort_col, $sort_dir);
    $data = $this->db->get('module_ad_system_invoices');
    if (count($data) > 0) {
      return $data;
    } else {
      return false;
    }
  }

  /* gets invoice data, and returns it. */

  public function get_invoice($invoice_id)
  {
    $this->db->where('invoice_id', $invoice_id);
    $invoice = $this->db->get_one('module_ad_system_invoices');
    if ($invoice) {
      return $invoice;
    }

    return null;
  }

  // Returns a list of ads to be added a playlist slot.

  // NOTE: For now this is the best that can done until Brook/Ruby adds some
  // changes to the core code.

  public function ad_slot_30()
  {
    $output = [];
    $ads = shuffle($this->db->get('module_ad_system_invoices'));
    foreach ($ads as $ad) {
      array_push($output, $ad['media_file_id']);
    }

    return $output;
  }

  // Returns a list of psas to be added a playlist slot.

  // NOTE: For now this is the best that can done until Brook/Ruby adds some
  // changes to the core code.

  public function psa_slot_30()
  {
    $output = [];
    $psas = shuffle($this->db->get('module_ad_system_invoices'));
    foreach ($psas as $psa) {
      array_push($output, $psa['media_file_id']);
    }

    return $output;
  }
}


?>
