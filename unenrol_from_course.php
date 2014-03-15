<?php

require_once('../../config.php');
require_once("$CFG->libdir/enrollib.php");
require_once("$CFG->libdir/moodlelib.php");
require_once('lib.php');

require_login();

global $DB;
$PAGE->set_context(get_system_context());

$courseid = optional_param("courseid", 0,PARAM_INT);
$userid = optional_param("userid", 0,PARAM_INT);



if ($courseid != 0 && $userid != 0) {
	$instance = $DB->get_records_sql("SELECT * FROM {enrol} where enrol= :enrol and courseid = :courseid and status = 0", array('enrol'=>'manual','courseid'=>$courseid));
	$instance = reset($instance);
	$user = $DB->get_record("user", array("id"=>$userid));

 
// 	//check if on waiting list
// 	//delete from waiting list
	//TODO

// 	//disable enrolment
	$DB->set_field("user_enrolments", "status", 1, array("enrolid"=>$instance->id, "userid"=>$userid));


	}
	
header("Location: " . $CFG->wwwroot."/" );

