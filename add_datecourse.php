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
$_SESSION['meta_target'] = optional_param_array('target',"",PARAM_INT);
$_SESSION['meta_content'] = optional_param_array('content',"",PARAM_RAW);
$_SESSION['meta_target_description'] = optional_param_array('target_description',"",PARAM_RAW);
$_SESSION['meta_cancellation'] = optional_param_array('cancellation',"",PARAM_RAW);
$_SESSION['meta_targetgroup'] = optional_param_array('targetgroup',"",PARAM_RAW);

$_SESSION['meta_lodging'] = optional_param_array('lodging',"",PARAM_RAW);
$_SESSION['meta_contact'] = optional_param_array('contact',"",PARAM_RAW);
$_SESSION['meta_instructors'] = optional_param('instructors',"",PARAM_TEXT);
$_SESSION['meta_comment'] = optional_param_array('comment',"",PARAM_RAW);
$_SESSION['meta_multiple_dates'] = optional_param_array('multiple_dates',"",PARAM_RAW);
$_SESSION['meta_duration'] = optional_param_array('duration',"",PARAM_TEXT);
$_SESSION['meta_coordinator'] = optional_param('coordinator',"",PARAM_INT);
$_SESSION['meta_provider'] = optional_param('provider',"",PARAM_TEXT);
$_SESSION['meta_unpublishdate'] = $_POST['unpublishdate']; //TODO: fix these posts to be secure. It's 3 am. and I don't want to fuck with moodle now.
$_SESSION['meta_competence'] = $_POST['competence']; //TODO: fix these posts to be secure. It's 3 am. and I don't want to fuck with moodle now.
$_SESSION['custom_email'] = $_POST['custom_email'];

$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('admin');
$PAGE->requires->jquery();
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
	 	//nothing to do here. Remove variables from session
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
		unset($_SESSION['meta_multiple_dates']);
		unset($_SESSION['meta_duration']);
		unset($_SESSION['meta_coordinator']);
		unset($_SESSION['meta_provider']);
		unset($_SESSION['meta_unpublishdate']);
		unset($_SESSION['meta_competence']);
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
	
	if (!check_provider_role($id)) {
		die("Access denied!");
	}

	echo $OUTPUT->header();

	// $datecourseNr = $DB->count_records('meta_datecourse', array("metaid"=>$id)); 
	$datecourses = $DB->get_records_sql("SELECT d.*, c.category FROM {meta_datecourse} d left join {course} c on c.id = d.courseid WHERE metaid = :metaid ORDER BY d.id ASC", array("metaid"=>$id));
	$datecourseNr = count($datecourses);

	$uselessCounter = 0;

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

