<?php

require_once('../../config.php');
require_once("$CFG->libdir/formslib.php");
require_once("$CFG->libdir/accesslib.php");
require_once('metacourse_form.php');
require_once('lib.php');

require_login();

$id = optional_param('id', 0, PARAM_INT);

$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('admin');
$URL = '/moodle/blocks/metacourse/edit_metacourse.php';
$PAGE->set_title(get_string('viewcourse','block_metacourse'));
$PAGE->set_heading("Moodle View Custom Course");
$PAGE->set_url($CFG->wwwroot."/blocks/metacourse/view_metacourse.php?id=$id");
$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string("frontpagecourselist"), new moodle_url('/blocks/metacourse/list_metacourses.php'));
$PAGE->navbar->add(get_string("viewcourse", "block_metacourse"), new moodle_url("/blocks/metacourse/view_metacourse.php?id=$id"));
$PAGE->requires->jquery();
$PAGE->requires->js("/blocks/metacourse/js/core.js");
$isTeacher = has_capability("moodle/course:create", context_system::instance());

echo $OUTPUT->header();
global $DB, $USER;

$titles = [];
$titles['purpose'] = get_string('purpose', 'block_metacourse'); 

$metacourse = $DB->get_records_sql("
	SELECT c.id, c.name, c.localname, c.localname_lang, c.purpose, c.target, c.target_description, c.content, c.instructors, c.comment, c.duration, c.duration_unit, c.cancellation, c.lodging, c.coordinator, c.multiple_dates, p.provider, c.contact, c.timemodified
	FROM {meta_course} c join {meta_providers} p on c.provider = p.id where c.id = :id", array("id"=>$id));
$metacourse = reset($metacourse);

$cancellation = "";

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
			// Remove text if course is set to 0 minutes. 
			$timeunit = ($unit <= 0) ? "" : $timeunit;
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
		if ($key == 'target' && !$isTeacher) continue; //we don't want to display the target group to students
		if ($key == "cancellation") {
			$cancellation = $course;

		}
		// get the name and the email instead of his id
		if ($key == 'coordinator') {
			if ($course != 0) {
				$course = $DB->get_records_sql("SELECT firstname, lastname, email, username from {user} where id = :id", array("id"=>$course));
				$course = reset($course);
				$course = $course->firstname . " " . $course->lastname. " (<a href='mailto:".$course->email."'>".$course->username."</a>)";
			} else {
				$course = "None";
			}
		}

		//format from unix timestamp to human readable
		if ($key == 'timemodified') {
			continue;
		}

		switch ($key) {
			case 'purpose':
				$context = context_system::instance();
				$course = file_rewrite_pluginfile_urls($course, 'pluginfile.php',
				$context->id, 'block_metacourse', 'purpose', $id);
				$key = get_string('purpose','block_metacourse');
				
				break;
			case 'target':
				$key = get_string('target','block_metacourse');
				$custom_categories = json_decode($course);
				$targets = "";
				foreach ($custom_categories as $t_key => $t_val) {
					$meta_cat= $DB->get_record("meta_category", array("id"=>$t_val))->name . "<br>";
					$targets .= $meta_cat;
				}
				$course = $targets;
				break;
			case 'target_description':
				$context = context_system::instance();
                                $course = file_rewrite_pluginfile_urls($course, 'pluginfile.php',
                                $context->id, 'block_metacourse', 'target_description', $id);

				$key = get_string("target_description", "block_metacourse");
				break;
			case 'content':
				$context = context_system::instance();
                                $course = file_rewrite_pluginfile_urls($course, 'pluginfile.php',
                                $context->id, 'block_metacourse', 'content', $id);

				$key = get_string('content','block_metacourse');
				break;
			case 'instructors':
				$key = get_string('instructors','block_metacourse');
				break;
			case 'comment':
				$context = context_system::instance();
                                $course = file_rewrite_pluginfile_urls($course, 'pluginfile.php',
                                $context->id, 'block_metacourse', 'comment', $id);

				$key = get_string('comment','block_metacourse');
				break;	
			case 'duration':
				$key = get_string('duration','block_metacourse');
				break;
			case 'cancellation':
				$context = context_system::instance();
                                $course = file_rewrite_pluginfile_urls($course, 'pluginfile.php',
                                $context->id, 'block_metacourse', 'cancellation', $id);

				$key = get_string('cancellation','block_metacourse');
				$cancellation = $course;
				break;
			case 'lodging':
				$context = context_system::instance();
                                $course = file_rewrite_pluginfile_urls($course, 'pluginfile.php',
                                $context->id, 'block_metacourse', 'lodging', $id);

				$key = get_string('lodging','block_metacourse');
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
			case 'multiple_dates':
				$key = get_string('multipledates','block_metacourse');
				break;
			default:
				break;
		}
		if (!$course || !$key) { continue; }
		$table->data[] = array(ucfirst($key), $course);
	}

	// gets all the views
	if ($isTeacher) {
		$nr_of_views = $DB->get_records_sql("SELECT * FROM {log} l where module = :module and url like :url", array("module"=>"metacourse","url"=>"%$id"));
		$nr_of_views = count($nr_of_views);
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

	// sort datecourses after date
	$ads = usort($datecourses, function($d1, $d2){
		if ($d1->startdate == $d2->startdate) {
			return 0;
		}
		return ($d1->startdate < $d2->startdate) ? -1 : 1;
	});

	$date_table = new html_table();
	$date_table->id = "view_date_table";
	$date_table->width = "100%";
	$date_table->tablealign = "center";

	$date_table->head = array(	get_string('coursestart', "block_metacourse"), 
								get_string('courseend','block_metacourse'), 
								get_string('location'), get_string('language'), 
								get_string("price", "block_metacourse"), 
								get_string("coordinator", "block_metacourse"), 
								get_string("availableseats", "block_metacourse"), 
								get_string("nrparticipants", "block_metacourse"), 
								get_string('signup', 'block_metacourse')
							);
	foreach ($datecourses as $key => $datecourse) {
		if (!$isTeacher) {
			// if not published skip it.
			if ($datecourse->publishdate > time()) {
				continue;
			}
			// you can't be added anymore
			if ($datecourse->startdate < time()) {
				continue;
			}
		}
		if(is_null($datecourse->courseid)){
			continue; // For some reason this datecourse is not assigned to a moodle course
		}
		// get coordinator
		$cor = $DB->get_records_sql("SELECT username FROM {user} where id = :id", array("id"=>$datecourse->coordinator));
		$cor = reset($cor);

		if ($datecourse->startdate == 0) {
			$start = "-";
			$end = "-";
		} else{
			if (!$isTeacher) {
				$start = date("d M Y - h:i A",$datecourse->startdate);
			}else {
				$start = "<a href='/course/view.php?id=$datecourse->courseid'>".date("d M Y - h:i A",$datecourse->startdate)."</a>";
			}
			$end = date("d M Y - h:i A",$datecourse->enddate);
		}

		$start = html_writer::link('/course/view.php?id=' . $datecourse->courseid, $start);

		//replace id with location
		$loc = $DB->get_record('meta_locations', array ('id'=> $datecourse->location), 'location');
		$location = $loc->location;

		//replace id with language
		$lang = $DB->get_record('meta_languages', array ('id'=> $datecourse->lang), 'language');
		$language =$lang->language;
		$price = str_replace(array(".",","), '', $datecourse->price);
		$price = $price;
		$price .= " " . $datecourse->currency;
		@$coordinator = strtoupper($cor->username);
		if (strlen($coordinator)<2) {
			$coordinator = "-";
		}

		$total_places =$datecourse->total_places;
		
		//Get all users enrolled in course. 
		$context = CONTEXT_COURSE::instance($datecourse->courseid);
		
		list($sql, $params) = get_enrolled_sql($context, '', 0, true);
		$sql = "SELECT u.*, je.* FROM {user} u
				JOIN ($sql) je ON je.id = u.id";
		$course_users = $DB->get_records_sql($sql, $params );


		$enrolled_users = array();
		foreach($course_users as $uid => $user){
			if(user_has_role_assignment($uid, 5, $context->id)){
				$enrolled_users[$uid] = $user;
			}elseif(user_has_role_assignment($uid, 3, $context->id)){
				$coordinators[$uid] = $user;
			}
		}
		$busy_places = count($enrolled_users);

		$waiting = $DB->get_record('meta_waitlist', array(
			'courseid' => $datecourse->courseid,
			'userid' => $USER->id,
			'nodates' => 0,
		));

		if ($waiting || array_key_exists($USER->id, $enrolled_users)) {
			// already enrolled
			$enrolMe = new single_button(new moodle_url('/blocks/metacourse/unenrol_from_course.php', array("courseid"=>$datecourse->courseid, "userid"=>$USER->id)), "");
			$enrolMe->class = 'unEnrolMeButton';
			$enrolMe->tooltip = get_string("unenrolmebutton", "block_metacourse");
		} else if ($busy_places >= $total_places) { 
			// waiting list
			$enrolMe = new single_button(new moodle_url('/blocks/metacourse/enrol_into_course.php', array("courseid"=>$datecourse->courseid, "userid"=>$USER->id)), "");
			$enrolMe->class = 'addToWaitingList';
			$enrolMe->tooltip = get_string("addtowaitinglist", "block_metacourse");
		} else {
			// regular enrol
			$enrolMe = new single_button(new moodle_url('/blocks/metacourse/enrol_into_course.php', array("courseid"=>$datecourse->courseid, "userid"=>$USER->id)), "");
			$enrolMe->class = 'enrolMeButton';
			$enrolMe->tooltip = get_string("enrolme", "block_metacourse");
		}

		//Always add enrol others button
 		$enrolOthers = new single_button(new moodle_url('/blocks/metacourse/enrol_others_into_course.php', array("courseid"=>$datecourse->courseid, "userid"=>$USER->id)), "");
 		$enrolOthers->class="enrolOthers";
 		$enrolOthers->tooltip = get_string("enrolOthers", "block_metacourse");

		// check if the enrolment is expired
		if ($datecourse->unpublishdate < time() && $datecourse->unpublishdate != 0) {
			$enrolMe->disabled = true;
			$enrolOthers->disabled = true;
		}

		if ($datecourse->startenrolment > time()) {
			$action = "You can't enrol yet";
		} else {
			$action = $OUTPUT->render($enrolMe);
			$action .= $OUTPUT->render($enrolOthers);
		}
		
		if ($busy_places > 0) {
			$date_table->data[] = array($start, $end,$location,$language,$price,$coordinator,$total_places, $OUTPUT->action_link(new moodle_url('/blocks/metacourse/enrolled_users.php', array("id"=>$datecourse->id)),$busy_places), $action);
			if ($datecourse->remarks) {
				$date_table->data[] = array($datecourse->remarks, "","","","","","","","");
			}
		} else {
			$date_table->data[] = array($start, $end,$location,$language,$price,$coordinator,$total_places, $busy_places, $action);
			if ($datecourse->remarks) {
				$date_table->data[] = array($datecourse->remarks, "","","","","","","","");
			}
		}
	}
	echo html_writer::table($date_table);

	echo html_writer::end_tag('div');

	$tos = $DB->get_records_sql("SELECT * FROM {meta_tos}");
	$tos = reset($tos);

?>
<div id='lean_background'>
	<div id='lean_overlay'>
		<h1><?php echo get_string('tostitle','block_metacourse') ?></h1>
        <div id='tos_content'><?php echo get_string('toscontent','block_metacourse') ?></div>
        <div id='cmd'>
        	<input type='checkbox' id="accept" name='accept'><label for="accept"><?php echo get_string('tosaccept','block_metacourse') ?></label><span id='waitingSpan' style='display:none'><? echo get_string('tosacceptwait','block_metacourse') ?></span>
        	<input id='accept_enrol' type='button' name='submit' value='<?php echo get_string('enrolme','enrol_self') ?>' >
        	<input type='button' name='cancel' value='<?php echo get_string('cancel') ?>' >
        </div>
		<div id='lean_close'></div>
    </div>
</div>

<div id='lean_background_unenrol'>
	<div id='lean_overlay'>
		<h1><?php echo get_string('cancellation','block_metacourse') ?></h1>
        <div id='tos_content'><?php echo $cancellation; ?></div>
        <div id='cmd'>
        	<input type='checkbox' id="accept_unenrol" name='accept_unenrol'><label for="accept_unenrol"><?php echo get_string('agreecancel','block_metacourse') ?></label>
        	<input id='accept_unenrol' type='button' name='submit' 'title'='unenrol' value='<?php echo get_string('unenrolme','block_metacourse') ?>' >
        	<input type='button' name='cancel' value='<?php echo get_string('cancel') ?>' >
        </div>
		<div id='lean_close'></div>
    </div>
</div>
<?php
}

if (!$isTeacher) {
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
}

echo $OUTPUT->footer();
