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
$PAGE->set_title(get_string('viewcourse','block_metacourse'));
$PAGE->set_heading("Moodle View Custom Course");
$PAGE->set_url($CFG->wwwroot."/blocks/metacourse/view_metacourse.php?id=$id");
$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string("frontpagecourselist"), new moodle_url('/blocks/metacourse/list_metacourses.php'));
$PAGE->navbar->add(get_string("viewcourse", "block_metacourse"), new moodle_url("/blocks/metacourse/view_metacourse.php?id=$id"));
$PAGE->requires->js("/lib/jquery/jquery-1.9.1.min.js");
$PAGE->requires->js("/blocks/metacourse/js/core.js");
$isTeacher = has_capability("moodle/course:create", get_system_context());

echo $OUTPUT->header();
global $DB, $USER;

$titles = [];
$titles['purpose'] = get_string('purpose', 'block_metacourse'); 

$metacourse = $DB->get_records_sql("
	SELECT c.id, c.name, c.localname, c.localname_lang, c.purpose, c.target, c.target_description, c.content, c.instructors, c.comment, c.duration, c.duration_unit, c.cancellation, c.coordinator, p.provider, c.contact, c.timemodified
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
			$course = substr($course->firstname, 0, 1) .".".substr($course->lastname, 0, 1). ". &lt;<a href='mailto:".$course->email."'>".$course->email."</a>&gt;";
		}

		//format from unix timestamp to human readable
		if ($key == 'timemodified') {
			continue;
		}

		switch ($key) {
			case 'purpose':
				$key = get_string('purpose','block_metacourse');
				break;
			case 'target':
				$key = get_string('target','block_metacourse');
				$course = $DB->get_record("meta_category", array("id"=>$course))->name;
				break;
			case 'target_description':
				$key = get_string("target_description", "block_metacourse");
				break;
			case 'content':
				$key = get_string('content','block_metacourse');
				break;
			case 'instructors':
				$key = get_string('instructors','block_metacourse');
				break;
			case 'comment':
				$key = get_string('comment','block_metacourse');
				break;	
			case 'duration':
				$key = get_string('duration','block_metacourse');
				break;
			case 'cancellation':
				$key = get_string('cancellation','block_metacourse');
				break;
			case 'coordinator':
				$key = get_string('coordinator','block_metacourse');
				break;
			case 'provider':
				$key = get_string('provider','block_metacourse');
				break;
			case 'contact':
				$key = get_string('contact','block_metacourse');
				break;
			default:
				break;
		}

		$table->data[] = array(ucfirst($key), $course);
	}

	// gets all the views
	$nr_of_views = $DB->get_records_sql("SELECT * FROM {log} l where module = :module and url like :url", array("module"=>"metacourse","url"=>"%$id"));
	$nr_of_views = count($nr_of_views);
	if ($isTeacher) {
		$table->data[] = array(get_string('nrviews','block_metacourse'), $nr_of_views);
	}

	if (!empty($localTitle) && (current_language() == $localLang)) {
		echo html_writer::tag('h1', $localTitle, array('id' => 'course_header', 'class' => 'main'));
	} else {
		echo html_writer::tag('h1', $title, array('id' => 'course_header', 'class' => 'main'));
	}
	echo html_writer::start_tag('div',array('id' => 'meta_wrapper'));
	echo html_writer::table($table);

	echo html_writer::tag('h1', get_string('coursedates','block_metacourse'), array('id' => 'course_header', 'class' => 'main'));
	$datecourses = $DB->get_records_sql("SELECT d.*, c.currency FROM {meta_datecourse} d join {meta_currencies} c on d.currencyid=c.id where metaid = :id", array("id"=>$id));

	$date_table = new html_table();
	$date_table->id = "view_date_table";
	$date_table->width = "100%";
	$date_table->tablealign = "center";

	$date_table->head = array(	get_string('coursestart'), 
								get_string('courseend','block_metacourse'), 
								get_string('location'), get_string('language'), 
								get_string("price", "block_metacourse"), 
								get_string("availableseats", "block_metacourse"), 
								get_string("nrparticipants", "block_metacourse"), 
								get_string('action')
							);

	foreach ($datecourses as $key => $datecourse) {
		if (!$isTeacher) {
			if ($datecourse->publishdate > time()) {
				continue;
			}
		}

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

 		$enrolMe = new single_button(new moodle_url('/blocks/metacourse/enrol_into_course.php', array("courseid"=>$datecourse->courseid, "userid"=>$USER->id)), get_string('enrolme','enrol_self'));

		//if no more places, disable the button
		if ($busy_places == $total_places) {
			$enrolMe = new single_button(new moodle_url('/blocks/metacourse/enrol_into_course.php', array("courseid"=>$datecourse->courseid, "userid"=>$USER->id, "wait"=>1)), get_string('addtowaitinglist','block_metacourse'));
			if ($DB->record_exists('meta_waitlist',array('userid'=>$USER->id, 'courseid'=>$datecourse->courseid))) {
				$enrolMe->disabled = true;
			}
		}

		/// if the user is already enrolled disable the button
		$coursecontext = context_course::instance($datecourse->courseid);
		$course_students = get_role_users(5, $coursecontext);
		$course_students = array_map(function($arg){
			return $arg->username;
		}, $course_students);

		if (in_array($USER->username, $course_students)) {
			$enrolMe = new single_button(new moodle_url('/blocks/metacourse/enrol_into_course.php', array("courseid"=>$datecourse->courseid, "userid"=>$USER->id, "wait"=>1)), get_string("youareenrolled",'block_metacourse'));
			$enrolMe->disabled = true;
		}
		$enrolMe->class = 'enrolMeButton';

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

?>
<div id='lean_background'>
	<div id='lean_overlay'>
		<h1><? echo get_string('tostitle','block_metacourse') ?></h1>
        <div id='tos_content'><? echo get_string('toscontent','block_metacourse') ?></div>
        <div id='cmd'>
        	<input type='checkbox' name='accept'> <? echo get_string('tosaccept','block_metacourse') ?> <span id='waitingSpan' style='display:none'><? echo get_string('tosacceptwait','block_metacourse') ?></span>
        	<input id='accept_enrol' type='button' name='submit' value='<? echo get_string('enrolme','enrol_self') ?>' >
        	<input type='button' name='cancel' value='<? echo get_string('cancel') ?>' >
        </div>
		<div id='lean_close'></div>
    </div>
</div>
	
<?php

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