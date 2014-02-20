<?php

require_once($CFG->dirroot.'/calendar/lib.php');
require_once($CFG->libdir.'/bennu/bennu.inc.php');


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

    $a = new stdClass();
    $a->username = $username;
    $a->course = $course->fullname;

    $message     = get_string("emailconf", 'block_metacourse', $a);
    $messagehtml = text_to_html(get_string('emailconfirmation', '', $data), false, false, true);

    $user->mailformat = 0;  // Always send HTML version as well

    // $teacherCC = $DB->get_records_sql("
    //   SELECT u.id, u.firstname, u.lastname, u.email, u.city, u.country, u.lastaccess
    //     FROM {role_assignments} ra, {user} u, {course} c, {context} cxt
    //     WHERE ra.userid = u.id
    //     AND ra.contextid = cxt.id
    //     AND cxt.contextlevel =50
    //     AND cxt.instanceid = c.id
    //     AND c.id = :courseid
    //     AND (roleid = 3)", array("courseid"=>$courseid));
    $teacherCC = $DB->get_records_sql("
      SELECT u.email from {user} u join {meta_datecourse} md on u.id = md.coordinator and md.courseid = :cid
      ", array("cid"=>$courseid));
    $teacherCC = reset($teacherCC);

    //iCal
    
    $datecourse = $DB->get_record("meta_datecourse", array("courseid"=>$course->id));

    $ical = new iCalendar;
    $ical->add_property('method', 'PUBLISH');

    $ev = new iCalendar_event;
    $ev->add_property('uid', $course->id.'@'.'novozymes.it-kartellet.dk');
    $ev->add_property('summary', $course->fullname);
    $ev->add_property('description', clean_param($course->summary, PARAM_NOTAGS));
    $ev->add_property('class', 'PUBLIC'); 
    $ev->add_property('last-modified', Bennu::timestamp_to_datetime($course->timemodified));
    $ev->add_property('dtstamp', Bennu::timestamp_to_datetime()); // now
    $ev->add_property('dtstart', Bennu::timestamp_to_datetime($datecourse->startdate)); // when event starts
    $ev->add_property('dtend', Bennu::timestamp_to_datetime($datecourse->enddate));
    
    // if ($course->id != 0) {
    //     $coursecontext = context_course::instance($course->id);
    //     $ev->add_property('categories', format_string($courses[$course->id]->shortname, true, array('context' => $coursecontext)));
    // }
    $ical->add_component($ev);
    
    $serialized = $ical->serialize();

    $file = $CFG->dataroot . "/" . time() . ".ics";

    $fh = fopen($file, "w+");
    fwrite($fh, $serialized);
    fclose($fh);

    if(empty($serialized)) {
        // TODO
        die('bad serialization');
    }
    // calendar_add_icalendar_event($ev, $course->id);
    //end iCal
    $result =  $this->send_enrolment_email($user, $supportuser, $subject, $message, $messagehtml, $teacherCC->email, $file, "event.ics");
    unlink($file);
    return $result;
  }

  private function send_enrolment_email($user, $from, $subject, $messagetext, $messagehtml='', $teacherCC ,$attachment='', $attachname='', $usetrueaddress=true, $replyto='', $replytoname='', $wordwrapwidth=79) {

    global $CFG;

    if (empty($user) || empty($user->email)) {
        $nulluser = 'User is null or has no email';
        error_log($nulluser);
        if (CLI_SCRIPT) {
            mtrace('Error: lib/moodlelib.php email_to_user(): '.$nulluser);
        }
        return false;
    }

    if (!empty($user->deleted)) {
        // do not mail deleted users
        $userdeleted = 'User is deleted';
        error_log($userdeleted);
        if (CLI_SCRIPT) {
            mtrace('Error: lib/moodlelib.php email_to_user(): '.$userdeleted);
        }
        return false;
    }

    if (!empty($CFG->noemailever)) {
        // hidden setting for development sites, set in config.php if needed
        $noemail = 'Not sending email due to noemailever config setting';
        error_log($noemail);
        if (CLI_SCRIPT) {
            mtrace('Error: lib/moodlelib.php email_to_user(): '.$noemail);
        }
        return true;
    }

    if (!empty($CFG->divertallemailsto)) {
        $subject = "[DIVERTED {$user->email}] $subject";
        $user = clone($user);
        $user->email = $CFG->divertallemailsto;
    }

    // skip mail to suspended users
    if ((isset($user->auth) && $user->auth=='nologin') or (isset($user->suspended) && $user->suspended)) {
        return true;
    }

    if (!validate_email($user->email)) {
        // we can not send emails to invalid addresses - it might create security issue or confuse the mailer
        $invalidemail = "User $user->id (".fullname($user).") email ($user->email) is invalid! Not sending.";
        error_log($invalidemail);
        if (CLI_SCRIPT) {
            mtrace('Error: lib/moodlelib.php email_to_user(): '.$invalidemail);
        }
        return false;
    }

    if (over_bounce_threshold($user)) {
        $bouncemsg = "User $user->id (".fullname($user).") is over bounce threshold! Not sending.";
        error_log($bouncemsg);
        if (CLI_SCRIPT) {
            mtrace('Error: lib/moodlelib.php email_to_user(): '.$bouncemsg);
        }
        return false;
    }

    // If the user is a remote mnet user, parse the email text for URL to the
    // wwwroot and modify the url to direct the user's browser to login at their
    // home site (identity provider - idp) before hitting the link itself
    if (is_mnet_remote_user($user)) {
        require_once($CFG->dirroot.'/mnet/lib.php');

        $jumpurl = mnet_get_idp_jump_url($user);
        $callback = partial('mnet_sso_apply_indirection', $jumpurl);

        $messagetext = preg_replace_callback("%($CFG->wwwroot[^[:space:]]*)%",
                $callback,
                $messagetext);
        $messagehtml = preg_replace_callback("%href=[\"'`]($CFG->wwwroot[\w_:\?=#&@/;.~-]*)[\"'`]%",
                $callback,
                $messagehtml);
    }
    $mail = get_mailer();
    // add teacher as a cc
    $mail->AddCC($teacherCC);

    if (!empty($mail->SMTPDebug)) {
        echo '<pre>' . "\n";
    }

    $temprecipients = array();
    $tempreplyto = array();

    $supportuser = generate_email_supportuser();

    // make up an email address for handling bounces
    if (!empty($CFG->handlebounces)) {
        $modargs = 'B'.base64_encode(pack('V',$user->id)).substr(md5($user->email),0,16);
        $mail->Sender = generate_email_processing_address(0,$modargs);
    } else {
        $mail->Sender = $supportuser->email;
    }

    if (is_string($from)) { // So we can pass whatever we want if there is need
        $mail->From     = $CFG->noreplyaddress;
        $mail->FromName = $from;
    } else if ($usetrueaddress and $from->maildisplay) {
        $mail->From     = $from->email;
        $mail->FromName = fullname($from);
    } else {
        $mail->From     = $CFG->noreplyaddress;
        $mail->FromName = fullname($from);
        if (empty($replyto)) {
            $tempreplyto[] = array($CFG->noreplyaddress, get_string('noreplyname'));
        }
    }

    if (!empty($replyto)) {
        $tempreplyto[] = array($replyto, $replytoname);
    }

    $mail->Subject = substr($subject, 0, 900);

    $temprecipients[] = array($user->email, fullname($user));

    $mail->WordWrap = $wordwrapwidth;                   // set word wrap

    if (!empty($from->customheaders)) {                 // Add custom headers
        if (is_array($from->customheaders)) {
            foreach ($from->customheaders as $customheader) {
                $mail->AddCustomHeader($customheader);
            }
        } else {
            $mail->AddCustomHeader($from->customheaders);
        }
    }

    if (!empty($from->priority)) {
        $mail->Priority = $from->priority;
    }

    if ($messagehtml && !empty($user->mailformat) && $user->mailformat == 1) { // Don't ever send HTML to users who don't want it
        $mail->IsHTML(true);
        $mail->Encoding = 'quoted-printable';           // Encoding to use
        $mail->Body    =  $messagehtml;
        $mail->AltBody =  "\n$messagetext\n";
    } else {
        $mail->IsHTML(false);
        $mail->Body =  "\n$messagetext\n";
    }

    if ($attachment && $attachname) {
        if (preg_match( "~\\.\\.~" ,$attachment )) {    // Security check for ".." in dir path
            $temprecipients[] = array($supportuser->email, fullname($supportuser, true));
            $mail->AddStringAttachment('Error in attachment.  User attempted to attach a filename with a unsafe name.', 'error.txt', '8bit', 'text/plain');
        } else {
            require_once($CFG->libdir.'/filelib.php');
            $mimetype = mimeinfo('type', $attachname);
            $mail->AddAttachment($attachment, $attachname, 'base64', $mimetype);
        }
    }

    // Check if the email should be sent in an other charset then the default UTF-8
    if ((!empty($CFG->sitemailcharset) || !empty($CFG->allowusermailcharset))) {

        // use the defined site mail charset or eventually the one preferred by the recipient
        $charset = $CFG->sitemailcharset;
        if (!empty($CFG->allowusermailcharset)) {
            if ($useremailcharset = get_user_preferences('mailcharset', '0', $user->id)) {
                $charset = $useremailcharset;
            }
        }

        // convert all the necessary strings if the charset is supported
        $charsets = get_list_of_charsets();
        unset($charsets['UTF-8']);
        if (in_array($charset, $charsets)) {
            $mail->CharSet  = $charset;
            $mail->FromName = textlib::convert($mail->FromName, 'utf-8', strtolower($charset));
            $mail->Subject  = textlib::convert($mail->Subject, 'utf-8', strtolower($charset));
            $mail->Body     = textlib::convert($mail->Body, 'utf-8', strtolower($charset));
            $mail->AltBody  = textlib::convert($mail->AltBody, 'utf-8', strtolower($charset));

            foreach ($temprecipients as $key => $values) {
                $temprecipients[$key][1] = textlib::convert($values[1], 'utf-8', strtolower($charset));
            }
            foreach ($tempreplyto as $key => $values) {
                $tempreplyto[$key][1] = textlib::convert($values[1], 'utf-8', strtolower($charset));
            }
        }
    }

    foreach ($temprecipients as $values) {
        $mail->AddAddress($values[0], $values[1]);
    }
    foreach ($tempreplyto as $values) {
        $mail->AddReplyTo($values[0], $values[1]);
    }

    if ($mail->Send()) {
        set_send_count($user);
        if (!empty($mail->SMTPDebug)) {
            echo '</pre>';
        }
        return true;
    } else {
        add_to_log(SITEID, 'library', 'mailer', qualified_me(), 'ERROR: '. $mail->ErrorInfo);
        if (CLI_SCRIPT) {
            mtrace('Error: lib/moodlelib.php email_to_user(): '.$mail->ErrorInfo);
        }
        if (!empty($mail->SMTPDebug)) {
            echo '</pre>';
        }
        return false;
    }
}


  /// method to add to the waiting list
  /// method to send mail when available place found.
}


// functions
function create_new_course($fullname, $shortname, $categoryid, $startdate = 0 , $summary="", $language = "") {
  global $DB;
  $category = $DB->get_record('course_categories', array('id'=>$categoryid), '*', MUST_EXIST);

  $course = new stdClass;
  $course->fullname = $fullname;
  $course->summary = $summary;
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
            // $DB->delete_records('user_enrolments', array('enrolid'=>$enrol->id));
            // $DB->delete_records('role_assignments', array('roleid'=>3, "contextid"=>$coursecontext->id));

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

function add_label($courseid, $meta) {
  global $DB;

  $label = new stdClass();
  $label->course = $courseid;
  $label->name = substr($meta->content,0, 30);
  $label->intro = $meta->purpose;
  $label->introformat = 1;
  $label->timemodified = time();

  $labelid = $DB->insert_record("label", $label);

  rebuild_course_cache($courseid);

  $course_module = new stdClass();
  $course_module->course = $courseid;
  $course_module->module = 12;
  $course_module->instance = $labelid;
  $course_module->visible = 1;
  $course_module->visibleold = 1;
  $course_module->groupmode = 0;
  $course_module->groupingid = 0;
  $course_module->groupmembersonly = 0;
  $course_module->showdescription = 0;
  $course_module->added = time();

  $course_module_id = $DB->insert_record('course_modules ',$course_module);

  rebuild_course_cache($courseid);

  $course_section = new stdClass();
  $course_section->course = $courseid;
  $course_section->section = 0;
  $course_section->summaryformat = 1;
  $course_section->sequence = $course_module_id;
  $course_section->visible = 1;
  $course_section->availablefrom = 0;
  $course_section->availableuntil = 0;
  $course_section->showavailability = 0;
  $course_section->groupingid = 0;

  $DB->insert_record("course_sections",$course_section);

  rebuild_course_cache($courseid);
}