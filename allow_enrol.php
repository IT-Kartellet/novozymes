<?php
require_once('../../config.php');
require_once('lib.php');
require_once("$CFG->libdir/tablelib.php");
require_once("$CFG->libdir/moodlelib.php");
require_once($CFG->dirroot . '/' . $CFG->admin . '/roles/lib.php');


require_login();

$PAGE->set_context(get_system_context());
$PAGE->set_pagelayout('admin');
$PAGE->requires->jquery();
$PAGE->requires->js(new moodle_url('js/core.js'));
$URL = '/moodle/blocks/metacourse/enrol_others_into_course.php';

$PAGE->set_title("Enrol others");
$PAGE->set_heading("Moodle Custom Courses");
$PAGE->set_url($CFG->wwwroot."/blocks/metacourse/enrol_others_into_course.php");
$PAGE->navbar->ignore_active();
$PAGE->navbar->add("Enrol others", new moodle_url('/blocks/metacourse/enrol_others_into_course.php'));

echo $OUTPUT->header();
//used to hide the buttons for adding new courses;
$teacher = has_capability("moodle/course:create", get_system_context());

global $DB, $USER, $PAGE, $CFG;

echo html_writer::tag('h1', "Who can enrol you?", array());
echo html_writer::start_tag('div',array('id' => 'meta_wrapper'));

echo html_writer::tag('h3', "Users who are allowed to enrol you into courses:");

$can_enroll_me = $DB->get_records_sql("select a.*, e.firstname, e.lastname from {meta_allow_enrol} a join {user} e on a.canenrol = e.id where a.canbeenrolled = :canbeenrolled order by e.firstname asc", array("canbeenrolled"=>$USER->id));

?>
<select id="canenroll">
	<?php foreach ($can_enroll_me as $key => $user) { ?>
		<option value="<?php echo $key; ?>"><?php echo $user->firstname . " " . $user->lastname; ?></option>
	<?php } ?>
</select>
<?php

echo "<input type='button' id='removeHim' value='Remove him'>";

echo html_writer::tag('h3', "List of users:");

$users = $DB->get_records_sql("SELECT * FROM {user} where id <> 1 order by firstname");

$users = array_filter($users, function($a) use ($can_enroll_me){
	foreach ($can_enroll_me as $ce) {
		if ($ce->canenrol == $a->id) {
			return false;
		}
	}
	return true;
});
?>
<select id="cantenroll">
	<?php foreach ($users as $key => $user) { ?>
		<option value="<?php echo $key; ?>"><?php echo $user->firstname . " " . $user->lastname; ?></option>
	<?php } ?>

</select>
<?php

echo "<input type='button' id='allowHim' value='Allow him'>";

echo html_writer::end_tag('div');

echo $OUTPUT->footer();