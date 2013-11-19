<?php
require_once('../../config.php');
require_once('lib.php');

require_login();

$PAGE->set_context(get_system_context());
$PAGE->set_pagelayout('admin');
$URL = '/moodle/blocks/metacourse/list_metacourses.php';

$PAGE->set_title("List of current courses");
$PAGE->set_heading("Moodle Custom Courses");
$PAGE->set_url($CFG->wwwroot."/blocks/metacourse/list_metacourses.php");
echo $OUTPUT->header();

global $DB;
echo html_writer::tag('h1', 'List of current courses', array('id' => 'course_header', 'class' => 'list_header'));

$metacourses = $DB->get_records_sql("SELECT * FROM {meta_course}");
foreach ($metacourses as $key => $course) {
	echo html_writer::link(new moodle_url('/blocks/metacourse/view_metacourse.php', array('id'=>$key)), $course->name) . "<br />";
}

echo "<br /><br /><br />";
echo html_writer::link(new moodle_url('/blocks/metacourse/add_metacourse.php', null, "Add a new metacourse") . "<br />";

echo $OUTPUT->footer();
