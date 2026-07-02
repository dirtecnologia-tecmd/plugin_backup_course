<?php

require('../../../../config.php');
require_once('form_search_course.php');

global $PAGE, $USER, $DB, $OUTPUT, $CFG, $FULLME, $SESSION;
if (isguestuser()) {
    // Login as real user!
    $SESSION->wantsurl = (string)new moodle_url('/search_courses/view_search_course.php');
    redirect(get_login_url());
}

$search = optional_param('search', '', PARAM_NOTAGS);
$id     = required_param('id_nodo', PARAM_INT);
$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);
context_helper::preload_course($course->id);
$context = context_course::instance($course->id, MUST_EXIST);
$PAGE->set_context($context);
require_login($course);

$url = new moodle_url($FULLME);
$PAGE->set_url($url);
$PAGE->set_heading($USER->id);
$PAGE->set_pagelayout('report');
$PAGE->requires->css('/local/backup_course/css/style.css');
$PAGE->requires->css('/local/backup_course/css/tostadas.css');
$PAGE->requires->css('/local/backup_course/css/jquery-confirm.css');
$PAGE->requires->js(new moodle_url($CFG->wwwroot . '/local/backup_course/js/llamados.js'));
$PAGE->requires->js(new moodle_url($CFG->wwwroot . '/local/backup_course/js/mensajes.js'));
echo '<div id="snackbar"></div>';
echo $OUTPUT->header();

if (has_capability('moodle/course:manageactivities', context_course::instance($id))) {
    $mform = new simplehtml_form_search_course('', '', '', '', array('id' => 'form_search_course'));
    $mform->display();
    echo '<div id="header_list_courses">Lista de cursos en el Padre<hr>
            <div class="list_courses_vacia">Busque un curso</div>
            <div id="list_courses_padre"> </div>
            <div class="loader_list_courses"></div>
        </div>
        <div class="modal fade" id="modal-default" style="display: none;">
            <div class="modal-dialog" style="  height: 90%; max-width: 95%;">
                <div class="modal-content" style="  height: 90%;">
                    <div class="modal-header">
                        <h4 class="modal-title" id="head_view_course" style="  width: 100%;  float: left;  text-align: center;">
                            
                        </h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin: 0; padding: 0; font-size: 30px; float: right;">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body" style="overflow-x: auto">
                        <div id="contenido_curso" class="container">
                        </div>
                    </div>
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>
          ';
} else {
    echo 'Sin permisos para este curso';
}

$PAGE->requires->js(new moodle_url($CFG->wwwroot . '/local/backup_course/js/jquery-confirm.js'));
$PAGE->requires->js(new moodle_url($CFG->wwwroot . '/local/backup_course/js/objetos/QRY.js'));
$PAGE->requires->js(new moodle_url($CFG->wwwroot . '/local/backup_course/js/objetos/UPD.js'));
$PAGE->requires->js(new moodle_url($CFG->wwwroot . '/local/backup_course/js/objetos/CRE.js'));
$PAGE->requires->js(new moodle_url($CFG->wwwroot . '/local/backup_course/js/objetos/DEL.js'));
$PAGE->requires->js(new moodle_url($CFG->wwwroot . '/local/backup_course/js/html2canvas.min.js'));
echo $OUTPUT->footer();
