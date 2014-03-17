<?php

require_once("$CFG->libdir/formslib.php");

class metacourse_form extends moodleform {
    //Add elements to form
    public function definition() {
        global $CFG, $DB, $USER, $PAGE;
        $PAGE->requires->js(new moodle_url('/lib/jquery/jquery-1.9.1.min.js'));
        $PAGE->requires->js(new moodle_url('js/select2/select2.min.js'));
        $PAGE->requires->js(new moodle_url('js/core.js'));

        $current_language = current_language();
        if ($current_language!='en') {
            switch ($current_language) {
                case 'pt':
                    $PAGE->requires->js(new moodle_url('js/select2/select2_locale_pt-PT.js'));
                    break;
                case 'zh_cn':
                    $PAGE->requires->js(new moodle_url('js/select2/select2_locale_zh-CN.js'));
                    break;
                default:
                    $PAGE->requires->js(new moodle_url('js/select2/select2_locale_'.$current_language.".js"));
                    break;
            }
            
        }



        $mform = $this->_form;
        $data = $this->_customdata['data'];

        $coordinators = $DB->get_records_sql("
            select distinct u.id, u.username, u.`firstname`, u.lastname, u.email from {user} u where u.id <> 1 and u.deleted <> 1 and u.suspended <> 1
                
         ");     
        $coordinators = array_map(function ($arg){
                return " (" .$arg->firstname . " " . $arg->lastname . ") " .$arg->email;
            }, $coordinators);
        //get the locations from the database
        $locations = $DB->get_records_sql("SELECT * FROM {meta_locations}");		
        $locations = array_map(function ($arg){
    			return $arg->location;
    		}, $locations);
        // $locations[] = 'Add new location';

        $providers = $DB->get_records_sql("SELECT * FROM {meta_providers}");      
        $providers = array_map(function ($arg){
                return $arg->provider;
            }, $providers);

        // Get the Target groups

        $meta_cat = $DB->get_records_sql("SELECT * FROM {meta_category}");      
        $meta_cat = array_map(function ($arg){
                return $arg->name;
            }, $meta_cat);

        $languages = $DB->get_records_sql("SELECT * FROM {meta_languages} where active = :active",array("active"=>1));
        $languages = array_map(function($lang){
            return $lang->language;
        }, $languages);
        $templates = $DB->get_records_sql("SELECT * from {meta_template}");
        $templates = array_map(function($template){
            return $template->name;
        }, $templates);
        $templates = array("0"=>"") + $templates;

        //ELEMENTS
 		$mform->addElement('header', 'header', 'Course Form');
 		$mform->addElement('hidden', 'id', 0);
        if (count($templates) > 1) {
            $mform->addElement('select', 'template', 'Choose a template', $templates, null);
        }
        $mform->addElement('text', 'name', get_string('name')); 
        $mform->addElement('text', 'localname', 'Local name');
        $mform->addElement('select', 'localname_lang', 'Local language', $languages, null);

		$mform->addElement('editor', 'purpose', 'Purpose', null, array('maxfiles'=>EDITOR_UNLIMITED_FILES, 'noclean'=>true));
        $mform->addElement('select', 'target', 'Target group', $meta_cat, "multiple");
        $mform->addElement('editor', 'target_description', 'Target description', null, array('maxfiles'=>EDITOR_UNLIMITED_FILES, 'noclean'=>true));
		$mform->addElement('editor', 'content', 'Content', null, array('maxfiles'=>EDITOR_UNLIMITED_FILES, 'noclean'=>true));
        $mform->addElement('text', 'instructors', 'Instructors');
        // $mform->addElement('text', 'comment', 'Comment');
        $mform->addElement('editor', 'comment', 'Comment', null, array('maxfiles'=>EDITOR_UNLIMITED_FILES, 'noclean'=>true));
        // $mform->addElement('text', 'duration', 'Duration (days)');
        $mform->addElement('duration', 'duration', "Duration");
        $mform->addElement('editor', 'cancellation', 'Cancellation policy',null, array('maxfiles'=>EDITOR_UNLIMITED_FILES, 'noclean'=>true));
        $mform->addElement('editor', 'lodging', 'Course Location & Lodging',null, array('maxfiles'=>EDITOR_UNLIMITED_FILES, 'noclean'=>true));
        $mform->addElement('editor', 'contact', 'Contact person',null, array('maxfiles'=>EDITOR_UNLIMITED_FILES, 'noclean'=>true));
        $mform->addElement('checkbox', 'multipledates', get_string('multipledates', 'block_metacourse'));
        $mform->addElement('editor', 'multiple_dates', 'Multiple Dates',null, array('maxfiles'=>EDITOR_UNLIMITED_FILES, 'noclean'=>true));

        $mform->addElement('checkbox', 'customemail', get_string('customemail', 'block_metacourse'));

        $active_languages = $DB->get_records_sql("SELECT * FROM {meta_languages} where active = 1");

        foreach ($active_languages as $lang) {
            if ($lang->active == 1) {
                $mform->addElement('editor', 'custom_email['.$lang->id."]", 'Email - ' . $lang->language,null, array('maxfiles'=>EDITOR_UNLIMITED_FILES, 'noclean'=>true));
            }
        }

        $mform->addElement('select', 'coordinator', 'Coordinator', $coordinators, null);
        $mform->setDefault('coordinator', $USER->id);
        $mform->addElement('select', 'provider', 'Provider', $providers, null);
        $mform->addElement('html',"<input type='button' id='saveTemplate' value='Add to templates'>");

        // $mform->addElement('text', 'provider', 'Provider');

        //ELEMENT TYPES
        $mform->setType('id', PARAM_INT);
        $mform->setType('name', PARAM_NOTAGS);
        $mform->setType('localname', PARAM_NOTAGS);
        $mform->setType('target', PARAM_NOTAGS);
		$mform->setType('purpose', PARAM_RAW); // no vulnerability prevention here, users must be trusted! :)
        $mform->setType('content', PARAM_RAW);
        $mform->setType('cancellation', PARAM_RAW);
		$mform->setType('contact', PARAM_RAW);
		$mform->setType('instructors', PARAM_NOTAGS);
        $mform->setType('comment', PARAM_NOTAGS);
		$mform->setType('duration', PARAM_NOTAGS);
        $mform->setType('provider', PARAM_INT);
		$mform->setType('template', PARAM_INT);

        //ELEMENT DEFAULTS
        // $mform->setDefault('name', 'Course name');       
        // $mform->setDefault('target', 'The target of this course');


		//RULES
		$mform->addRule('name', get_string('required'), 'required', null, 'client');
		$mform->addRule('purpose', get_string('required'), 'required', null, 'client');
		$mform->addRule('target', get_string('required'), 'required', null, 'client');
        $mform->addRule('content', get_string('required'), 'required', null, 'client');
		$mform->addRule('duration', get_string('required'), 'required', null, 'client');
        $mform->addRule('instructors', get_string('required'), 'required', null, 'client');
		$mform->addRule('cancellation', get_string('required'), 'required', null, 'client');

		//BUTTONS
      	$this->add_action_buttons(true, "Next");

      	$this->set_data($data);


    }
    //Custom validation should be added here
    // function validation($data, $files) {
    //     return array();
    // }

    
}