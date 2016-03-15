<?php
require_once('../../config.php');
require_once("$CFG->libdir/formslib.php");
require_once('datecourse_form.php');
require_once('metacourse_form.php');
require_once('lib.php');

require_login();
require_capability('moodle/course:create', context_system::instance());

//users must be trusted
$id = optional_param('id', 0, PARAM_INT);

$meta['meta_id'] = $id;
$meta['meta_name'] = optional_param('name',"",PARAM_TEXT);
$meta['meta_localname'] = optional_param('localname',"",PARAM_TEXT);
$meta['meta_localname_lang'] = optional_param('localname_lang',"",PARAM_TEXT);
$meta['meta_purpose'] = optional_param_array('purpose',"",PARAM_RAW);
$meta['meta_target'] = optional_param_array('target',"",PARAM_INT);
$meta['meta_content'] = optional_param_array('content',"",PARAM_RAW);
$meta['meta_target_description'] = optional_param_array('target_description',"",PARAM_RAW);
$meta['meta_cancellation'] = optional_param_array('cancellation',"",PARAM_RAW);
$meta['meta_targetgroup'] = optional_param_array('targetgroup',"",PARAM_RAW);
$meta['meta_lodging'] = optional_param_array('lodging',"",PARAM_RAW);
$meta['meta_contact'] = optional_param_array('contact',"",PARAM_RAW);
$meta['meta_instructors'] = optional_param('instructors',"",PARAM_TEXT);
$meta['meta_comment'] = optional_param_array('comment',"",PARAM_RAW);
$meta['meta_multiple_dates'] = optional_param_array('multiple_dates',"",PARAM_RAW);
$meta['meta_duration'] = optional_param_array('duration',"",PARAM_TEXT);
$meta['meta_coordinator'] = optional_param('coordinator',"",PARAM_INT);
$meta['meta_provider'] = optional_param('provider',"",PARAM_TEXT);
$meta['meta_unpublishdate'] = $_POST['unpublishdate'];
$meta['meta_competence'] = $_POST['competence'];
$meta['customemail'] = optional_param('customemail', false, PARAM_BOOL);

if ($meta['customemail']) {
	$meta['custom_email'] = $_POST['custom_email']; // TODO: Does not work with optional param.
}

$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('admin');
$PAGE->requires->jquery();
$PAGE->requires->yui_module('moodle-block_metacourse-dateform', 'M.block_metacourse.dateform.init');
$URL = '/blocks/metacourse/list_metacourses.php';


$prev_form = new metacourse_form('add_datecourse.php');
if ($prev_form->is_cancelled()) {
  	redirect($URL, 'Your action was canceled!');
}

if ($id == 0) {
	$PAGE->set_title("Add course");
	$PAGE->set_heading("Add course");
	$PAGE->set_url($CFG->wwwroot."/blocks/metacourse/add_datecourse.php");
	$PAGE->navbar->ignore_active();
	$PAGE->navbar->add("List courses", new moodle_url('/blocks/metacourse/list_metacourses.php'));
	$PAGE->navbar->add("Add course", new moodle_url('/blocks/metacourse/add_metacourse.php'));
	$PAGE->navbar->add("Add course dates", new moodle_url('/blocks/metacourse/add_datecourse.php'));

	echo $OUTPUT->header();

	$mform = new datecourse_form("process_forms.php", array('meta' => serialize($meta)));

	$mform->display();
} else {
	//EDIT
	$PAGE->set_title("Edit course");
	$PAGE->set_heading("Edit course");
	$PAGE->set_url($CFG->wwwroot."/blocks/metacourse/add_datecourse.php");
	$PAGE->navbar->ignore_active();
	$PAGE->navbar->add("List courses", new moodle_url('/blocks/metacourse/list_metacourses.php'));
	$PAGE->navbar->add("Edit course", new moodle_url('/blocks/metacourse/add_metacourse.php'));
	$PAGE->navbar->add("Edit course dates", new moodle_url('/blocks/metacourse/add_datecourse.php'));

	if (!check_provider_role($id)) {
		die("Access denied!");
	}

	echo $OUTPUT->header();

	$datecourses = $DB->get_records_sql("SELECT d.*, c.category FROM {meta_datecourse} d left join {course} c on c.id = d.courseid WHERE metaid = :metaid AND deleted = 0 ORDER BY d.id ASC", array("metaid"=>$id));
	$datecourseNr = count($datecourses);

	$uselessCounter = 0;
	$mform = new datecourse_form("process_forms.php", array('dateCourseNr'=>$datecourseNr, "data"=>$datecourses, 'meta' => serialize($meta)));
	$mform->display();
}

echo $OUTPUT->footer();

// the modal window for a new location
echo "<div id='lean_background'>
		<div id='lean_overlay_loc'>
			<h1>Add a new location</h1>
			<input type='text' name='newLeanLocation' />
			<input type='button' name='addL' value='Add location' />
			<input type='button' name='cancel' value='Cancel' />
		</div>	
	  <div>";