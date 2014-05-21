<?php
require_once('../../config.php');
require_once("$CFG->libdir/enrollib.php");
require_once("$CFG->libdir/moodlelib.php");
require_once('lib.php');

require_login();

global $DB;
$PAGE->set_context(context_system::instance());

$courseid = optional_param("courseid", 0,PARAM_INT);
$datecourseid = optional_param("datecourseid",0, PARAM_INT);
$userid = optional_param("userid", 0,PARAM_INT);
$wait = optional_param("wait", 0,PARAM_INT);
$courseid = ($datecourseid != 0) ? $datecourseid : $courseid;
$datecourseid = 0;
$enrol = new enrol_manual_pluginITK();

if (($courseid != 0 && $userid != 0) || ($datecourseid !=0 && $userid != 0)){
	if ($datecourseid != 0) {
		$waitlist = new stdClass();
		$waitlist->userid = $userid;
		$waitlist->courseid = $datecourseid;
		$waitlist->timestart = 0;
		$waitlist->timeend = 0;
		$waitlist->timecreated = time();
		$waitlist->nodates = 1;
	} else {
		$instance = $DB->get_records_sql("SELECT * FROM {enrol} where enrol= :enrol and courseid = :courseid and status = 0", array('enrol'=>'manual','courseid'=>$courseid));
		$instance = reset($instance);
		$user = $DB->get_record("user", array("id"=>$userid));

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
		}
	}
}
$enrol->send_confirmation_email($user, $courseid);
redirect(new moodle_url($CFG->wwwroot."/blocks/metacourse/list_metacourses.php"), "You've been enrolled", 5);