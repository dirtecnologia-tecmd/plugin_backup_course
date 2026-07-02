<?php
require_once('../../../../config.php');
require_once("form_s3.php");


$context = context_user::instance($USER->id, MUST_EXIST);
$PAGE->set_context($context);
$PAGE->requires->css('/local/backup_course/css/style.css');
$PAGE->requires->css('/local/backup_course/css/tostadas.css');
$PAGE->requires->css('/local/backup_course/css/jquery-confirm.css');
$PAGE->requires->js(new moodle_url($CFG->wwwroot . '/local/backup_course/js/llamados.js'));
$PAGE->requires->js(new moodle_url($CFG->wwwroot . '/local/backup_course/js/mensajes.js'));
echo '<div id="snackbar"></div>';
$url = new moodle_url($FULLME);
$PAGE->set_url($url);
$PAGE->set_heading($USER->id);
$PAGE->set_pagelayout('report');
require_login(0, false);
echo $OUTPUT->header();
$mform_insert = new simplehtml_form_config_s3('', '', '', '', array('id' => 'form_config_s3'));
$mform_insert->display();

echo $OUTPUT->footer();
