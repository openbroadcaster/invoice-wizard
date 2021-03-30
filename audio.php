<?php

$filename = null;

if (isset($_GET['filename'])) {
  $filename = $_GET['filename'];
}

if ($filename == null) {
  die('Invaild filename!');
} else {
  $file_path = '/tmp/temp_media/'. $filename;
  header('Content-Type: audio/wav');
  header('Content-Length: '.filesize($file_path));
  //echo $data;
  echo file_get_contents($file_path);
}

?>
