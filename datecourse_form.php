<?php

require_once("$CFG->libdir/formslib.php");

class metacourse_form extends moodleform {
    //Add elements to form
    public function definition() {
        global $CFG, $DB;
 
        $mform = $this->_form;
        $data = $this->_customdata['data'];


        $mform->addElement('header', 'header_courses', 'COURSES');
        $mform->addElement('date_selector', 'datecourse_timestart_0', get_string("from"));
        $mform->addElement('date_selector', 'datecourse_timeend_0', get_string("to"));
        $mform->addElement('select', 'meta[datecourse][0][location]', 'Location', $locations, null);
        $mform->addElement('select', 'meta[datecourse][0][language]', 'Language', $languages, null);
        $mform->addElement('text', 'meta[datecourse][0][price]', 'Price');
        $mform->addElement('text', 'meta[datecourse][0][places]', 'Nr. of places');

        $mform->setType('meta[datecourse][0][name_course]', PARAM_NOTAGS);
		$mform->setType('meta[datecourse][0][price]', PARAM_NOTAGS);
		$mform->setType('meta[datecourse][0][places]', PARAM_NOTAGS);

		$mform->addRule('meta[datecourse][0][places]', "Needs to be a number", 'numeric', null, 'client');
    }


    $this->add_action_buttons();
    $this->set_data($data);

}