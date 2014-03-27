<?php

require_once("$CFG->libdir/formslib.php");

class datecourse_form extends moodleform {
    public $number = 0;

    public function definition() {
        global $CFG, $DB, $PAGE, $USER;
        $PAGE->requires->js(new moodle_url('/lib/jquery/jquery-1.9.1.min.js'));
        $PAGE->requires->js(new moodle_url('js/core.js'));
 
        $mform = $this->_form;
        $numberOfDates = ($this->_customdata['dateCourseNr'])? $this->_customdata['dateCourseNr'] : 1;

        //get locations from the database
        $locations = $DB->get_records_sql("SELECT * FROM {meta_locations}");        
        $locations = array_map(function ($arg){
                return $arg->location;
            }, $locations);
        //create the language select
        $languages = $DB->get_records_sql("SELECT * FROM {meta_languages} where active = :active",array("active"=>1));
        $languages = array_map(function($lang){
            return $lang->language;
        }, $languages);

        //create the currency select
        $currencies = $DB->get_records_sql("SELECT * FROM {meta_currencies} order by currency");
        $currencies = array_map(function($curr){
            return $curr->currency;
        }, $currencies);

        //create the categories select
        // $categories = $DB->get_records_sql("SELECT id, name FROM {course_categories}");
        // $categories = array_map(function($cat){
        //     return $cat->name;
        // }, $categories); 

        $coordinators = $DB->get_records_sql("
            select distinct u.id, u.username, u.`firstname`, u.lastname, u.email from {user} u join 
                {role_assignments} ra on u.id = ra.userid and ra.roleid in (1,2,3,4) and u.id <> 1
         ");     
        $coordinators = array_map(function ($arg){
                return " (" .$arg->firstname . " " . $arg->lastname . ") " .$arg->email;
            }, $coordinators);

        $mform->addElement('header', 'header_courses', 'COURSES');
        $mform->addElement('html',"<div id='wrapper'>");

        $key = $this->number;
        while($key <= $numberOfDates-1) {
            $mform->addElement('html',"<div class='template'>");
            $mform->addElement('hidden','datecourse['. $key .'][id]', '0');
            // $mform->addElement('select', 'datecourse['. $key .'][category]', 'Category', $categories, null);
            $mform->addElement('date_time_selector', 'timestart['. $key .']', get_string("from"), array('startyear'=>2013, 'stopyear'=>2020, 'optional'=>false));
            $mform->addElement('date_time_selector', 'timeend['. $key .']', get_string("to"), array('startyear'=>2013, 'stopyear'=>2020, 'optional'=>false));
            $mform->addElement('select', 'datecourse['. $key .'][location]', 'Location', $locations, null);
            $mform->addElement('html', "<div class='fitem'><div class='felement'> <a href='#' class='anotherLocation' > + another location </a></div></div>");
            $mform->addElement('select', 'datecourse['. $key .'][language]', 'Language', $languages, null);

            $mform->addElement('text', 'datecourse['. $key .'][price]', 'Price');
            $mform->addElement('select', 'datecourse['. $key .'][currency]', 'Currency', $currencies, null);
            $mform->addElement('text', 'datecourse['. $key .'][places]', 'Nr. of places');
            $mform->addElement('select', 'datecourse['. $key .'][coordinator]', 'Coordinator', $coordinators, null);
            $mform->setDefault('coordinator', $USER->id);
            $mform->addElement('date_time_selector', 'publishdate['. $key .']', "Publish date", array('startyear'=>2013, 'stopyear'=>2020, 'optional'=>false));
            $mform->addElement('date_time_selector', 'startenrolment['. $key .']', "Start enrolment date", array('startyear'=>2013, 'stopyear'=>2020, 'optional'=>false));
            $mform->addElement('date_time_selector', 'unpublishdate['. $key .']', "End enrolment date", array('startyear'=>2013, 'stopyear'=>2020, 'optional'=>false));

            $mform->addElement('html',"</div>");

            $mform->setType('datecourse['. $key .'][id]', PARAM_INT);
            $mform->setType('datecourse['. $key .'][price]', PARAM_NOTAGS);
            $mform->setType('datecourse['. $key .'][places]', PARAM_NOTAGS);

            $mform->addRule('datecourse['. $key .'][places]', "Needs to be a number", 'numeric', null, 'client');
            $mform->addRule('datecourse['. $key .'][price]', "Needs to be a number", 'numeric', null, 'client');
            $mform->addRule('datecourse['. $key .'][places]', get_string('required'), 'required', null, 'client');
            $mform->addRule('datecourse['. $key .'][price]', get_string('required'), 'required', null, 'client');
            $mform->addRule('timestart['. $key .']', get_string('required'), 'required', null, 'client');
            $mform->addRule('timeend['. $key .']', get_string('required'), 'required', null, 'client');
           
            $key++;
        }
        unset($key);


        $mform->addElement('html',"</div>");
        $mform->addElement('html',"<input type='button' id='addDateCourse' value='Add another date'>");
        $mform->addElement('html',"<input type='button' id='removeDateCourse' value='Remove date'>");

        $this->add_action_buttons(true, "Save");

        if ($data = $this->_customdata['data']) {
            $awesomeData = new stdClass();

            $horribleCounter = 0; // he doesn't eat his vegetables
            foreach ($data as $key => $dc) {
                $awesomeData->{'datecourse['. $horribleCounter .'][id]'} = $dc->id;
                // $awesomeData->{'datecourse['. $horribleCounter .'][category]'} = $dc->category;
                $awesomeData->{'timestart['. $horribleCounter .']'} = $dc->startdate;
                $awesomeData->{'timeend['. $horribleCounter .']'} = $dc->enddate;
                $awesomeData->{'datecourse['. $horribleCounter .'][location]'} = $dc->location;
                $awesomeData->{'datecourse['. $horribleCounter .'][language]'} = $dc->lang;
                $awesomeData->{'datecourse['. $horribleCounter .'][price]'} = $dc->price;
                $awesomeData->{'datecourse['. $horribleCounter .'][currency]'} = $dc->currencyid;
                $awesomeData->{'datecourse['. $horribleCounter .'][places]'} = $dc->total_places;
                $awesomeData->{'datecourse['. $horribleCounter .'][coordinator]'} = $dc->coordinator;

                $horribleCounter++;
            }
            unset($horribleCounter);

            $this->set_data($awesomeData);
        } else {
            $this->set_data(null);
        }



    }



}