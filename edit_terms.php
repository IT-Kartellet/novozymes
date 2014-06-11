 <?php

require_once('../../config.php');
require_once("$CFG->libdir/formslib.php");
require_once('tos_form.php');
require_once('lib.php');

require_login();

require_capability('moodle/site:config', context_system::instance());

global $DB;
$PAGE->set_context(get_system_context());
$PAGE->set_pagelayout('admin');
$PAGE->navbar->ignore_active();
$PAGE->requires->jquery();
$PAGE->navbar->add("List courses", new moodle_url('/blocks/metacourse/list_metacourses.php'));
$PAGE->navbar->add("Settings", new moodle_url('/blocks/metacourse/edit_terms.php'));
$URL = '/blocks/metacourse/list_metacourses.php';


$PAGE->set_title("List of current courses");
$PAGE->set_heading("Moodle Custom Courses");
$PAGE->set_url($CFG->wwwroot."/blocks/metacourse/edit_metacourse.php");
echo $OUTPUT->header();

$mform = new tos_form();

$tos = $DB->get_records_sql("SELECT * FROM {meta_tos}");
$tos = reset($tos);
$active_langs = $DB->get_records_sql("SELECT * FROM {meta_languages} where active = :active",array("active"=>1));

//populate the form for editing purposes
$data = new stdClass();
$data->id = $tos->id;
$data->tos = array();
$data->tos['text'] = $tos->tos;

foreach ($active_langs as $key => $value) {
	$data->lang[$key] = $value->active;
}

$mform->set_data($data);

if ($mform->is_cancelled()) {
 	//nothing to do here.
  	redirect($URL, 'Your action was canceled!');

} else if ($fromform = $mform->get_data()) {
	$languages = $fromform->lang;
	//set all languages as inactive
	$DB->set_field('meta_languages', 'active', 0, array());

	//set the needed ones as active
	foreach ($languages as $key => $active) {
		$DB->set_field('meta_languages', 'active', $active, array("id"=>$key));
	}

	redirect($URL, "The settings have been saved!");
} else {

	$toform = $mform->get_data();
	$mform->set_data(null);
	$mform->display();
}

echo $OUTPUT->footer();