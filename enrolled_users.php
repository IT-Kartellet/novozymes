<?php
require_once('../../config.php');
require_once("$CFG->libdir/formslib.php");
require_once('datecourse_form.php');
require_once('lib.php');

require_login();
require_capability('moodle/course:create', context_system::instance());

//users must be trusted
$id = optional_param('id', 0, PARAM_INT);

$PAGE->set_context(get_system_context());
$PAGE->set_pagelayout('admin');
$PAGE->set_url($CFG->wwwroot."/blocks/metacourse/enrolled_users.php");
$PAGE->set_title("Enrolled users");
$PAGE->set_heading("Enrolled users");
$PAGE->navbar->ignore_active();
$PAGE->navbar->add("List courses", new moodle_url('/blocks/metacourse/list_metacourses.php'));
$PAGE->navbar->add("Enrolled users", new moodle_url('/blocks/metacourse/enrolled_users.php'));

global $DB, $USER, $PAGE, $CFG;

echo $OUTPUT->header();
if ($id) {
	echo html_writer::tag('h1', 'Enrolled users', array('id' => 'course_header', 'class' => 'main'));

	echo html_writer::start_tag('div',array('id' => 'meta_wrapper'));
	$datecourse = $DB->get_records_sql("SELECT * FROM {meta_datecourse} where id = :id",array("id"=>$id));
	$datecourse = reset($datecourse);
	if ($datecourse) {
		$enrolled_users = $DB->get_records_sql("select * from mdl_user u join (select md.courseid as cid, j.* from mdl_meta_datecourse md join 
			(select ue.userid, e.courseid from mdl_user_enrolments ue join mdl_enrol e on ue.enrolid = e.id) j on 
			md.courseid = j.courseid where j.courseid = :courseid) a on u.id = a.userid",array("courseid"=>$datecourse->courseid));

		$table = new html_table();
		$table->id = "meta_table";
		$table->width = "100%";
		$table->tablealign = "center";
		$table->head = array('Fullname', 'Username', 'Email', 'City', 'Country', 'Last access');

		foreach ($enrolled_users as $key => $user) {

			$table->data[] = array($user->firstname . $user->lastname, $user->username, $user->email, $user->city, $user->country, date("j/m/Y - h:i A",$user->lastaccess));

		}

		echo html_writer::table($table);

	}
		echo html_writer::end_tag('div');
}



echo $OUTPUT->footer();
