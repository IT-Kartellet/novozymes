<?php
require_once('../../config.php');
require_once("$CFG->libdir/formslib.php");
require_once('datecourse_form.php');
require_once('lib.php');

require_login();

$PAGE->set_context(get_system_context());

$name = $_SESSION['meta_name'];
$purpose = $_SESSION['meta_purpose'];
$target = $_SESSION['meta_target'];
$content = $_SESSION['meta_content'];
$instructors = $_SESSION['meta_instructors'];
$comment = $_SESSION['meta_comment'];
$coordinator = $_SESSION['meta_coordinator'];
$provider = $_SESSION['meta_provider'];

$datecourses = $_POST['datecourse'];
$timestarts = $_POST['timestart'];
$timeends = $_POST['timeend'];

$meta = new stdClass();
$meta->name = $name;
$meta->purpose = $purpose['text'];
$meta->target = $target;
$meta->content = $content['text'];
$meta->instructors = $instructors;
$meta->comment = $comment;
$meta->coordinator = $coordinator;
$meta->provider = $provider;
$meta->timemodified = time();

$metaid = $DB->insert_record('meta_course', $meta);
foreach ($datecourses as $key => $course) {

	$dc = new stdClass();
	$dc->metaid = $metaid;

	$dc->startdate = strtotime( implode("-",array_reverse($timestarts[$key]))  . " 00:00:00");
	$dc->enddate = strtotime( implode("-",array_reverse($timestarts[$key])) . " 23:59:59");
	$loc = $DB->get_records_sql("SELECT id from {meta_locations} where location = :location", array("location"=>$course['location']));
	$loc = reset($loc);

	if ($loc->id == null) {
		$location = new stdClass();
		$location->location = $course['location'];
		$loc_id = $DB->insert_record("meta_locations",$location);
		$dc->location = $loc_id;
		
	} else {
		$dc->location = $loc->id;
	}
	$dc->lang = $course['language'];
	$dc->price = $course['price'];
	$dc->total_places = $course['places'];
	$dc->free_places = $course['places'];
	$dc->open = 1;
	$dc->timemodified = time();
	if ($dc->location == null || $dc->price == null || $dc->total_places == null) {
		continue;
	}

	$datecourseid = $DB->insert_record('meta_datecourse', $dc);

	$courseName = $meta->name."-".$dc->lang."-".$datecourseid;
	create_new_course($courseName,$courseName, 1);

	//TODO: take the id of the course inserted and update the datecourse record;
}

header("Location: " . $CFG->wwwroot."/blocks/metacourse/list_metacourses.php" );