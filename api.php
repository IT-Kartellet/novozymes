<?php
require_once('../../config.php');
require_once("$CFG->libdir/moodlelib.php");


require_login();
require_capability('moodle/course:create', context_system::instance());

$newLocation    = optional_param("newLocation", "",PARAM_TEXT);
$getLocations   = optional_param("getLocations",0, PARAM_INT);
$getProviders   = optional_param("getProviders",0, PARAM_INT);
$deleteLocation = optional_param("deleteLocation", 0, PARAM_INT);
$newProvider    = optional_param("newProvider", "", PARAM_TEXT);
$deleteProvider = optional_param("deleteProvider", 0, PARAM_INT);
$deleteMeta     = optional_param("deleteMeta", 0, PARAM_INT);

//template
$saveTemplate         = optional_param("saveTemplate", 0, PARAM_INT);
$courseName           = optional_param("courseName", "" ,PARAM_TEXT);
$courseLocalName      = optional_param("courseLocalName", "", PARAM_TEXT);
$courseLocalNameLang  = optional_param("courseLocalNameLang","", PARAM_TEXT);
$coursePurpose        = optional_param("coursePurpose","",PARAM_RAW);
$courseTarget         = optional_param("courseTarget","",PARAM_TEXT);
$courseContent        = optional_param("courseContent","",PARAM_RAW);
$courseInstructors    = optional_param("courseInstructors","",PARAM_TEXT);
$courseComment        = optional_param("courseComment","",PARAM_TEXT);
$courseDurationNumber = optional_param("courseDurationNumber",0,PARAM_INT);
$courseDurationUnit   = optional_param("courseDurationUnit",0,PARAM_INT);
$courseCancellation   = optional_param("courseCancellation","",PARAM_RAW);
$courseCoordinator    = optional_param("courseCoordinator",0,PARAM_INT);
$courseProvider       = optional_param("courseProvider",0,PARAM_INT);

$getTemplate = optional_param("getTemplate", 0, PARAM_INT);

if ($newLocation) {
	$loc = new stdClass();
	$loc->location = $newLocation;
	$loc->active = 1;

	$loc_id = $DB->insert_record("meta_locations", $loc);

	$location = $DB->get_records_sql("SELECT * FROM {meta_locations} where id = :id", array("id"=>$loc_id));

	echo json_encode($location);

}

if ($newProvider) {
	$pro = new stdClass();	
	$pro->provider = $newProvider;
	$pro->active = 1;

	$pro_id = $DB->insert_record("meta_providers", $pro);

	$provider = $DB->get_records_sql("SELECT * FROM {meta_providers} where id = :id", array("id"=>$pro_id));

	echo json_encode($provider);

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
		delete_course($dc->courseid, false);
	}
	try{
		//delete datecourses
		$DB->delete_records("meta_datecourse",array("metaid"=>$deleteMeta));

		//delete metacourses
		$DB->delete_records("meta_course",array("id"=>$deleteMeta));
	} catch(Exception $e){
		//courses have already been deleted by the delete_course hook.
	}
	header("Location: " . $CFG->wwwroot."/blocks/metacourse/list_metacourses.php" );

}

if ($deleteProvider != 0) {
	try{
		$DB->delete_records("meta_providers", array("id"=>$deleteProvider));
	} catch(Exception $e){
		echo "Could not delete provider!";
	}

	$providers = $DB->get_records_sql("SELECT * FROM {meta_providers}");
	echo json_encode($providers);
}

if ($saveTemplate && $coursePurpose && 
	$courseTarget && $courseContent && 
	$courseInstructors && $courseDurationUnit && 
	$courseDurationNumber && $courseCoordinator && 
	$courseProvider) {
	
	try {
		$template                 = new stdClass();
		$template->name           = $courseName;
		$template->localname      = $courseLocalName;
		$template->localname_lang = $courseLocalNameLang;
		$template->purpose        = $coursePurpose;
		$template->target         = $courseTarget;
		$template->content        = $courseContent;
		$template->instructors    = $courseInstructors;
		$template->comment        = $courseComment;
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
		echo "Error. Please try again";
	}

}

if ($getTemplate != 0) {
	$return = $DB->get_record("meta_template", array("id"=>$getTemplate));
	if ($return) {
		echo json_encode($return);
	} else {
		echo json_encode("");
	}
}