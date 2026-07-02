<?php
require_once('../../../../config.php');

$context = context_user::instance($USER->id, MUST_EXIST);
$PAGE->set_context($context);
$PAGE->requires->css('/local/backup_course/css/style.css');
$PAGE->requires->css('/local/backup_course/css/tostadas.css');
$PAGE->requires->css('/local/backup_course/css/jquery-confirm.css');
$PAGE->requires->js(new moodle_url("https://code.jquery.com/jquery-3.6.3.min.js"));
$PAGE->requires->js(new moodle_url($CFG->wwwroot . '/local/backup_course/js/llamados.js'));
$PAGE->requires->js(new moodle_url($CFG->wwwroot . '/local/backup_course/js/mensajes.js'));
echo '<div id="snackbar"></div>';
$url = new moodle_url($FULLME);
$PAGE->set_url($url);
$PAGE->set_heading($USER->id);
$PAGE->set_pagelayout('report');
require_login(0, false);
echo $OUTPUT->header();
echo '<div class="loader"></div>';
echo '<div class="header_list_tokens">Lista de Tokens Bloqueados<hr>';
echo '<div id="list_tokens_black"> </div>';
echo '</div>';
echo '<div class="header_list_tokens">Lista de Tokens Activos<hr>';
echo '<div id="list_tokens_activos"> </div>';
echo '</div>';
$PAGE->requires->js(new moodle_url($CFG->wwwroot . '/local/backup_course/js/ajax/admin_tokens.js'));
echo $OUTPUT->footer();