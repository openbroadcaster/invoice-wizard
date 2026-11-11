<?php

/**
 * Logs model; Used to store Log messages for the module.
 */
class LogsModel extends OBFModel
{

  public function get($sort_col='method', $sort_dir='asc')
  {
    // only the columns the logs page sorts by, and asc or desc, reach the query.
    $allowed_sort = ['method', 'time', 'message'];
    if (!is_string($sort_col) || !in_array($sort_col, $allowed_sort, true)) {
      $sort_col = 'method';
    }
    $sort_dir = (is_string($sort_dir) && strtolower($sort_dir) === 'desc') ? 'desc' : 'asc';
    // time is 12-hour text, which doesn't sort ('11:00am' after '01:00pm'); ids are in the order the rows were written.
    $this->db->orderby($sort_col === 'time' ? 'id' : $sort_col, $sort_dir);
    $data = $this->db->get('module_ad_system_logs');
    echo $this->db->error();
    return $data;
  }

  public function save($method, $message)
  {
    $values = ['method' => $method, 'message' => $message, 'time' => date('Y/m/d h:i:sa')];
    $this->db->insert('module_ad_system_logs', $values);
    if ($this->db->error() != '') {
      echo $this->db->error();
      return false;
    }
    return true;
  }
}

?>
