 <?php

//not used anywhere for the moment;
require_once('../../config.php');
require_once("$CFG->libdir/formslib.php");
require_once('metacourse_form.php');
require_once('lib.php');

require_login();
require_capability('moodle/course:create', context_system::instance());


$id = optional_param('id', -1, PARAM_INT);

$PAGE->set_context(get_system_context());
$PAGE->set_pagelayout('admin');
$URL = '/moodle/blocks/metacourse/edit_metacourse.php';
// default id, just see the list of courses
if ($id == -1) {
	$PAGE->set_title("List of current courses");
	$PAGE->set_heading("Moodle Custom Courses");
	$PAGE->set_url($CFG->wwwroot."/blocks/metacourse/edit_metacourse.php");
	echo $OUTPUT->header();

	require_once('list_metacourses.php');

// add a new course
}else if($id == 0){
	$PAGE->set_title("Add course");
	$PAGE->set_heading("Add course");
	$PAGE->set_url($CFG->wwwroot."/blocks/metacourse/edit_metacourse.php?id=0");
	echo $OUTPUT->header();

	$mform = new metacourse_form();

	//the id of the metacourse
	$data = new stdClass();
	$data->id = $id;
	$mform->set_data($data);

	if ($mform->is_cancelled()) {
	 	//nothing to do here.
	  	redirect($URL ."?id=-1", 'Your action was canceled!');

	} else if ($fromform = $mform->get_data()) {
		try {
			$meta = new stdClass();
			$meta->name = $fromform->meta['name'];
			$meta->purpose = $fromform->meta['purpose'];
			$meta->target = $fromform->meta['target'];
			$meta->content = $fromform->meta['content'];
			$meta->instructors = $fromform->meta['instructors'];
			$meta->comment = $fromform->meta['comment'];
			$meta->coordinator = $fromform->meta['coordinator'];
			$meta->provider = $fromform->meta['provider'];
			$meta->timemodified = time();

		  	$metaid = $DB->insert_record('meta_course', $meta);
		  } catch(Exception $e){
		  	var_dump($e);
		  }
	  	$datecourses = array();
	  	foreach ($fromform->meta['datecourse'] as $key => $course) {
	  		try {
		  		$dc = new stdClass();
		  		$dc->metaid = $metaid;
		  		$dc->startdate = $fromform->{"datecourse_timestart_".$key};
		  		$dc->enddate = $fromform->{"datecourse_timeend_".$key};
		  		$dc->location = $course['location'];
		  		$dc->lang = $course['language'];
		  		$dc->price = $course['price'];
		  		$dc->total_places = $course['places'];
		  		$dc->free_places = $course['places'];
		  		$dc->open = 1;
		  		$dc->timemodified = time();
		  		$datecourseid = $DB->insert_record('meta_datecourse', $dc);

		  		$courseName = $meta->name."-".$dc->lang."-".$datecourseid;
				create_new_course($courseName,$courseName, 1);
			} catch (Exception $e){
				var_dump($e);
			}

	  	}

	  	redirect($URL ."?id=-1",'Your course was added!' );
	} else {
		//if data not valid

		$toform = $mform->get_data();

		$mform->set_data(null);
		$mform->display();

	}

//edit a course
} else {
	if (!check_provider_role($id)) {
		print_r("SSSSSS");
		die("Access denied!");
	} else {
		print_r("expression");
	}

	$PAGE->set_title("Edit course");
	$PAGE->set_heading("Edit course");
	$PAGE->set_url($CFG->wwwroot."/blocks/metacourse/edit_metacourse.php?id=$id");
	echo $OUTPUT->header();

	$mform = new metacourse_form();
	$data = $DB->get_records_sql("SELECT * FROM {meta_course} WHERE id = :id", array("id"=>$id));
	


	$data[$id]->purpose = array('text'=>$data[$id]->purpose);
	$data[$id]->content = array('text'=>$data[$id]->content);
	$mform->set_data($data[$id]);

	if ($mform->is_cancelled()) {
    	//noting to do here. Go back;
    	$id = 0;
	  	redirect($URL ."?id=-1", 'Your action was canceled!');

	} else if ($fromform = $mform->get_data()) {
		$meta = new stdClass();
		$meta->id = $fromform->id;
		$meta->name = $fromform->meta['name'];
		$meta->purpose = $fromform->meta['purpose'];
		$meta->target = $fromform->meta['target'];
		$meta->content = $fromform->meta['content'];
		$meta->instructors = $fromform->meta['instructors'];
		$meta->comment = $fromform->meta['comment'];
		$meta->coordinator = $fromform->meta['coordinator'];
		$meta->provider = $fromform->meta['provider'];
		$meta->timemodified = time();

		$DB->update_record('meta_course', $meta);
	  	redirect($URL ."?id=-1",'Your course was updated!');

	} else {
		$toform = $mform->get_data();
		$mform->set_data(null);
		$mform->display();
	}

}

echo $OUTPUT->footer();



