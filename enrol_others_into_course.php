<?php
require_once('../../config.php');
require_once('lib.php');
require_once("$CFG->libdir/tablelib.php");
require_once("$CFG->libdir/moodlelib.php");
require_once($CFG->dirroot . '/' . $CFG->admin . '/roles/lib.php');


require_login();

$PAGE->set_context(get_system_context());
$PAGE->set_pagelayout('admin');
$PAGE->requires->js(new moodle_url('/lib/jquery/jquery-1.9.1.min.js'));
$PAGE->requires->js(new moodle_url('js/core.js'));
$URL = '/moodle/blocks/metacourse/enrol_others_into_course.php';

$PAGE->set_title("Enrol others");
$PAGE->set_heading("Moodle Custom Courses");
$PAGE->set_url($CFG->wwwroot."/blocks/metacourse/enrol_others_into_course.php");
$PAGE->navbar->ignore_active();
$PAGE->navbar->add("Enrol others", new moodle_url('/blocks/metacourse/enrol_others_into_course.php'));

$PAGE->requires->js(new moodle_url('/lib/jquery/jquery-1.9.1.min.js'));

$courseid = optional_param("courseid",0,PARAM_INT);

echo $OUTPUT->header();
//used to hide the buttons for adding new courses;
$teacher = has_capability("moodle/course:create", get_system_context());

global $DB, $USER, $PAGE, $CFG;

echo html_writer::tag('h1', "Enrol others", array('id' => 'course_header', 'class' => 'main'));
echo html_writer::start_tag('div',array('id' => 'meta_wrapper'));

echo html_writer::tag('h3', "Users you can enrol:");

// select only the ones that allow you;
// $users = $DB->get_records_sql("select e.id, a.canenrol, a.canbeenrolled, e.firstname, e.lastname from {meta_allow_enrol} a join {user} e on canbeenrolled = e.id where canenrol = :id", array("id"=>$USER->id));
$users= $DB->get_records_sql("SELECT * from {user} where id NOT IN (:guest, :own)", array("guest"=>1, "own"=> $USER->id));
$users = array_filter($users, function($user) use ($courseid){
	return check_if_not_enrolled($user->id,$courseid);
});
if (count($users) > 0) { ?>
	<select id="icanenrol">
		<?php foreach ($users as $key => $user) { ?>
			<option value="<?php echo $key; ?>"><?php echo $user->firstname . " " . $user->lastname; ?></option>
		<?php } ?>
	</select>
	<input type="hidden" id="courseID" value = "<?php echo $courseid ?>" />
	<input type='button' id='enrolHim' value='Enrol'>
<?php }  else {
	echo "You can't enrol anyone right now";
}


echo html_writer::end_tag('div');

echo $OUTPUT->footer();