<?php
$handlers = array(
	'user_enrolled'=>array(
		'handlerfile'=>'/blocks/metacourse/lib.php',
		'handlerfunction'=>'enrol_update_free_places',
		'schedule'=>'instant',
		'internal'=>1
	),
	'course_updated'=>array(
		'handlerfile'=>'/blocks/metacourse/lib.php',
		'handlerfunction'=>'update_metacourse',
		'schedule'=>'instant',
		'internal'=>1
	),
	'course_deleted'=>array(
		'handlerfile'=>'/blocks/metacourse/lib.php',
		'handlerfunction'=>'delete_metacourse',
		'schedule'=>'instant',
		'internal'=>1
	),
	'coursecreated'=>array(
		'handlerfile'=>'/blocks/metacourse/lib.php',
		'handlerfunction'=>'course_created_enrol_waiters',
		'schedule'=>'instant',
		'internal'=>1
	)
);

$observers = array(
	array(
		'eventname' => '\core\event\user_enrolment_deleted',
		'callback' => 'enrol_waiting_user',
		'includefile' => 'blocks/metacourse/lib.php'
	)
);