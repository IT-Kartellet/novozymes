<?php


class enrol_manual_pluginITK extends enrol_plugin{

  public function send_confirmation_email($user, $courseid) {
    global $CFG, $DB;

    $site = get_site();
    $course = $DB->get_record("course",array("id"=>$courseid));
    $supportuser = generate_email_supportuser();

    $data = new stdClass();
    $data->firstname = fullname($user);
    $data->sitename  = format_string($site->fullname);
    $data->admin     = generate_email_signoff();

    $subject = format_string($site->fullname) . ": enrolment confirmation";

    $username = urlencode($user->username);
    $username = str_replace('.', '%2E', $username); // prevent problems with trailing dots
    $data->link  = $CFG->wwwroot;
    $message     = "
      Hi $username,
      We hearby confirm that you have been enrolled into $course->fullname.
      Custom email message will be added here.
    ";
    $messagehtml = text_to_html(get_string('emailconfirmation', '', $data), false, false, true);

    $user->mailformat = 0;  // Always send HTML version as well

    //directly email rather than using the messaging system to ensure its not routed to a popup or jabber
    return email_to_user($user, $supportuser, $subject, $message, $messagehtml);

  }


  /// method to add to the waiting list
  /// method to send mail when available place found.
}


// functions
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

function update_meta_course($metaid, $datecourse, $category = 1){
  global $DB;
  $meta = $DB->get_record("meta_course",array("id"=>$metaid));

  $course = $DB->get_record("course",array("id"=>$datecourse->courseid));

  $oldCategory = $DB->get_record('course_categories', array('id'=>$course->category), '*', MUST_EXIST);
  $newCategory = $DB->get_record('course_categories', array('id'=>$category), '*', MUST_EXIST);

  //TODO: fix the naming and the category
  $updatedCourse = new stdClass();
  $updatedCourse->id = $datecourse->courseid;
  $updatedCourse->fullname = $meta->name."-".$datecourse->lang."-".$datecourse->id;
  $updatedCourse->shortname = $meta->name."-".$datecourse->lang."-".$datecourse->id;
  $updatedCourse->startdate = $datecourse->startdate;
  $updatedCourse->lang = $datecourse->lang;
  $updatedCourse->category = $category;
  $updatedCourse->sortorder = 0;
  $updatedCourse->timemodified = time();

  $DB->update_record("course",$updatedCourse);

  if ($oldCategory != $newCategory) {

    $DB->set_field('course_categories', 'coursecount', $oldCategory->coursecount - 1, array('id'=>$oldCategory->id));
    $DB->set_field('course_categories', 'coursecount', $newCategory->coursecount + 1, array('id'=>$newCategory->id));
  }
  //enrol users from the waiting list if we find available seats
  for ($i=0; $i < $datecourse->free_places; $i++) { 
    enrol_waiting_user($datecourse);
  }

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

        if (!$DB->record_exists('user_enrolments', $conds) || !$DB->record_exists('role_assignments', $roles)) {
            $DB->delete_records('user_enrolments', array('enrolid'=>$enrol->id));
            $DB->delete_records('role_assignments', array('roleid'=>3, "contextid"=>$coursecontext->id));
            
            $DB->insert_record('user_enrolments', $conds);
            $DB->insert_record('role_assignments', $roles);
        } else { 
            $ueID = $DB->get_record('user_enrolments',$conds);
            $conds['id'] = $ueID->id;
            $DB->update_record('user_enrolments', $conds);

            $raID = $DB->get_record('role_assignments', $roles);
            $roles['id'] = $raID->id;
            $DB->update_record('role_assignments', $roles);
        }
    }
  }

function enrol_waiting_user($eventData){
  global $DB;
  //get the first user on the waiting list
  $user = $DB->get_records_sql("SELECT * FROM {meta_waitlist} order by timecreated asc");
  $user = reset($user);

  //if there is anyone on the waiting list...
  if ($user) {
    $instance = $DB->get_records_sql("SELECT * FROM {enrol} where enrol= :enrol and courseid = :courseid and status = 0", array('enrol'=>'manual','courseid'=>$eventData->courseid));
    $instance = reset($instance);

    $enrolPlugin = new enrol_manual_pluginITK();

    $enrolPlugin->enrol_user($instance, $user->userid, 5);

    $full_user = $DB->get_record("user",array("id"=>$user->userid));
    $enrolPlugin->send_confirmation_email($full_user, $instance->courseid);
    $DB->delete_records('meta_waitlist',array('courseid'=>$instance->courseid,'userid'=>$user->userid));
  }
}

function update_metacourse($eventData){
  global $DB;
  //nothing yet
}

function delete_metacourse($eventData){
  global $DB;
  $courseid = $eventData->id;
  try{
    $datecourses = $DB->get_records_sql("SELECT id, metaid FROM {meta_datecourse} where courseid = :courseid",array("courseid"=>$courseid));
    $datecourse = reset($datecourses);
    //supress the warning, as sometimes the datecourse can be deleted without deleting the course first
    @$metaid = $datecourse->metaid;

    $DB->delete_records("meta_datecourse",array("metaid"=>$metaid));
    $DB->delete_records("meta_course",array("id"=>$metaid));
    $DB->delete_records("meta_waitlist",array("courseid"=>$courseid));
    $DB->delete_records("meta_tos_accept",array("courseid"=>$courseid));
  } catch(Exception $e){
    //the records no longer exist
  }
}

function enrol_update_free_places($eventData){
  global $DB;
  $record = new stdClass();
  $current_record = $DB->get_record("meta_datecourse",array("courseid"=>$eventData->courseid));
  $record->id = $eventData->courseid;
  $record->free_places = $current_record->free_places - 1;
}