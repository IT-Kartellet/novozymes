<?php
require_once('../../config.php');
require_once('lib.php');
require_once("$CFG->libdir/tablelib.php");
require_once("$CFG->libdir/moodlelib.php");
require_once($CFG->dirroot . '/' . $CFG->admin . '/roles/lib.php');

global $DB, $USER, $PAGE, $CFG;

require_login();

$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('admin');
$PAGE->requires->jquery();
$PAGE->requires->js(new moodle_url('js/core.js'));
$PAGE->requires->js(new moodle_url('js/enrol.js'));
$URL = '/moodle/blocks/metacourse/enrol_others_into_course.php';

$courseid = optional_param("courseid",0,PARAM_INT);

add_to_log($courseid, 'metacourse', 'enrol others', $URL, '', 0, $USER->id);

$PAGE->set_title("Sign others up");
$PAGE->set_heading("Moodle Custom Courses");
$PAGE->set_url($CFG->wwwroot."/blocks/metacourse/enrol_others_into_course.php");
$PAGE->navbar->add("List of courses", new moodle_url('/blocks/metacourse/list_metacourses.php'));

$refferal = htmlentities($_SERVER["HTTP_REFERER"]);
$murl = explode("/blocks/", $refferal);
$PAGE->navbar->add("View course", new moodle_url('/blocks/'. $murl[1]));
$PAGE->navbar->add("Sign others up", new moodle_url('/blocks/metacourse/enrol_others_into_course.php?courseid='.$courseid));

echo $OUTPUT->header();
echo html_writer::tag('h1', "Sign others up", array('id' => 'course_header', 'class' => 'main'));
echo html_writer::start_tag('div',array('id' => 'meta_wrapper'));

$datecourse = $DB->get_record('meta_datecourse', array(
    'courseid' => $courseid,
));
$metacourse = $DB->get_record('meta_course', array('id' => $datecourse->metaid));
$isCoordinator = $metacourse->coordinator === $USER->id || $datecourse->coordinator === $USER->id;

list($enrolled_users, $not_enrolled_users, $waiting_users) = get_datecourse_users($courseid);

$table = new html_table();
$table->id = 'enrol_info';

$table->data[] = array('Seats', $datecourse->elearning ? get_string('no_limit', 'block_metacourse') : $datecourse->total_places);
$table->data[] = array('Currently signed up', count($enrolled_users));
$table->data[] = array('Waiting list', count($waiting_users));

echo html_writer::table($table);
?>

<span>Select user role: &nbsp; </span>
<select name="user_role_enrol" id="enrol_role">
	<option value="student" id='enrol_student'>Employee</option>
	<option value="teacher" id='enrol_teacher'>Teacher</option>
</select>

<form id="assignform" method="post" ><div>
  <input type="hidden" name="sesskey" value="<?php echo sesskey() ?>" />
  <input type="hidden" id="manual_enrol" value = "<?php echo ($datecourse->manual_enrol===null || $datecourse->manual_enrol == 0 ? '0' : '1') ?>" />

  <table id="assigningrole" summary="" class="admintable roleassigntable generaltable" cellspacing="0">
    <tr>
      <td id="existingcell">
          <p><label for="removeselect">Signed up users</label></p>
          <?php output_users_for_enrolment($enrolled_users, 'remove', 15); ?>
          <p>
		    <label for="waitingselect">Waiting users</label>
			<?php if (($isCoordinator || is_siteadmin($USER)) && $datecourse->manual_enrol !== null && $datecourse->manual_enrol != 0 ): ?>
			  <input name="promote" id="promote" type="submit" disabled=true style="float:right; margin-top:-31px; margin-bottom:3px" value="<?php echo '&#9650;'.get_string('promote', 'block_metacourse'); ?>" title="<?php print_string('promote', 'block_metacourse'); ?>" />
			  <input type="hidden" id="courseID" value = "<?php echo $courseid ?>" />
			<?php endif; ?>
		  </p>
    	  <?php output_users_for_enrolment($waiting_users, 'waiting', 4); ?>
          <div class="search_filter">
				<label for="removeselect_searchtext">Search</label>
				<input type="text" name="removeselect_searchtext" id="removeselect_searchtext" size="15" value="">
				<input type="button" value="Clear" id="removeselect_clearbutton" >
		  </div>
      </td>
      <td id="buttonscell">
          <div id="addcontrols">
              <input name="add" id="add" type="submit" disabled=true value="<?php echo $OUTPUT->larrow().'&nbsp;'.get_string('signup', 'block_metacourse'); ?>" title="<?php print_string('signup', 'block_metacourse'); ?>" /><br />
              <input type="hidden" id="courseID" value = "<?php echo $courseid ?>" />
          </div>
          <?php if ($isCoordinator || is_siteadmin($USER)): ?>
            <div id="removecontrols">
                <input name="remove" id="remove" type="submit" disabled=true value="<?php echo get_string('cancel').'&nbsp;'.$OUTPUT->rarrow(); ?>" title="<?php print_string('cancel'); ?>" />
                <input type="hidden" id="courseID" value = "<?php echo $courseid ?>" />
            </div>
          <?php endif; ?>
      </td>
      <td id="potentialcell">
          <p><label for="addselect">Users not signed up</label></p>
          <?php output_users_for_enrolment($not_enrolled_users, 'add'); ?>
          <div class="search_filter">
				<label for="addselect_searchtext">Search</label>
				<input type="text" name="addselect_searchtext" id="addselect_searchtext" size="15" value="">
				<input type="button" value="Clear" id="addselect_clearbutton" >
		  </div>
      </td>
    </tr>
  </table>
</div></form>

<?php

if (is_siteadmin()) {
  echo '<span>Send signup / unenroll email: </span><input type="checkbox" checked="checked" id="sendEmail" />';
}

echo html_writer::end_tag('div');

echo $OUTPUT->footer();

function output_users_for_enrolment($users, $action, $size = 22) {
	echo "<div class='userselector' id='{$action}_select_wrapper' >";
	echo "<select name='{$action}select[]' id='{$action}select' multiple='multiple' size='$size' >";

	foreach ($users as $id => $user) {
		echo "<option value='". $id ."'> $user->firstname $user->lastname ($user->email) </option>";
	}
	echo "</select></div>";
}
