<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once('../../../../config.php');

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
echo '<div class="loader"></div>';
 
echo '<div class="row" id="contenListCourse_import">
            <div class="col-md-6" id="lista_cate_act" >
                <div class="box box-info box_content_cate">
                    <div class="box-header with-border">
                        <h3 class="box-title">Nodos</h3>
                    </div>
                    <div class="box-body" style="border: solid 1px #022f94; border-top: 3px solid #022f94;  border-radius: 8px;">
                        <div class="table-responsive">
                            <table class="table no-margin">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>URL Hijos</th>
                                    </tr>
                                </thead>
                                <tbody id="list_updates_black">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6" id="content_prin_course" >
                <div class="box box-info box_content_cate">
                    <div class="box-header with-border">
                        <h3 class="box-title" id="title_course_list">CURSOS</h3>
                    </div>
                    <div class="box-body" style="border: solid 1px #045ab3; border-top: 3px solid #045ab3;  border-radius: 8px;">
                        <div class="table-responsive">
                            <table class="table no-margin">
                                <thead>
                                    <tr>
                                        <th>Curso</th>
                                        <th>Alfa</th>
                                    </tr>
                                </thead>
                                <tbody id="list_updates_activos">

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12"><br></div>
            
            <div class="col-md-12" id="content_items_course" >
                <div class="box box-info box_content_cate">
                    <div class="box-header with-border">
                        <h3 class="box-title" id="title_items_course_list">ITEMS DE CURSO</h3>
                    </div>
                    <div class="box-body" style="border: solid 1px #1c84f1; border-top: 3px solid #1c84f1;  border-radius: 8px;">
                        <div class="table-responsive">
                            <table class="table no-margin">
                                <thead>
                                    <tr>
                                        <th style="width: 20%; float: left;">Item</th>
                                        <th style="width: 20%; float: left;">Fecha</th>
                                        <th style="width: 40%; float: left;">User</th>
                                        <th style="width: 20%; float: left;">Ver</th>
                                    </tr>
                                </thead>
                                <tbody id="list_updates_items">

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>';

$PAGE->requires->js( new moodle_url('https://code.jquery.com/jquery-3.6.3.min.js'));
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/local/backup_course/js/ajax/view_updates.js') );
echo $OUTPUT->footer();