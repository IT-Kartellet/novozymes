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
$wait = optional_param("wait", 0,PARAM_INT);

if ($courseid != 0 && $userid != 0) {
	$instance = $DB->get_records_sql("SELECT * FROM {enrol} where enrol= :enrol and courseid = :courseid and status = 0", array('enrol'=>'manual','courseid'=>$courseid));
	$instance = reset($instance);
	$user = $DB->get_record("user", array("id"=>$userid));
	

	// $enrol = new enrol_manual_pluginITK();
	// print_r(array("courseid"=>$courseid,"userid"=>$userid));
	// exit();

	//check if we still have places
	$busy_places = $DB->get_records_sql("
			select count(ra.id) as busy_places from {role_assignments} ra 
			join 
			(select co.id as contextid from {course} c 
				join {context} co on c.id=co.instanceid where c.id = :cid) b 
			on b.contextid = ra.contextid 
			where ra.roleid = 5", array("cid"=>$courseid));
	$busy_places = reset($busy_places);
	$busy_places = $busy_places->busy_places;

	$total_places = $DB->get_records_sql("SELECT total_places from {meta_datecourse} where courseid = :cid", array("cid"=>$courseid));
	$total_places = reset($total_places);
	$total_places = $total_places->total_places;

	if ($wait) {
		$waitRecord = new stdClass();
		$waitRecord->userid = $userid;
		$waitRecord->courseid = $courseid;
		$waitRecord->timestart = 0;
		$waitRecord->timeend = 0;
		$waitRecord->timecreated = time();
		$DB->insert_record('meta_waitlist', $waitRecord);
	} elseif ($total_places - $busy_places > 0) {
		$enrol = new enrol_manual_pluginITK();

		//TODO: check if we need the dates also
		$enrol->enrol_user($instance, $userid, 5);
		$enrol->send_confirmation_email($user, $courseid);

		$accept = new stdClass();
		$accept->userid = $userid;
		$accept->courseid = $courseid;
		$accept->accepted = 1;
		$accept->timeaccepted = time();

		$DB->insert_record("meta_tos_accept", $accept);
	}
	
}

header("Location: " . $CFG->wwwroot."/" );

