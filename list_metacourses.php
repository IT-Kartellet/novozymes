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
$PAGE->navbar->add(get_string('frontpagecourselist'), new moodle_url('/blocks/metacourse/list_metacourses.php'));

$PAGE->requires->js(new moodle_url('/lib/jquery/jquery-1.9.1.min.js'));
$PAGE->requires->js(new moodle_url('js/dataTables.js'));
$PAGE->requires->js(new moodle_url('js/dataTables_start.js'));

echo $OUTPUT->header();

//used to hide the buttons for adding new courses;
$teacher = has_capability("moodle/course:create", get_system_context());

global $DB, $USER, $PAGE, $CFG;

echo html_writer::tag('h1', get_string('frontpagecourselist'), array('id' => 'course_header', 'class' => 'main'));
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
	$table->head = array(get_string('course'), get_string('administrator'), get_string('provider','block_metacourse'), get_string('coursestart'), get_string('action'));

} else {
	$table->head = array(get_string('course'), get_string('provider','block_metacourse'), get_string('coursestart'));
}

foreach ($metacourses as $key => $course) {
	$datecourses = $DB->get_records_sql("SELECT * FROM {meta_datecourse} where metaid = :id", array("id"=>$course->id));

	$deleteCourse = new single_button(new moodle_url("/blocks/metacourse/api.php", array("deleteMeta"=>$key)), "", 'post');
	$deleteCourse->tooltip = "Delete course";
	$deleteCourse->class = "delete_course_btn";

	$editCourse = new single_button(new moodle_url("/blocks/metacourse/add_metacourse.php", array("id"=>$key)), "", 'post');
	$editCourse->tooltip = "Edit course";
	$editCourse->class = "edit_course_btn";

	$exportExcel = new single_button(new moodle_url("/blocks/metacourse/api.php", array("exportExcel"=>$key)), "", 'post');
	$exportExcel->tooltip = "Export .xls";
	$exportExcel->class = "export_course_btn";

	// count the number of users already enrolled in the course
	$sql = "select count(distinct ue.userid) as nr_users 
		from {enrol} e join {user_enrolments} ue 
		on e.id = ue.enrolid where courseid in (";

	foreach ($datecourses as $k => $dc) {
			$sql .= $dc->courseid . ",";
	}

	$sql = substr($sql, 0, -1); // remove the last comma
	$sql .= ") and e.roleid = 5";
	$nr_enrolled = $DB->get_records_sql($sql);
	$nr_enrolled = reset($nr_enrolled);
	$nr_enrolled->nr_users--; // substract the coordinator of the course


	$deleteCourse->add_confirm_action("Are you sure you want to delete it?  There are $nr_enrolled->nr_users students enrolled in this course.");
	
	if (!empty($course->localname) && (current_language() == $course->localname_lang)) {
		$link = html_writer::link(new moodle_url('/blocks/metacourse/view_metacourse.php', array('id'=>$key)), html_entity_decode($course->localname));
	} else {
		$link = html_writer::link(new moodle_url('/blocks/metacourse/view_metacourse.php', array('id'=>$key)), html_entity_decode($course->name));
	}
	$coordinator = $course->firstname." ".$course->lastname . " &lt;" . $course->email . "&gt;";
	$provider = $course->provider;

	$dates = "<ul>";

	$count_datecourses = 0;
	foreach ($datecourses as $key => $datecourse) {
		if (!$teacher) {
			if ($datecourse->publishdate < time()) {
				$dates .= "<li>" . date("j/m/Y",$datecourse->startdate) . "</li>";
				$count_datecourses++;
			}
		} else {
			$dates .= "<li>" . date("j/m/Y",$datecourse->startdate) . "</li>";
			$count_datecourses++;
		}
		
	}
	$dates .= "</ul>";

	if ($teacher && $count_datecourses) {
		$table->data[] = array($link, $coordinator, $provider, $dates, $OUTPUT->render($editCourse). $OUTPUT->render($exportExcel) . $OUTPUT->render($deleteCourse));
	} else {
		if ($count_datecourses) {
			$table->data[] = array($link, $provider, $dates);
		}
	}
}

$newCourse = new single_button(new moodle_url('/blocks/metacourse/add_metacourse.php', array()), get_string('addnewcourse'));
$newCourse->class = "new_course_btn";
$newCourse->tooltip = "New course";

$editTerms = new single_button(new moodle_url('/blocks/metacourse/edit_terms.php', array()), get_string('settings'));
$editTerms->class = "settings_btn";
$editTerms->tooltip = "Settings";

if ($teacher) {
	echo $OUTPUT->render($newCourse);
	echo $OUTPUT->render($editTerms);
}
echo html_writer::table($table);

echo html_writer::end_tag('div');

echo $OUTPUT->footer();
