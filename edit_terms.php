 <?php

require_once('../../config.php');
require_once("$CFG->libdir/formslib.php");
require_once('tos_form.php');
require_once('lib.php');

require_login();
require_capability('moodle/course:create', context_system::instance());


global $DB;
$PAGE->set_context(get_system_context());
$PAGE->set_pagelayout('admin');
$PAGE->navbar->ignore_active();
$PAGE->navbar->add("List courses", new moodle_url('/blocks/metacourse/list_metacourses.php'));
$PAGE->navbar->add("Edit terms", new moodle_url('/blocks/metacourse/edit_terms.php'));
$URL = '/moodle/blocks/metacourse/list_metacourses.php';


$PAGE->set_title("List of current courses");
$PAGE->set_heading("Moodle Custom Courses");
$PAGE->set_url($CFG->wwwroot."/blocks/metacourse/edit_metacourse.php");
echo $OUTPUT->header();

$mform = new tos_form();

$tos = $DB->get_records_sql("SELECT * FROM {meta_tos}");
$tos = reset($tos);

$data = new stdClass();
$data->id = $tos->id;
$data->tos = array();
$data->tos['text'] = $tos->tos;

$mform->set_data($data);

if ($mform->is_cancelled()) {
 	//nothing to do here.
  	redirect($URL, 'Your action was canceled!');

} else if ($fromform = $mform->get_data()) {
	$DB->set_field('meta_tos', 'tos', $fromform->tos['text'], array("id"=>1));
	redirect($URL, "The terms of service have been saved!");
} else {

	$toform = $mform->get_data();
	$mform->set_data(null);
	$mform->display();
}

echo $OUTPUT->footer();



