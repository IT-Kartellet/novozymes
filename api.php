<?php

require_once('../../config.php');
require_once("$CFG->libdir/formslib.php");
require_once('metacourse_form.php');

require_login();

$id = optional_param('id', -1, PARAM_INT);
$course = optional_param('addCourse',"", PARAM_RAW);

$PAGE->set_context(get_system_context());
$PAGE->set_pagelayout('admin');
$URL = '/moodle/blocks/metacourse/api.php';

if ($course!="") {
	$course = json_decode($course, true);
	$response = array();
	try {
		//create the metacourse and insert it;
		$metacourse = new stdClass();
		$metacourse->name = $course['name'];
		$metacourse->purpose = $course['purpose'];
		$metacourse->target = $course['target'];
		$metacourse->content = $course['content'];
		$metacourse->instructors = $course['instructor'];
		$metacourse->comment = $course['comment'];
		$metacourse->coordinator = $course['coordinator'];
		$metacourse->provider = $course['provider'];
		$metacourse->timemodified = time();

		$meta_id = $DB->insert_record('metacourse', $metacourse);

		$response[] = array('meta_id',$meta_id);
	} catch (Exception $e){
		$response[] = array('exception META',$e);
	}

	try{
		//create and insert the datecourses;
		for ($i = 0; $i < count($course['dateCourses'])-1; $i++) {
			$datecourse = new stdClass();
			$datecourse->metaid = $meta_id;
			$datecourse->startdate = strtotime($course['dateCourses'][$i][0] . "00:01:00");
			$datecourse->endate = strtotime($course['dateCourses'][$i][1] . "00:01:00");
			$datecourse->location = $course['dateCourses'][$i][2];
			$datecourse->lang = $course['dateCourses'][$i][3];
			$datecourse->price = $course['dateCourses'][$i][4];
			$datecourse->total_places = $course['dateCourses'][$i][5];
			$datecourse->free_places = $course['dateCourses'][$i][5];
			$datecourse->open = 1;
			$datecourse->timemodified = time();

			$datecourse_id = $DB->insert_record('datecourse',$datecourse);
			$courseName = $metacourse->name."-".$datecourse->lang."-".$datecourse_id;
			create_new_course($courseName,$courseName, 1);
			$response[] = array('datecourse_id',$datecourse);
		}
	} catch (Exception $e){
		$response[] = array('exception DATECOURSE',$e);
	}

	echo json_encode($response);
}




// functions
/**
 * @param $fullname
 * @param $shortname
 * @param $categoryid
 */
function create_new_course($fullname, $shortname, $categoryid) {
  global $DB;
  $category = $DB->get_record('course_categories', array('id'=>$categoryid), '*', MUST_EXIST);

  $course = new stdClass;
  $course->fullname = $fullname;
  $course->shortname = $shortname;
  $course->category = $category->id;
  $course->sortorder = 0;
  $course->timecreated  = time();
  $course->timemodified = $course->timecreated;
  $course->visible = 1;

  $courseid = $DB->insert_record('course', $course);

  $category->coursecount++;
  $DB->update_record('course_categories', $category);

  return $courseid;
}