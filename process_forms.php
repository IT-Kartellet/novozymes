<?php
require_once('../../config.php');
require_once("$CFG->libdir/formslib.php");
require_once("$CFG->libdir/moodlelib.php");
require_once("$CFG->libdir/adminlib.php");
require_once("$CFG->libdir/modinfolib.php");
require_once('datecourse_form.php');
require_once('lib.php');

$context = context_system::instance();
require_login();
require_capability('moodle/course:create', $context);
$PAGE->set_context($context);

$prev_form = new datecourse_form("process_forms.php");
if ($prev_form->is_cancelled()) {
  	redirect('/blocks/metacourse/list_metacourses.php', 'Your action was canceled!');
}

//Get all info about meta course.
$meta = optional_param('meta',"",PARAM_RAW);
$meta = unserialize($meta);

$metaid = $meta['meta_id'];
$name = $meta['meta_name'];
$localname = $meta['meta_localname'];
$localname_lang = $meta['meta_localname_lang'];
$purpose = $meta['meta_purpose'];
$target = $meta['meta_target'];
$targetgroup = $meta['meta_targetgroup'];
$target_description = $meta['meta_target_description'];
$content = $meta['meta_content'];
$instructors = $meta['meta_instructors'];
$comment = $meta['meta_comment'];
$duration = $meta['meta_duration'];
$cancellation = $meta['meta_cancellation'];
$lodging = $meta['meta_lodging'];
$contact = $meta['meta_contact'];
$multiple_dates = $meta['meta_multiple_dates'];
$coordinator = $meta['meta_coordinator'];
$provider = $meta['meta_provider'];
$competence = $meta['meta_competence'];

if ($meta['customemail']) {
	$custom_emails = $meta['custom_email'];
} else {
	$custom_emails = false;
}

//TODO: find a smarter and sober method to transform these dates into unix timestamp
$unpublish_meta = $meta['meta_unpublishdate'];

$unpublish_meta_time = array("day"=>$unpublish_meta['day'],
								"month"=>$unpublish_meta['month'],
								"year"=>$unpublish_meta['year'],
								"hour"=>$unpublish_meta['hour'],
								"minute"=>$unpublish_meta['minute']);
$umt = implode("-",array($unpublish_meta_time['year'], $unpublish_meta_time['month'], $unpublish_meta_time['day']));
$umt .= " " . $unpublish_meta_time['hour'] . ":" . $unpublish_meta_time['minute'] . ":00";

global $DB, $USER;

$datecourses = @$_POST['datecourse'];

$meta = new stdClass();
$meta->id = $metaid;
$meta->name = $name;
$meta->localname = $localname;
$iso_lang = $DB->get_record("meta_languages",array('id'=>$localname_lang));
$meta->localname_lang = $iso_lang->iso;

$meta->purpose = $purpose['text'];
$meta->target = json_encode(array_keys(array_filter($targetgroup)));
$meta->target_description = $target_description['text'];
$meta->content = $content['text'];
$meta->instructors = $instructors;
$meta->comment = $comment['text'];
$meta->duration = ($duration['number']) ? $duration['number'] : 0;
$meta->duration_unit = $duration['timeunit'];
$meta->cancellation = $cancellation['text'];
$meta->lodging = $lodging['text'];
$meta->contact = $contact['text'];
$meta->multiple_dates = $multiple_dates['text'];
$meta->coordinator = $coordinator;
$meta->provider = $provider;
$meta->unpublishdate = date_timestamp_get(date_create($umt));
$meta->timemodified = time();

//if we are editing
if ($metaid) {
	$DB->update_record('meta_course', $meta);
} else {
	$metaid = $DB->insert_record('meta_course', $meta);
	$meta->id = $metaid;
}

if ($custom_emails) {
	foreach ($custom_emails as $lang => $email) {
		$record = $DB->get_record('meta_custom_emails', array(
			'metaid' => $metaid,
			'lang' => $lang
		));

		$text = html_to_text($email['text']);
		if ($record) {
			$record->text = $text;
			$DB->update_record('meta_custom_emails', $record);
		} else {
			$em = new stdClass();
			$em->metaid = $metaid;
			$em->lang = $lang;
			$em->text = $text;
			$DB->insert_record("meta_custom_emails", $em);
		}
	}
} else {
	$DB->delete_records('meta_custom_emails', array(
		'metaid' => $metaid
	));
}


// Save draft area files from all input
$changed = false;
foreach (array(
	'purpose',
	'target_description',
	'content',
	'comment',
	'cancellation',
	'lodging'
) as $input) {
	$field = $$input;
	$text = $field['text'];

	$matches = array();
	// Only if new files were uploaded, that need to be moved from the draft area
	if (preg_match('/\/user\/draft\/([0-9]*)\//', $text, $matches)) {
		$changed = true;
		$draftid = $matches[1];
		$meta->{$input} = file_save_draft_area_files(
		        $draftid, 
        		$context->id, 
		        'block_metacourse',
        		$input, 
	       		$metaid, 
		        array('subdirs'=>true), 
        		$text
		);
	}
}

// Some of the texts were changed because we moved files from draft to permanent storage
if ($changed) {
	$DB->update_record('meta_course', $meta);
}

$count_deleted = array_reduce($datecourses, function ($acc, $datecourse) {
	if (@$datecourse['deleted'] == 1) {
		$acc++;
	} 
	return $acc;
}, 0);

if ($count_deleted === count($datecourses)) {
	// Print an error message and die here
	print_error('deleted_all_courses_error', 'block_metacourse', $CFG->wwwroot . "blocks/metacourse/add_metacourse.php?id=$metaid");
}

function get_unixtime($course, $key) {
	$time = "{$course[$key]['year']}-{$course[$key]['month']}-{$course[$key]['day']} {$course[$key]['hour']}:{$course[$key]['minute']}{$course['timezone']}";

	return (new DateTime($time))->getTimestamp();
}

foreach ($datecourses as $key => $course) {
	// Delete a datecourse, which is the same as a Moodle-course. 
	$dc = new stdClass();
	if (@$course['deleted'] == 1 && $course['courseid'] != 0) {
		delete_course($course['courseid'], false);
		continue;
	}

	//if we are editing
	if (@$course['courseid']) {
		$dc->courseid = $course['courseid'];
	}
	
	//if we are editing
	if (@$course['id']) {
		$dc->id = $course['id'];
	}
	$dc->metaid = $metaid;

	if (!empty($course['elearning'])) {
		$starttime = 0;
		$endtime = null;
		$places = null;
		$location = null;
		$price = null;
	} else {
		$starttime = get_unixtime($course, 'timestart');
		$endtime = get_unixtime($course, 'timeend');
		$places = $course['places'];
		$location = $course['location'];
		$price = $course['price'];
	}

	$dc->startdate = $starttime;
	$dc->enddate = $endtime;
	$dc->publishdate = get_unixtime($course, 'publishdate');
	$dc->unpublishdate = get_unixtime($course, 'unpublishdate');
	$dc->startenrolment = get_unixtime($course, 'startenrolment');
	$dc->timezone = $course['timezone'];
	$dc->location = $location;
	$dc->country = $course['country'];
	$dc->lang = $course['language'];
	$dc->category = $competence;
	$dc->price = $price;
	$dc->currencyid = $course['currency'];
	$dc->total_places = $places;
	$dc->elearning = !empty($course['elearning']) ? 1 : 0;
	// only if have a new course we add the free seats
	if (@!$dc->id) {
		$dc->free_places = $places;
	} else {
		// update the nr of free places
		$places = $DB->get_records_sql("SELECT total_places, free_places from {meta_datecourse} where id=:id",array("id"=>$dc->id));
		$places = reset($places);
		$dc->free_places = ($dc->total_places - $places->total_places) + $places->free_places;
	}

	$dc->coordinator = $course['coordinator'];
	$dc->remarks = (isset($course['remarks'])) ? $course['remarks'] : '';
	$dc->timemodified = time();

	//if we have id we update on old one
	if (@$dc->id) {
		if(@is_null($dc->courseid)){
			$courseName = $meta->name."-".$dc->lang."-".$dc->id;

			if($mCourse = $DB->get_record('course', array('fullname' => $courseName))){
				$dc->courseid = $mCourse->id;
			}else{
				$created_courseid = create_new_course($courseName,$courseName, $competence, $dc->startdate, $meta->content);
				$dc->courseid = $created_courseid;
			}
		}
		$DB->update_record('meta_datecourse', $dc);
		$dc = $DB->get_records_sql("SELECT * FROM {meta_datecourse} where id = :id",array("id"=>$dc->id));
		$dc = reset($dc);

		update_meta_course($metaid, $dc, $competence);
		$updatedCourseId = $DB->get_record('meta_datecourse', array('id'=>$dc->id));

		if ($meta->coordinator != 0) {
		//	 add_coordinator($meta->coordinator, $updatedCourseId->courseid);	
		}
		//add_coordinator($dc->coordinator, $updatedCourseId->courseid);

		//go and add people from the waiting list
	} else {
		// else insert and create new courses
		$datecourseid = $DB->insert_record('meta_datecourse', $dc);
		//create the course
		$courseName = $meta->name."-".$dc->lang."-".$datecourseid;

		$created_courseid = create_new_course($courseName,$courseName, $competence, $dc->startdate, $meta->content);

		// update the datecourse with the course id
		$DB->set_field('meta_datecourse', 'courseid', $created_courseid, array("id"=>$datecourseid));

		if ($meta->coordinator == 0) {
			 add_coordinator($meta->coordinator, $created_courseid);
		}
		add_coordinator($dc->coordinator, $created_courseid);

		//add the label with the description
		add_label($created_courseid, $meta);
	}
}
add_to_log(1, 'metacourse', 'Saved metacourse', '', $name, 0, $USER->id);
redirect(new moodle_url($CFG->wwwroot."/blocks/metacourse/view_metacourse.php?id=".$metaid), "Your course has been saved", 5);