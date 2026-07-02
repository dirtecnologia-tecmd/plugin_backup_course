<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require('../../../../config.php');
require_once('./form_install_library.php');
global $PAGE, $USER, $DB, $OUTPUT, $CFG, $FULLME, $SESSION;

require_login(0, false);
if (isguestuser()) {
    // Login as real user!
    $SESSION->wantsurl = (string)new moodle_url('/search_courses/view_search_course.php');
    redirect(get_login_url());
}
$context = context_user::instance($USER->id, MUST_EXIST);
$PAGE->set_context($context);
$url1 = new moodle_url($FULLME);
$PAGE->set_url($url1); 
$PAGE->set_heading($USER->id);
$PAGE->set_pagelayout('report');
$PAGE->requires->css('/local/backup_course/css/style.css');
$PAGE->requires->css('/local/backup_course/css/tostadas.css');
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/lib/jquery/jquery-3.6.1.js') );
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/local/backup_course/js/mensajes.js') );
echo $OUTPUT->header();
echo '<div id="snackbar"></div>';
if ($DB->get_manager()->table_exists('hvp_libraries')) {
    if(empty($DB->get_records_sql('SELECT * FROM {hvp_libraries}'))){
        $course = $DB->get_record_select('course','id <> 1 LIMIT 1');
        if(empty($course)){
            $course = new stdClass();
            $course->category = 1;
            $course->fullname = 'Curso demo H5P';
            $course->shortname = 'demo_H5P';
            $course->format = 'weeks';
            $course->id = $DB->insert_record('course', $course);
        }
        
        include '../../update/layouts/loader.php';   
        require_once($CFG->libdir . '/filelib.php');
        require_once($CFG->dirroot.'/mod/hvp/library/h5p.classes.php');
        $context1 = context_course::instance($course->id);
        $libraryid = $DB->get_record('hvp_libraries_hub_cache',array('machine_name'=>'H5P.CoursePresentation'));
        $token_hvp = \H5PCore::createToken('editorajax');
        $url = $CFG->wwwroot. '/local/backup_course/methods/ajax.php?action=libraryinstall&id=H5P.CoursePresentation&contextId='.$context1->id.'&token='.$token_hvp;
        $mform = new simplehtml_form_install_library($url, '', 'post', '',array('id'=>'form_install_library'));
        echo '<h2>Instalar librerias del Módulo HVP</h2>';
        $mform->display();
    }else{
        echo '<h2>Las librerias del módulo HVP ya fueron instaladas</h2>';
    }
    
}else{
    echo '<h2>Debe instar el módulo HVP</h2>';
}
    
echo $OUTPUT->footer();

