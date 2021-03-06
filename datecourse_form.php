<?php

require_once("$CFG->libdir/formslib.php");

class datecourse_form extends moodleform {
    public $number = 0;

    public function definition() {
        global $CFG, $DB, $PAGE, $USER;
        $PAGE->requires->js(new moodle_url('js/core.js'));
 
        $mform = $this->_form;
		//Add all meta-info and send it through.
		$mform->addElement('hidden','meta', serialize($this->_customdata['meta']));
		$mform->addElement('hidden','meta_coordinator', $this->_customdata['meta']['meta_coordinator']);
		$mform->addElement('hidden','current_user', $USER->id);
		$mform->addElement('hidden','meta_price', $this->_customdata['meta']['meta_price']);
		$mform->addElement('hidden','meta_currencyid', $this->_customdata['meta']['meta_currencyid']);
		$mform->addElement('hidden','nodates', $this->_customdata['meta']['meta_nodates_enabled']==1 ? '1' : '0');
		$mform->setType('meta', PARAM_RAW);
		
        @$numberOfDates = ($this->_customdata['dateCourseNr'])? $this->_customdata['dateCourseNr'] : 0;

        //timezones
        $timezones = array("-11" => "-11", "-10" => "-10", "-9" => "-9", "-8" => "-8", "-7" => "-7", "-6" => "-6", "-5" => "-5", "-4" => "-4", "-3" => "-3",
            "-2" => "-2", "-1" => "-1", "+0" => "0", "+1" => "+1", "+2" => "+2", "+3" => "+3", "+4" => "+4", "+5" => "+5", "+5:30" => "+5:30", "+6" => "+6", "+7" => "+7",
            "+8" => "+8", "+9" => "+9", "+10" => "+10", "+11" => "+11", "+12" => "+12");
		$tmzones = DateTimeZone::listIdentifiers();
		$timezonenames = array();
		foreach ($tmzones as $tz) $timezonenames[$tz] = $tz;

        //get locations from the database
        $locations = $DB->get_records_sql("SELECT * FROM {meta_locations} order by location asc");
		$locationTimeZones = array_map(function($arg) {
			return $arg->timezonename;
		}, $locations);
        $locations = array_map(function ($arg){
			return $arg->location;
		}, $locations);
		$mform->addElement('hidden','locationTimeZones', json_encode($locationTimeZones));
			
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
        $coordinators = get_available_coordinators($this->_customdata['meta']['meta_provider'], $this->_customdata['meta']['meta_id'], $this->_customdata['meta']['meta_coordinator']);

        $mform->addElement('header', 'header_courses', 'COURSES');
        $mform->addElement('html',"<div id='wrapper'>");

        $key = 0;
		
		// The data here is keyed by course id, reset it to zero based for easy indexing while we iterate
		@$data = array_values($this->_customdata['data']);
		
		//while($key <= $numberOfDates) {
		while($key <= ($this->_customdata['meta']['meta_nodates_enabled']==1 || $numberOfDates > 0 ? $numberOfDates : $numberOfDates + 1)) {
			// $key = 0 is the template used to create new date courses.
			if ($key==0 || $numberOfDates==0) @$course_data = null;
			else @$course_data = $data[$key-1];
            
			if (isset($course_data->timezone)) {
                $timezone = $course_data->timezone;
            } else {
                $timezone = 0;
            }
			if (isset($course_data->timezonename)) {
                $tmzone = $course_data->timezonename;
            } else {
                $tmzone = 'Europe/Copenhagen';
            }
			$tz = new DateTimeZone($tmzone);

            // Datetimeselector needs 5.5 as format, not 5:30
            $timezone = format_tz_offset($timezone);

            $mform->addElement('html',"<div class='template'" . ($key==0 ? " style='display:none'" : "") . ">");
            $mform->addElement('hidden','datecourse['. $key .'][id]', '0');
            $mform->addElement('hidden','datecourse['. $key .'][courseid]', '0');
            $mform->addElement('hidden','datecourse['. $key .'][deleted]', $key==0 ? '1' : '0');
            $mform->addElement('html',"<input type='button' id='removeDateCourse' title='Remove date' value='X' class='$key'>");

            $mform->addElement('checkbox', 'datecourse[' . $key . '][elearning]', 'Elearning', '', array('class' => 'elearning'));
            $mform->addHelpButton('datecourse[' . $key . '][elearning]', 'elearning', 'block_metacourse');
			
			if (isset($course_data->startdate) && $course_data->startdate!==null) $tzoffs = $tz->getOffset((new DateTime())->setTimestamp($course_data->startdate)) / 3600;
			else $tzoffs = 0;
            $mform->addElement('date_time_selector', 'datecourse['. $key .'][timestart]', "Start", array('startyear'=>2013, 'stopyear'=>2030, 'optional'=>false, "id"=>"timestart", 'timezone' => $tzoffs),array("class"=>"timestart"));
			if (isset($course_data->enddate) && $course_data->enddate!==null) $tzoffs = $tz->getOffset((new DateTime())->setTimestamp($course_data->enddate)) / 3600;
			else $tzoffs = 0;
            $mform->addElement('date_time_selector', 'datecourse['. $key .'][timeend]', "End", array('startyear'=>2013, 'stopyear'=>2030, 'optional'=>false, 'timezone' => $tzoffs),array("class"=>"timeend"));
            
			//$mform->addElement('select', 'datecourse['. $key .'][timezone]', "Time zone", $timezones, array("class"=>"timezone"))->setSelected("0");
            //$mform->addHelpButton('datecourse['. $key .'][timezone]', 'timezone', 'block_metacourse');

            $mform->addElement('select', 'datecourse['. $key .'][location]', 'Location', $locations, array("class"=>"location"));
			$mform->addElement('select', 'datecourse['. $key .'][timezonename]', "Time zone", $timezonenames, array("class"=>"timezonename"))->setSelected("0");
            $mform->addHelpButton('datecourse['. $key .'][timezonename]', 'timezonename', 'block_metacourse');
            
			$mform->addElement('select', 'datecourse['. $key .'][country]', 'Where', $countries, array("class"=>"country"));
            $mform->addElement('html', "<div class='fitem'><div class='felement'> <a href='#' class='anotherLocation' > + another location </a></div></div>");
            $mform->addElement('select', 'datecourse['. $key .'][language]', 'Language', $languages, array("class"=>"language"));
            $mform->addElement('text', 'datecourse['. $key .'][price]', get_string('price', 'block_metacourse'), array("class"=>"price"));
            $mform->addElement('select', 'datecourse['. $key .'][currency]', get_string('currency', 'block_metacourse'), $currencies, array("class"=>"currency"));
            $mform->addElement('text', 'datecourse['. $key .'][places]', 'No. of places',array("class"=>"noPlaces"));
            $mform->addElement('select', 'datecourse['. $key .'][coordinator]', 'Coordinator', $coordinators, array("class"=>"coordinator"));
            $mform->setDefault('coordinator', $USER->id);
			if (isset($course_data->publishdate) && $course_data->publishdate!==null) $tzoffs = $tz->getOffset((new DateTime())->setTimestamp($course_data->publishdate)) / 3600;
			else $tzoffs = 0;
            $mform->addElement('date_time_selector', 'datecourse['. $key .'][publishdate]', "Publish date", array('startyear'=>2013, 'stopyear'=>2030, 'optional'=>false, 'timezone' => $tzoffs), array("class"=>"publishdate"));
			if (isset($course_data->realunpublishdate) && $course_data->realunpublishdate!==null) $tzoffs = $tz->getOffset((new DateTime())->setTimestamp($course_data->realunpublishdate)) / 3600;
			else $tzoffs = 0;
			$mform->addElement('date_time_selector', 'datecourse['. $key .'][realunpublishdate]', get_string('unpublish_date', 'block_metacourse'), array('startyear'=>2013, 'stopyear'=>2030, 'optional'=>true, 'timezone' => $tzoffs),array("class"=>"realunpublishdate"));
			$mform->addHelpButton('datecourse[' . $key . '][realunpublishdate]', 'date_course_realunpublishdate', 'block_metacourse');
			if (isset($course_data->startenrolment) && $course_data->startenrolment!==null) $tzoffs = $tz->getOffset((new DateTime())->setTimestamp($course_data->startenrolment)) / 3600;
			else $tzoffs = 0;
            $mform->addElement('date_time_selector', 'datecourse['. $key .'][startenrolment]', "Start enrolment date", array('startyear'=>2013, 'stopyear'=>2030, 'optional'=>false, 'timezone' => $tzoffs), array("class"=>"startenrolment"));
			if (isset($course_data->unpublishdate) && $course_data->unpublishdate!==null) $tzoffs = $tz->getOffset((new DateTime())->setTimestamp($course_data->unpublishdate)) / 3600;
			else $tzoffs = 0;
            $mform->addElement('date_time_selector', 'datecourse['. $key .'][unpublishdate]', "End enrolment date", array('startyear'=>2013, 'stopyear'=>2030, 'optional'=>false, 'timezone' => $tzoffs),array("class"=>"unpublishdate"));
			
			$mform->addElement('checkbox', 'datecourse[' . $key . '][manual_enrol]', get_string('manual_enrol', 'block_metacourse'), '', array('class' => 'manual_enrol'));
            $mform->addHelpButton('datecourse[' . $key . '][manual_enrol]', 'manual_enrol', 'block_metacourse');
			
            $mform->addElement('text', 'datecourse['. $key .'][remarks]', 'Remarks',array("class"=>"date_remarks"));
            //$mform->addElement('advcheckbox', "datecourse_no_dates[".$key."]", "No dates", null, array('group' => 1), false);

            $mform->addElement('html',"</div>");

            $mform->setType('datecourse['. $key .'][id]', PARAM_INT);
			$mform->setType('datecourse['. $key .'][courseid]', PARAM_INT);
			$mform->setType('datecourse['. $key .'][deleted]', PARAM_INT);
            $mform->setType('datecourse['. $key .'][price]', PARAM_NOTAGS);
            $mform->setType('datecourse['. $key .'][places]', PARAM_NOTAGS);
            $mform->setType('datecourse['. $key .'][remarks]', PARAM_TEXT);
            //$mform->setType('datecourse['. $key .'][timezone]', PARAM_TEXT);
			$mform->setType('datecourse['. $key .'][timezonename]', PARAM_TEXT);

            // All fields except remark and real unpublish date are required.
			$mform->addRule('datecourse['. $key .'][places]', "Needs to be a number", 'numeric', null, 'client');
			$mform->addRule('datecourse['. $key .'][places]', get_string('required'), 'required', null, 'client');
			$mform->addRule('datecourse['. $key .'][price]', get_string('required'), 'required', null, 'client');
			$mform->addRule('datecourse['. $key .'][timestart]', get_string('required'), 'required', null, 'client');
			$mform->addRule('datecourse['. $key .'][timeend]', get_string('required'), 'required', null, 'client');
			//$mform->addRule('datecourse['. $key .'][timezone]', get_string('required'), 'required', null, 'client');
			$mform->addRule('datecourse['. $key .'][timezonename]', get_string('required'), 'required', null, 'client');
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

		$awesomeData = new stdClass();
		$awesomeData->{'datecourse[0][price]'} = 0;
		$awesomeData->{'datecourse[0][places]'} = 0;
		$awesomeData->{'datecourse[0][coordinator]'} = $this->_customdata['meta']['meta_coordinator'];
        if (@$data = $this->_customdata['data']) {		

            $horribleCounter = 1; // he doesn't eat his vegetables
            foreach ($data as $key => $dc) {
				
                $awesomeData->{'datecourse['. $horribleCounter .'][id]'} = $dc->id;
				$awesomeData->{'datecourse['. $horribleCounter .'][courseid]'} = $dc->courseid;
				$awesomeData->{'datecourse['. $horribleCounter .'][timestart]'} = ($dc->startdate == 0) ? time() : $dc->startdate;
                $awesomeData->{'datecourse['. $horribleCounter .'][timeend]'} = ($dc->enddate == 0) ? time() : $dc->enddate;
                $awesomeData->{'datecourse['. $horribleCounter .'][elearning]'} = $dc->elearning;
                $awesomeData->{'datecourse['. $horribleCounter .'][publishdate]'} = $dc->publishdate;
				$awesomeData->{'datecourse['. $horribleCounter .'][realunpublishdate]'} = $dc->realunpublishdate;
                $awesomeData->{'datecourse['. $horribleCounter .'][unpublishdate]'} = $dc->unpublishdate;
                $awesomeData->{'datecourse['. $horribleCounter .'][startenrolment]'} = $dc->startenrolment;
                //$awesomeData->{'datecourse['. $horribleCounter .'][timezone]'} = $dc->timezone;
				$awesomeData->{'datecourse['. $horribleCounter .'][timezonename]'} = $dc->timezonename;
                $awesomeData->{'datecourse['. $horribleCounter .'][location]'} = $dc->location;
                $awesomeData->{'datecourse['. $horribleCounter .'][country]'} = $dc->country;
                $awesomeData->{'datecourse['. $horribleCounter .'][language]'} = $dc->lang;
                $awesomeData->{'datecourse['. $horribleCounter .'][price]'} = $dc->price;
				$awesomeData->{'datecourse['. $horribleCounter .'][manual_enrol]'} = $dc->manual_enrol;
                $awesomeData->{'datecourse['. $horribleCounter .'][remarks]'} = $dc->remarks;
                $awesomeData->{'datecourse['. $horribleCounter .'][currency]'} = $dc->currencyid;
                $awesomeData->{'datecourse['. $horribleCounter .'][places]'} = $dc->total_places;
                $awesomeData->{'datecourse['. $horribleCounter .'][coordinator]'} = $dc->coordinator;

                $horribleCounter++;
            }
            unset($horribleCounter);

            //$this->set_data($awesomeData);
        } else {
			if ($this->_customdata['meta']['meta_nodates_enabled']!==1) {
				$awesomeData->{'datecourse[1][price]'} = 0;
				$awesomeData->{'datecourse[1][places]'} = 0;
				$awesomeData->{'datecourse[1][coordinator]'} = $this->_customdata['meta']['meta_coordinator'];
			}
            //$this->set_data(null);
        }
		$this->set_data($awesomeData);
    }
	// Perform some extra moodle validation
    function validation($data, $files) {
	
        $errors= array();
		$errors = parent::validation($data, $files);
		
        return $errors;
    }
}