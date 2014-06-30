<?php

require_once('../../config.php');
require_once("$CFG->libdir/moodlelib.php");
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
$sendEmail = optional_param("sendEmail", false, PARAM_BOOL);
$enrolRole = optional_param("enrolRole", "", PARAM_TEXT);

$getTemplate = optional_param("getTemplate", 0, PARAM_INT);

if ($enrolGuy && $enrolCourse && $enrolRole) {
	try {
		$instance = $DB->get_records_sql("SELECT * FROM {enrol} where enrol= :enrol and courseid = :courseid and status = 0", array('enrol'=>'manual','courseid'=>$enrolCourse));
		$instance = reset($instance);
		
		$datecourse = $DB->get_record('meta_datecourse', array('courseid' => $enrolCourse));
		
		$context = CONTEXT_COURSE::instance($enrolCourse);
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
		
		if($datecourse->total_places <=  count($enrolled_users)) {
			$waitRecord = new stdClass();
			$waitRecord->userid = $enrolGuy;
			$waitRecord->courseid = $enrolCourse;
			$waitRecord->timestart = 0;
			$waitRecord->timeend = 0;
			$waitRecord->timecreated = time();
			$DB->insert_record('meta_waitlist', $waitRecord);
			echo json_encode(array(
				'action' => 'enrol',
				'status' => 'waitlist',
			));
			return;
		}
		
		if(!$instance){
		  $enrolManual = enrol_get_plugin('manual');
		  $course = $DB->get_record('course', array('id' => $enrolCourse));
		  $instance = $enrolManual->add_default_instance($course);
		  $instance = $DB->get_records_sql("SELECT * FROM {enrol} where enrol= :enrol and courseid = :courseid and status = 0", array('enrol'=>'manual','courseid'=>$enrolCourse));
		  $instance = reset($instance);
		}
		
		$enrol = new enrol_manual_pluginITK();
		$role = ($enrolRole == 'teacher') ? 3 : 5;
		$enrolUser = $DB->get_record("user", array("id"=>$enrolGuy));
		$enrol->enrol_user($instance, $enrolGuy, $role);
		$DB->set_field("user_enrolments", "status", 0, array("enrolid"=>$instance->id, "userid"=>$enrolGuy));
		if ($sendEmail) {
			$enrol->send_confirmation_email($enrolUser, $enrolCourse);
		}
		echo json_encode(array(
			'action' => 'enrol',
			'status' => 'done',
			'role' => $role,
		));
	} catch (Exception $e) {
		http_response_code(500);
		echo json_encode($e);
	}
}


if ($unenrolGuy && $enrolCourse) {
	try {
		$instance = $DB->get_records_sql("SELECT * FROM {enrol} where enrol= :enrol and courseid = :courseid and status = 0", array('enrol'=>'manual','courseid'=>$enrolCourse));
		$instance = reset($instance);
		
		$DB->delete_records('meta_waitlist', array(
			'courseid' => $enrolCourse,
			'userid' => $unenrolGuy,
			'nodates' => 0,
		));

		$enrolments = enrol_get_plugin('manual');
		$enrolments->unenrol_user($instance, $unenrolGuy);

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

	$courses = $DB->get_records_sql("SELECT * FROM {meta_datecourse} where metaid = :id ", array("id"=> $courseid));

	foreach ($courses as $key => $course) {
		$context = context_course::instance($course->courseid);

		$query = 'select u.id as id, firstname, lastname, picture, imagealt, email from {role_assignments} as a, {user} as u where contextid=' . $context->id . ' and roleid=5 and a.userid=u.id';
		$rs = $DB->get_recordset_sql( $query ); 
		foreach( $rs as $r ) { 
         file_put_contents("C:\\xampp\htdocs\moodle\\enrolled_users.xls", $r->firstname . "\t" . $r->lastname ."\t" .$r->email . "\n", FILE_APPEND);
		}
	}
	$file_url = "C:\\xampp\htdocs\moodle\\enrolled_users.xls";
	header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
	header("Content-Disposition: attachment; filename=enrolled_users.xls");  //File name extension was wrong
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Cache-Control: private",false);
    ob_clean();
    flush();
    readfile($file_url);
    unlink($file_url);
    exit;
}