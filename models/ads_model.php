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

  public function get_invoices($sort_col='id', $sort_dir='asc')
  {
    // whitelist the sort column. an invalid value (eg a stale 'name' saved
    // client-side) otherwise fails the entire query and returns nothing.
    $allowed_sort = ['id', 'invoice_id', 'campaign_name', 'advertiser', 'buyer', 'start_date', 'stop_date', 'device_names', 'invoice_number', 'contract_number', 'sales_rep'];
    if (!is_string($sort_col) || !in_array($sort_col, $allowed_sort, true)) {
        $sort_col = 'id';
    }
    $sort_dir = (is_string($sort_dir) && strtolower($sort_dir) === 'desc') ? 'desc' : 'asc';
    $this->db->orderby($sort_col, $sort_dir);
    $data = $this->db->get('module_ad_system_invoices');
    if ($data != null && count($data) > 0) {
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

  // Returns the approved creatives of the campaigns running today, in random order.
  // Core calls slot callbacks without the player or air time, so this can't
  // target stations or the actual air date yet, and ads and PSAs share one list.

  private function running_campaign_media()
  {
    $today = date('Y-m-d');
    $media_ids = [];
    foreach ($this->db->get('module_ad_system_invoices') ?: [] as $campaign) {
      $start = strtotime($campaign['start_date']);
      $stop = strtotime($campaign['stop_date']);
      if ($start === false || $stop === false || !preg_match('/^[0-9]+$/', (string) $campaign['media_file_id'])) {
        continue;
      }
      if (date('Y-m-d', $start) <= $today && $today <= date('Y-m-d', $stop)) {
        $media_ids[] = $campaign['media_file_id'];
      }
    }
    // core plays whatever ids a slot returns, so only keep media that may air.
    $media_ids = array_values(array_filter(array_unique($media_ids), function ($media_id) {
      $this->db->where('id', $media_id);
      $media = $this->db->get_one('media');
      return $media && $media['is_approved'] == 1 && $media['is_archived'] == 0;
    }));
    shuffle($media_ids);

    return $media_ids;
  }

  // Returns a list of ads to be added a playlist slot.

  // NOTE: For now this is the best that can done until Brook/Ruby adds some
  // changes to the core code.

  public function ad_slot_30()
  {
    return $this->running_campaign_media();
  }

  // Returns a list of psas to be added a playlist slot.

  // NOTE: For now this is the best that can done until Brook/Ruby adds some
  // changes to the core code.

  public function psa_slot_30()
  {
    return $this->running_campaign_media();
  }
}


?>
