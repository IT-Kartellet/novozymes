<?php

require_once($CFG->dirroot.'/calendar/lib.php');
require_once($CFG->libdir.'/bennu/bennu.inc.php');

require('../../vendor/autoload.php');

function block_metacourse_pluginfile($course, $cm, $context, $filearea, array $args, $forcedownload, array $options=array()) {
  $fs = get_file_storage();

  $filename = array_pop($args);
  $itemid = array_pop($args);
  if (!$file = $fs->get_file($context->id, 'block_metacourse', $filearea, $itemid, '/', $filename) or $file->is_directory()) {
    send_file_not_found();
  }

  send_stored_file($file, null, 0, true);
}

function format_tz_offset($offset) {
  if (strstr($offset, ':30')) {
    // Convert from xx:30 to xx.5 so we can multiply by it
    // 30 minutes = 0.5 hour. I hope we do something smarter before we have to take :45 offsets into account ...
    $offset = intval($offset);

    if ($offset >= 0) {
      $offset += .5;
    } else {
      $offset -= .5;
    }
  }

  return $offset;
}

function format_date_with_tz($timestamp, $offset, $asString = true) {
  $oldtimezone = date_default_timezone_get();

  $offset = format_tz_offset($offset);

  // http://stackoverflow.com/questions/11820718/convert-utc-offset-to-timezone-or-date
  // First lets get the timezone matching the offset
  $tz = timezone_name_from_abbr(null, $offset * 3600, false);

  if ($tz === false) {
    // Perhaps there is no timezone matching that offset - lets try with DST on ..
    $tz = timezone_name_from_abbr(null, $offset * 3600, true);
  }

  // Set that as the default, in order to figure out if DST is in effect for that offset
  date_default_timezone_set($tz);

  $dstInEffect = date('I', $timestamp) == '1';
  $timezoneName = timezone_name_from_abbr("", $offset * 3600, $dstInEffect); // At first, try to get the timezone with adjustment for DST

  if ($timezoneName === false) {
    $timezoneName = timezone_name_from_abbr("", $offset * 3600, false); // If that fails, fall back to ignoring DST
  }

  if ($timezoneName === false) {
    $timezoneName = $tz;
  }

  // And then reset to the original timezone
  date_default_timezone_set($oldtimezone);

  $timezone = new DateTimeZone($timezoneName);
  $date = new DateTime(null, $timezone);
  $date->setTimestamp($timestamp);

  if ($asString) {
    $date = $date->format("d M Y - h:i A");
  }

  return $date;
}

// Implodes a list like humans would do, e.g. array(x, y, z) => x, y, and z
function human_implode(array $items) {
  if (count($items) == 1) {
    return $items[0];
  }

  $parts = array_slice($items, 0, count($items) - 2);
  $parts[] =get_string('and', '', (object) array(
    'one' => $items[count($items) - 2],
    'two' => $items[count($items) - 1]
  ));
  $str = implode(', ', $parts);

  return $str;
}

class enrol_manual_pluginITK extends enrol_plugin {

  public function sendUnenrolMail($userid, $courseid, $waiting = false) {
    global $CFG, $DB;

    $site = get_site();
    $user = $DB->get_record("user", array("id" => $userid));
    $subject = format_string($site->fullname) . ": cancellation confirmation";

    $username = urlencode($user->username);
    $username = str_replace('.', '%2E', $username); // prevent problems with trailing dots
	
	if ($courseid>=0) {
		// Date course unenrolled.
		$teacherCC = $DB->get_records_sql("
		  SELECT u.* from {user} u join {meta_datecourse} md on u.id = md.coordinator and md.courseid = :cid
		  ", array("cid"=>$courseid));
		$teacherCC = reset($teacherCC);
		
		$datecourse = $DB->get_record_sql("
		  SELECT d.*, c.currency, m.content, l.location as loc, m.name as metaname
		  FROM {meta_datecourse} d
		  JOIN {meta_currencies} c on d.currencyid=c.id
		  LEFT JOIN {meta_locations} l ON d.location = l.id
		  JOIN {meta_course} m ON d.metaid = m.id
		  where courseid = :id",
		array("id"=>$courseid));
		$course = $DB->get_record('course', array('id' => $courseid));
	}
	else {
		// Meta course unenrolled.
		$teacherCC = $DB->get_records_sql("
		  SELECT u.* from {user} u join {meta_course} mc on u.id = mc.coordinator and mc.id = :cid
		  ", array("cid"=>-$courseid));
		$teacherCC = reset($teacherCC);
		if ($teacherCC===false) $teacherCC = '';
		
		$datecourse = $DB->get_record_sql("
		  SELECT m.*, c.currency, m.name as metaname
		  FROM {meta_course} m
		  LEFT JOIN {meta_currencies} c on m.currencyid=c.id
		  where m.id = :id",
		array("id"=>-$courseid));
	}

    $a = new stdClass();
    $a->username = $username;
    $a->firstname = $user->firstname;
    $a->lastname = $user->lastname;
    $a->course = $datecourse->metaname;
    $a->department = $user->department;
	if ($courseid>=0) {
		$a->periodfrom = format_date_with_tz($datecourse->startdate, $datecourse->timezone);
		$a->periodto = format_date_with_tz($datecourse->enddate, $datecourse->timezone);
		$a->location = $datecourse->loc;
	}
    $a->currency = $datecourse->currency;
    $a->price = $datecourse->price;
    $a->coordinator = $teacherCC->firstname." ".$teacherCC->lastname;
    $a->coordinatorinitials = $teacherCC->username;
    $a->myhome = $CFG->wwwroot."/my";

    if ($waiting) {
      if ($courseid>=0) $message = get_string("emailunenrolwaitconf", 'block_metacourse', $a);
	  else $message = get_string("emailunenrolmetawaitconf", 'block_metacourse', $a);
    } else {
      $message = get_string("emailunenrolconf", 'block_metacourse', $a);
    }

    if (!$datecourse->elearning && $courseid>=0) {
      $attachment = $this->get_ical($datecourse, $course, $user, $teacherCC, 'CANCEL', 1);
    } else {
      $attachment = false;
    }

    $messagehtml = text_to_html($message, false, false, true);
    $result = $this->send_enrolment_email($user, $teacherCC, $subject, $message, $messagehtml, $attachment);
    return $result;
  }

  private function get_ical($datecourse, $course, $user, $teacher, $method = 'REQUEST', $sequence = 0) {
    global $DB;

    if (!empty($datecourse->location)) {
      $location = $DB->get_field('meta_locations', 'location', array(
        'id' => $datecourse->location
      ));
    } else {
      $location = $datecourse->loc;
    }

    if (empty($datecourse->content)) {
      $content = $DB->get_field('meta_course', 'content', array(
        'id' => $datecourse->metaid
      ));
    } else {
      $content = $datecourse->content;
    }

    $vCalendar = new \Eluceo\iCal\Component\Calendar('dkta01grow');
    $vCalendar->setMethod($method);

    $vEvent = new \Eluceo\iCal\Component\Event();

    $vEvent->setUniqueId($course->id . '@' . 'novozymes.it-kartellet.dk');
    $vEvent->setSummary($course->fullname);
    $vEvent->setDescription(str_replace("\n", '\\n', html_to_text($content)));
    $vEvent->setLocation($location);

    $vEvent->setSequence($sequence);

    $vEvent->setModified(new DateTime('@'.$course->timemodified));
    $vEvent->setDtStamp(new DateTime());
    $vEvent->setDtStart(new DateTime('@'.$datecourse->startdate));
    $vEvent->setDtEnd(new DateTime('@'.$datecourse->enddate));

    $vEvent->setOrganizer(new \Eluceo\iCal\Property\Event\Organizer('mailto:' . $teacher->email, array(
      'cn' => $teacher->firstname . ' ' . $teacher->lastname
    )));

    $vEvent->addAttendee('mailto:' . $user->email, array(
      'PARTSTAT' => 'ACCEPTED',
      'CN' => $user->firstname . ' ' . $user->lastname
    ));

    switch ($method) {
      case 'REQUEST':
        $vEvent->setStatus('CONFIRMED');
        break;
      case 'CANCEL':
        $vEvent->setStatus('CANCELLED');
        break;
    }

    $vCalendar->addComponent($vEvent);

    $out = $vCalendar->render();

    return $out;
  }

  public function send_course_updated_email($user, $datecourse, $existing_datecourse, array $changed_attributes) {
    global $DB;
    $site = get_site();

    $metacourse = $DB->get_record('meta_course', array(
      'id' => $datecourse->metaid
    ));

    $changes = array();
    $changes_summary = array();

    foreach (array('startdate', 'enddate') as $key) {
      if (in_array($key, $changed_attributes)) {
        $changes_summary[] = strtolower(get_string($key, 'block_metacourse'));

        $old = format_date_with_tz($existing_datecourse->{$key}, $existing_datecourse->timezone, false);
        $new = format_date_with_tz($datecourse->{$key}, $datecourse->timezone, false);

        $format = array();
        if ($old->format('d M Y') !== $new->format('d M Y')) {
          $format[] = "d M Y";
        }
        if ($old->format('h:i A') !== $new->format('h:i A')) {
          $format[] = "h:i A";
        }

        $format = implode(' - ', $format);

        $changes[] = get_string('course_details_updated_time', 'block_metacourse', (object) array(
          'name' => get_string($key, 'block_metacourse'),
          'old' => $old->format($format),
          'new' => $new->format($format)
        ));
      }
    }
    if (in_array('location', $changed_attributes)) {
      $changes_summary[] = strtolower(get_string('location', 'block_metacourse'));
      $old_location = $DB->get_field('meta_locations', 'location', array(
        'id' => $existing_datecourse->location
      ));

      $new_location = $DB->get_field('meta_locations', 'location', array(
        'id' => $datecourse->location
      ));

      $changes[] = get_string('course_details_updated_location', 'block_metacourse', (object) array(
        'old' => $old_location,
        'new' => $new_location
      ));
    }

    $teacherCC = $DB->get_records_sql("SELECT u.* from {user} u join {meta_datecourse} md on u.id = md.coordinator and md.courseid = :cid", array(
      "cid" => $datecourse->courseid
    ));
    $teacherCC = reset($teacherCC);

    $a = new stdClass();
    $a->firstname = $user->firstname;
    $a->lastname = $user->lastname;
    $a->changes_summary = human_implode($changes_summary);

    $changes[0] = ucfirst($changes[0]);
    $a->changes = human_implode($changes);
    $a->coursename = $metacourse->name;
    $a->coordinator = $teacherCC->firstname . ' ' . $teacherCC->lastname;

    $subject = format_string($site->fullname) . ": "  . get_string('course_details_updated_subject', 'block_metacourse', $metacourse->name);
    $message = get_string('course_details_updated_body', 'block_metacourse', $a);
    $messagehtml = text_to_html($message);

    $course = $DB->get_record("course", array(
      "id" => $datecourse->courseid
    ));

    $course->timemodified = time();

    $attachment = $this->get_ical($datecourse, $course, $user, $teacherCC, 'REQUEST', 1);

    return $this->send_enrolment_email($user, $teacherCC, $subject, $message, $messagehtml, $attachment);
  }

  public function send_waitlist_email($user, $courseid){
    global $CFG, $DB;

    $site = get_site();
	if ($courseid>=0) {
		$course = $DB->get_record("course",array("id"=>$courseid));
	}
    if(is_int($user)){
      $user = $DB->get_record("user",array("id"=>$user));
    }

    $data = new stdClass();
    $data->firstname = fullname($user);
    $data->sitename  = format_string($site->fullname);
    $data->admin     = generate_email_signoff();

    $subject = format_string($site->fullname) . ": Enrolment for waiting list confirmation";

    $username = urlencode($user->username);
    $username = str_replace('.', '%2E', $username); // prevent problems with trailing dots
    $data->link  = $CFG->wwwroot;
	
	if ($courseid>=0) {
		$teacherCC = $DB->get_records_sql("
		  SELECT u.* from {user} u join {meta_datecourse} md on u.id = md.coordinator and md.courseid = :cid
		  ", array("cid"=>$courseid));
		$teacherCC = reset($teacherCC);
		$datecourse = $DB->get_record_sql("
			SELECT d.*, c.currency, l.location as loc, m.name as metaname 
			FROM {meta_datecourse} d 
			JOIN {meta_currencies} c on d.currencyid=c.id 
			JOIN {meta_locations} l ON d.location = l.id 
			JOIN {meta_course} m ON d.metaid = m.id 
			where courseid = :id", 
		array("id"=>$course->id));
	}
	else {
		$teacherCC = $DB->get_records_sql("
		  SELECT u.* from {user} u join {meta_course} mc on u.id = mc.coordinator and mc.id = :cid
		  ", array("cid"=>-$courseid));
		$teacherCC = reset($teacherCC);
		if ($teacherCC===false) $teacherCC = '';
		$datecourse = $DB->get_record_sql("
			SELECT m.*, c.currency, m.name as metaname 
			FROM {meta_course} m
			LEFT JOIN {meta_currencies} c on m.currencyid=c.id
			where m.id = :id", 
		array("id"=>-$courseid));
	}

    $a = new stdClass();
    $a->username = $username;
    $a->firstname = $user->firstname;
    $a->lastname = $user->lastname;
    $a->course = $datecourse->metaname;
    $a->department = $user->department;
	if ($courseid>=0) {
		$a->periodfrom = format_date_with_tz($datecourse->startdate, $datecourse->timezone);
		$a->periodto = format_date_with_tz($datecourse->enddate, $datecourse->timezone);
		$a->location = $datecourse->loc;
	}
    $a->currency = $datecourse->currency;
    $a->price = $datecourse->price;
    $a->coordinator = $teacherCC->firstname." ".$teacherCC->lastname;
    $a->coordinatorinitials = $teacherCC->username;
    $a->myhome = $CFG->wwwroot."/my";

    if ($courseid>=0) $message = get_string("emailwait", 'block_metacourse', $a);
	else $message = get_string("emailmetawait", 'block_metacourse', $a);
    $messagehtml = text_to_html($message, false, false, true);
	if ($courseid>=0) $attachment = $this->get_ical($datecourse, $course, $user, $teacherCC);
	else $attachment = false;
    $result =  $this->send_enrolment_email($user, $teacherCC, $subject, $message, $messagehtml, $attachment);

    return $result;
  }

  public function send_confirmation_email($user, $courseid) {
    global $CFG, $DB;

    $site = get_site();
    $course = $DB->get_record("course",array("id"=>$courseid));
    $metacourse_id = $DB->get_field('meta_datecourse', 'metaid', array(
      'courseid' => $courseid
    ));
    if(is_int($user)){
      $user = $DB->get_record("user",array("id"=>$user));
    }

    $data = new stdClass();
    $data->firstname = fullname($user);
    $data->sitename  = format_string($site->fullname);
    $data->admin     = generate_email_signoff();

    $subject = format_string($site->fullname) . ": enrolment confirmation";

    $username = urlencode($user->username);
    $username = str_replace('.', '%2E', $username); // prevent problems with trailing dots
    $data->link  = $CFG->wwwroot;

    $teacherCC = $DB->get_records_sql("
      SELECT u.* from {user} u join {meta_datecourse} md on u.id = md.coordinator and md.courseid = :cid
      ", array("cid"=>$courseid));
    $teacherCC = reset($teacherCC);

    $datecourse = $DB->get_record_sql("
      SELECT d.*, c.currency, l.location as loc, m.name as metaname, m.content as content
      FROM {meta_datecourse} d
      JOIN {meta_currencies} c on d.currencyid=c.id
      LEFT JOIN {meta_locations} l ON d.location = l.id
      JOIN {meta_course} m ON d.metaid = m.id
      WHERE courseid = :id",
      array("id"=>$course->id)
    );

    $a = new stdClass();
    $a->username = $username;
    $a->firstname = $user->firstname;
    $a->lastname = $user->lastname;
    $a->course = $datecourse->metaname;
    $a->department = $user->department;
    $a->periodfrom = format_date_with_tz($datecourse->startdate, $datecourse->timezone);
    $a->periodto = format_date_with_tz($datecourse->enddate, $datecourse->timezone);
    $a->currency = $datecourse->currency;
    $a->price = $datecourse->price;
    $a->location = $datecourse->loc;
    $a->coordinator = $teacherCC->firstname." ".$teacherCC->lastname;
    $a->coordinatorinitials = $teacherCC->username;
    $a->myhome = $CFG->wwwroot."/my";

    $lang_id = $DB->get_field('meta_languages', 'id', array(
      'iso' => $user->lang
    ));

    $text = $DB->get_record('meta_custom_emails', array(
      'metaid' => $metacourse_id,
      'lang' => $lang_id
    ));
    if ($text && strlen(trim($text->text)) > 0) {
      // Replacement code copied from core_string_manager_standard->get_string
      $search = array();
      $replace = array();

      foreach ($a as $key => $value) {
        $search[]  = '{$a->'.$key.'}';
        $replace[] = (string)$value;
      }
      $message = str_replace($search, $replace, $text->text);
    } else {
      $message = get_string("emailconf", 'block_metacourse', $a);
    }
    $messagehtml = text_to_html($message);

    if (!$datecourse->elearning) {
      $attachment = $this->get_ical($datecourse, $course, $user, $teacherCC);
    } else {
      $attachment = false;
    }

    $result =  $this->send_enrolment_email($user, $teacherCC, $subject, $message, $messagehtml, $attachment);

    return $result;
  }

  private function send_enrolment_email($user, $from, $subject, $messagetext, $messagehtml='', $attachment='') {
    global $CFG;
	
	if ($from===false) {
		$from = new stdClass();
		$from->email = 'noreply@novozymes.com';
		
	}

    if (empty($user) || empty($user->email)) {
      $nulluser = 'User is null or has no email';

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

    $mail = get_mailer();
    // add teacher as a cc
    $mail->AddCC($from->email);

    if (!empty($mail->SMTPDebug)) {
      echo '<pre>' . "\n";
    }

    $temprecipients = array();
    $tempreplyto = array();

    $supportuser = core_user::get_support_user();

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
    } else if ($from->maildisplay) {
      $mail->From     = $from->email;
      $mail->FromName = fullname($from);
    } else {
      $mail->From     = $CFG->noreplyaddress;
      $mail->FromName = fullname($from);
      if (empty($replyto)) {
        $tempreplyto[] = array($CFG->noreplyaddress, get_string('noreplyname'));
      }
    }

    $mail->Subject = substr($subject, 0, 900);

    $temprecipients[] = array($user->email, fullname($user));

    $mail->WordWrap = 79;                   // set word wrap

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

    $mail->IsHTML(true);
    $mail->Encoding = 'quoted-printable';
    $mail->Body    =  $messagehtml;
    $mail->AltBody =  "\n$messagetext\n";

    if ($attachment) {
      $mail->AltBody = $mail->Body;
      $mail->Ical = $attachment;
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

    $send = $mail->Send();
    if ($send) {
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
  $course->id = $courseid;

  $enrolManual = enrol_get_plugin('manual');
  $instance = $enrolManual->add_default_instance($course);

  $category->coursecount++;
  $DB->update_record('course_categories', $category);

  return $courseid;
}

function update_meta_course($metaid, $datecourse, $category){
  global $DB;

  // if we have a date, and an actual course for it.
  if ($datecourse->courseid) {
    $meta = $DB->get_record("meta_course", array("id" => $metaid));

    $course = $DB->get_record("course",array("id"=>$datecourse->courseid));

    $oldCategory = $DB->get_record('course_categories', array('id'=>$course->category), '*', MUST_EXIST);
    $newCategory = $DB->get_record('course_categories', array('id'=> $category ), '*', MUST_EXIST);

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
    do {
      $user_enrolled = enrol_waiting_user($datecourse);
    } while ($user_enrolled);
  }
}

function get_or_create_enrol($params) {
  global $DB;

  if (!isset($params['courseid']) || !isset($params['enrol']) || !isset($params['roleid'])) {
    throw new Exception('enrol should contain courseid, enrol and roleid');
  }

  $params['status'] = ENROL_INSTANCE_ENABLED;
  $enrol = $DB->get_record('enrol', $params);

  if (!$enrol) {
    $enrol = new stdClass();
    $enrol->enrol          = $params['enrol'];
    $enrol->status         = ENROL_INSTANCE_ENABLED;
    $enrol->courseid       = $params['courseid'];
    $enrol->enrolstartdate = 0;
    $enrol->enrolenddate   = 0;
    $enrol->roleid         = $params['roleid'];
    $enrol->timemodified   = time();
    $enrol->timecreated    = $enrol->timemodified;
    $enrol->sortorder      = $DB->get_field('enrol', 'COALESCE(MAX(sortorder), -1) + 1', array('courseid'=>$params['courseid']));

    $enrol->id = $DB->insert_record('enrol', $enrol);
  }

  return $enrol;
}

// enrols a coordinator in a course with a teacher role
function add_coordinator($user_id, $course_id) {
  global $DB;
  $coursecontext = context_course::instance($course_id);

  $enrol = get_or_create_enrol(array(
    'courseid' => $course_id,
    'enrol' => 'manual',
    'roleid' => 3
  ));

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

function enrol_waiting_user($eventData){
  global $DB;

  //get the first user on the waiting list
  $metaid = $DB->get_field('meta_datecourse', 'metaid', array('courseid'=>$eventData->courseid));
  $user = $DB->get_records_sql(
	"SELECT mw.*
		 FROM {meta_waitlist} mw
		 WHERE (mw.courseid = :courseid and mw.nodates = 0) or (mw.courseid = :metaid and mw.nodates = 1)
		 ORDER BY timecreated asc",
	array("courseid"=>$eventData->courseid, "metaid"=>$metaid)
  );
  //$user = $DB->get_records_sql("SELECT * FROM {meta_waitlist} WHERE courseid = :courseid order by timecreated asc", array('courseid' => $eventData->courseid));
  $user = reset($user);

  $enrolmentEnd = $DB->get_records_sql("SELECT * FROM {meta_datecourse} where courseid = :courseid and unpublishdate > :time and (manual_enrol is null or manual_enrol = 0)", array("courseid" => $eventData->courseid, "time"=>time()));

  list($enrolled_users, $not_enrolled_users, $waiting_users) = get_datecourse_users($eventData->courseid);
  $busy_places = count($enrolled_users);

  $total_places = $DB->get_field_sql("SELECT total_places from {meta_datecourse} where courseid = :cid", array("cid"=>$eventData->courseid));

  if ($user && //if there is anyone on the waiting list...
    $enrolmentEnd && // the course is still active and automatic enrolment enabled
    $busy_places < $total_places // and there's still space
  ) {
    $instance = $DB->get_records_sql("SELECT * FROM {enrol} where enrol= :enrol and courseid = :courseid and status = 0", array('enrol'=>'manual', 'courseid' => $eventData->courseid));
    $instance = reset($instance);
    $enrolPlugin = new enrol_manual_pluginITK();

    if(!$instance){
      $enrolManual = enrol_get_plugin('manual');
      $course = $DB->get_record('course', array('id' => $eventData->courseid));
      $instance = $enrolManual->add_default_instance($course);
    }

    $enrolPlugin->enrol_user($instance, $user->userid, 5);

    $full_user = $DB->get_record("user",array("id"=>$user->userid));
    $enrolPlugin->send_confirmation_email($full_user, $instance->courseid);
    $DB->delete_records('meta_waitlist', array('id'=> $user->id));

    add_to_log($eventData->courseid, 'block_metacourse', 'add enrolment', 'blocks/metacourse/lib.php', "$user->id successfully moved from waiting list to course. Email sent? 1");

    return true;
  }

  return false;
}

function delete_datecourse($datecourse){
  global $DB, $USER;

  $datecourse = $DB->get_record("meta_datecourse",array("courseid"=>$datecourse->courseid));

  try {
    // If a course is deleted or unpublished before it has started it has been cancelled and should not show up in my courses
    // If the course has already started it should still show up in my courses, but not on the course overview page so we mark it as deleted and keep the moodle course
    if ($datecourse->startdate != 0 && $datecourse->startdate < time()) {
      delete_course($datecourse->courseid);
      $DB->delete_records("meta_datecourse",array("courseid"=>$datecourse->courseid));
    } else {
      $datecourse->deleted = 1;
      $DB->update_record("meta_datecourse", $datecourse);
    }

    $DB->delete_records("meta_waitlist",array("courseid"=>$datecourse->courseid));
    $DB->delete_records("meta_tos_accept",array("courseid"=>$datecourse->courseid));
  } catch(Exception $e){
    add_to_log(1, 'metacourse_err', 'course_deleted_error', "", json_encode($e), 0, $USER->id);
  }
}

function add_label($courseid, $meta) {
  global $DB;

  $label = new stdClass();
  $label->course = $courseid;
  $label->name = "Content of the course";
  $label->intro = $meta->content;
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

function create_role_and_provider($provider){
  global $DB, $USER;

  $role = new stdClass();
  $role->shortname = str_replace(" ", "", strtolower($provider));
  $role->name = $provider;
  $role->description = $provider;
  $role->sortorder = $DB->get_records_sql("SELECT max(sortorder) as sortorder from {role}");
  $role->sortorder = reset($role->sortorder);
  $role->sortorder = $role->sortorder->sortorder;
  ++$role->sortorder;
  try {
    $role_id = $DB->insert_record('role',$role);
    $role_context = new stdClass();
    $role_context->roleid = $role_id;
    $role_context->contextlevel = 10;

    $DB->insert_record('role_context_levels',$role_context);

    $providerRec = new stdClass();
    $providerRec->provider = $provider;
    $providerRec->role = $role_id;
    $DB->insert_record('meta_providers', $providerRec);
    echo "200";
  } catch (Exception $e) {
    echo json_encode($e);
  }
}


function check_provider_role($courseid){
  global $USER, $DB;
  $context = context_system::instance();
  $roles = get_user_roles($context, $USER->id, true);

  $metacourse = $DB->get_record("meta_course",array("id"=>$courseid));

  $provider_id = $metacourse->provider;
  $provider = $DB->get_record("meta_providers", array("id"=>$provider_id));
  $course_role = $provider->role;

  foreach ($roles as $key => $role) {
    if ($role->roleid == $course_role) {
      return true;
    }
  }

  return false;
}

function check_if_not_enrolled($userid, $courseid) {
  global $DB;

  //$context = context_course::instance($courseid);
  $students = $DB->record_exists_sql("select u.id from user u join (select ue.*
            from user_enrolments ue
            join enrol e on ue.enrolid = e.id where e.courseid = :cid and ue.status = 0) a
            on u.id = a.userid
			AND u.id = :userid
			",
    array("cid"=>$courseid, "userid" => $userid)
  );
  return $students;
}

function get_courses_in_category($category_id, $competence_id){
  global $DB;
  $courses = $DB->get_records_sql("
        select  distinct cde.*, da.category from {meta_datecourse} da
        JOIN
        (SELECT d.*, pr.provider
                FROM {meta_providers} pr JOIN (
                    SELECT c.id, c.localname,c.localname_lang, c. target, c.name, c.provider as providerid, u.username, u.firstname, u.lastname, u.email, c.unpublishdate
                    FROM {meta_course} c
                    LEFT JOIN {user} u on c.coordinator = u.id
                    ORDER BY c.provider asc) d
                ON pr.id = d.providerid) cde
        ON cde.id = da.metaid");

  $result = array();

  foreach ($courses as $i => $course) {
    $targets = json_decode($course->target);
    if ($category_id != 0 && $competence_id != 0) {
      if (in_array($category_id, $targets) && ($course->category == $competence_id)) {
        $result[$i] = $course;
      }
    }
    if ($category_id == 0 && $competence_id != 0) {
      if ($course->category == $competence_id) {
        $result[$i] = $course;
      }
    }
    if ($category_id != 0 && $competence_id == 0) {
      if (in_array($category_id, $targets)) {
        $result[$i] = $course;
      }
    }
  }
  return $result;
}

function get_users_on_waitinglist($courseid) {
  global $DB;
  $metaid = $DB->get_field('meta_datecourse', 'metaid', array('courseid'=>$courseid));
  return $DB->get_records_sql(
    "SELECT u.*
         FROM {meta_waitlist} mw
         JOIN {user} u
         ON mw.userid = u.id
         WHERE (mw.courseid = :courseid and mw.nodates = 0) or (mw.courseid = :metaid and mw.nodates = 1)
		 ORDER BY mw.timecreated",
    array("courseid"=>$courseid, "metaid"=>$metaid)
  );
}

function is_user_enrolled($userid, $courseid){
  global $DB;

  return $DB->record_exists_sql("SELECT e.courseid, ue.userid FROM {enrol} e
        JOIN {user_enrolments} ue ON e.id = ue.enrolid
        WHERE e.courseid = :courseid  AND ue.userid = :userid",
    array('userid' => $userid, 'courseid' => $courseid));
}

//newly added function that returns enrolled, not enrolled and waitlist users
function get_datecourse_users($courseid){
  global $DB;

  $context = CONTEXT_COURSE::instance($courseid);

  $metacourse = $DB->get_records_sql("SELECT mp.role as providerid, mc.coordinator as metacoordinatorid, md.coordinator as datecoordinatorid FROM {meta_course} mc
                                        JOIN {meta_datecourse} md ON md.metaid = mc.id
                                        JOIN {meta_providers} mp ON mp.id = mc.provider
                                        WHERE md.courseid = :courseid", array('courseid' => $courseid));

  $enrolled_users = $DB->get_records_sql("SELECT ra.userid, u.*
            FROM {role_assignments} ra
            JOIN {user} u ON ra.userid = u.id
            WHERE ra.contextid = :contextid
            AND ra.roleid = 5
			ORDER BY u.username ASC",
    array('contextid' => $context->id));



  $waiting_users = get_users_on_waitinglist($courseid);

  $excluded_uids = array_map(function ($user) {
      return $user->userid;
    }, $enrolled_users) + array_map(function ($user) {
      return $user->id;
    }, $waiting_users);

  if (count($excluded_uids)) {
    list($where, $params) = $DB->get_in_or_equal($excluded_uids, SQL_PARAMS_NAMED, 'param', false);
    $where = 'u.id ' . $where . ' AND ';
  } else {
    $where = '';
    $params = array();
  }

  $not_enrolled_users = $DB->get_records_sql("
      SELECT u.id, u.firstname, u.lastname, u.username, u.email
      FROM {user} u
      WHERE $where u.id <> :guest and u.deleted <> 1 AND u.firstname IS NOT NULL AND u.firstname <> ''
      ORDER BY u.username ASC", $params + array("guest"=>1));

  return array($enrolled_users, $not_enrolled_users, $waiting_users);
}

function get_available_coordinators() {
  global $DB;

  $coordinators = $DB->get_records_sql("
    select distinct u.id, u.username, u.`firstname`, u.lastname, u.email
    from {user} u
    where u.id <> 1 and u.deleted <> 1 and u.suspended <> 1 AND u.email <> '' AND u.firstname <> ''
    ORDER BY username ASC
 ");

  return array_map(function ($arg){
    return strtoupper($arg->username) . " - " . $arg->firstname . " " . $arg->lastname;
  }, $coordinators);
}