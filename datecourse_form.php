<?php

require_once("$CFG->libdir/formslib.php");

class datecourse_form extends moodleform {
    //Add elements to form
    public function definition() {
        global $CFG, $DB, $PAGE;
 
        $mform = $this->_form;
        $data = $this->_customdata['data'];
        //get locations from the database
        $locations = $DB->get_records_sql("SELECT * FROM mdl_meta_locations");		
        $locations = array_map(function ($arg){
    			return $arg->location;
    		}, $locations);
        //create the language select
		$languages = $DB->get_records_sql("SELECT * FROM {meta_languages}");
        $languages = array_map(function($lang){
            return $lang->language;
        }, $languages);

        //create the categories select
        $categories = $DB->get_records_sql("SELECT id, name FROM {course_categories}");
        $categories = array_map(function($cat){
            return $cat->name;
        }, $categories);


        $mform->addElement('header', 'header_courses', 'COURSES');
        $mform->addElement('html',"<div id='wrapper'>");
        $mform->addElement('html',"<div class='template'>");
        $mform->addElement('select', 'datecourse[0][category]', 'Category', $categories, null);
        $mform->addElement('date_selector', 'timestart[0]', get_string("from"));
        $mform->addElement('date_selector', 'timeend[0]', get_string("to"));
        // $mform->addElement('select', 'datecourse[0][location]', 'Location', $locations, null);
        $mform->addElement('select', 'datecourse[0][language]', 'Language', $languages, null);



        // locations. editable input
        $mform->addElement("html","<div class='fitem fitem_fselect' >
            <div class='fitemtitle'>
                <label>Location:</label>
            </div>
            <div class='felement fselect'>
                <input list='locations' name='datecourse[0][location]'>");

        $mform->addElement("html","<datalist id='locations'>");
        foreach ($locations as $key => $value) {
            $mform->addElement("html",'<option value=' ."'". $value."'" . "/>");
        }
        $mform->addElement("html","</datalist></div></div>");
        //end locations


        $mform->addElement('text', 'datecourse[0][price]', 'Price');
        $mform->addElement('text', 'datecourse[0][places]', 'Nr. of places');
        $mform->addElement('html',"</div>");
        $mform->addElement('html',"</div>");
        $mform->addElement('html',"<input type='button' id='addDateCourse' value='Add another course'>");

        $mform->setType('datecourse[0][name_course]', PARAM_NOTAGS);
		$mform->setType('datecourse[0][price]', PARAM_NOTAGS);
		$mform->setType('datecourse[0][places]', PARAM_NOTAGS);

        $mform->addRule('datecourse[0][places]', "Needs to be a number", 'numeric', null, 'client');
        $mform->addRule('datecourse[0][places]', get_string('required'), 'required', null, 'client');
		$mform->addRule('datecourse[0][price]', get_string('required'), 'required', null, 'client');

           
	    $this->add_action_buttons(true, "FINISH");
    	$this->set_data($data);

        $PAGE->requires->js(new moodle_url('/lib/jquery/jquery-1.9.1.min.js'));
        $PAGE->requires->js(new moodle_url('js/core.js'));

    }



}