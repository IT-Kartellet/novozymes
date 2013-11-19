<?php

// functions
/**
 * @param fullname
 * @param shortname
 * @param categoryid
 * @return courseid
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