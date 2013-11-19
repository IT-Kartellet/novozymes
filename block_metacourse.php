<?php

class block_metacourse extends block_list {
	function init() {
        $this->title = get_string('modulenameplural', 'block_metacourse');
    }

    function get_content(){
    	global $DB, $CFG, $USER, $OUTPUT, $PAGE;
    	$CFG->stylesheets[] = $CFG->wwwroot.'/blocks/metacourse/styles.css';

    	if ($this->content !== null) {
	      return $this->content;
	    }

	    $this->content         =  new stdClass;
	 	$this->content->items  = array();
	 	$this->content->icons  = array();

	 	$this->content->items[] = $OUTPUT->action_link("/blocks/metacourse/list_metacourses.php", 'Do the magic trick', null);
	 	// $this->content->items[] = $OUTPUT->action_link("#", 'test!', null, array('id'=>'magic'));

	 	// $PAGE->requires->js(new moodle_url($CFG->wwwroot. '/lib/jquery/jquery-1.9.1.min.js'));
	 	// $PAGE->requires->js(new moodle_url('/blocks/metacourse/js/jquery.handsontable.full.js'));
	 	// $PAGE->requires->js(new moodle_url('/blocks/metacourse/js/magic.js'));


	    return $this->content;
    }

}