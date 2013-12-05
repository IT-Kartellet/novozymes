<?php
require_once('../../config.php');
require_once('lib.php');
require_once("$CFG->libdir/tablelib.php");
require_once("$CFG->libdir/moodlelib.php");

require_login();

$PAGE->set_context(get_system_context());
$PAGE->set_pagelayout('admin');
$URL = '/moodle/blocks/metacourse/list_metacourses.php';

$PAGE->set_title("List of current courses");
$PAGE->set_heading("Moodle Custom Courses");
$PAGE->set_url($CFG->wwwroot."/blocks/metacourse/list_metacourses.php");
$PAGE->navbar->ignore_active();
$PAGE->navbar->add("List courses", new moodle_url('/blocks/metacourse/list_metacourses.php'));

echo $OUTPUT->header();

//used to hide the buttons for adding new courses;
$teacher = has_capability("moodle/course:create", get_system_context());

global $DB, $USER, $PAGE, $CFG;
echo html_writer::tag('h1', 'List of current courses', array('id' => 'course_header', 'class' => 'main'));
echo html_writer::start_tag('div',array('id' => 'meta_wrapper'));
// $metacourses = $DB->get_records_sql("SELECT c.id, c.name, c.provider, u.firstname, u.lastname, u.email FROM {meta_course} c join {user} u on c.coordinator = u.id order by c.provider asc");
$metacourses = $DB->get_records_sql("SELECT d.*, pr.provider FROM {meta_providers} pr join 
									(SELECT c.id, c.localname,c.localname_lang, c.name, c.provider as providerid, u.firstname, u.lastname, u.email 
									FROM {meta_course} c join {user} u on c.coordinator = u.id order by c.provider asc) d 
									on pr.id = d.providerid");

$table = new html_table();
$table->id = "meta_table";
$table->width = "100%";
$table->tablealign = "center";
if ($teacher) {
	$table->head = array('Course name', 'Coordinator', 'Provider', 'Starting dates', 'Action');

} else {
	$table->head = array('Course name', 'Coordinator', 'Provider', 'Starting dates');
}

foreach ($metacourses as $key => $course) {
	$datecourses = $DB->get_records_sql("SELECT * FROM {meta_datecourse} where metaid = :id", array("id"=>$course->id));

	$deleteCourse = new single_button(new moodle_url("/blocks/metacourse/api.php", array("deleteMeta"=>$key)), "Delete course", 'post');
	$editCourse = new single_button(new moodle_url("/blocks/metacourse/add_metacourse.php", array("id"=>$key)), "Edit course", 'post');

	$deleteCourse->add_confirm_action("Are you sure you want to delete this course?");
	
	if (!empty($course->localname) && (current_language() == $course->localname_lang)) {
		$link = html_writer::link(new moodle_url('/blocks/metacourse/view_metacourse.php', array('id'=>$key)), html_entity_decode($course->localname));
	} else {
		$link = html_writer::link(new moodle_url('/blocks/metacourse/view_metacourse.php', array('id'=>$key)), html_entity_decode($course->name));
	}
	$coordinator = $course->firstname." ".$course->lastname . " &lt;" . $course->email . "&gt;";
	$provider = $course->provider;

	$dates = "<ul>";
	foreach ($datecourses as $key => $datecourse) {
		$dates .= "<li>" . date("j/m/Y",$datecourse->startdate) . "</li>";
	}
	$dates .= "</ul>";

	if ($teacher) {
		$table->data[] = array($link, $coordinator, $provider, $dates, $OUTPUT->render($deleteCourse) . $OUTPUT->render($editCourse));
	} else {
		$table->data[] = array($link, $coordinator, $provider, $dates);
	}
}

echo html_writer::table($table);
$newCourse = new single_button(new moodle_url('/blocks/metacourse/add_metacourse.php', array()), "Add new course");
$editTerms = new single_button(new moodle_url('/blocks/metacourse/edit_terms.php', array()), "Settings");

if ($teacher) {
	echo $OUTPUT->render($newCourse);
	echo $OUTPUT->render($editTerms);
}

echo html_writer::end_tag('div');


echo $OUTPUT->footer();
