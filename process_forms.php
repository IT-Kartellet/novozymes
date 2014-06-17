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
$timestarts = @$_POST['timestart'];
$timeends = @$_POST['timeend'];
$publishdate = @$_POST['publishdate'];
$unpublishdate = @$_POST['unpublishdate'];
$startenrolment = @$_POST['startenrolment'];
$custom_emails = @$_SESSION['custom_email'];

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
	// add the custom emails
	foreach ($custom_emails as $lang => $email) {
		$em = new stdClass();
		$em->metaid = $metaid;
		$em->lang = $lang;
		$em->text = $email['text'];
		$DB->insert_record("meta_custom_emails", $em);
	}
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

if ($changed) {
	$DB->update_record('meta_course', $meta);
}

foreach ($datecourses as $key => $course) {
	//TODO:// delete course
	$dc = new stdClass();
	if (is_null($course['location']) && is_null($course['language'])) {
		$toDeleteCourse = $DB->get_records_sql("SELECT * FROM {meta_datecourse} where id = :id", array("id"=>$course['id']));
		$toDeleteCourse = reset($toDeleteCourse);

		delete_course($toDeleteCourse->courseid, false);
		continue;
	}

	//if we are editing
	if (@$course['id']) {
		$dc->id = $course['id'];
	}
	$dc->metaid = $metaid;
	
	if(is_null($timestarts[$key]['day'])){
		redirect(new moodle_url($CFG->wwwroot."/blocks/metacourse/list_metacourses.php"), "We experienced an error when saving the course. Please try again.", 5);
		$dc->startdate = 0;
		$dc->enddate = 0;
		$dc->publishdate = 0;
		$dc->unpublishdate = 0;
		$dc->startenrolment = 0;
		$dc->free_places = 0;
		$dc->open = 0;
		$dc->coordinator = $course['coordinator'];
		$dc->timemodified = time();
		$dc->location = $course['location'];
		$dc->country = $course['country'];
		$dc->lang = $course['language'];
		$dc->category = $competence;
		$dc->price = $course['price'];
		$dc->currencyid = $course['currency'];
		$dc->total_places = $course['places'];
		$dc->free_places = $course['places'];
		$dc->remarks = $course['remarks'];

	} else{
		$starttime = array("day"=>$timestarts[$key]['day'],
							"month"=>$timestarts[$key]['month'],
							"year"=>$timestarts[$key]['year'],
							"hour"=>$timestarts[$key]['hour'],
							"minute"=>$timestarts[$key]['minute']
		 );

		$endtime = array("day"=>$timeends[$key]['day'],
							"month"=>$timeends[$key]['month'],
							"year"=>$timeends[$key]['year'],
							"hour"=>$timeends[$key]['hour'],
							"minute"=>$timeends[$key]['minute']
		 );

		$publishtime = array("day"=>$publishdate[$key]['day'],
							"month"=>$publishdate[$key]['month'],
							"year"=>$publishdate[$key]['year'],
							"hour"=>$publishdate[$key]['hour'],
							"minute"=>$publishdate[$key]['minute']
		 );
		$unpublishtime = array("day"=>$unpublishdate[$key]['day'],
							"month"=>$unpublishdate[$key]['month'],
							"year"=>$unpublishdate[$key]['year'],
							"hour"=>$unpublishdate[$key]['hour'],
							"minute"=>$unpublishdate[$key]['minute']
		 );

		$startenrolmenttime = array("day"=>$startenrolment[$key]['day'],
								"month"=>$startenrolment[$key]['month'],
								"year"=>$startenrolment[$key]['year'],
								"hour"=>$startenrolment[$key]['hour'],
								"minute"=>$startenrolment[$key]['minute']
		 );
		
		//format the times
		$ts = implode("-",array($starttime['year'], $starttime['month'], $starttime['day']));
		$ts .= " " . $starttime['hour'] . ":" . $starttime['minute'] . ":00";
		$te = implode("-",array($endtime['year'], $endtime['month'], $endtime['day']));
		$te .= " " . $endtime['hour'] . ":" . $endtime['minute'] . ":00";
		$pd = implode("-",array($publishtime['year'], $publishtime['month'], $publishtime['day']));
		$pd .= " " . $publishtime['hour'] . ":" . $publishtime['minute'] . ":00";

		$upd = implode("-",array($unpublishtime['year'], $unpublishtime['month'], $unpublishtime['day']));
		$upd .= " " . $unpublishtime['hour'] . ":" . $unpublishtime['minute'] . ":00";

		$ste = implode("-",array($startenrolmenttime['year'], $startenrolmenttime['month'], $startenrolmenttime['day']));
		$ste .= " " . $startenrolmenttime['hour'] . ":" . $startenrolmenttime['minute'] . ":00";

		$dc->startdate = date_timestamp_get(date_create($ts));
		$dc->enddate = date_timestamp_get(date_create($te));
		$dc->publishdate = date_timestamp_get(date_create($pd));
		$dc->unpublishdate = date_timestamp_get(date_create($upd));
		$dc->startenrolment = date_timestamp_get(date_create($ste));
		$dc->location = $course['location'];
		$dc->country = $course['country'];
		$dc->lang = $course['language'];
		$dc->category = $competence;
		$dc->price = $course['price'];
		$dc->currencyid = $course['currency'];
		$dc->total_places = $course['places'];
		// only if have a new course we add the free seats
		if (@!$dc->id) {
			$dc->free_places = $course['places'];
		} else {
			// update the nr of free places
			$places = $DB->get_records_sql("SELECT total_places, free_places from {meta_datecourse} where id=:id",array("id"=>$dc->id));
			$places = reset($places);
			$dc->free_places = ($dc->total_places - $places->total_places) + $places->free_places;
		}
		$dc->open = 1;
		$dc->coordinator = $course['coordinator'];
		$dc->remarks = $course['remarks'];
		$dc->timemodified = time();
	}

	//if we have id we update on old one
	if (@$dc->id) {
		if(@is_null($dc->courseid)){
			$courseName = $meta->name."-".$dc->lang."-".$dc->id;
			//echo $courseName."\n";
			if($mCourse = $DB->get_record('course', array('fullname' => $courseName))){
				$dc->courseid = $mCourse->id;
				//echo $dc->courseid;
			}else{
				$created_courseid = create_new_course($courseName,$courseName, $competence, $dc->startdate, $meta->content);
				$dc->courseid = $created_courseid;
				//echo $dc->courseid;
			}
		}
		$DB->update_record('meta_datecourse', $dc);
		$dc = $DB->get_records_sql("SELECT * FROM {meta_datecourse} where id = :id",array("id"=>$dc->id));
		$dc = reset($dc);

		update_meta_course($metaid, $dc, $competence);
		$updatedCourseId = $DB->get_record('meta_datecourse', array('id'=>$dc->id));
		//var_dump($updatedCourseId);
		if ($meta->coordinator != 0) {
		//	 add_coordinator($meta->coordinator, $updatedCourseId->courseid);	
		}
		//add_coordinator($dc->coordinator, $updatedCourseId->courseid);

		//go and add people from the waiting list
	} else {
		// else insert and create new courses
		
		$datecourseid = $DB->insert_record('meta_datecourse', $dc);
		//create the course
		if ($dc->open == 1) {
			$courseName = $meta->name."-".$dc->lang."-".$datecourseid;

			$created_courseid = create_new_course($courseName,$courseName, $competence, $dc->startdate, $meta->content);

			// add the manual enrolment
			$DB->insert_record("enrol",array("enrol"=>"manual","status"=>0, "roleid"=>5,"courseid"=>$created_courseid));

			// update the datecourse with the course id
			$DB->set_field('meta_datecourse', 'courseid', $created_courseid, array("id"=>$datecourseid));
			if ($meta->coordinator!=0) {
				 add_coordinator($meta->coordinator, $created_courseid);
			}
			add_coordinator($dc->coordinator, $created_courseid);

			//add the label with the description
			add_label($created_courseid, $meta);
		}
	}
	//purge_all_caches();
}
add_to_log(1, 'metacourse', 'Saved metacourse', '', $name, 0, $USER->id);
redirect(new moodle_url($CFG->wwwroot."/blocks/metacourse/list_metacourses.php"), "You've course has been saved", 5);
