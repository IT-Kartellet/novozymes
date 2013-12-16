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
	
	$metaid = 0;
	$mform = new metacourse_form("add_datecourse.php");

	//the id of the metacourse
	$data = new stdClass();
	$data->id = $id;
	$mform->set_data($data);

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
	$mform = new metacourse_form("add_datecourse.php");
	
	$meta = $DB->get_record("meta_course" ,array("id"=>$id));
	$meta->id = $id;
	$meta->purpose = array("text"=>$meta->purpose);
	$meta->content = array("text"=>$meta->content);
	$meta->cancellation = array("text"=>$meta->cancellation);
	$meta->contact = array("text"=>$meta->contact);
	$duration_time = $meta->duration;
	$meta->duration = array();
	$meta->duration['number'] = $duration_time;
	$meta->duration['timeunit'] = $meta->duration_unit;
	$mform->set_data($meta);

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
	// redirect($CFG->wwwroot ."/blocks/metacourse/add_metacourse.php", 'Course not found!');
}

echo $OUTPUT->footer();
