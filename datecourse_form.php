<?php

require_once("$CFG->libdir/formslib.php");

class datecourse_form extends moodleform {
    public $number = 0;

    public function definition() {
        global $CFG, $DB, $PAGE, $USER;
        $PAGE->requires->js(new moodle_url('js/core.js'));
 
        $mform = $this->_form;
		
		//Add all meta-info and send it through.
		$mform->addElement('hidden','meta', $this->_customdata['meta']);
		$mform->setType('meta', PARAM_RAW);
		
        @$numberOfDates = ($this->_customdata['dateCourseNr'])? $this->_customdata['dateCourseNr'] : 1;

        //timezones
        $timezones = array("-11" => "-11", "-10" => "-10", "-9" => "-9", "-8" => "-8", "-7" => "-7", "-6" => "-6", "-5" => "-5", "-4" => "-4", "-3" => "-3",
            "-2" => "-2", "-1" => "-1", "+0" => "0", "+1" => "+1", "+2" => "+2", "+3" => "+3", "+4" => "+4", "+5" => "+5", "+5:30" => "+5:30", "+6" => "+6", "+7" => "+7",
            "+8" => "+8", "+9" => "+9", "+10" => "+10", "+11" => "+11", "+12" => "+12");

        //get locations from the database
        $locations = $DB->get_records_sql("SELECT * FROM {meta_locations} order by location asc");        
        $locations = array_map(function ($arg){
                return $arg->location;
            }, $locations);
			
        //create the language select
        $languages = $DB->get_records_sql("SELECT * FROM {meta_languages} where active = :active order by language",array("active"=>1));
        $languages = array_map(function($lang){
            return $lang->language;
        }, $languages);

        $ordered_languages = array();
        foreach ($languages as $key => $value) {
            if ($value == 'English') {
                $ordered_languages[$key] = $value;
                unset($languages[$key]);
                array_filter($languages);
            }
        }
        $languages = $ordered_languages + $languages;

        $countries = $DB->get_records_sql("SELECT * FROM {meta_countries} ");
        $countries = array_map(function($c){
            return $c->country;
        }, $countries);

        //create the currency select
        $currencies = $DB->get_records_sql("SELECT * FROM {meta_currencies} order by currency");
        $currencies = array_map(function($curr){
            return $curr->currency;
        }, $currencies);

        $coordinators = get_available_coordinators();

        $mform->addElement('header', 'header_courses', 'COURSES');
        $mform->addElement('html',"<div id='wrapper'>");

        $key = $this->number;
		
		// The data here is keyed by course id, reset it to zero based for easy indexing while we iterate
		@$data = array_values($this->_customdata['data']);
        while($key <= $numberOfDates-1) {
            @$course_data = $data[$key];
            if (isset($course_data->timezone)) {
                $timezone = $course_data->timezone;
            } else {
                $timezone = 0;
            }

            // Datetimeselector needs 5.5 as format, not 5:30
            $timezone = format_tz_offset($timezone);

            $mform->addElement('html',"<div class='template'>");
            $mform->addElement('hidden','datecourse['. $key .'][id]', '0');
            $mform->addElement('hidden','datecourse['. $key .'][courseid]', '0');
            $mform->addElement('hidden','datecourse['. $key .'][deleted]', '0');
            $mform->addElement('html',"<input type='button' id='removeDateCourse' title='Remove date' value='X' class='$key'>");

            $mform->addElement('checkbox', 'datecourse[' . $key . '][elearning]', 'Elearning', '', array('class' => 'elearning'));
            $mform->addHelpButton('datecourse[' . $key . '][elearning]', 'elearning', 'block_metacourse');

            $mform->addElement('date_time_selector', 'datecourse['. $key .'][timestart]', "Start", array('startyear'=>2013, 'stopyear'=>2030, 'optional'=>false, "id"=>"timestart", 'timezone' => $timezone),array("class"=>"timestart"));
            $mform->addElement('date_time_selector', 'datecourse['. $key .'][timeend]', "End", array('startyear'=>2013, 'stopyear'=>2030, 'optional'=>false, 'timezone' => $timezone),array("class"=>"timeend"));
            $mform->addElement('select', 'datecourse['. $key .'][timezone]', "Time zone", $timezones, array("class"=>"timezone"))->setSelected("0");
            $mform->addHelpButton('datecourse['. $key .'][timezone]', 'timezone', 'block_metacourse');

            $mform->addElement('select', 'datecourse['. $key .'][location]', 'Location', $locations, array("class"=>"location"));
            $mform->addElement('select', 'datecourse['. $key .'][country]', 'Where', $countries, array("class"=>"country"));
            $mform->addElement('html', "<div class='fitem'><div class='felement'> <a href='#' class='anotherLocation' > + another location </a></div></div>");
            $mform->addElement('select', 'datecourse['. $key .'][language]', 'Language', $languages, array("class"=>"language"));
            $mform->addElement('text', 'datecourse['. $key .'][price]', 'Price',array("class"=>"price"));
            $mform->addElement('select', 'datecourse['. $key .'][currency]', 'Currency', $currencies, array("class"=>"currency"));
            $mform->addElement('text', 'datecourse['. $key .'][places]', 'No. of places',array("class"=>"noPlaces"));
            $mform->addElement('select', 'datecourse['. $key .'][coordinator]', 'Coordinator', $coordinators, array("class"=>"coordinator"));
            $mform->setDefault('coordinator', $USER->id);
            $mform->addElement('date_time_selector', 'datecourse['. $key .'][publishdate]', "Publish date", array('startyear'=>2013, 'stopyear'=>2030, 'optional'=>false, 'timezone' => $timezone), array("class"=>"publishdate"));
            $mform->addElement('date_time_selector', 'datecourse['. $key .'][startenrolment]', "Start enrolment date", array('startyear'=>2013, 'stopyear'=>2030, 'optional'=>false, 'timezone' => $timezone), array("class"=>"startenrolment"));
            $mform->addElement('date_time_selector', 'datecourse['. $key .'][unpublishdate]', "End enrolment date", array('startyear'=>2013, 'stopyear'=>2030, 'optional'=>false, 'timezone' => $timezone),array("class"=>"unpublishdate"));
            $mform->addElement('text', 'datecourse['. $key .'][remarks]', 'Remarks',array("class"=>"date_remarks"));
            //$mform->addElement('advcheckbox', "datecourse_no_dates[".$key."]", "No dates", null, array('group' => 1), false);

            $mform->addElement('html',"</div>");

            $mform->setType('datecourse['. $key .'][id]', PARAM_INT);
			$mform->setType('datecourse['. $key .'][courseid]', PARAM_INT);
			$mform->setType('datecourse['. $key .'][deleted]', PARAM_INT);
            $mform->setType('datecourse['. $key .'][price]', PARAM_NOTAGS);
            $mform->setType('datecourse['. $key .'][places]', PARAM_NOTAGS);
            $mform->setType('datecourse['. $key .'][remarks]', PARAM_TEXT);
            $mform->setType('datecourse['. $key .'][timezone]', PARAM_TEXT);

            // All fields except remark are required.
            $mform->addRule('datecourse['. $key .'][places]', "Needs to be a number", 'numeric', null, 'client');
            $mform->addRule('datecourse['. $key .'][places]', get_string('required'), 'required', null, 'client');
            $mform->addRule('datecourse['. $key .'][price]', get_string('required'), 'required', null, 'client');
            $mform->addRule('datecourse['. $key .'][timestart]', get_string('required'), 'required', null, 'client');
            $mform->addRule('datecourse['. $key .'][timeend]', get_string('required'), 'required', null, 'client');
            $mform->addRule('datecourse['. $key .'][timezone]', get_string('required'), 'required', null, 'client');
            $mform->addRule('datecourse['. $key .'][publishdate]', get_string('required'), 'required', null, 'client');
            $mform->addRule('datecourse['. $key .'][startenrolment]', get_string('required'), 'required', null, 'client');
			$mform->addRule('datecourse['. $key .'][unpublishdate]', get_string('required'), 'required', null, 'client');
			$mform->addRule('datecourse['. $key .'][language]', get_string('required'), 'required', null, 'client');
			$mform->addRule('datecourse['. $key .'][currency]', get_string('required'), 'required', null, 'client');
			$mform->addRule('datecourse['. $key .'][location]', get_string('required'), 'required', null, 'client');
			$mform->addRule('datecourse['. $key .'][coordinator]', get_string('required'), 'required', null, 'client');
			$mform->addRule('datecourse['. $key .'][country]', get_string('required'), 'required', null, 'client');

			$mform->addRule('datecourse['. $key .'][timestart]', "Can't be 0", 'nonzero', null, 'client');
			$mform->addRule('datecourse['. $key .'][timeend]', "Can't be 0", 'nonzero', null, 'client');

            $key++;
        }
        unset($key);

        $mform->addElement('html',"</div>");
        $mform->addElement('html',"<input type='button' id='addDateCourse' value='Add another date'>");

        $this->add_action_buttons(true, "Save");

        if (@$data = $this->_customdata['data']) {		
            $awesomeData = new stdClass();

            $horribleCounter = 0; // he doesn't eat his vegetables
            foreach ($data as $key => $dc) {
				
                $awesomeData->{'datecourse['. $horribleCounter .'][id]'} = $dc->id;
				$awesomeData->{'datecourse['. $horribleCounter .'][courseid]'} = $dc->courseid;
				$awesomeData->{'datecourse['. $horribleCounter .'][timestart]'} = ($dc->startdate == 0) ? time() : $dc->startdate;
                $awesomeData->{'datecourse['. $horribleCounter .'][timeend]'} = ($dc->enddate == 0) ? time() : $dc->enddate;
                $awesomeData->{'datecourse['. $horribleCounter .'][elearning]'} = $dc->elearning;
                $awesomeData->{'datecourse['. $horribleCounter .'][publishdate]'} = $dc->publishdate;
                $awesomeData->{'datecourse['. $horribleCounter .'][unpublishdate]'} = $dc->unpublishdate;
                $awesomeData->{'datecourse['. $horribleCounter .'][startenrolment]'} = $dc->startenrolment;
                $awesomeData->{'datecourse['. $horribleCounter .'][timezone]'} = $dc->timezone;
                $awesomeData->{'datecourse['. $horribleCounter .'][location]'} = $dc->location;
                $awesomeData->{'datecourse['. $horribleCounter .'][country]'} = $dc->country;
                $awesomeData->{'datecourse['. $horribleCounter .'][language]'} = $dc->lang;
                $awesomeData->{'datecourse['. $horribleCounter .'][price]'} = $dc->price;
                $awesomeData->{'datecourse['. $horribleCounter .'][remarks]'} = $dc->remarks;
                $awesomeData->{'datecourse['. $horribleCounter .'][currency]'} = $dc->currencyid;
                $awesomeData->{'datecourse['. $horribleCounter .'][places]'} = $dc->total_places;
                $awesomeData->{'datecourse['. $horribleCounter .'][coordinator]'} = $dc->coordinator;
				//var_dump($awesomeData->{'datecourse['. $horribleCounter .'][timeend]'});

                $horribleCounter++;
            }
            unset($horribleCounter);

            $this->set_data($awesomeData);
        } else {
            $this->set_data(null);
        }
    }
	// Perform some extra moodle validation
    function validation($data, $files) {
	
        $errors= array();
		$errors = parent::validation($data, $files);
		
        return $errors;
    }
}