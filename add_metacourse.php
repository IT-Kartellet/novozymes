<?php
require_once('../../config.php');
require_once("$CFG->libdir/formslib.php");
require_once('metacourse_form.php');
require_once('lib.php');

require_login();

$id = optional_param('id', 0, PARAM_INT);

$PAGE->set_context(get_system_context());
$PAGE->set_pagelayout('admin');
$URL = '/moodle/blocks/metacourse/list_metacourses.php';
$PAGE->set_title("Add course");
$PAGE->set_heading("Add course");
$PAGE->set_url($CFG->wwwroot."/blocks/metacourse/add_metacourse.php");
$PAGE->navbar->ignore_active();
$PAGE->navbar->add("List courses", new moodle_url('/blocks/metacourse/list_metacourses.php'));
$PAGE->navbar->add("Add course", new moodle_url('/blocks/metacourse/add_metacourse.php'));
echo $OUTPUT->header();

if ($id == 0) {
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
	//TODO: edit current course
}

echo $OUTPUT->footer();
