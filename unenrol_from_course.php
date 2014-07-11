<?php

require_once('../../config.php');
require_once("$CFG->libdir/enrollib.php");
require_once("$CFG->libdir/moodlelib.php");
require_once('lib.php');

require_login();

global $DB;
$PAGE->set_context(context_system::instance());

$courseid = optional_param("courseid", 0,PARAM_INT);
$userid = optional_param("userid", 0,PARAM_INT);

if ($courseid != 0 && $userid != 0) {
	$instance = $DB->get_records_sql("SELECT * FROM {enrol} where enrol= :enrol and courseid = :courseid and status = 0", array('enrol'=>'manual','courseid'=>$courseid));
	$instance = reset($instance);
	$user = $DB->get_record("user", array("id"=>$userid));
 
 	//check if on waiting list
 	//delete from waiting list
	$DB->delete_records('meta_waitlist', array(
		'courseid' => $courseid,
		'userid' => $userid,
		'nodates' => 0,
	));

 	//disable enrolment
	$enrol = new enrol_manual_pluginITK();
	$enrol->unenrol_user($instance, $user->id);
	$enrol->sendUnenrolMail($userid, $courseid);
}
redirect(new moodle_url($CFG->wwwroot."/blocks/metacourse/list_metacourses.php"), "You've been unenrolled", 5);