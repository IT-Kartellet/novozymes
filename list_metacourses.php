<?php
require_once('../../config.php');
require_once('lib.php');
require_once("$CFG->libdir/tablelib.php");
require_once("$CFG->libdir/moodlelib.php");

require_login();

$category = optional_param("category",0,PARAM_INT);
$competence = optional_param("competence",0,PARAM_INT);

$PAGE->set_context(context_system::instance() );
$PAGE->set_pagelayout('admin');
$URL = '/moodle/blocks/metacourse/list_metacourses.php';

$PAGE->set_title("List of current courses");
$PAGE->set_heading("Moodle Custom Courses");
$PAGE->set_url($CFG->wwwroot."/blocks/metacourse/list_metacourses.php");
$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('frontpagecourselist'), new moodle_url('/blocks/metacourse/list_metacourses.php'));

$PAGE->requires->jquery();
$PAGE->requires->js(new moodle_url('js/dataTables.js'));
$PAGE->requires->js(new moodle_url('js/dataTables_start.js'));
$PAGE->requires->js(new moodle_url('js/core.js'));

echo $OUTPUT->header();
//used to hide the buttons for adding new courses;
$teacher = has_capability("moodle/course:create", context_system::instance() );

global $DB, $USER, $PAGE, $CFG;

if ($category != 0) {
	$cat = $DB->get_record("meta_category", array("id"=>$category));
	echo html_writer::tag('h1', get_string('coursesfor','block_metacourse') . " " .$cat->name, array('id' => 'course_header', 'class' => 'main'));
} else {
	echo html_writer::tag('h1', get_string('listofcourses', 'block_metacourse'), array('id' => 'course_header', 'class' => 'main'));
}

echo html_writer::start_tag('div',array('id' => 'meta_wrapper'));

if ($category != 0 || $competence != 0) {
	$metacourses = get_courses_in_category($category, $competence);
} else {
	$metacourses = $DB->get_records_sql("SELECT d.*, pr.provider FROM {meta_providers} pr join 
									(SELECT c.id, c.localname,c.localname_lang, c.name, c.provider as providerid, u.username, u.firstname, u.lastname, u.email, c.unpublishdate 
									FROM {meta_course} c left outer join {user} u on c.coordinator = u.id order by c.provider asc) d 
									on pr.id = d.providerid");
}
$table = new html_table();
$table->id = "meta_table";
$table->width = "100%";
$table->tablealign = "center";
if ($teacher) {
	$table->head = array(get_string('course'), get_string('provider','block_metacourse'), get_string("languages","block_metacourse"),get_string("countries", "block_metacourse"), get_string("published", "block_metacourse"), get_string('action'));

} else {
	$table->head = array(get_string('course'), get_string('provider','block_metacourse'),get_string("languages","block_metacourse"), get_string("countries", "block_metacourse"));
}

foreach ($metacourses as $key => $course) {
	$isProvider = check_provider_role($course->id);
	$isPublished = ($course->unpublishdate > time());
	//don't display if they are overdue

	if (!$isPublished && !$teacher) {
		continue;
	}

	$languages = $DB->get_records_sql("SELECT DISTINCT ml.id, ml.language from {meta_datecourse} md JOIN {meta_languages} ml on md.lang = ml.id where metaid = :metaid",
		array("metaid"=>$key));
	$datecourses = $DB->get_records_sql("SELECT * FROM {meta_datecourse} where metaid = :id", array("id"=>$course->id));
	$countries = $DB->get_records_sql("select a.id, mct.country from {meta_countries} mct join (select md.id, md.country from {meta_course} mc join {meta_datecourse} md on mc.id = md.metaid where md.metaid = :metaid) a on mct.id = a.country", array("metaid"=>$key));
	
	$deleteCourse = new single_button(new moodle_url("/blocks/metacourse/api.php", array("deleteMeta"=>$key)), "", 'post');
	$deleteCourse->tooltip = "Delete course";
	$deleteCourse->class = "delete_course_btn icon-trash";

	$editCourse = new single_button(new moodle_url("/blocks/metacourse/add_metacourse.php", array("id"=>$key)), "", 'post');
	$editCourse->tooltip = "Edit course";
	$editCourse->class = "edit_course_btn icon-cog";

	$exportExcel = new single_button(new moodle_url("/blocks/metacourse/api.php", array("exportExcel"=>$key)), "", 'post');
	$exportExcel->tooltip = "Export .xls";
	$exportExcel->class = "export_course_btn icon-export-alt";

	if (!$isProvider) {
		$deleteCourse->disabled = true;
		$editCourse->disabled = true;
		$exportExcel->disabled = true;
	}

	// count the number of users already enrolled in the course
	$sql = "select count(distinct ue.userid) as nr_users 
		from {enrol} e join {user_enrolments} ue 
		on e.id = ue.enrolid where courseid in (";
	
	// print_r($datecourses);
	// print_r($course->id);

	foreach ($datecourses as $k => $dc) {
		if ($dc->courseid) {
			$sql .= $dc->courseid . ",";
		}
	}

	$sql = substr($sql, 0, -1); // remove the last comma
	$sql .= ") and e.roleid = 5";
	try{
		$nr_enrolled = $DB->get_records_sql($sql);
		$nr_enrolled = reset($nr_enrolled);
		$nr_enrolled->nr_users--; // substract the coordinator of the course
		print($course);
		print($nr_enrolled->nr_users);
		echo "<hr />";
	} catch(Exception $e){
		// echo $sql;
	}
	

	$deleteCourse->add_confirm_action("Are you sure you want to delete it?  There are $nr_enrolled->nr_users students enrolled in this course.");

	if (!empty($course->localname) && (current_language() == $course->localname_lang)) {
		$link = html_writer::link(new moodle_url('/blocks/metacourse/view_metacourse.php', array('id'=>$key)), html_entity_decode($course->localname));
	} else {
		$link = html_writer::link(new moodle_url('/blocks/metacourse/view_metacourse.php', array('id'=>$key)), html_entity_decode($course->name));
	}
	$coordinator = strtoupper($course->username);
	$provider = $course->provider;

	$dates = "<ul>";

	$count_datecourses = 0;
	$competence = "";
	foreach ($datecourses as $key => $datecourse) {
		$competence = $datecourse->category;
		$languages[] = $datecourse->lang;
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

	$competence = $DB->get_record("course_categories", array("id"=>$competence));
	$competence = $competence->name;

	$dates .= "</ul>";

	$languages = array_map(function($l){
		return @$l->language;
	}, $languages);
	// remove empty langs
	$languages = array_filter($languages);

	$countries = array_map(function($l){
		return @$l->country;
	}, $countries);
	$countries = array_unique($countries);


	if ($teacher && $count_datecourses) {
		$status = (($isPublished) ? "Yes" : "No");
		if (!$isProvider) {
			$table->data[] = array($link, $provider, rtrim(join("<br>",$languages),','),rtrim(join("<br>",$countries),','), $status ,"");
		} else {
			$table->data[] = array($link, $provider, rtrim(join("<br>",$languages),',') , rtrim(join("<br>",$countries),','), $status,$OUTPUT->render($editCourse). $OUTPUT->render($exportExcel) . $OUTPUT->render($deleteCourse));
		}
	} else {
		if ($count_datecourses) {
			$table->data[] = array($link, $provider, rtrim(join("<br>",$languages),','), rtrim(join("<br>",$countries),','));
		}
	}
}

$newCourse = new single_button(new moodle_url('/blocks/metacourse/add_metacourse.php', array()), get_string('addnewcourse'));
$newCourse->class = "new_course_btn";
$newCourse->tooltip = "New course";

$editTerms = new single_button(new moodle_url('/blocks/metacourse/edit_terms.php', array()), get_string('settings'));
$editTerms->class = "settings_btn";
$editTerms->tooltip = "Settings";

$allowEnrol = new single_button(new moodle_url('/blocks/metacourse/allow_enrol.php', array()), "Enrolment access");
$allowEnrol->class = "who_enrol";
$allowEnrol->tooltip = "Enrolment access";


if ($teacher) {
	echo $OUTPUT->render($newCourse);
	echo $OUTPUT->render($editTerms);
}
//TODO: see what's up with this
// echo $OUTPUT->render($allowEnrol);

$meta_categories = $DB->get_records_sql("select * from {meta_category} order by name asc");
$meta_competences = $DB->get_records_sql("select * from {course_categories} order by name asc");
?>
<div class="filter_courses">
	<h4 class="filter_title">Filters</h4>
	<form id="filters_form" action="/blocks/metacourse/list_metacourses.php">
		<fieldset>
			<label for="category">Employee group</label>
			<select name="category" id="filters" onchange="this.form.submit()">
				<option value="0">All</option>
				<?php foreach ($meta_categories as $key => $cat) { 
					if ($key == $category) { ?>
					<option selected value="<?php echo $cat->id; ?>"><?php echo $cat->name; ?></option>
				<?php } else { ?>
					<option value="<?php echo $cat->id; ?>"><?php echo $cat->name; ?></option>
				<?php } 
				}?>
			</select>
			<label for="competence">Competence</label>
			<select name="competence" id="competence" onchange="this.form.submit()">
				<option value="0">All</option>
				<?php foreach ($meta_competences as $a => $com) { 
					if ($a == $competence) {  ?>
					<option selected value="<?php echo $com->id; ?>"><?php echo $com->name; ?></option>
				<?php } else { ?>
					<option value="<?php echo $com->id; ?>"><?php echo $com->name; ?></option>
				<?php } 
				}?>
			</select>
		</fieldset>
	</form>
</div>

<?php
echo html_writer::table($table);

echo html_writer::end_tag('div');

echo $OUTPUT->footer();