<?php
require_once('../../config.php');
require_once("$CFG->libdir/enrollib.php");
require_once("$CFG->libdir/moodlelib.php");
require_once('lib.php');

require_login();

global $DB;
$PAGE->set_context(context_system::instance());

$courseid = required_param("courseid", PARAM_INT);
$userid = required_param("userid", PARAM_INT);
$enrol = new enrol_manual_pluginITK();

$context = context_course::instance($courseid);

$instance = $DB->get_records_sql("SELECT * FROM {enrol} where enrol= :enrol and courseid = :courseid and status = 0", array('enrol'=>'manual','courseid'=>$courseid));
$instance = reset($instance);
$user = $DB->get_record("user", array("id"=>$userid), '*', MUST_EXIST);

//check if we still have places
list($sql, $params) = get_enrolled_sql($context, '', 0, true);
$sql = "SELECT u.*, je.* FROM {user} u
		JOIN ($sql) je ON je.id = u.id";
$course_users = $DB->get_records_sql($sql, $params );

$enrolled_users = array();

$busy_places = 0;
foreach($course_users as $id => $course_user){
	if(user_has_role_assignment($id, 5, $context->id)){
		$busy_places++;
	}
}

$total_places = $DB->get_records_sql("SELECT total_places from {meta_datecourse} where courseid = :cid", array("cid"=>$courseid));
$total_places = reset($total_places);
$total_places = $total_places->total_places;

if ($total_places - $busy_places > 0) {

	$current_enrolment = $DB->get_records_sql("
		SELECT u.id as userid, e.id as enrolid FROM {user} u 
		JOIN {user_enrolments} ue ON ue.userid = u.`id`
		JOIN {enrol} e ON ue.enrolid = e.id 
		AND e.courseid = :courseid 
		AND ue.status = 1 
		AND u.id <> 1 
		AND u.deleted = 0 
		AND u.suspended = 0
		AND u.id = :userid", array("courseid"=>$courseid, "userid"=>$userid));

	$current_enrolment = reset($current_enrolment);

	if (!empty($current_enrolment)) {
		$DB->set_field("user_enrolments", "status", 0, array("enrolid"=>$current_enrolment->enrolid, "userid"=>$current_enrolment->userid));
	} else {
		$enrol->enrol_user($instance, $userid, 5);

		$accept = new stdClass();
		$accept->userid = $userid;
		$accept->courseid = $courseid;
		$accept->accepted = 1;
		$accept->timeaccepted = time();

		$DB->insert_record("meta_tos_accept", $accept);
	}

	$wait = false;
} else {
	$waitRecord = new stdClass();
	$waitRecord->userid = $userid;
	$waitRecord->courseid = $courseid;
	$waitRecord->timestart = 0;
	$waitRecord->timeend = 0;
	$waitRecord->timecreated = time();
	$DB->insert_record('meta_waitlist', $waitRecord);

	$wait = true;
}

//Send wait list mail or confirmation. 
if($wait){
	$enrol->send_waitlist_email($user, $courseid);
	redirect(new moodle_url($CFG->wwwroot."/blocks/metacourse/list_metacourses.php"), "You've been signed up for the waitlist", 5);
}else{
	$enrol->send_confirmation_email($user, $courseid);
	redirect(new moodle_url($CFG->wwwroot."/blocks/metacourse/list_metacourses.php"), "You've been enrolled", 5);
}