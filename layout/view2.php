<?php

require_once(__DIR__ . '/../../../config.php');
require_once("$CFG->libdir/formslib.php");
require_once($CFG->dirroot.'/lib/formslib.php'); 
require_once($CFG->libdir.'/gdlib.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/user/editadvanced_form.php');
require_once($CFG->dirroot.'/user/editlib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');
require_once($CFG->dirroot.'/user/lib.php');
require_once($CFG->dirroot.'/lib/pear/HTML/QuickForm.php');
require_once(__DIR__ . '/../../../config.php');
require_once('view.php');
global $CFG, $OUTPUT, $PAGE, $USER;  
$context = context_user::instance($USER->id, MUST_EXIST);
$url = new moodle_url("$CFG->wwwroot/local/backup_course/layout/view.php");


$PAGE->set_context($context);
$PAGE->set_url($url);
        
class simplehtml_form_innovame extends moodleform {
    /*public function __construct() {  
        
    }*/

    public function definition() {

        $id = optional_param('id', 0, PARAM_INT);
        //$urlparams = array('id' => $id);
        //print_r($id);
        
        $mform = $this->_form; // Formulario
        $attributes=array('size'=>'20', 'value'=> $id, 'type'=> 'hidden');
        $mform->addElement('hidden', 'name_id','Digite el id', $attributes);
        $mform->setType('name_id', PARAM_TEXT);
        $mform->addElement('date_time_selector', 'duedate', 'Desde', array('optional'=>true));
        $mform->addElement('date_time_selector', 'cutoffdate', 'Hasta', array('optional'=>true));
        $mform->addElement('submit', 'submit', get_string('savechanges'));     
    } 
}
//$met = new simplehtml_form_innovame();
//$met->display();



