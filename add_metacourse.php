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

	//the id of the metacourse
	$data = new stdClass();
	$data->id = $id;
	$data->cancellation = array("text"=>get_string("cancellationaccept", "block_metacourse"));
	$mform->set_data($data);

	if ($mform->is_cancelled()) {
	  	redirect($URL, 'Your action was canceled!');

	} else if ($fromform = $mform->get_data()) {
		// redirect($URL, "TESTING THIS THIS");
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
	$langid = $DB->get_record("meta_languages",array("iso"=>$meta->localname_lang));
	$data->localname_lang = $langid->id;
	$data->instructors = $meta->instructors;
	$data->purpose = array("text"=>$meta->purpose);
	$data->content = array("text"=>$meta->content);
	$data->cancellation = array("text"=>$meta->cancellation);
	$data->lodging = array("text"=>$meta->lodging);
	$data->contact = array("text"=>$meta->contact);
	$data->target_description = array("text"=>$meta->target_description);
	$data->targetgroup = array();
	$targets = $DB->get_records_sql("SELECT id from {meta_category} order by name asc");

	$targ = json_decode($meta->target);
	foreach ($targets as $i => $t) {
		$data->targetgroup[$t->id] = 0;
	}

	foreach ($targ as $i => $t) {
		$data->targetgroup[$t] = 1;
	}
	// moodle fucks with this
	echo "<script> var itk_targets = ". json_encode($data->targetgroup) ."</script>";

	$data->comment = array("text"=>$meta->comment);
	$data->multiple_dates = array("text"=>$meta->multiple_dates);
	$data->multipledates = 1;
	$data->coordinator = $meta->coordinator;
	$data->provider = $meta->provider;
	$data->duration['number'] = $meta->duration;
	$data->duration['timeunit'] = $meta->duration_unit;
	$data->unpublishdate = $meta->unpublishdate;

	// get the competence from the dates and use it here
	$one_date = $DB->get_records("meta_datecourse", array("metaid"=>$id));
	$one_date = reset($one_date);
	$data->competence = $one_date->category;

	$mform->set_data($data);

	if ($mform->is_cancelled()) {
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