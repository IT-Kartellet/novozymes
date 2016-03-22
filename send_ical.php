<?php

define('CLI_SCRIPT', true);
require('../../config.php');


require('./lib.php');

$enrol = new enrol_manual_pluginITK();

$user = $DB->get_record('user', array(
  'id' => 2
));
$user->email = 'computerfreak_jan@hotmail.com';
$user->firstname = 'Jan';
$user->lastname = 'Meier';
$enrol->send_confirmation_email($user, 51);