<?php

require_once("$CFG->libdir/formslib.php");

class metacourse_form extends moodleform {
    //Add elements to form
    public function definition() {
        global $CFG, $DB;
 
        $mform = $this->_form;
        $data = $this->_customdata['data'];

        // get coordinator from the database
        $coordinators = $DB->get_records_sql("select distinct u.id, u.username, u.`firstname`, u.lastname, u.email from mdl_user u where u.id not in (1)"); //skip the guest		
        $coordinators = array_map(function ($arg){
    			return " (" .$arg->firstname . " " . $arg->lastname . ") " .$arg->email;
    		}, $coordinators);
        //get the languages from the database
        $locations = $DB->get_records_sql("SELECT * FROM mdl_meta_locations");		
        $locations = array_map(function ($arg){
    			return $arg->location;
    		}, $locations);
        $locations[] = 'Add new location';
        //create the language select
        $languages = "Afrikaans,Albanian,Amharic,Arabic,Armenian,Basque,Bengali,Byelorussian,Burmese,Bulgarian,Catalan,Czech,Chinese,Croatian,Danish,Dari,Dzongkha,Dutch,English,Esperanto,Estonian,Faroese,Farsi,Finnish,French,Gaelic,Galician,German,Greek,Hebrew,Hindi,Hungarian,Icelandic,Indonesian,Inuktitut (Eskimo),Italian,Japanese,Khmer,Korean,Kurdish,Laotian,Latvian,Lappish,Lithuanian,Macedonian,Malay,Maltese,Nepali,Norwegian,Pashto,Polish,Portuguese,Romanian,Russian,Scots,Serbian,Slovak,Slovenian,Somali,Spanish,Swedish,Swahili,Tagalog-Filipino,Tajik,Tamil,Thai,Tibetan,Tigrinya,Tongan,Turkish,Turkmen,Ucrainian,Urdu,Uzbek,Vietnamese,Welsh";
		$languages = explode(',',$languages);

        //ELEMENTS
 		$mform->addElement('header', 'header', 'Metacourse Form');
 		$mform->addElement('hidden', 'id', 0);
        $mform->addElement('text', 'name', get_string('name')); 
		$mform->addElement('editor', 'purpose', 'Purpose', null, array('maxfiles'=>EDITOR_UNLIMITED_FILES, 'noclean'=>true));
        $mform->addElement('text', 'target', 'Target group');
		$mform->addElement('editor', 'content', 'Content', null, array('maxfiles'=>EDITOR_UNLIMITED_FILES, 'noclean'=>true));
        $mform->addElement('text', 'instructors', 'Instructors');
        $mform->addElement('text', 'comment', 'Comment');
        $mform->addElement('select', 'coordinator', 'Coordinator', $coordinators, null);
        $mform->addElement('text', 'provider', 'Provider');

        //ELEMENT TYPES
        $mform->setType('id', PARAM_INT);
        $mform->setType('name', PARAM_NOTAGS);
        $mform->setType('target', PARAM_NOTAGS);
		$mform->setType('purpose', PARAM_RAW); // no XSS prevention here, users must be trusted! :)
		$mform->setType('content', PARAM_RAW);
		$mform->setType('instructors', PARAM_NOTAGS);
		$mform->setType('comment', PARAM_NOTAGS);
		$mform->setType('provider', PARAM_NOTAGS);

        //ELEMENT DEFAULTS
        // $mform->setDefault('name', 'Course name');       
        // $mform->setDefault('target', 'The target of this course');


		//RULES
		$mform->addRule('name', get_string('required'), 'required', null, 'client');
		$mform->addRule('purpose', get_string('required'), 'required', null, 'client');
		$mform->addRule('target', get_string('required'), 'required', null, 'client');
		$mform->addRule('content', get_string('required'), 'required', null, 'client');
		$mform->addRule('instructors', get_string('required'), 'required', null, 'client');
		$mform->addRule('provider', get_string('required'), 'required', null, 'client');

		//BUTTONS
      	$this->add_action_buttons(true, "Next");

      	$this->set_data($data);


    }
    //Custom validation should be added here
    // function validation($data, $files) {
    //     return array();
    // }

    
}