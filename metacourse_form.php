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
        $mform->addElement('text', 'meta[name]', get_string('name')); 
		$mform->addElement('editor', 'meta[purpose]', 'Purpose', null, array('maxfiles'=>EDITOR_UNLIMITED_FILES, 'noclean'=>true));
        $mform->addElement('text', 'meta[target]', 'Target group');
		$mform->addElement('editor', 'meta[content]', 'Content', null, array('maxfiles'=>EDITOR_UNLIMITED_FILES, 'noclean'=>true));
        $mform->addElement('text', 'meta[instructors]', 'Instructors');
        $mform->addElement('text', 'meta[comment]', 'Comment');
        $mform->addElement('select', 'meta[coordinator]', 'Coordinator', $coordinators, null);
        $mform->addElement('text', 'meta[provider]', 'Provider');

        //ELEMENTS COURSES
 		$mform->addElement('header', 'header_courses', 'COURSES');
        $mform->addElement('date_selector', 'datecourse_timestart_0', get_string("from"));
        $mform->addElement('date_selector', 'datecourse_timeend_0', get_string("to"));
        $mform->addElement('select', 'meta[datecourse][0][location]', 'Location', $locations, null);
        $mform->addElement('select', 'meta[datecourse][0][language]', 'Language', $languages, null);
        $mform->addElement('text', 'meta[datecourse][0][price]', 'Price');
        $mform->addElement('text', 'meta[datecourse][0][places]', 'Nr. of places');


        //ELEMENT TYPES
        $mform->setType('id', PARAM_INT);
        $mform->setType('meta[name]', PARAM_NOTAGS);
        $mform->setType('meta[target]', PARAM_NOTAGS);
		$mform->setType('meta[purpose]', PARAM_RAW); // no XSS prevention here, users must be trusted! :)
		$mform->setType('meta[content]', PARAM_RAW);
		$mform->setType('meta[instructors]', PARAM_NOTAGS);
		$mform->setType('meta[comment]', PARAM_NOTAGS);
		$mform->setType('meta[provider]', PARAM_NOTAGS);

		//ELEMENT TYPES COURSES
		$mform->setType('meta[datecourse][0][name_course]', PARAM_NOTAGS);
		$mform->setType('meta[datecourse][0][price]', PARAM_NOTAGS);
		$mform->setType('meta[datecourse][0][places]', PARAM_NOTAGS);

        
        //ELEMENT DEFAULTS
        // $mform->setDefault('name', 'Course name');       
        // $mform->setDefault('target', 'The target of this course');


		//RULES
		$mform->addRule('meta[name]', get_string('required'), 'required', null, 'client');
		$mform->addRule('meta[purpose]', get_string('required'), 'required', null, 'client');
		$mform->addRule('meta[target]', get_string('required'), 'required', null, 'client');
		$mform->addRule('meta[content]', get_string('required'), 'required', null, 'client');
		$mform->addRule('meta[instructors]', get_string('required'), 'required', null, 'client');
		$mform->addRule('meta[provider]', get_string('required'), 'required', null, 'client');

		//RULES_COURSES
		$mform->addRule('meta[datecourse][0][places]', "Needs to be a number", 'numeric', null, 'client');



		//BUTTONS
      	$this->add_action_buttons();

      	$this->set_data($data);


    }
    //Custom validation should be added here
    // function validation($data, $files) {
    //     return array();
    // }

    
}