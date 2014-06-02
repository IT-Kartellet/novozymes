<?php
require_once('../../config.php');
require_once('lib.php');
require_once("$CFG->libdir/tablelib.php");
require_once("$CFG->libdir/moodlelib.php");
require_once($CFG->dirroot . '/' . $CFG->admin . '/roles/lib.php');

require_login();

$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('admin');
$PAGE->requires->jquery();
$PAGE->requires->js(new moodle_url('js/core.js'));
$PAGE->requires->js(new moodle_url('js/enrol.js'));
$URL = '/moodle/blocks/metacourse/enrol_others_into_course.php';

$courseid = optional_param("courseid",0,PARAM_INT);

$PAGE->set_title("Enrol others");
$PAGE->set_heading("Moodle Custom Courses");
$PAGE->set_url($CFG->wwwroot."/blocks/metacourse/enrol_others_into_course.php");
$PAGE->navbar->add("List of courses", new moodle_url('/blocks/metacourse/list_metacourses.php'));

$refferal = htmlentities($_SERVER["HTTP_REFERER"]);
$murl = explode("/blocks/", $refferal);
$PAGE->navbar->add("View course", new moodle_url('/blocks/'. $murl[1]));
$PAGE->navbar->add("Enrol others", new moodle_url('/blocks/metacourse/enrol_others_into_course.php?courseid='.$courseid));

echo $OUTPUT->header();

//used to hide the buttons for adding new courses;
$teacher = has_capability("moodle/course:create", context_system::instance());

global $DB, $USER, $PAGE, $CFG;

echo html_writer::tag('h1', "Enrol others", array('id' => 'course_header', 'class' => 'main'));
echo html_writer::start_tag('div',array('id' => 'meta_wrapper'));

// select only the ones that allow you;
// $users = $DB->get_records_sql("select e.id, a.canenrol, a.canbeenrolled, e.firstname, e.lastname from {meta_allow_enrol} a join {user} e on canbeenrolled = e.id where canenrol = :id", array("id"=>$USER->id));
$users= $DB->get_records_sql("SELECT id, firstname, lastname, username, email from {user} where id <> :guest and deleted <> 1 AND email IS NOT NULL AND firstname IS NOT NULL AND email <> '' AND firstname <> '' ORDER BY username ASC", array("guest"=>1));

$context = CONTEXT_COURSE::instance($courseid);
list($sql, $params) = get_enrolled_sql($context, '', 0, true);
$sql = "SELECT u.*, je.* FROM {user} u
		JOIN ($sql) je ON je.id = u.id";
$course_users = $DB->get_records_sql($sql, $params );

$enrolled_users = array();

foreach($course_users as $id => $user){
	if(user_has_role_assignment($id, 5, $context->id)){
		$enrolled_users[$id] = $user;
		//unset($users[$id]);
	}
}

$not_enrolled_users = $users;

?>
<span>Select user role: &nbsp; </span>
<select name="user_role_enrol" id="enrol_role">
	<option value="student" id='enrol_student'>Employee</option>
	<option value="teacher" id='enrol_teacher'>Teacher</option>
</select> 

<form id="assignform" method="post" ><div>
  <input type="hidden" name="sesskey" value="<?php echo sesskey() ?>" />

  <table id="assigningrole" summary="" class="admintable roleassigntable generaltable" cellspacing="0">
    <tr>
      <td id="existingcell">
          <p><label for="removeselect">Enrolled users</label></p>
          <?php output_users_for_enrolment($enrolled_users); ?>
          <div class="search_filter">
				<label for="removeselect_searchtext">Search</label>
				<input type="text" name="removeselect_searchtext" id="removeselect_searchtext" size="15" value="">
				<input type="button" value="Clear" id="removeselect_clearbutton" >
		  </div>
      </td>
      <td id="buttonscell">
          <div id="addcontrols">
              <input name="add" id="add" type="submit" value="<?php echo $OUTPUT->larrow().'&nbsp;'.get_string('add'); ?>" title="<?php print_string('add'); ?>" /><br />
              <input type="hidden" id="courseID" value = "<?php echo $courseid ?>" />
          </div>
          <div id="removecontrols">
              <input name="remove" id="remove" type="submit" value="<?php echo get_string('remove').'&nbsp;'.$OUTPUT->rarrow(); ?>" title="<?php print_string('remove'); ?>" />
              <input type="hidden" id="courseID" value = "<?php echo $courseid ?>" />
          </div>
      </td>
      <td id="potentialcell">
          <p><label for="addselect">Users not enrolled</label></p>
          <?php output_users_for_enrolment($not_enrolled_users, true); ?>
          <div class="search_filter">
				<label for="addselect_searchtext">Search</label>
				<input type="text" name="addselect_searchtext" id="addselect_searchtext" size="15" value="">
				<input type="button" value="Clear" id="addselect_clearbutton" >
		  </div>
      </td>
    </tr>
  </table>
</div></form>
<span>Send enrolment email: </span><input type="checkbox" id="sendEmail" />

<?php

echo html_writer::end_tag('div');

echo $OUTPUT->footer();

function output_users_for_enrolment($users, $add = false, $teacher = false){
	global $USER;
	if ($add) {
		echo "<div class='userselector' id='add_select_wrapper' >";
		echo "<select name='addselect[]' id='addselect' multiple='multiple' size='20' >";

		foreach ($users as $id => $user) {
			echo "<option value='". $id ."'> $user->firstname $user->lastname ($user->email) </option>";
		}
		echo "</select></div>";
	} else {
		echo "<div class='userselector' id='remove_select_wrapper' >";
		echo "<select name='removeselect[]' id='removeselect' multiple='multiple' size='20' >";

		foreach ($users as $id => $user) {
			if(!$teacher){
				echo "<option value='". $id ."'> $user->firstname $user->lastname ($user->email) </option>";
			}elseif($USER->id == $id){
				echo "<option value='". $id ."'> $user->firstname $user->lastname ($user->email) </option>";
			}
		}
		echo "</select></div>";
	}
}

