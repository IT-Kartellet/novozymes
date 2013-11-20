<?php

class block_metacourse extends block_list {
	function init() {
        $this->title = get_string('modulenameplural', 'block_metacourse');
    }

    function get_content(){
    	global $DB, $CFG, $USER, $OUTPUT, $PAGE;
    	$CFG->stylesheets[] = $CFG->wwwroot.'/blocks/metacourse/styles.css';

    	$course = $this->page->course;
        $context = get_context_instance(CONTEXT_COURSE, $course->id);
        if (!has_capability('moodle/course:manageactivities', $context)) {
            return;
        }

    	if ($this->content !== null) {
	      return $this->content;
	    }

	    $this->content         =  new stdClass;
	 	$this->content->items  = array();
	 	$this->content->icons  = array();

	 	$this->content->items[] = $OUTPUT->action_link("/blocks/metacourse/list_metacourses.php", 'Do the magic trick', null);

	    return $this->content;
    }

}