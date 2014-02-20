<?php
require_once('../../config.php');
require_once("$CFG->libdir/formslib.php");
require_once('datecourse_form.php');
require_once('lib.php');

require_login();
require_capability('moodle/course:create', context_system::instance());

//users must be trusted
$id = optional_param('id', 0, PARAM_INT);

$_SESSION['meta_id'] = optional_param('id',"",PARAM_INT);
$_SESSION['meta_name'] = optional_param('name',"",PARAM_TEXT);
$_SESSION['meta_localname'] = optional_param('localname',"",PARAM_TEXT);
$_SESSION['meta_localname_lang'] = optional_param('localname_lang',"",PARAM_TEXT);
$_SESSION['meta_purpose'] = optional_param_array('purpose',"",PARAM_RAW);
$_SESSION['meta_target'] = optional_param('target',"",PARAM_TEXT);
$_SESSION['meta_content'] = optional_param_array('content',"",PARAM_RAW);
$_SESSION['meta_cancellation'] = optional_param_array('cancellation',"",PARAM_RAW);
$_SESSION['meta_contact'] = optional_param_array('contact',"",PARAM_RAW);
$_SESSION['meta_instructors'] = optional_param('instructors',"",PARAM_TEXT);
$_SESSION['meta_comment'] = optional_param('comment',"",PARAM_TEXT);
$_SESSION['meta_duration'] = optional_param_array('duration',"",PARAM_TEXT);
$_SESSION['meta_coordinator'] = optional_param('coordinator',"",PARAM_INT);
$_SESSION['meta_provider'] = optional_param('provider',"",PARAM_TEXT);

$PAGE->set_context(get_system_context());
$PAGE->set_pagelayout('admin');
$URL = '/moodle/blocks/metacourse/list_metacourses.php';

if ($id == 0) {
	$PAGE->set_title("Add course");
	$PAGE->set_heading("Add course");
	$PAGE->set_url($CFG->wwwroot."/blocks/metacourse/add_datecourse.php");
	$PAGE->navbar->ignore_active();
	$PAGE->navbar->add("List courses", new moodle_url('/blocks/metacourse/list_metacourses.php'));
	$PAGE->navbar->add("Add course", new moodle_url('/blocks/metacourse/add_metacourse.php'));
	$PAGE->navbar->add("Add course dates", new moodle_url('/blocks/metacourse/add_datecourse.php'));

	echo $OUTPUT->header();

	$mform = new datecourse_form("process_forms.php");

	//the id of the metacourse
	$data = new stdClass();
	// $data->id = $id;  /// UNCOMMENT ME
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
	//EDIT
	$PAGE->set_title("Edit course");
	$PAGE->set_heading("Edit course");
	$PAGE->set_url($CFG->wwwroot."/blocks/metacourse/add_datecourse.php");
	$PAGE->navbar->ignore_active();
	$PAGE->navbar->add("List courses", new moodle_url('/blocks/metacourse/list_metacourses.php'));
	$PAGE->navbar->add("Edit course", new moodle_url('/blocks/metacourse/add_metacourse.php'));
	$PAGE->navbar->add("Edit course dates", new moodle_url('/blocks/metacourse/add_datecourse.php'));

	echo $OUTPUT->header();

	$datecourseNr = $DB->count_records('meta_datecourse', array("metaid"=>$id)); 
	$datecourses = $DB->get_records_sql("SELECT d.*, c.category FROM {meta_datecourse} d join {course} c on c.id = d.courseid WHERE metaid = :metaid ORDER BY d.id ASC", array("metaid"=>$id));
	$mform = new datecourse_form("process_forms.php",array('dateCourseNr'=>$datecourseNr, "data"=>$datecourses));

	$mform->set_data($datecourses);

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

