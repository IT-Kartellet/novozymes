<?php

class block_metacourse extends block_list {
	function init() {
        $this->title = " ";
    }

    function get_content(){
    	global $DB, $CFG, $USER, $OUTPUT, $PAGE;
    	$CFG->stylesheets[] = $CFG->wwwroot.'/blocks/metacourse/styles.css';
        $PAGE->requires->jquery();
        $PAGE->requires->js("/blocks/metacourse/js/core.js");

    	$course = $this->page->course;
        $context = context_course::instance( $course->id);

    	if ($this->content !== null) {
	      return $this->content;
	    }

	    $this->content         =  new stdClass;
	 	$this->content->items  = array();
	 	$this->content->icons  = array();
    
        $this->content->items[] = html_writer::tag('a',"Grow", array('href'=>'/blocks/metacourse/list_metacourses.php'));    

	    return $this->content;
    }

}

//SUPER OO-mode enabled
class meta_category {

// <properties>
    private $id;
    private $title;
    private $description;
    private $parent;
    private $visible;
    private $courses = array();
    private $categories = array();

// </properties>

// <getters>
    public function get_id(){
        return $this->id;
    }
    public function set_id($id){
        $this->id = $id;
    }
    public function get_courses(){
        return $this->courses;
    }
    public function set_courses($courses){
        $this->courses = $courses;
    }
    public function add_course($course){
        $this->courses[] = $course;
    }
    public function get_categories(){
        return $this->categories;
    }
    public function set_categories($categories){
        $this->categories = $categories;
    }
    public function add_category($category){
        $this->categories[] = $category;
    }
    public function get_title(){
        return $this->title;
    }
    public function set_title($title){
        $this->title = $title;
    }
    public function get_description(){
        return $this->description;
    }
    public function set_description($description){
        $this->description = $description;
    }
    public function get_parent(){
        return $this->parent;
    }
    public function set_parent($parent){
        $this->parent = $parent;
    }
    public function isVisible(){
        return $this->visible;
    }
    public function set_visible($visible){
        $this->visible = $visible;
    }
// </getters>

// <functions>

    public function output_category(){
        $div_meta_category_open = "<div class='meta_category'>";
        $div_open = "<div>";
        $h1_open = "<h1>";
        $p_open = "<p>";


        $h1_close = "</h1>";
        $p_close = "</p>";
        $div_close = "</div>";

        $result .= $div_meta_category_open;
        $result .= $h1_open;
        $result .= $this->get_title();
        $result .= $h1_close;
        $result .= $this->get_description();

        foreach ($categories as $category) {
            # code...
        }

        $result .= $div_close;

        return $result;
    }

// </functions>


}