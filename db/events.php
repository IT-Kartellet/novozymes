<?php
$handlers = array(
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