<?php

/* TRANSLATABLE */

$string['pluginname'] = 'Metacourse';
$string['modulename'] = 'Metacourse';
$string['name'] = 'Metacourse';
$string['contentheader'] = 'Metacourse Settings';
$string['modulenameplural'] = 'Courses';
$string['metacourse:view'] = 'View the block';
$string['metacourse:addinstance'] = 'Add the block on the My page';
$string['coursesavailable'] = 'Course menu';
$string['instructors'] = 'Instructors'; 
$string['coursedates'] = 'Course dates'; 
$string['location'] = 'Location'; 
$string['action'] = 'Action'; 
$string['signup'] = 'Sign up'; 
$string['enrolme'] = 'Enrol me'; 
$string['enrolOthers'] = 'Enrol others'; 
$string['enrolOthers-wait'] = 'Sign up others for waiting list'; 
$string['unenrolme'] = 'Confirm'; 
$string['unenrolmebutton'] = 'Cancel my signup'; 
$string['customemail'] = 'Custom email'; 
$string['competence'] = 'Competence'; 
$string['listofcourses'] = "List of all courses";
$string['coursesfor'] = "Courses for";
$string['countries'] = "Countries";
$string['published'] = 'Published'; 

// need translation

$string['purpose'] = 'Purpose';
$string['languages'] = 'Languages';
$string['target'] = 'Target';
$string['target_description'] = "Target description";
$string['content'] = 'Content'; 
$string['comment'] = 'Comment'; 
$string['duration'] = 'Duration'; 
$string['cancellation'] = 'Cancellation'; 
$string['coordinator'] = 'Coordinator'; 
$string['lodging'] = 'Course Location & Lodging'; 
$string['provider'] = 'Provider'; 
$string['contact'] = 'Course Owner'; 
$string['multipledates'] = 'Multiple dates'; 
$string['nrviews'] = 'Views'; 
$string['viewcourse'] = 'View course'; 
$string['courseend'] = 'End'; 
$string['coursestart'] = 'Start'; 
$string['price'] = 'Price'; 
$string['availableseats'] = 'Seats'; 
$string['nrparticipants'] = 'Enrolled'; 
$string['coursedates'] = 'Course dates';
$string['youareenrolled'] = 'You are enrolled'; 
$string['expiredenrolment'] = 'Enrolment ended'; 
$string['addtowaitinglist'] = 'Add me to waiting list'; 
$string['enrol_waitinglist_title'] = 'Waiting list terms title';
$string['enrol_waitinglist_contents'] = 'Waiting list terms contents';
$string['enrol_waitinglist_tos'] = 'Waiting list TOS';

$string['tostitle'] = 'Terms of agreement'; 
$string['tosaccept'] = 'I accept the terms of agreement'; 
$string['cancellationaccept'] = 'If cancelling later than 5 weeks prior to the course your department will be charged the full amount.'; 
$string['agreecancel'] = 'I agree to the cancellation policy.'; 
$string['tosacceptwait'] = 'and I have acknowledged that I will be enrolled as soon as there is an available seat'; 
$string['toscontent'] = 'Registration requires an accept from you manager. Please tick the box below to confirm that you have this accept. 

Cancellation
Please read the terms of cancellation for the specific course before signing up.

Payments
Course fees will be charged you department immediately after the course.
'; 
//Enrolment email
$string['emailconf'] = '
Dear {$a->firstname} {$a->lastname},
 
We hereby confirm that you have been signed up for:
Title: {$a->course}
Dates: {$a->periodfrom} - {$a->periodto}
Price: {$a->currency} {$a->price} - If no amount is stated there is no fee. (Except for language courses in Danish)
Location: {$a->location}

Billing information:
Billing department: {$a->department}

Further information will follow before the course. 

Please note that you are responsible for for adding the event to your calendar; however, to make things easier, you can use the attached link to automate the process. 

Payment:
If any course fee the amount will be debit your department immediately after the course. 

Cancellation
Please see the cancellation policy in the course description. 

To cancel your participation please follow the link to {$a->myhome}

Best regards
{$a->coordinator}
';

//Enrolment email
$string['emailwait'] = '
Dear {$a->firstname} {$a->lastname},
 
We hearby confirm your request for:
Title: {$a->course}

You will automatically be contacted when the course has been scheduled. 

Best regards
{$a->coordinator}
';

//Cancellation Email
$string['emailunenrolconf'] = '
Dear {$a->firstname} {$a->lastname},
 
We have recieved your cancellation for:
Title: {$a->course}
Dates: {$a->periodfrom} - {$a->periodto}
Price: {$a->currency} {$a->price} - If no amount is stated there is no fee. (Except for language courses in Danish)

If your cancellation exceeds the cancellation deadline your department will be required to pay the full course feed. The cancellation policy can be seen in the course description. 

Best regards
{$a->coordinator}
';

// help buttons
$string["name"] = "Name";
$string["meta_name"] = "The name of the course must be in English";
$string["meta_name_help"] = "The name of the course must be in English";
$string["localname"] = "Local name";
$string["localname_help"] = "Title in a different language that will be displayed only to the users using the specified language";
$string["localname_lang"] = "Local language";
$string["localname_lang_help"] = "The language for which the local title should be displayed";
$string["purpose_help"] = "The purpose of the course";
$string["target_help"] = "The target of the course";
$string["target_description_help"] = "The description of the target group of the course";
$string["content_help"] = "The content of the course";
$string["comment_help"] = "Any comments for the course?";
$string["instructors_help"] = "Who will be the instructor of the course?";
$string["cancellation_help"] = "What happens if a user cancels a course";
$string['multipledates_help'] = 'Will your course be held on multiple dates?'; 
$string['customemail_help'] = 'Do you want the user to receive a custom "Welcome email" when they get enrolled into a course?'; 
$string['unpublishdate'] = 'Unpublish date'; 
$string['unpublishdate_help'] = 'When do you want the course not to be shown to student anymore?'; 

$string['timezone'] = 'Timezone?';
$string['timezone_help'] = 'The timezone for the place where the course is held. Remember to account for daylight saving time (DST). For example if the course is held in Denmark (timezone = +1), and DST is active, you have to select +2, since DST adds one hour.';