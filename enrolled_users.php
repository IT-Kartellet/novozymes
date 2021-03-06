<?php
require_once('../../config.php');
require_once("$CFG->libdir/formslib.php");
require_once('datecourse_form.php');
require_once('lib.php');

require_login();

//users must be trusted
$id = optional_param('id', 0, PARAM_INT);

$PAGE->set_context(context_system::instance());
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
	//enrolled users
	echo html_writer::tag('h1', 'Enrolled users', array('id' => 'course_header', 'class' => 'main'));

	echo html_writer::start_tag('div',array('id' => 'meta_wrapper'));
	$datecourse = $DB->get_records_sql("SELECT * FROM {meta_datecourse} where id = :id",array("id"=>$id));
	$datecourse = reset($datecourse);
	
	if ($datecourse) {
		$context = CONTEXT_COURSE::instance($datecourse->courseid);
	
		list($sql, $params) = get_enrolled_sql($context, '', 0, true);
		$sql = "SELECT u.*, je.* FROM {user} u
		JOIN ($sql) je ON je.id = u.id";
		$course_users = $DB->get_records_sql($sql, $params );
		$students = array();

		foreach($course_users as $id => $user){
			if(user_has_role_assignment($id, 5, $context->id)){
				$students[$id] = $user;
				
			}
		}

		$table = new html_table();
		$table->id = "meta_table";
		$table->width = "100%";
		$table->tablealign = "center";
		$table->head = array('Fullname', 'Username', 'Email', 'City', 'Country', 'Last access');

		foreach ($students as $key => $user) {
			$table->data[] = array($user->firstname ." ". $user->lastname, $user->username, $user->email, $user->city, $user->country, date("j/m/Y - h:i A",$user->lastaccess));
		}

		echo html_writer::table($table);

	}
	echo html_writer::end_tag('div');

	// waiting users
	if ($datecourse) {
		$waiting_users = get_users_on_waitinglist($datecourse->courseid);
		if (count($waiting_users) > 0) {
			echo html_writer::tag('h1', 'Waiting list', array('id' => 'course_header', 'class' => 'main'));

			echo html_writer::start_tag('div',array('id' => 'meta_wrapper'));
			$table = new html_table();
			$table->id = "meta_table";
			$table->width = "100%";
			$table->tablealign = "center";
			$table->head = array('Fullname', 'Username', 'Email', 'City', 'Country', 'Last access');

			foreach ($waiting_users as $key => $user) {

				$table->data[] = array($user->firstname ." ". $user->lastname, $user->username, $user->email, $user->city, $user->country, date("j/m/Y - h:i A",$user->lastaccess));

			}

			echo html_writer::table($table);
			echo html_writer::end_tag('div');
		}	
	}
}
echo $OUTPUT->footer();
