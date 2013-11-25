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

echo $OUTPUT->header();
global $DB, $USER;
$metacourse = $DB->get_records_sql("SELECT * FROM {meta_course} where id = :id", array("id"=>$id));
$metacourse = reset($metacourse);

// default id, just see the list of courses
if ($metacourse) {

	echo html_writer::tag('h1', 'View course', array('id' => 'course_header', 'class' => 'main'));
	echo html_writer::start_tag('div',array('id' => 'meta_wrapper'));
	
	$table = new html_table();
	$table->id = "view_meta_table";
	$table->width = "100%";
	$table->tablealign = "center";
	foreach ($metacourse as $key => $course) {
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
	echo html_writer::table($table);

	echo html_writer::tag('h1', 'Course dates', array('id' => 'course_header', 'class' => 'main'));
	$datecourses = $DB->get_records_sql("SELECT * FROM {meta_datecourse} where metaid = :id", array("id"=>$id));
	
	$date_table = new html_table();
	$date_table->id = "view_date_table";
	$date_table->width = "100%";
	$date_table->tablealign = "center";
	$date_table->head = array('Start date', 'End date', 'Location', 'Language', 'Price', 'Total places', 'Free places', 'Action');

	foreach ($datecourses as $key => $datecourse) {

		$start = date("F j, Y",$datecourse->startdate);
		$end = date("F j, Y",$datecourse->enddate);

		//replace id with location
		$loc = $DB->get_record('meta_locations', array ('id'=> $datecourse->location), 'location'); 
		$location = $loc->location;

		//replace id with language
		$lang = $DB->get_record('meta_languages', array ('id'=> $datecourse->lang), 'language'); 
		$language =$lang->language;

		$price =$datecourse->price;
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

		$free_places = $total_places - $busy_places;

		//TODO:send also the dates maybe? for the enrolments?
		$enrolMe = new single_button(new moodle_url('/blocks/metacourse/enrol_into_course.php', array("courseid"=>$datecourse->courseid, "userid"=>$USER->id)), "Enrol me");
		
		//if no more places, disable the button
		if ($free_places <= 0) {
			$free_places = 0;
			$enrolMe->disabled = true;
		}

		/// if the user is already enrolled disable the button
		$coursecontext = context_course::instance($datecourse->courseid);
		if (is_enrolled($coursecontext, $USER->id)) {
			$enrolMe->disabled = true;
		}


		$action = $OUTPUT->render($enrolMe);

		$date_table->data[] = array($start, $end,$location,$language,$price,$total_places, $free_places, $action);
	}
	echo html_writer::table($date_table);

	echo html_writer::end_tag('div');

	$tos = $DB->get_records_sql("SELECT * FROM {meta_tos}");
	$tos = reset($tos);

	echo "<div id='lean_background'>
			<div id='lean_overlay'>
			<h1>Terms of agreement</h1>
            <div id='tos_content'>$tos->tos</div>
            <div id='cmd'>
            	<input type='checkbox' name='accept'> I accept the terms of agreement
            	<input id='accept_enrol' type='button' name='submit' value='Enrol me' >
            	<input type='button' name='cancel' value='Cancel' >

            </div>
			<div id='lean_close'></div>
        </div></div>";

}
echo $OUTPUT->footer();