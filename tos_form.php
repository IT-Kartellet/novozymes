<?php
require_once("$CFG->libdir/formslib.php");

class tos_form extends moodleform {
    //Add elements to form
    public function definition() {
        global $CFG, $DB;
 
        $mform = $this->_form;
        $data = $this->_customdata['data'];

		$mform->addElement('editor', 'tos', 'Terms of service', null, array('maxfiles'=>EDITOR_UNLIMITED_FILES, 'noclean'=>true));
		$mform->setType('tos', PARAM_RAW);
		$mform->addRule('tos', get_string('required'), 'required', null, 'client');

      	$this->add_action_buttons();

      	$this->set_data($data);


    }
    
}