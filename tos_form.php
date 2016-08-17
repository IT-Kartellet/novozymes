<?php
require_once("$CFG->libdir/formslib.php");

class tos_form extends moodleform {
    //Add elements to form
    public function definition() {
        global $CFG, $DB, $OUTPUT, $PAGE;

        $PAGE->requires->jquery();
        $PAGE->requires->js(new moodle_url('js/core.js'));
 
        $mform = $this->_form;
        $data = $this->_customdata['data'];
        
        //TERMS
  //       $mform->addElement('header', 'header_terms', 'Terms');
		// $mform->addElement('editor', 'tos', 'Terms of service', null, array('maxfiles'=>EDITOR_UNLIMITED_FILES, 'noclean'=>true));
		// $mform->setType('tos', PARAM_RAW);
		// $mform->addRule('tos', get_string('required'), 'required', null, 'client');



        //LOCATIONS
        $locations = $DB->get_records_sql("SELECT * FROM {meta_locations}");      
        $locations = array_map(function ($arg){
				if ($arg->timezonename===null) return $arg->location;
                else return $arg->location . ' | ' . $arg->timezonename;
            }, $locations);
		$tmzones = DateTimeZone::listIdentifiers();
		$timezones = array();
		$timezones['-- UNDEFINED --'] = '-- UNDEFINED --';
		foreach ($tmzones as $tz) $timezones[$tz] = $tz;

        $mform->addElement('header', 'header_locations', 'Locations');

        $mform->addElement('text', 'addLocation', 'New location');
		$mform->addElement('select', 'addLocationTZ', 'Time zone', $timezones, null);
        $mform->setType('addLocation', PARAM_TEXT);
        $mform->addElement('button', 'addLoc', "Add location");

        $mform->addElement('select', 'locations', 'Locations', $locations, null);
        $mform->setType('locations', PARAM_TEXT);
        $mform->addElement('button', 'deleteLoc', "Delete selected location");
        $mform->addElement('text', 'renameLocation', 'Rename selected location');
		$mform->addElement('select', 'changeLocationTZ', 'Change time zone', $timezones, null);
        $mform->setType('renameLocation', PARAM_TEXT);
        $mform->addElement('button', 'renameLoc', "Update");



        //LANGUAGES
        $languages = $DB->get_records_sql("SELECT * FROM {meta_languages}");
        // $languages = array_map(function($lang){
        //     return $lang->language;
        // }, $languages);

        $mform->addElement('header', 'header_languages', 'Languages');
        $mform->addElement('html', '<br /><p>Check the languages you need:</p>');


        foreach ($languages as $key => $lang) {
            $mform->addElement('checkbox', "lang[$key]", $lang->language);
        }

        //providers
        $providers = $DB->get_records_sql("SELECT * FROM {meta_providers}");      
        $providers = array_map(function ($arg){
                return $arg->provider;
            }, $providers);

        $mform->addElement('header', 'header_providers', 'Providers');
        
        $mform->addElement('text', 'addProvider', 'New provider');
        $mform->setType('addProvider', PARAM_TEXT);
        $mform->addElement('button', 'addPro', "Add provider");

        $mform->addElement('select', 'providers', 'Providers', $providers, null);
        $mform->setType('providers', PARAM_TEXT);
        $mform->addElement('button', 'deletePro', "Delete selected provider");

        $mform->addElement('text', 'renameProvider', 'Rename selected provider');
        $mform->setType('renameProvider', PARAM_TEXT);
        $mform->addElement('button', 'renamePro', "Rename");

        

      	$this->add_action_buttons();

      	$this->set_data($data);


    }
    
}