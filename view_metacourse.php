<?php

require_once('../../config.php');
require_once("$CFG->libdir/formslib.php");
require_once("$CFG->libdir/accesslib.php");
require_once('metacourse_form.php');
require_once('lib.php');

require_login();

$id = required_param('id', PARAM_INT);

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

$metacourse = $DB->get_records_sql("
	SELECT c.id, c.name, c.localname, c.localname_lang, c.purpose, c.target, c.target_description, c.content, c.instructors, c.comment, c.duration, c.duration_unit, if(c.price is null or c.price='', '', concat(c.price, if(cur.currency is null, '', concat(' ', cur.currency)))) as price, c.cancellation, c.lodging, c.coordinator, c.multiple_dates, p.provider, c.contact, c.timemodified, c.nodates_enabled
	FROM {meta_course} c join {meta_providers} p on c.provider = p.id left join {meta_currencies} cur on c.currencyid = cur.id where c.id = :id", array("id"=>$id));
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
	$nodates = 0;
	if ($metacourse->coordinator!==null) $meta_coordinator = $metacourse->coordinator;
	else $meta_coordinator = 0;
	
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
		if ($key == 'nodates_enabled') {
			$nodates = $course;
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
		$nr_of_views = $DB->count_records('meta_views_log', array("metaid"=>$id));
		$table->data[] = array(get_string('nrviews','block_metacourse'), $nr_of_views);
	}
	
	// Read date courses.
	$datecourses = $DB->get_records_sql("SELECT d.*, c.currency FROM {meta_datecourse} d join {meta_currencies} c on d.currencyid=c.id where metaid = :id AND deleted = 0 ORDER BY startdate ASC", array("id"=>$id));
	
	// Sign on to meta course waiting list.
	if ($nodates == 1) {
		$showMetaWaitEnrol = true;
		foreach ($datecourses as $key => $datecourse) {
			if (($datecourse->realunpublishdate == null || $datecourse->realunpublishdate > time()) and
				$datecourse->publishdate < time() and
				($datecourse->elearning || $datecourse->startdate > time()) and
				!is_null($datecourse->courseid) and
				($datecourse->unpublishdate > time() || $datecourse->unpublishdate == 0) and
				$datecourse->startenrolment < time()
				) $showMetaWaitEnrol = false;
		}
		if ($showMetaWaitEnrol) {
			if ($DB->record_exists_sql(
				'SELECT * FROM {meta_waitlist} WHERE courseid = :courseid AND userid = :userid AND nodates = 1',
				array('courseid'=>$id, 'userid'=>$USER->id))) {
				$enrolMe = new single_button(new moodle_url('/blocks/metacourse/unenrol_from_course.php', array("courseid"=>$id, "userid"=>$USER->id, "nodates"=>1)), "");
				$enrolMe->class = 'unEnrolMeButton';
				$enrolMe->tooltip = get_string("unenrolmebutton", "block_metacourse");
			}
			else {
				$enrolMe = new single_button(new moodle_url('/blocks/metacourse/enrol_into_course.php', array("courseid"=>$id, "userid"=>$USER->id, "nodates"=>1)), "");
				$enrolMe->class = 'addToMetaWaitingList';
				$enrolMe->tooltip = get_string("addtowaitinglist", "block_metacourse");
			}
			$row = new html_table_row(array(get_string('signupwait', 'block_metacourse'), text_to_html(get_string('enrol_meta_wait_list_explain', 'block_metacourse')) . $OUTPUT->render($enrolMe)));
			//foreach ($row->cells as $key => $cell) {
			//	$cell->attributes['class'] = 'no_print';
			//}
			$row->attributes['class'] = 'no_print';
			//$table->data[] = array(get_string('signupwait', 'block_metacourse'), text_to_html(get_string('enrol_meta_wait_list_explain', 'block_metacourse')) . $OUTPUT->render($enrolMe));
			$table->data[] = $row;
		}
	}

	if (!empty($localTitle) && (current_language() == $localLang)) {
		echo html_writer::tag('h1', $localTitle, array('id' => 'course_header', 'class' => 'main'));
	} else {
		echo html_writer::tag('h1', $title, array('id' => 'course_header', 'class' => 'main'));
	}
	echo html_writer::start_tag('div',array('id' => 'meta_wrapper'));
	echo html_writer::table($table);

	echo html_writer::tag('h1', get_string('coursedates','block_metacourse'), array('id' => 'course_header', 'class' => 'main'));

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
		
		$isPublished = ($datecourse->realunpublishdate == null || $datecourse->realunpublishdate > time());
		$isCoordinator = ($USER->id == $datecourse->coordinator ||$USER->id == $meta_coordinator || is_siteadmin($USER));
		
		if (!$isPublished) {
			continue;
		}
		
		if (!$isPublished && !$isCoordinator) {
			continue;
		}
		
		if (!$isTeacher) {
			// if not published skip it.
			if ($datecourse->publishdate > time()) {
				continue;
			}
			// you can't be added anymore
			if (!$datecourse->elearning && $datecourse->startdate < time()) {
				continue;
			}
		}
		if(is_null($datecourse->courseid)){
			continue; // For some reason this datecourse is not assigned to a moodle course
		}
		
		// get coordinator
		$cor = $DB->get_records_sql("SELECT username FROM {user} where id = :id", array("id"=>$datecourse->coordinator));
		$cor = reset($cor);
		
		// get user relations to course
		list($enrolled_users, $not_enrolled_users, $waiting_users) = get_datecourse_users($datecourse->courseid, false);

		$row = array();
		
		if (!empty($datecourse->elearning) && (is_user_enrolled($USER->id, $datecourse->courseid) || $isTeacher)) {
			if (array_key_exists($USER->id, $waiting_users) || array_key_exists($USER->id, $enrolled_users))
				$cell = new html_table_cell(html_writer::link(new moodle_url('/course/view.php', array('id' => $datecourse->courseid)), get_string('goto_course', 'block_metacourse')));
			else
				$cell = new html_table_cell(html_writer::link(new moodle_url('/blocks/metacourse/enrol_into_course.php', array("courseid"=>$datecourse->courseid, "userid"=>$USER->id, "redirect"=>"elearn")), get_string('goto_course', 'block_metacourse')));
			$cell->colspan = 2;
			$row[] = $cell;
		} else {
			if ($datecourse->startdate == 0) {
				$start = "-";
				$end = "-";
			} else{
				$start = format_date_with_tz($datecourse->startdate, $datecourse->timezone);
				if ($isTeacher) {
					$start = "<a href='/course/view.php?id=$datecourse->courseid'>".$start."</a>";
				}

				$end = format_date_with_tz($datecourse->enddate, $datecourse->timezone);
			}
			$row[] = $start;
			$row[] = $end;
		}

		if ($datecourse->elearning) {
			$location = 'Online';
		} else {
			//replace id with location
			$loc = $DB->get_record('meta_locations', array ('id'=> $datecourse->location), 'location');
			$location = $loc->location;
		}
		$row[] = $location;

		//replace id with language
		$lang = $DB->get_record('meta_languages', array ('id'=> $datecourse->lang), 'language');
		$row[] = $lang->language;

		if ($datecourse->elearning) {
			$row[] = '-';
		} else {
			$price = str_replace(array(".",","), '', $datecourse->price);
			$row[] = $price .  " " . $datecourse->currency;
		}
		@$coordinator = strtoupper($cor->username);
		if (strlen($coordinator)<2) {
			$coordinator = "-";
		}
		$row[] = $coordinator;
		$total_places =$datecourse->total_places;

		$busy_places = count($enrolled_users);

		if (array_key_exists($USER->id, $waiting_users) || array_key_exists($USER->id, $enrolled_users)) {
			// already enrolled
			$enrolMe = new single_button(new moodle_url('/blocks/metacourse/unenrol_from_course.php', array("courseid"=>$datecourse->courseid, "userid"=>$USER->id)), "");
			$enrolMe->class = 'unEnrolMeButton';
			$enrolMe->tooltip = get_string("unenrolmebutton", "block_metacourse");
		} else if (!$datecourse->elearning && ($busy_places >= $total_places || count($waiting_users)>0)) {
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

		if ($datecourse->elearning) {
			$enrolMe->class .= ' elearning';
		}

		//Always add enrol others button
		$enrolOthers = new single_button(new moodle_url('/blocks/metacourse/enrol_others_into_course.php', array("courseid"=>$datecourse->courseid, "userid"=>$USER->id)), "");
		$enrolOthers->class="enrolOthers";

		//Set one tooltip if waiting list and another without waiting list.
		if(!$datecourse->elearning && ($busy_places >= $total_places || count($waiting_users)>0)){
			$enrolOthers->tooltip = get_string("enrolOthers-wait", "block_metacourse");
		}else{
			$enrolOthers->tooltip = get_string("enrolOthers", "block_metacourse");
		}

		// check if the enrolment is expired
		if (!$isPublished || ($datecourse->unpublishdate < time() && $datecourse->unpublishdate != 0)) {
			$enrolMe->disabled = true;
			$enrolOthers->disabled = true;
		}

		if ($datecourse->startenrolment > time()) {
			$action = "You can't enrol yet";
		} else {
			$action = $OUTPUT->render($enrolMe);
			$action .= $OUTPUT->render($enrolOthers);
		}

		if ($datecourse->elearning) {
			$total_places = get_string('elearning_course', 'block_metacourse');
		}
		if ($busy_places > 0) {
			$busy_places = $OUTPUT->action_link(new moodle_url('/blocks/metacourse/enrolled_users.php', array("id"=>$datecourse->id)),$busy_places);
		} else {
			$busy_places = 0;
		}
		$row[] = $total_places;
		$row[] = $busy_places;

		$row[] = $action;

		$date_table->data[] = $row;

		if ($datecourse->remarks) {
			$date_table->data[] = array($datecourse->remarks, "","","","","","","","");
		}
	}
	echo html_writer::table($date_table);
	
	//echo html_writer::tag('a', get_string('coursedates','block_metacourse'), array('id' => 'course_header', 'class' => 'main'));
	echo html_writer::tag('a', '<img title="'.get_string('print','block_metacourse').'" src="'.$CFG->wwwroot.'/blocks/metacourse/pix/print.png"> '.get_string('print','block_metacourse'), array('class' => 'metacourse_print', 'href' => 'javascript: window.print()'));

	echo html_writer::end_tag('div');

	$tos = $DB->get_records_sql("SELECT * FROM {meta_tos}");
	$tos = reset($tos);

?>
<div id='lean_background'>
	<div id='lean_overlay'>
		<h1><?php echo get_string('tostitle','block_metacourse') ?></h1>
        <div id='tos_content'><?php echo text_to_html(get_string('toscontent','block_metacourse')) ?></div>
        <div id='cmd'>
        	<input type='checkbox' id="accept" name='accept'><label for="accept"><?php echo get_string('tosaccept','block_metacourse') ?></label>
        	<input id='accept_enrol' type='button' name='submit' value='<?php echo get_string('enrolme','block_metacourse') ?>' >
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
        	<input type='checkbox' id='chck_unenroll' name='accept_unenrol'><label for="chck_unenroll"><?php echo get_string('agreecancel','block_metacourse') ?></label>
        	<input id='accept_unenrol' type='button' name='submit' 'title'='unenrol' value='<?php echo get_string('unenrolme','block_metacourse') ?>' >
        	<input type='button' name='cancel' value='<?php echo get_string('cancel') ?>' >
        </div>
		<div id='lean_close'></div>
    </div>
</div>

<div id='lean_background_waiting'>
	<div id='lean_overlay'>
		<h1><?php echo get_string('enrol_waitinglist_title','block_metacourse') ?></h1>
        <div id='tos_content'><?php echo text_to_html(get_string('enrol_waitinglist_contents','block_metacourse')) ?></div>
        <div id='cmd'>
        	<input type='checkbox' id="accept" name='accept'><label for="accept"><?php echo get_string('enrol_waitinglist_tos','block_metacourse') ?></label>
        	<input id='accept_enrol' type='button' name='submit' value='<?php echo get_string('enrolme','block_metacourse') ?>' >
        	<input type='button' name='cancel' value='<?php echo get_string('cancel') ?>' >
        </div>
		<div id='lean_close'></div>
    </div>
</div>

<div id='lean_background_meta_waiting'>
	<div id='lean_overlay'>
		<h1><?php echo get_string('enrol_meta_waitinglist_title','block_metacourse') ?></h1>
        <div id='tos_content'><?php echo text_to_html(get_string('enrol_meta_waitinglist_contents','block_metacourse')) ?></div>
        <div id='cmd'>
        	<input type='checkbox' id="accept" name='accept'><label for="accept"><?php echo get_string('enrol_meta_waitinglist_tos','block_metacourse') ?></label>
        	<input id='accept_enrol' type='button' name='submit' value='<?php echo get_string('enrolme','block_metacourse') ?>' >
        	<input type='button' name='cancel' value='<?php echo get_string('cancel') ?>' >
        </div>
		<div id='lean_close'></div>
    </div>
</div>
<?php
}

if (!$isTeacher) {
	// Add to the regular moodle log so we can easily monitor meta course views.
	add_to_log(0, 'metacourse', 'view', "view_metacourse.php?id=$id");
	
	// And add a record to our own metacourse view log, to optimize performance when counting the number of views
	$log_record = new stdClass();
	$log_record->timestamp = time();
	$log_record->user = $USER->id;
	$log_record->metaid = $id;
	try {
		$DB->insert_record('meta_views_log', $log_record);
	} catch (Exception $e) { }
}

echo $OUTPUT->footer();
