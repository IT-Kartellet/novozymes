<?php
	ini_set('display_errors',1);
	ini_set('display_startup_errors',1);
	error_reporting(-1);
	
    require_once('../../config.php');
    global $CFG;
    require_once("$CFG->libdir/enrollib.php");
    require_once("$CFG->libdir/filelib.php");

    $action = 'assign';
    $deleted = '1';
    $time = '1405915200';

    global $DB;

    $enrollib = enrol_get_plugin('manual');

    $users_course = $DB->get_records_sql("SELECT l.id, l.time, l.userid, u.username, l.course, u.deleted, u.firstname, u.lastname, u.email, l.url
                FROM {log} l JOIN {user} u on u.id = l.userid
                WHERE action = :action AND time < :time
				GROUP BY l.userid, l.course, l.url
                ORDER BY time ASC",
                array('action' => $action, 'time' => $time));
    $users_to_enroll = array();

    foreach ($users_course as $user_course){

        $enrolled = $DB->get_record_sql("SELECT count(*) as count FROM {log}
                WHERE userid = :userid AND course = :course AND (action = :enrolled OR action =:unenrolled)",
                array('userid' => $user_course->userid, 'course' => $user_course->course, 'enrolled' => 'assign', 'unenrolled' => 'unassign'));

        if (!user_enrolled($enrolled->count)){
            continue;
        }

        $new_account = $DB->get_record_sql("SELECT * FROM {user}
                WHERE firstname = :firstname AND lastname = :lastname AND deleted = 0 AND username LIKE :username AND username NOT LIKE '%(%)%'",
                array('firstname' => $user_course->firstname, 'lastname' => $user_course->lastname, 'username' => '%'.$user_course->username.'%'));
        if (empty($new_account)){
            continue;
        }
        $enrolment = $DB->get_records_sql("SELECT * FROM enrol e
	            JOIN user_enrolments ue ON e.id = ue.enrolid
	            WHERE ue.userid = :userid AND e.courseid = :courseid",
                array('userid' => $new_account->id, 'courseid' => $user_course->course));
        if (empty($enrolment)){
            $instance = $DB->get_records_sql("SELECT * FROM {enrol}
                WHERE enrol= :enrol AND courseid = :courseid AND status = 0",
                array('enrol' => 'manual','courseid'=>$user_course->course));
            $role_id = parse_role_id($user_course->url);
            if (!empty($role_id)){
				$new_account->course = $user_course->course;
				$new_account->roleid = $role_id;
                $users_to_enroll[] = $new_account;
                
            }
        }
    }

    export_to_xls($users_to_enroll);

    function parse_role_id($url){
        $arr_url = explode('&', $url);
        $roleid = '';

        foreach($arr_url as $part){
            $index = strpos($part, 'roleid');
            if ($index !== false){
                $roleid = substr($part, $index + 7);
                return $roleid;
            }
        }
        return $roleid;
    }

    function user_enrolled($count){
        return ($count % 2) == 1;
    }

    function export_to_xls($users){
        global $CFG;

        $file = $CFG->tempdir . '\\enrolled_users' . uniqid() . '.xls';

         foreach ($users as $user) {
             file_put_contents($file, $user->firstname . "\t" . $user->lastname ."\t" . $user->username . "\t" . $user->course . "\t" . $user->roleid . "\n", FILE_APPEND);
         }
         send_temp_file($file, 'enrolled_users.xls');
        
    }

?>