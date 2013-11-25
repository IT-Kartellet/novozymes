<?php

// functions
/**
 * @param fullname
 * @param shortname
 * @param categoryid
 * @return courseid
 */
function create_new_course($fullname, $shortname, $categoryid, $startdate = 0 , $language = "") {
  global $DB;
  $category = $DB->get_record('course_categories', array('id'=>$categoryid), '*', MUST_EXIST);

  $course = new stdClass;
  $course->fullname = $fullname;
  $course->shortname = $shortname;
  $course->startdate = $startdate;
  $course->lang = $language;
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

// enrols a coordinator in a course with a teacher role
function add_coordinator($user_id, $course_id) {
    global $DB;
    $coursecontext = context_course::instance($course_id);

    $enrol = $DB->get_record('enrol', array(
        'courseid' => $course_id,
        'enrol' => 'manual'
        )
    );

    if ($enrol) {
        $conds = array(
            'enrolid' => $enrol->id,
            'userid' => $user_id
        );

        $roles = array(
            'roleid' => 3,
            'contextid' => $coursecontext->id,
            'userid' => $user_id    
        );

        if (!$DB->record_exists('user_enrolments', $conds)) {
            $DB->insert_record('user_enrolments', $conds);
            $DB->insert_record('role_assignments', $roles);

        }
    }
  }