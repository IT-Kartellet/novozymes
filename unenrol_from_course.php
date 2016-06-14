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
$nodates = optional_param("nodates", 0, PARAM_INT);

if ($courseid != 0 && $userid != 0) {
	$user = $DB->get_record("user", array("id"=>$userid));
	
	if ($nodates==0) {
		$instance = $DB->get_records_sql("SELECT * FROM {enrol} where enrol= :enrol and courseid = :courseid and status = 0", array('enrol'=>'manual','courseid'=>$courseid));
		$instance = reset($instance);
		
		//check if on waiting list
		$waiting = $DB->record_exists('meta_waitlist', array(
			'courseid' => $courseid,
			'userid' => $userid,
			'nodates' => 0,
		));
	}
	else $waiting = true;

 	//delete from waiting list
	$DB->delete_records('meta_waitlist', array(
		'courseid' => $courseid,
		'userid' => $userid,
		'nodates' => $nodates
	));

 	//disable enrolment
	$enrol = new enrol_manual_pluginITK();
	if ($nodates==0) {
		$enrol->unenrol_user($instance, $user->id);
		$enrol->sendUnenrolMail($userid, $courseid, $waiting);

		add_to_log($courseid, 'block_metacourse', 'remove enrolment', 'blocks/metacourse/unenrol_from_course.php', "Unenrolled $userid from $courseid");
	}
	else {
		$enrol->sendUnenrolMail($userid, -$courseid, $waiting);
		add_to_log(SITEID, 'block_metacourse', 'remove enrolment', 'blocks/metacourse/unenrol_from_course.php', "Unenrolled $userid from meta course $courseid");
	}
}

if ($nodates==0) {
	$datecourse = $DB->get_record('meta_datecourse', array(
		'courseid' => $courseid
	), '*', MUST_EXIST);
	redirect(new moodle_url($CFG->wwwroot."/blocks/metacourse/view_metacourse.php", array('id' => $datecourse->metaid)), "Your signup has been removed", 5);
}
else {
	redirect(new moodle_url($CFG->wwwroot."/blocks/metacourse/view_metacourse.php", array('id' => $courseid)), "Your signup has been removed", 5);
}
