<?php
require_once('../../config.php');
require_once("$CFG->libdir/formslib.php");
require_once('metacourse_form.php');
require_once('lib.php');

require_login();
require_capability('moodle/course:create', context_system::instance());


$id = optional_param('id', 0, PARAM_INT);

$PAGE->set_context(get_system_context());
$PAGE->set_pagelayout('admin');
$URL = '/moodle/blocks/metacourse/list_metacourses.php';

//we have to set these before starting the output
if ($id == 0) {
	$PAGE->set_url($CFG->wwwroot."/blocks/metacourse/add_metacourse.php");
	$PAGE->set_title("Add course");
	$PAGE->set_heading("Add course");
	$PAGE->navbar->ignore_active();
	$PAGE->navbar->add("List courses", new moodle_url('/blocks/metacourse/list_metacourses.php'));
	$PAGE->navbar->add("Add course", new moodle_url('/blocks/metacourse/add_metacourse.php'));
} else {
	$PAGE->set_url(new moodle_url($CFG->wwwroot."/blocks/metacourse/add_metacourse.php)", array('id'=>$id)))	;
	$PAGE->set_title("Edit course");
	$PAGE->set_heading("Edit course");
	$PAGE->navbar->ignore_active();
	$PAGE->navbar->add("List courses", new moodle_url('/blocks/metacourse/list_metacourses.php'));
	$PAGE->navbar->add("Edit course", new moodle_url('/blocks/metacourse/add_metacourse.php'));
}

echo $OUTPUT->header();

if ($id == 0) {
	
	$PAGE->navbar->add("Add course", new moodle_url('/blocks/metacourse/add_metacourse.php'));
	$mform = new metacourse_form("add_datecourse.php");

	if (isset($_SESSION['meta_name'])) {
		// echo "string";
		$meta = new stdClass();
		$meta->id = $_SESSION['meta_id'];
		$meta->name = $_SESSION['meta_name'];
		$meta->content = array("text"=>$_SESSION['meta_content']['text']);
		$meta->localname = $_SESSION['meta_localname'];
		$meta->localname_lang = $_SESSION['meta_localname_lang'];
		$meta->purpose = array("text"=>$_SESSION['meta_purpose']['text']);
		$meta->cancellation = array("text"=>$_SESSION['meta_cancellation']['text']);
		$meta->lodging = array("text"=>$_SESSION['meta_lodging']['text']);
		$meta->contact = array("text"=>$_SESSION['meta_contact']['text']);
		$meta->target = $_SESSION['meta_target'];
		$meta->instructors = $_SESSION['meta_instructors'];
		$meta->target_description = array("text"=>$_SESSION['meta_target_description']['text']);
		$meta->comment = array("text"=>$_SESSION['meta_comment']['text']);
		$meta->multiple_dates = array("text"=>$_SESSION['meta_multiple_dates']['text']);
		$meta->duration = array();
		$meta->duration['number'] = $_SESSION['meta_duration']['number'];
		$meta->duration['timeunit'] = $_SESSION['meta_duration']['timeunit'];
		$mform->set_data($meta);
	} else {
		// echo "ELSE";
		// TODO:
		$metaid = 0;

		//the id of the metacourse
		$data = new stdClass();
		$data->id = $id;
		$mform->set_data($data);
	}
	if ($mform->is_cancelled()) {
	 	//nothing to do here.
	  	redirect($URL, 'Your action was canceled!');

	} else if ($fromform = $mform->get_data()) {
		
	} else {
		//if data not valid

		$toform = $mform->get_data();
		$mform->set_data(null);
		$mform->display();

	}
} else {
	// EDIT

	// check if he has the role needed to edit the course
	if (!check_provider_role($id)) {
		die("Access denied!");
	}

	$mform = new metacourse_form("add_datecourse.php");
	
	$meta = $DB->get_record("meta_course" ,array("id"=>$id));

	$data = new stdClass();
	$data->id = $id;
	$data->name = $meta->name;
	$data->localname = $meta->localname;
	$data->localname_lang = $meta->localname_lang;
	$data->instructors = $meta->instructors;
	$data->purpose = array("text"=>$meta->purpose);
	$data->content = array("text"=>$meta->content);
	$data->cancellation = array("text"=>$meta->cancellation);
	$data->lodging = array("text"=>$meta->lodging);
	$data->contact = array("text"=>$meta->contact);
	$data->target_description = array("text"=>$meta->target_description);
	$data->target = json_decode($meta->target);
	$data->comment = array("text"=>$meta->comment);
	$data->multiple_dates = array("text"=>$meta->multiple_dates);
	$data->multipledates = 1;

	$data->duration['number'] = $meta->duration;
	$data->duration['timeunit'] = $meta->duration_unit;
	$mform->set_data($data);

	if ($mform->is_cancelled()) {
	 	//nothing to do here.
	 	unset($_SESSION['meta_id']);
		unset($_SESSION['meta_name']);
		unset($_SESSION['meta_localname']);
		unset($_SESSION['meta_localname_lang']);
		unset($_SESSION['meta_purpose']);
		unset($_SESSION['meta_target']);
		unset($_SESSION['meta_content']);
		unset($_SESSION['meta_target_description']);
		unset($_SESSION['meta_cancellation']);
		unset($_SESSION['meta_lodging']);
		unset($_SESSION['meta_contact']);
		unset($_SESSION['meta_instructors']);
		unset($_SESSION['meta_comment']);
		unset($_SESSION['meta_duration']);
		unset($_SESSION['meta_coordinator']);
		unset($_SESSION['meta_provider']);
	  	redirect($URL, 'Your action was canceled!');

	} else if ($fromform = $mform->get_data()) {
		
	} else {
		//if data not valid

		$toform = $mform->get_data();
		$mform->set_data(null);
		$mform->display();

	}
	// redirect($CFG->wwwroot ."/blocks/metacourse/add_metacourse.php", 'Course not found!');
}

echo $OUTPUT->footer();
