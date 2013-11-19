<?php
 
class block_metacourse_edit_form extends block_edit_form {
 
    protected function specific_definition($mform) {
 
        // Section header title according to language file.
        $mform->addElement('header', 'configheader', "Metacourse editing");
 
        // A sample string variable with a default value.
        $mform->addElement('text', 'config_text', "Name");
        $mform->setDefault('config_text', 'default value');
        $mform->setType('config_text', PARAM_MULTILANG);        
 
    }
}