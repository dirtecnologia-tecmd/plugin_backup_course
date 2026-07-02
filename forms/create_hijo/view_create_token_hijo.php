<?php
require_once('../../../../config.php');
require_once("form_create_hijo.php");

$context = context_user::instance($USER->id, MUST_EXIST);
$PAGE->set_context($context);
$PAGE->requires->css('/local/backup_course/css/style.css');
$PAGE->requires->css('/local/backup_course/css/tostadas.css');
$PAGE->requires->css('/local/backup_course/css/jquery-confirm.css');
$PAGE->requires->jquery();
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/local/backup_course/js/llamados.js') );
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/local/backup_course/js/mensajes.js') );
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/local/backup_course/js/jquery-confirm.js') );
echo '<div id="snackbar"></div>';
$url = new moodle_url($FULLME);
$PAGE->set_url($url); 

$PAGE->set_heading($USER->id);
$PAGE->set_pagelayout('report');
require_login(0,false);
echo $OUTPUT->header();
$mform_insert = new simplehtml_form_create_hijo('', '', '', '',array('id'=>'form_create_hijo'));
$mform_insert->display();

echo '<div id="header_list_tokens">Lista de Registros creados<hr>';
    echo '<div class="loader"></div>';
    echo '<div id="list_tokens_creados"> </div>';
echo '</div>';    



$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/local/backup_course/js/objetos/QRY.js') );
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/local/backup_course/js/objetos/UPD.js') );
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/local/backup_course/js/objetos/CRE.js') );
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/local/backup_course/js/objetos/DEL.js') );
echo $OUTPUT->footer();




