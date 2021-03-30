<?php

/*
    Copyright 2012-2020 OpenBroadcaster, Inc.
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

    This file includes code from Valums File Uploader. © 2010 Andrew Valums.
    Licensed under GPL v2 or later.
    See license information in extras/valums-file-uploader.
*/

// Checks if string is only 0-9 chars.

function check_num($value) {
  if (! preg_match('/^[0-9]+$/', $value)) {
    return false;
  }
  return true;
}

// Checks string is A-Z, and spaces only.

function check_a_z($value) {
  if (! preg_match('/^[A-Z|a-z| ]+$/', $value)) {
    return false;
  }
  return true;
}

// Checks if text only has A-z, spaces, 0-9, and dashes.

function check_street_addr($value) {
  return check_a_z_0_9_dashes($value);
}

// Checks if text only has A-z, spaces, 0-9, and dashes.

function check_a_z_0_9_dashes($value) {
  if (! preg_match('/^[A-Za-z0-9\– ]+$/', $value)) {
    return false;
  }
  return true;
}

// Checks zip/postal code.

function check_zip_code($value) {
  if (! preg_match('/^[0-9][0-9][0-9][0-9][0-9]$/', $value)) {
    if (! preg_match('/^[A-Za-z0-9]+$/', $value)) {
      if (strlen($value) > 10) {
        return 'The zip / postal code is to long! Must be a vaild zip/postal code';
      }
      return 'The zip / postal code has invaild character(s)! Must be a vaild zip/postal code';
    }
  }
  return '';
}

// Checks if text only has A-z, spaces, 0-9, and dashes.

function check_phone_number($value) {
  if (! preg_match('/^[\d]{0,3}[\-\s]{0,1}[\d]{3}[\-|]{0,1}[\d]{3}[\-|]{0,1}[\d]{4}$/', $value)) {
    return false;
  }
  return true;
}

// Addes dashes to the number if they are missing.

function format_phone_number($value) {
  // checks without the country code.
  if (preg_match('/^([0-9]{3})([0-9]{3})([0-9]{4})$/', $value, $matches)) {
    return $matches[1]. '-'. $matches[2]. '-'. $matches[3];
    // Checks with the country code included.
  } elseif (preg_match('/^([0-9]{0,14})([0-9]{3})([0-9]{3})([0-9]{4})$/', $value, $matches)) {
    return $matches[1]. '-'. $matches[2]. '-'. $matches[3]. '-'. $matches[4];
  } else {
    // Should already have been formated. So no changes are needed.
    return $value;
  }
}

?>
