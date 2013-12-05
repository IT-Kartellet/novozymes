<?php
 
class block_metacourse_edit_form extends block_edit_form {
 
    protected function specific_definition($mform) {
 
        // Section header title according to language file.
        $mform->addElement('header', 'configheader', "Course editing");
 
        // A sample string variable with a default value.
        $mform->addElement('html', "<p>No settings available</p>");
        $mform->setType('config_text', PARAM_MULTILANG);        
 
    }
}