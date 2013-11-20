<?php
require_once('../../config.php');
require_once('lib.php');
require_once("$CFG->libdir/tablelib.php");

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

global $DB;
echo html_writer::tag('h1', 'List of current courses', array('id' => 'course_header', 'class' => 'main'));
echo html_writer::start_tag('div',array('id' => 'meta_wrapper'));

$metacourses = $DB->get_records_sql("SELECT c.id, c.name, c.provider, u.firstname, u.lastname, u.email FROM {meta_course} c join {user} u on c.coordinator = u.id ");

$table = new html_table();
$table->id = "meta_table";
$table->width = "100%";
$table->tablealign = "center";
$table->head = array('Course name', 'Coordinator', 'Provider');

foreach ($metacourses as $key => $course) {
	$link = html_writer::link(new moodle_url('/blocks/metacourse/view_metacourse.php', array('id'=>$key)), $course->name);
	$coordinator = $course->firstname." ".$course->lastname . " &lt;" . $course->email . "&gt;";
	$provider = $course->provider;
	$table->data[] = array($link, $coordinator, $provider);
}

echo html_writer::table($table);
$newCourse = new single_button(new moodle_url('/blocks/metacourse/add_metacourse.php', array()), "Add new metacourse");

echo $OUTPUT->render($newCourse);
echo html_writer::end_tag('div');


echo $OUTPUT->footer();
