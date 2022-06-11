<?php

if (isset($_GET['id'])) {
  $invoice_id = $_GET['id'];
} else {
  die('Invaild invoice id!');
}



require('../../../components.php');

$load = OBFLoad::get_instance();
$db = OBFDB::get_instance();
$user = OBFUser::get_instance();

$contenteditable = "true";

// Check user can use the inline tmp feature of edit mode.

if (isset($_GET['mode'])) {
  if ($_GET['mode'] == '1') {
    $contenteditable = "true";
  }
}

$SettingsModel = $load->model('Settings');

$InvoicesModel = $load->model('Ads');

$CompanyModel = $load->model('Company');

$invoice_data = $InvoicesModel->get_invoice($invoice_id);

// $from_company_name = $SettingsModel->get_setting('company_name');
// $from_company_name_addr = $SettingsModel->get_setting('company_street_address');
// $from_company_name_city = $SettingsModel->get_setting('company_city');
// $from_company_state_prov = $SettingsModel->get_setting('company_state_prov');
// $from_company_zip_code = $SettingsModel->get_setting('company_zip_code');
// $from_company_country = $SettingsModel->get_setting('company_country');
// $from_company_phone = $SettingsModel->get_setting('company_phone');
//$from_company_logo = '../temp_media/station_logo.jpeg';

// $from_company_id = $invoice_data['companie_id'];
// $from_company_data = $CompanyModel->get_one($from_company_id);
// print_r($from_company_data);
$from_company_name_addr = $SettingsModel->get_setting('company_street_address');
$from_company_name_city = $SettingsModel->get_setting('company_city');
$from_company_state_prov = $SettingsModel->get_setting('company_state_prov');
$from_company_zip_code = $SettingsModel->get_setting('company_zip_code');
$from_company_country = $SettingsModel->get_setting('company_country');
$from_company_phone = $SettingsModel->get_setting('company_phone');

$station_logo = $SettingsModel->get_setting('station_logo');

if ($invoice_data == null) {
  die('Invaild invoice id!');
}

?>

<?php print_r($invoice_data); ?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="invoice.css">
    <title>Invoice</title>
  </head>
  <body>
    <h1 class="invoice-title">Invoice / Affidavit</h1>
    <div class="invoice">
      <div class="top-area">
        <div class="station-info">
          <pre>
            <?php print($from_company_name. "\n"); ?>
            <?php print($from_company_name_addr. "\n"); ?>
            <?php print($from_company_name_city); ?>, <?php print($from_company_state_prov); ?> <?php print($from_company_zip_code. "\n"); ?>
            <?php print($from_company_country); ?>
          </pre>
        </div>
        <img src="../../../preview.php?id=<?php echo $station_logo; ?>" alt="station logo" class="station-logo">
        <p>We warrant that the broadcast information shown on this invoice was taken
from the program logs</p>
        <div class="top-right">
          <div class="col">
            <div class="item" contenteditable=<?php echo $contenteditable; ?>>
              <label for="">Broadcast Month</label>
              <?php
              // Placeholder until added the DB.
              echo date('F');
              ?>
            </div>
            <div class="item" contenteditable=<?php echo $contenteditable; ?>>
              <label for="">Invoice Date</label>
              <?php print($invoice_data['invoice_date']); ?>
            </div>
            <div class="item" contenteditable=<?php echo $contenteditable; ?>>
              <label for="">Print Date</label>
              <?php echo date('M. j, Y'); ?>
            </div>
          </div>
          <div class="col">
            <div class="item" contenteditable=<?php echo $contenteditable; ?>>
              <label for="">Contract Number</label>
              <?php print($invoice_data['contract_number']) ?>
            </div>
            <div class="item" contenteditable=<?php echo $contenteditable; ?>>
              <label for="">Invoice Number</label>
              <?php print($invoice_data['invoice_number']); ?>
            </div>
            <div class="item" contenteditable=<?php echo $contenteditable; ?>>
              <label for="">Page</label>
              1
            </div>
          </div>
          <div class="item" contenteditable=<?php echo $contenteditable; ?>>
            <label for="">Revenue Type</label>
            <?php print($invoice_data['revenue_type']) ?>
          </div>
          <div class="item" contenteditable=<?php echo $contenteditable; ?>>
            <label for="">Product</label>
            <?php print($invoice_data['campaign_name']); ?>
          </div>
        </div>
        <div class="row">
        <div class="col">
          <div class="item">
            <label for="">BILL TO:</label>
            <div class="bill_to_text">
              <?php print($invoice_data['billing_mailing_address']) ?>
              <br>
              <?php print($invoice_data['billing_mailing_city']) ?> <?php print($invoice_data['billing_mailing_state_prov']) ?>
              <br>
              <?php print($invoice_data['billing_mailing_zip_code']) ?>
            </div>
          </div>
        </div>
        </div>
      </div>
      <div class="col">
        <div class="item">
          <label for="">Stations</label>
          <?php
          $devices = explode(",", $invoice_data['device_names']);
          if (count($devices) > 1) {
            foreach ($devices as $device) {
              print($device. ', ');
            }
          } else {
            print($devices[0]);
          }
          ?>
        </div>
        <div class="item" contenteditable=<?php echo $contenteditable; ?>>
          <label for="">Advertiser</label>
          <?php print($invoice_data['advertiser']); ?>
        </div>
        <div class="item" contenteditable=<?php echo $contenteditable; ?>>
          <label for="">Sales Rep</label>
          <?php echo $invoice_data['sales_rep']; ?>
        </div>
      </div>
      <div class="col">
        <div class="item" contenteditable=<?php echo $contenteditable; ?>>
          <label for="">Creative Name:</label>
          <?php echo $invoice_data['creative'] ?>
        </div>
        <div class="item" contenteditable=<?php echo $contenteditable; ?>>
          <label for="">Ad-ID:</label>
          <?php echo $invoice_data['ad_id'] ?>
        </div>
      </div>
      <table>
        <thead>
          <th>
            Date
          </th>
          <th>Len</th>
          <th>Time</th>
          <th>Qty</th>
          <th>Rate</th>
          <th>Total</th>
        </thead>
        <tbody contenteditable=<?php echo $contenteditable; ?>>
          <td>
            <?php
              $time_slots = explode(',', $invoice_data['time_slots']);
              //print_r($time_slots);
              foreach ($time_slots as $time_slot) {
                if ($time_slot == 'morning') {
                  echo "BREAKFAST 5:00A-10A<br>";
                } elseif ($time_slot == 'drive') {
                  echo "DRIVE 3P-9P M-SUN<br>";
                } elseif ($time_slot == 'midday') {
                  echo "MIDDAY 10A-3P M-SUN<br>";
                } elseif ($time_slot == 'ros') {
                  echo "ROS 5A-12A M-SUN<br>";
                }
              }
            ?>
          </td>
          <!-- <td>
            BREAKFAST 5:00A-10A
            <br>
            M
            <br>
            DRIVE 3P-9P M-SUN
            <br>
            MIDDAY 10A-3P M-SUN
          </td> -->
          <td>
            30
          </td>
          <td>
            05:22:00 AM
          </td>
          <td>
            1
          </td>
          <td>
            $50
          </td>
          <td>
            $50
          </td>
        </tbody>
      </table>
      <div class="bottom-area">
        <div class="item">
          PAYMENT TERMS: IMMEDIDATE
          <br>
          Please remit payment to:
          <br>
          <?php echo $from_company_name; ?>
          <br>
          <br>
          if you have any questions or wish to make payment arrangements, please contact <?php echo $from_company_phone; ?>.
        </div>
        <div class="item" contenteditable=<?php echo $contenteditable; ?>>
          Actual Occasions 1
          <hr>
          Agency commission $10
          <hr>
          Net $10
          <hr>
          HST 000 999 888 888
          <hr>
          Payments/Adjustments $0.00
          <hr>
          Total Due $70
        </div>
      </div>
    </div>
  </body>
</html>
