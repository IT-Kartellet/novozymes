<?php
require_once('../../config.php');
require_once("$CFG->libdir/formslib.php");
require_once("$CFG->libdir/adminlib.php");
require_once("$CFG->libdir/modinfolib.php");
require_once('datecourse_form.php');
require_once('lib.php');

require_login();
require_capability('moodle/course:create', context_system::instance());


$PAGE->set_context(get_system_context());

$metaid = $_SESSION['meta_id'];
$name = $_SESSION['meta_name'];
$localname = $_SESSION['meta_localname'];
$localname_lang = $_SESSION['meta_localname_lang'];
$purpose = $_SESSION['meta_purpose'];
$target = $_SESSION['meta_target'];
$target_description = $_SESSION['meta_target_description'];
$content = $_SESSION['meta_content'];
$instructors = $_SESSION['meta_instructors'];
$comment = $_SESSION['meta_comment'];
$duration = $_SESSION['meta_duration'];
$cancellation = $_SESSION['meta_cancellation'];
$contact = $_SESSION['meta_contact'];
$coordinator = $_SESSION['meta_coordinator'];
$provider = $_SESSION['meta_provider'];

$datecourses = $_POST['datecourse'];
$timestarts = $_POST['timestart'];
$timeends = $_POST['timeend'];
$publishdate = $_POST['publishdate'];



$meta = new stdClass();
$meta->id = $metaid;
$meta->name = $name;
$meta->localname = $localname;
$iso_lang = $DB->get_record("meta_languages",array('id'=>$localname_lang));
$meta->localname_lang = $iso_lang->iso;
$meta->purpose = $purpose['text'];
$meta->target = $target;
$meta->target_description = $target_description['text'];
$meta->content = $content['text'];
$meta->instructors = $instructors;
$meta->comment = $comment['text'];
$meta->duration = $duration['number'];
$meta->duration_unit = $duration['timeunit'];
$meta->cancellation = $cancellation['text'];
$meta->contact = $contact['text'];
$meta->coordinator = $coordinator;
$meta->provider = $provider;
$meta->timemodified = time();

//if we are editing
if ($metaid) {
	$DB->update_record('meta_course',$meta);
} else {
	$metaid = $DB->insert_record('meta_course', $meta);
}

foreach ($datecourses as $key => $course) {
	$dc = new stdClass();

	//if we are editing
	if ($course['id']) {
		$dc->id = $course['id'];
	}
	$dc->metaid = $metaid;

	$starttime = array(	"day"=>$timestarts[$key]['day'],
						"month"=>$timestarts[$key]['month'],
						"year"=>$timestarts[$key]['year'],
						"hour"=>$timestarts[$key]['hour'],
						"minute"=>$timestarts[$key]['minute']
	 );

	$endtime = array(	"day"=>$timeends[$key]['day'],
						"month"=>$timeends[$key]['month'],
						"year"=>$timeends[$key]['year'],
						"hour"=>$timeends[$key]['hour'],
						"minute"=>$timeends[$key]['minute']
	 );

	$publishtime = array(	"day"=>$publishdate[$key]['day'],
						"month"=>$publishdate[$key]['month'],
						"year"=>$publishdate[$key]['year'],
						"hour"=>$publishdate[$key]['hour'],
						"minute"=>$publishdate[$key]['minute']
	 );
	
	//format the times
	$ts = implode("-",array($starttime['year'], $starttime['month'], $starttime['day']));
	$ts .= " " . $starttime['hour'] . ":" . $starttime['minute'] . ":00";
	$te = implode("-",array($endtime['year'], $endtime['month'], $endtime['day']));
	$te .= " " . $endtime['hour'] . ":" . $endtime['minute'] . ":00";
	$pd = implode("-",array($publishtime['year'], $publishtime['month'], $publishtime['day']));
	$pd .= " " . $publishtime['hour'] . ":" . $publishtime['minute'] . ":00";


	$dc->startdate = date_timestamp_get(date_create($ts));
	$dc->enddate = date_timestamp_get(date_create($te));
	$dc->publishdate = date_timestamp_get(date_create($pd));
	$dc->location = $course['location'];

	$dc->lang = $course['language'];
	$dc->category = $course['category'];
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
	$dc->timemodified = time();

	//if we have id we update on old one
	if (@$dc->id) {
		$DB->update_record('meta_datecourse', $dc);
		$dc = $DB->get_records_sql("SELECT * FROM {meta_datecourse} where id = :id",array("id"=>$dc->id));
		$dc = reset($dc);
		
		update_meta_course($metaid,$dc, $course['category']);
		$updatedCourseId = $DB->get_record('meta_datecourse', array('id'=>$dc->id));
		add_coordinator($meta->coordinator, $updatedCourseId->courseid);
		add_coordinator($dc->coordinator, $updatedCourseId->courseid);

		//go and add people from the waiting list
	} else {
		// else insert and create new courses

		$datecourseid = $DB->insert_record('meta_datecourse', $dc);
		//create the course
		$courseName = $meta->name."-".$dc->lang."-".$datecourseid;

		$created_courseid = create_new_course($courseName,$courseName, $course['category'], $dc->startdate, $meta->content);

		// add the manual enrolment
		$DB->insert_record("enrol",array("enrol"=>"manual","status"=>0, "roleid"=>5,"courseid"=>$created_courseid));

		// update the datecourse with the course id
		$DB->set_field('meta_datecourse', 'courseid', $created_courseid, array("id"=>$datecourseid));
		add_coordinator($meta->coordinator, $created_courseid);
		add_coordinator($dc->coordinator, $created_courseid);

		//add the label with the description
		add_label($created_courseid, $meta);

	}


	purge_all_caches();
}
header("Location: " . $CFG->wwwroot."/blocks/metacourse/list_metacourses.php" );