<?php

require_once("$CFG->libdir/formslib.php");

class metacourse_form extends moodleform {
    //Add elements to form
    public function definition() {
        global $CFG, $DB, $USER, $PAGE;
		
		$context = context_system::instance();
        
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
            select distinct u.id, u.username, u.`firstname`, u.lastname, u.email from {user} u where u.id <> 1 and u.deleted <> 1 and u.suspended <> 1 AND u.email <> '' AND u.firstname <> '' ORDER BY username ASC
         ");     
		 
        $coordinators = array_map(function ($arg){
                return strtoupper($arg->username) . " - " . $arg->firstname . " " . $arg->lastname;
            }, $coordinators);

        $nocoordinator = array("0" => "none");
        $coordinators = $nocoordinator + $coordinators;

        //get the locations from the database
        $locations = $DB->get_records_sql("SELECT * FROM {meta_locations}");		
        $locations = array_map(function ($arg){
    			return $arg->location;
    		}, $locations);
        // $locations[] = 'Add new location';

        $providers = $DB->get_records_sql("SELECT * FROM {meta_providers} order by provider asc");      
        $providers = array_map(function ($arg){
                return $arg->provider;
            }, $providers);

        
        // Ugly code to take the roles and the providers with their id.
        $context = context_system::instance();
        $roles = get_user_roles($context, $USER->id, false);
        $roles = array_map(function($role){
            if ($role->roleid != 1) {
                return $role->name;
            }
            return null;
        }, $roles);
        $roles = array_filter($roles);
        foreach ($providers as $key => $name) {
            $found = false;
            foreach ($roles as $rid => $role) {
                if ($name == $role) {
                    $found = true;
                }
            }
            if (!$found) {
                unset($providers[$key]);
            }
        }
        $providers = array_filter($providers);
        
        // Get the Target groups
        $meta_cat = $DB->get_records_sql("SELECT * FROM {meta_category} order by name asc");      
        $meta_cat = array_map(function ($arg){
                return $arg->name;
            }, $meta_cat);
        $languages = $DB->get_records_sql("SELECT * FROM {meta_languages} where active = :active order by language asc",array("active"=>1));
        $languages = array_map(function($lang){
            return $lang->language;
        }, $languages);

        //put the god darn english first
        $ordered_languages = array();
        foreach ($languages as $key => $value) {
            if ($value == 'English') {
                $ordered_languages[$key] = $value;
                unset($languages[$key]);
                array_filter($languages);
            }
        }

        $languages = $ordered_languages + $languages;

        $categories = $DB->get_records_sql("SELECT id, name FROM {course_categories} order by name asc");
        $categories = array_map(function($cat){
            return $cat->name;
        }, $categories); 

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
        $mform->addElement('text', 'name', "Name"); 
        $mform->addHelpButton('name', 'meta_name', 'block_metacourse');
        $mform->addElement('text', 'localname', 'Local name');
        $mform->addHelpButton('localname', 'localname', 'block_metacourse');
        $mform->addElement('select', 'localname_lang', 'Local language', $languages, null);
        $mform->addHelpButton('localname_lang', 'localname_lang', 'block_metacourse');
		$mform->addElement('editor', 'purpose', 'Purpose', null, array('maxfiles'=>EDITOR_UNLIMITED_FILES, 'noclean'=>true, 'context' => $context));
        $mform->addHelpButton('purpose', 'purpose', 'block_metacourse');
        // $mform->addElement('select', 'target', 'Target group', $meta_cat, "multiple");

        // == checkbox target
        $mform->addElement('html',"<div class='fitem'>");
        $mform->addElement('html',"<div class='fitemtitle'><label>Target<label></div>");
        $mform->addElement('html',"<div class='felement chkbox'>");

        foreach ($meta_cat as $key => $cat) {
            $mform->addElement('advcheckbox', "targetgroup[".$key."]", $cat, null, array('group' => 1), false);
        }
        $this->add_checkbox_controller(1);
        $mform->addElement('html',"</div></div>");
        // == end checkbox target
        
        $mform->addElement('editor', 'target_description', 'Target description', null, array('maxfiles'=>EDITOR_UNLIMITED_FILES, 'noclean'=>true));
        $mform->addHelpButton('target_description', 'target_description', 'block_metacourse');
		
		$mform->addElement('editor', 'content', 'Content', null, array('maxfiles'=>EDITOR_UNLIMITED_FILES, 'noclean'=>true));
        $mform->addHelpButton('content', 'content', 'block_metacourse');
        
		$mform->addElement('text', 'instructors', 'Instructors');
        $mform->addHelpButton('instructors', 'instructors', 'block_metacourse');
        
		
        $mform->addElement('editor', 'comment', 'Comment', null, array('maxfiles'=>EDITOR_UNLIMITED_FILES, 'noclean'=>true));
        $mform->addHelpButton('comment', 'comment', 'block_metacourse');
       
        $mform->addElement('duration', 'duration', "Duration");
		$mform->setDefault('duration', 1);
        
		$mform->addElement('editor', 'cancellation', 'Cancellation policy',null, array('maxfiles'=>EDITOR_UNLIMITED_FILES, 'noclean'=>true));
        $mform->addHelpButton('cancellation', 'cancellation', 'block_metacourse');
        
		$mform->addElement('editor', 'lodging', 'Course Location & Lodging',null, array('maxfiles'=>EDITOR_UNLIMITED_FILES, 'noclean'=>true));
        
		$mform->addElement('editor', 'contact', "Course owner",null, array('maxfiles'=>EDITOR_UNLIMITED_FILES, 'noclean'=>true));
        
		$mform->addElement('checkbox', 'multipledates', get_string('multipledates', 'block_metacourse'));
        $mform->addHelpButton('multipledates', 'multipledates', 'block_metacourse');
        
		$mform->addElement('editor', 'multiple_dates', 'Multiple Dates',null, array('maxfiles'=>EDITOR_UNLIMITED_FILES, 'noclean'=>true));

        $mform->addElement('checkbox', 'customemail', get_string('customemail', 'block_metacourse'));
        $mform->addHelpButton('customemail', 'customemail', 'block_metacourse');
		
        $active_languages = $DB->get_records_sql("SELECT * FROM {meta_languages} where active = 1");

        foreach ($active_languages as $lang) {
            if ($lang->active == 1) {
                $mform->addElement('editor', 'custom_email['.$lang->id."]", 'Email - ' . $lang->language ,null, array('maxfiles'=>EDITOR_UNLIMITED_FILES, 'noclean'=>true));
				$mform->setDefault('custom_email['.$lang->id.']', array("text" => get_string_manager()->get_string('emailconf', 'block_metacourse', array(), $lang->iso)));
            }
        }

        $mform->addElement('select', 'coordinator', 'Coordinator', $coordinators, null);
        $mform->setDefault('coordinator', $USER->id);
        $mform->addElement('select', 'provider', 'Provider', $providers, null);
        $mform->addElement('date_time_selector', 'unpublishdate', get_string("unpublishdate", "block_metacourse"), array('startyear'=>2013, 'stopyear'=>2030, 'optional'=>false));
        $mform->addHelpButton('unpublishdate', 'unpublishdate', 'block_metacourse');
        $mform->addElement('select', 'competence', 'Competence', $categories, null);

        $mform->addElement('html',"<input type='button' id='saveTemplate' value='Add to templates'>");


        //ELEMENT TYPES
        $mform->setType('id', PARAM_INT);
        $mform->setType('name', PARAM_NOTAGS);
        $mform->setType('localname', PARAM_NOTAGS);
        // $mform->setType('target', PARAM_NOTAGS);
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
		// $mform->addRule('target', get_string('required'), 'required', null, 'client');
        $mform->addRule('content', get_string('required'), 'required', null, 'client');
        $mform->addRule('cancellation', get_string('required'), 'required', null, 'client');

		//BUTTONS
        $this->set_data($data);

        $this->add_action_buttons(true, "Next");
    }
}