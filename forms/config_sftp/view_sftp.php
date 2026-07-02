<?php
require_once('../../../../config.php');
require_once("form_sftp.php");


$context = context_user::instance($USER->id, MUST_EXIST);
$PAGE->set_context($context);
$PAGE->requires->css('/local/backup_course/css/style.css');
$PAGE->requires->css('/local/backup_course/css/tostadas.css');
$PAGE->requires->css('/local/backup_course/css/jquery-confirm.css');
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/local/backup_course/js/llamados.js') );
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/local/backup_course/js/mensajes.js') );
echo '<div id="snackbar"></div>';
$url = new moodle_url($FULLME);
$PAGE->set_url($url); 
$PAGE->set_heading($USER->id);
$PAGE->set_pagelayout('report');
require_login(0,false);
echo $OUTPUT->header();
$mform_insert = new simplehtml_form_config_sftp('', '', '', '',array('id'=>'form_config_sftp'));
$mform_insert->display();
/*echo '<pre>';
print_r($_SERVER);
echo '</pre>';*/
echo '<div id="header_list_sftp">Lista de Registros creados<hr>';
    echo '<div class="loader"></div>';
    echo '<div id="list_sftCreados"> </div>';
echo '</div>';    

echo $OUTPUT->footer();

