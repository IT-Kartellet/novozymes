<?php

require_once('../../config.php');
require_once("$CFG->libdir/formslib.php");
require_once("$CFG->libdir/accesslib.php");
require_once('metacourse_form.php');
require_once('lib.php');

require_login();

$id = optional_param('id', 0, PARAM_INT);

$PAGE->set_context(get_system_context());
$PAGE->set_pagelayout('admin');
$URL = '/moodle/blocks/metacourse/edit_metacourse.php';
$PAGE->set_title("View course");
$PAGE->set_heading("Moodle View Custom Course");
$PAGE->set_url($CFG->wwwroot."/blocks/metacourse/view_metacourse.php?id=$id");
$PAGE->navbar->ignore_active();
$PAGE->navbar->add("List courses", new moodle_url('/blocks/metacourse/list_metacourses.php'));
$PAGE->navbar->add("View course", new moodle_url("/blocks/metacourse/view_metacourse.php?id=$id"));
$PAGE->requires->js("/lib/jquery/jquery-1.9.1.min.js");
$PAGE->requires->js("/blocks/metacourse/js/core.js");
$isTeacher = has_capability("moodle/course:create", get_system_context());

echo $OUTPUT->header();
global $DB, $USER;
$metacourse = $DB->get_records_sql("
	SELECT c.id, c.name, c.localname, c.localname_lang, c.purpose, c.target, c.content, c.instructors, c.comment, c.duration, c.duration_unit, c.cancellation, c.coordinator, p.provider, c.contact, c.timemodified 
	FROM {meta_course} c join {meta_providers} p on c.provider = p.id where c.id = :id", array("id"=>$id));
$metacourse = reset($metacourse);

// default id, just see the list of courses
if ($metacourse) {
	
	$table = new html_table();
	$table->id = "view_meta_table";
	$table->width = "100%";
	$table->tablealign = "center";
	$title = null;
	$localTitle = null;
	$localLang = null;

	//TODO: replace with a switch the if-s
	foreach ($metacourse as $key => $course) {
		//if field empty
		if ($course == "") {
			continue;
		}
		if ($key == 'duration_unit') {

			//skip the duration_unit as a separate row, and add it instead in the duration row

			//get the duration from the table data.
			$table_index = count( $table->data ) - 1;
			$unit_index = count($table->data[$table_index]) - 1;
			$unit = $table->data[$table_index][$unit_index];

			$timeunit = "";
			switch ($course) {
				case '1':
					$timeunit = "seconds";
					break;
				case '60':
					$timeunit = "minutes";
					break;
				case '3600':
					$timeunit = "hours";
					break;
				case '86400':
					$timeunit = "days";
					break;
				case '604800':
					$timeunit = "weeks";
					break;	
				default:
					# code...
					break;
			}
			//add the timeunit to the duration strip the s from the end of the timeunit if only one.
			$unit .= ($unit == 1) ? " ".substr($timeunit, 0, -1) : " $timeunit";
			$table->data[$table_index][$unit_index] = $unit;
			continue;
		}

		// if name, put it as a header
		if ($key == 'name') {
			$title = ucfirst($course);
			continue;
		}
		if ($key == 'localname') {
			$localTitle = ucfirst($course);
			continue;
		}
		if ($key == 'localname_lang') {
			$localLang = $course;
			continue;
		}
		if ($key == 'id') continue; //we don't want to display the id
		// get the name and the email instead of his id
		if ($key == 'coordinator') {
			$course = $DB->get_records_sql("SELECT firstname, lastname, email from {user} where id = :id", array("id"=>$course));
			$course = reset($course);
			$course = $course->firstname." ".$course->lastname." &lt;".$course->email."&gt;";
		}
		//format from unix timestamp to human readable
		if ($key == 'timemodified') {
			$course = date("F j, Y, g:i a",$course);
		}

		$table->data[] = array(ucfirst($key), $course);
	}
	// get only teachers' views
	// $nr_of_views = $DB->get_records_sql("SELECT * FROM {log} l where module = :module and url like :url and l.userid not in (SELECT u.id
	// 		FROM {role_assignments} ra, {user} u, {course} c, {context} cxt
	// 		WHERE ra.userid = u.id
	// 		AND ra.contextid = cxt.id
	// 		AND cxt.contextlevel =50
	// 		AND cxt.instanceid = c.id
	// 		AND (roleid = 3))", array("module"=>"metacourse","url"=>"%$id")); 

	// gets all the views
	$nr_of_views = $DB->get_records_sql("SELECT * FROM {log} l where module = :module and url like :url", array("module"=>"metacourse","url"=>"%$id")); 
	$nr_of_views = count($nr_of_views);
	if ($isTeacher) {
		$table->data[] = array("Views", $nr_of_views);
	}

	if (!empty($localTitle) && (current_language() == $localLang)) {
		echo html_writer::tag('h1', $localTitle, array('id' => 'course_header', 'class' => 'main'));
	} else {
		echo html_writer::tag('h1', $title, array('id' => 'course_header', 'class' => 'main'));
	}
	echo html_writer::start_tag('div',array('id' => 'meta_wrapper'));
	echo html_writer::table($table);

	echo html_writer::tag('h1', 'Course dates', array('id' => 'course_header', 'class' => 'main'));
	$datecourses = $DB->get_records_sql("SELECT d.*, c.currency FROM {meta_datecourse} d join {meta_currencies} c on d.currencyid=c.id where metaid = :id", array("id"=>$id));
	
	$date_table = new html_table();
	$date_table->id = "view_date_table";
	$date_table->width = "100%";
	$date_table->tablealign = "center";
	$date_table->head = array('Start date', 'End date', 'Location', 'Language', 'Price', 'Number of seats/Attendees available', 'Number of participants', 'Action');

	foreach ($datecourses as $key => $datecourse) {

		$start = date("j/m/Y - h:i A",$datecourse->startdate);
		$end = date("j/m/Y - h:i A",$datecourse->enddate);

		//replace id with location
		$loc = $DB->get_record('meta_locations', array ('id'=> $datecourse->location), 'location'); 
		$location = $loc->location;

		//replace id with language
		$lang = $DB->get_record('meta_languages', array ('id'=> $datecourse->lang), 'language'); 
		$language =$lang->language;
		$price = str_replace(array(".",","), '', $datecourse->price);
		$price = number_format($price);
		$price .= " " . $datecourse->currency;
		$total_places =$datecourse->total_places;
		$busy_places = $DB->get_records_sql("
			select count(ra.id) as busy_places from {role_assignments} ra 
			join 
			(select co.id as contextid from {course} c 
				join {context} co on c.id=co.instanceid where c.id = :cid) b 
			on b.contextid = ra.contextid 
			where ra.roleid = 5", array("cid"=>$datecourse->courseid));
		$busy_places = reset($busy_places);
		$busy_places = $busy_places->busy_places;

 
		//TODO:send also the dates maybe? for the enrolments?
		$enrolMe = new single_button(new moodle_url('/blocks/metacourse/enrol_into_course.php', array("courseid"=>$datecourse->courseid, "userid"=>$USER->id)), "Enrol me");
		
		//if no more places, disable the button
		if ($busy_places == $total_places) {
			$enrolMe = new single_button(new moodle_url('/blocks/metacourse/enrol_into_course.php', array("courseid"=>$datecourse->courseid, "userid"=>$USER->id, "wait"=>1)), "Add me to waiting list");
			if ($DB->record_exists('meta_waitlist',array('userid'=>$USER->id, 'courseid'=>$datecourse->courseid))) {
				$enrolMe->disabled = true;
			}
		}

		/// if the user is already enrolled disable the button
		$coursecontext = context_course::instance($datecourse->courseid);
		if (is_enrolled($coursecontext, $USER->id,"block/metacourse:iamstudent")) {
			$enrolMe = new single_button(new moodle_url('/blocks/metacourse/enrol_into_course.php', array("courseid"=>$datecourse->courseid, "userid"=>$USER->id, "wait"=>1)), "You are enrolled");
			$enrolMe->disabled = true;
		}

		$action = $OUTPUT->render($enrolMe);
		if ($isTeacher) {
			$date_table->data[] = array($start, $end,$location,$language,$price,$total_places, $OUTPUT->action_link(new moodle_url('/blocks/metacourse/enrolled_users.php', array("id"=>$datecourse->id)),$busy_places), $action);
		} else {
			$date_table->data[] = array($start, $end,$location,$language,$price,$total_places, $busy_places, $action);
		}
	}
	echo html_writer::table($date_table);

	echo html_writer::end_tag('div');

	$tos = $DB->get_records_sql("SELECT * FROM {meta_tos}");
	$tos = reset($tos);

	// the modal window for TOS
	echo "<div id='lean_background'>
			<div id='lean_overlay'>
			<h1>Terms of agreement</h1>
            <div id='tos_content'>$tos->tos</div>
            <div id='cmd'>
            	<input type='checkbox' name='accept'> I accept the terms of agreement <span id='waitingSpan' style='display:none'>and I have acknowledged that I will be enrolled as soon as there is an available seat</span>
            	<input id='accept_enrol' type='button' name='submit' value='Enrol' >
            	<input type='button' name='cancel' value='Cancel' >

            </div>
			<div id='lean_close'></div>
        </div></div>";

}

$log_record = new stdClass();
$log_record->time = time();
$log_record->userid = $USER->id;
$log_record->ip = "0:0:0:0:0:0:0:1";
$log_record->course = 0;
$log_record->module = 'metacourse';
$log_record->cmid = 0;
$log_record->action = 'view';
$log_record->url = "view_metacourse.php?id=$id";
$log_record->info = 1;

try {
	$DB->insert_record('log',$log_record);
} catch (Exception $e) {
	
}


echo $OUTPUT->footer();