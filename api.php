<?php

require_once('../../config.php');
require_once("$CFG->libdir/moodlelib.php");
require_once("$CFG->libdir/filelib.php");
require_once('lib.php');

require_login();
// require_capability('moodle/course:create', context_system::instance());

$newLocation    = optional_param("newLocation", "",PARAM_TEXT);
$newTarget    = optional_param("newTarget", "",PARAM_TEXT);
$getLocations   = optional_param("getLocations",0, PARAM_INT);
$getProviders   = optional_param("getProviders",0, PARAM_INT);
$deleteLocation = optional_param("deleteLocation", 0, PARAM_INT);
$renameLocationID = optional_param("renameLocationID", 0, PARAM_INT);
$renameProviderID = optional_param("renameProviderID", 0, PARAM_INT);
$renameLocationText = optional_param("renameLocationText", "", PARAM_TEXT);
$renameProviderText = optional_param("renameProviderText", "", PARAM_TEXT);
$newProvider    = optional_param("newProvider", "", PARAM_TEXT);
$deleteProvider = optional_param("deleteProvider", 0, PARAM_INT);
$deleteMeta     = optional_param("deleteMeta", 0, PARAM_INT);
$exportExcel     = optional_param("exportExcel", 0, PARAM_INT);

//template
$saveTemplate         = optional_param("saveTemplate", 0, PARAM_INT);
$courseName           = optional_param("courseName", "" ,PARAM_TEXT);
$courseLocalName      = optional_param("courseLocalName", "", PARAM_TEXT);
$courseLocalNameLang  = optional_param("courseLocalNameLang","", PARAM_TEXT);
$coursePurpose        = optional_param("coursePurpose","",PARAM_RAW);
$courseTarget         = optional_param("courseTarget","",PARAM_TEXT);
$courseTargetDesc     = optional_param("courseTargetDesc","",PARAM_RAW);
$courseContent        = optional_param("courseContent","",PARAM_RAW);
$courseInstructors    = optional_param("courseInstructors","",PARAM_TEXT);
$courseComment        = optional_param("courseComment","",PARAM_RAW);
$courseDurationNumber = optional_param("courseDurationNumber",0,PARAM_INT);
$courseDurationUnit   = optional_param("courseDurationUnit",0,PARAM_INT);
$courseCancellation   = optional_param("courseCancellation","",PARAM_RAW);
$courseLodging   	  = optional_param("courseLodging","",PARAM_RAW);
$courseContact   	  = optional_param("courseContact","",PARAM_RAW);
$courseCoordinator    = optional_param("courseCoordinator",0,PARAM_INT);
$courseProvider       = optional_param("courseProvider",0,PARAM_INT);

/// allow others to enroll you
$newAllow = optional_param("newAllow",0, PARAM_INT);
$removeAllow = optional_param("removeAllow",0, PARAM_INT);
$enrolGuy = optional_param("enrolGuy",0,PARAM_INT);
$unenrolGuy = optional_param("unenrolGuy",0,PARAM_INT);
$enrolCourse = optional_param("enrolCourse",0,PARAM_INT);
$enrolRole = optional_param("enrolRole", "", PARAM_TEXT);

$getTemplate = optional_param("getTemplate", 0, PARAM_INT);

if ($enrolGuy && $enrolCourse && $enrolRole) {
	try {
		$role = ($enrolRole == 'teacher') ? 3 : 5;

		$instance = $DB->get_records('enrol', array('status' => 0, 'enrol'=>'manual','courseid'=>$enrolCourse, 'roleid' => $role));
		$instance = reset($instance);
		
		$datecourse = $DB->get_record('meta_datecourse', array('courseid' => $enrolCourse));
		
		$context = CONTEXT_COURSE::instance($enrolCourse);
		$PAGE->set_context($context);

		$course = $DB->get_record('course', array('id' => $enrolCourse));
			
		list($students, $not_enrolled_users) = get_datecourse_users($enrolCourse);
	
		$enrol = new enrol_manual_pluginITK();

		if (!$datecourse->elearning && $enrolRole === 'student' && $datecourse->total_places <= count($students)) {
			$waitRecord = new stdClass();
			$waitRecord->userid = $enrolGuy;
			$waitRecord->courseid = $enrolCourse;
			$waitRecord->timestart = 0;
			$waitRecord->timeend = 0;
			$waitRecord->timecreated = time();
			$DB->insert_record('meta_waitlist', $waitRecord);

			add_to_log($enrolCourse, 'block_metacourse', 'add enrolment', 'blocks/metacourse/enrol_others_into_course.php', "$enrolGuy successfully added to the waiting list.");

			$enrol->send_waitlist_email($enrolGuy, $enrolCourse);

			echo json_encode(array(
				'action' => 'enrol',
				'status' => 'waitlist',
			));
			return;
		}

		if (!$instance) {
			$instance = new stdClass();
			$instance->enrol          = 'manual';
			$instance->status         = ENROL_INSTANCE_ENABLED;
			$instance->courseid       = $course->id;
			$instance->enrolstartdate = 0;
			$instance->enrolenddate   = 0;
			$instance->roleid         = $role;
			$instance->timemodified   = time();
			$instance->timecreated    = $instance->timemodified;
			$instance->sortorder      = $DB->get_field('enrol', 'COALESCE(MAX(sortorder), -1) + 1', array('courseid'=>$course->id));

			$instance->id = $DB->insert_record('enrol', $instance);
		}

		$enrolUser = $DB->get_record("user", array("id"=>$enrolGuy));
		$enrol->enrol_user($instance, $enrolGuy, $role);
		$DB->set_field("user_enrolments", "status", 0, array("enrolid"=>$instance->id, "userid"=>$enrolGuy));

		if (is_user_enrolled($enrolGuy, $enrolCourse)) {
			add_to_log($enrolCourse, 'block_metacourse', 'add enrolment', 'blocks/metacourse/enrol_others_into_course.php', "$enrolGuy successfully enrolled.");
			$enrol->send_confirmation_email($enrolUser, $enrolCourse);
			echo json_encode(array(
				'action' => 'enrol',
				'status' => 'done',
				'role' => $role,
			));
		} else {
			add_to_log($enrolCourse, 'block_metacourse', 'add enrolment', 'blocks/metacourse/enrol_others_into_course.php', "Tried to enrol $enrolGuy into course $enrolCourse, but somehow that failed");

			echo json_encode(array(
				'action' => 'enrol',
				'status' => 'error',
				'role' => $role,
			));
		}
	} catch (Exception $e) {
		http_response_code(500);
		echo json_encode($e);
	}
}


if ($unenrolGuy && $enrolCourse) {
	try {
		$instance = $DB->get_records_sql("SELECT * FROM {enrol} where enrol= :enrol and courseid = :courseid and status = 0", array('enrol'=>'manual','courseid'=>$enrolCourse));
		$instance = reset($instance);

		//check if on waiting list
		$waiting = $DB->record_exists('meta_waitlist', array(
			'courseid' => $enrolCourse,
			'userid' => $unenrolGuy,
			'nodates' => 0,
		));
		
		$DB->delete_records('meta_waitlist', array(
			'courseid' => $enrolCourse,
			'userid' => $unenrolGuy,
			'nodates' => 0,
		));

		$PAGE->set_context(context_course::instance($enrolCourse)); // Needed in send mail
		$enrolments = enrol_get_plugin('manual');
		$enrolments->unenrol_user($instance, $unenrolGuy);
		$enrol = new enrol_manual_pluginITK();
		$enrol->sendUnenrolMail($unenrolGuy, $enrolCourse, $waiting);

		add_to_log($enrolCourse, 'block_metacourse', 'remove enrolment', 'blocks/metacourse/enrol_others_into_course.php', "Unenrolled $unenrolGuy from $enrolCourse");

		echo json_encode("done");
	} catch (Exception $e) {
		http_response_code(500);
		echo json_encode($e);
	}
}

if ($newAllow != 0) {
	try {
		$allow = new stdClass();
		$allow->canenrol = $newAllow;
		$allow->canbeenrolled = $USER->id;
		$DB->insert_record("meta_allow_enrol", $allow);
		
	} catch (Exception $e){
		
	}
}

if ($removeAllow != 0) {
	try {
		$DB->delete_records("meta_allow_enrol",array("canenrol"=>$removeAllow, "canbeenrolled"=>$USER->id));
	} catch (Exception $e){
		
	}
}

if ($newLocation) {
	$loc = new stdClass();
	$loc->location = $newLocation;
	$loc->active = 1;

	$loc_id = $DB->insert_record("meta_locations", $loc);

	$location = $DB->get_records_sql("SELECT * FROM {meta_locations} where id = :id", array("id"=>$loc_id));

	echo json_encode($location);

}

if ($renameLocationID && $renameLocationText) {
	
	$loc_id = $DB->set_field('meta_locations', 'location', $renameLocationText, array('id'=>$renameLocationID));
	$loc = $DB->set_field('meta_locations', 'location', $renameLocationText, array('id' => $renameLocationID));
	$locations = $DB->get_records_sql("SELECT * FROM {meta_locations}");

	echo json_encode($locations);
}

if ($renameProviderID && $renameProviderText) {
	
	$DB->set_field('meta_providers', 'provider', $renameProviderText, array('id' => $renameProviderID));
	$providers = $DB->get_records_sql("SELECT * FROM {meta_providers}");

	$r_id = $DB->get_record("meta_providers",array("id"=>$renameProviderID));
	$r_id = $r_id->role;
	$DB->set_field('role', 'name', $renameProviderText, array('id' => $r_id));
	$DB->set_field('role', 'description', $renameProviderText, array('id' => $r_id));
	$DB->set_field('role', 'shortname', str_replace(" ", "", strtolower($renameProviderText)), array('id' => $r_id));


	echo json_encode($providers);

}

if ($newTarget) {
	$target = new stdClass();
	$target->name = $newTarget;

	$tar_id = $DB->insert_record("meta_locations", $target);

	$new_t = $DB->get_records_sql("SELECT * FROM {meta_category} where id = :id", array("id"=>$tar_id));

	echo json_encode($new_t);

}

if ($newProvider) {
		if(create_role_and_provider($newProvider)){
			echo json_encode("200");
		} else {
			echo json_encode("500");
		}
}

if ($getLocations == 1) {
	$locations = $DB->get_records_sql("SELECT * FROM {meta_locations}");

	echo json_encode($locations);
}

if ($getProviders == 1) {
	$providers = $DB->get_records_sql("SELECT * FROM {meta_providers}");

	echo json_encode($providers);
}


if ($deleteLocation != 0) {
	try{
		$DB->delete_records("meta_locations", array("id"=>$deleteLocation));
	} catch(Exception $e){
		echo "Could not delete location";
	}

	$locations = $DB->get_records_sql("SELECT * FROM {meta_locations}");
	echo json_encode($locations);
}

if ($deleteMeta) {
	$datecourses = $DB->get_records_sql("SELECT * FROM {meta_datecourse} WHERE metaid = :id", array("id"=>$deleteMeta));

	//delete courses
	foreach ($datecourses as $key => $dc) {
		add_to_log($dc->courseid, 'metacourse', 'Delete datecourse', '', "Metacourse: $deleteMeta", 0, $USER->id);
		delete_course($dc->courseid, false);
	}
	try{
		//delete datecourses
		$DB->delete_records("meta_datecourse",array("metaid"=>$deleteMeta));

		//delete metacourses
		$DB->delete_records("meta_course",array("id"=>$deleteMeta));
		//delete logs
		//$DB->delete_records("log",array("module"=>"metacourse", "url"=>"view_metacourse.php?id=$deleteMeta"));
	} catch(Exception $e){
		//courses have already been deleted by the delete_course hook.
	}
	header("Location: " . $CFG->wwwroot."/blocks/metacourse/list_metacourses.php" );

}

if ($deleteProvider != 0) {
	try{
		$roleToBeDeleted = $DB->get_record("meta_providers", array("id"=>$deleteProvider));
		$roleToBeDeleted = $roleToBeDeleted->role;
		$DB->delete_records("meta_providers", array("id"=>$deleteProvider));
		$DB->delete_records("role",array("id"=>$roleToBeDeleted));
	} catch(Exception $e){
		echo "Could not delete provider!";
	}

	$providers = $DB->get_records_sql("SELECT * FROM {meta_providers}");
	echo json_encode($providers);
}
if ($saveTemplate) {
	try {
		$template                 = new stdClass();
		$template->name           = $courseName;
		$template->localname      = $courseLocalName;
		$template->localname_lang = $courseLocalNameLang;
		$template->purpose        = $coursePurpose;
		$template->target         = $courseTarget;
		$template->target_description         = $courseTargetDesc;
		$template->content        = $courseContent;
		$template->instructors    = $courseInstructors;
		$template->comment        = $courseComment;
		$template->lodging        = $courseLodging;
		$template->contact        = $courseContact;
		$template->duration       = $courseDurationNumber;
		//TODO:
		$template->duration_unit  = $courseDurationUnit;
		$template->cancellation   = $courseCancellation;
		$template->coordinator    = $courseCoordinator;
		$template->provider       = $courseProvider;
		$template->timemodified   = time();
		
		$DB->insert_record("meta_template", $template);

		echo json_encode("Course template was saved");
	} catch (Exception $e) {
		http_response_code(500);
		echo "Error. Please try again";
	}

}

if ($getTemplate != 0) {
	$return = $DB->get_record("meta_template", array("id"=>$getTemplate));
	if ($return) {
		echo json_encode($return);
	} else {
		http_response_code(404);
		echo json_encode("1234");
	}
}

if ($exportExcel) {
	$courseid = $exportExcel;

	// only shows courses from the current year
	$year_start = strtotime("01-01-" . date("Y"));
	
	$metacourse = $DB->get_record('meta_course', array('id' => $courseid));
	$datecourses = $DB->get_records_sql(
		"SELECT meta.*, currency.currency FROM {meta_datecourse} meta 
		LEFT JOIN  {meta_currencies} currency ON meta.currencyid = currency.id 
		WHERE metaid = :id AND meta.startdate > :year_start ORDER BY meta.startdate ASC", 
		array("id"=> $courseid, "year_start" => $year_start)
	);
	
	// Find the next upcomming course
	$upcomming_course = null;
	foreach ($datecourses as $key => $course ) {
		if ($course->startdate > time()) {
			$upcomming_course = $course;
			
			unset($datecourses[$key]);
			break;
		}	
	}
	
	if ($upcomming_course) {
		// And add it to the beginning of the sequence so its output first
		array_unshift($datecourses, $upcomming_course);
	}

	$users = array();
	foreach ($datecourses as $key => $course) {
		if(is_null($course->courseid)){
			echo "Error. Please save this course before exporting";
			exit;
		}
		$context = context_course::instance($course->courseid);

		list($enrolled_users) = get_datecourse_users($course->courseid);
		
		// Sort by firstname, lastname
		usort($enrolled_users, function ($u1, $u2) {
			if ($u1->firstname === $u2->firstname) {
				return $u1->lastname > $u2->lastname;
			}
			return $u1->firstname > $u2->firstname;
		});

		foreach ($enrolled_users as $user) {
			$user->startdate = $course->startdate;
			$user->enddate = $course->enddate;
			$user->timezone = $course->timezone;
			$user->price = $course->price . ' ' . $course->currency;
			$users[] = $user;
		}
	}

	require_once "lib/excel.class.php"; 
	$filename = $CFG->tempdir . '/enrolled_users' . uniqid() . '.xls';
	$fp = fopen("xlsfile://" . $filename, "wb"); 
	if (!is_resource($fp)) 
	{ 
		die("Cannot open $filename"); 
	} 
	
	// Convert headers to upper case and user objects to arrays
	$users_to_write = array();
	foreach ($users as $user) {
		$users_to_write[] = array(
			'First name' => @iconv('UTF-8', 'Windows-1252', $user->firstname),
			'Last name' => @iconv('UTF-8', 'Windows-1252', $user->lastname),
			'Initials' => @$user->email,
			'Department' => @$user->department,
			'Business area' => @$user->institution,
			'Country' => @$user->country,
			'Start date' => !empty($user->startdate) ? format_date_with_tz($user->startdate, $user->timezone) : '',
			'End date' => !empty($user->enddate) ? format_date_with_tz($user->enddate, $user->timezone): '',
			'Price' => !empty($user->price) ? $user->price: '',
		);
	}

	fwrite($fp, serialize($users_to_write)); 
	fclose($fp); 

	send_temp_file($filename, str_replace(' ',	'_', $metacourse->name) . '_enrolled_users.xls');
}