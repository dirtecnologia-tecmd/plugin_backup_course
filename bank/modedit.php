<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
* Adds or updates modules in a course using new formslib
*
* @package    moodlecore
* @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

require("../../../config.php");
require_once($CFG->dirroot ."/course/lib.php");
require_once($CFG->libdir.'/filelib.php');
require_once($CFG->libdir.'/gradelib.php');
require_once($CFG->libdir.'/completionlib.php');
require_once($CFG->libdir.'/plagiarismlib.php');
require_once($CFG->dirroot . '/course/modlib.php');
require_once($CFG->dirroot . '/course/moodleform_mod.php');

$add    = optional_param('add', '', PARAM_ALPHANUM);     // Module name.
$update = optional_param('update', 0, PARAM_INT);
$return = optional_param('return', 0, PARAM_BOOL);    //return to course/view.php if false or mod/modname/view.php if true
$type   = optional_param('type', '', PARAM_ALPHANUM); //TODO: hopefully will be removed in 2.0
$sectionreturn = optional_param('sr', null, PARAM_INT);

$url = new moodle_url('/local/backup_course/bank/modedit.php');
$url->param('sr', $sectionreturn);
if (!empty($return)) {
    $url->param('return', $return);
}

if (!empty($add)) {
    $section = required_param('section', PARAM_INT);
    $course  = required_param('course', PARAM_INT);

    $url->param('add', $add);
    $url->param('section', $section);
    $url->param('course', $course);
    $PAGE->set_url($url);

    $course = $DB->get_record('course', array('id'=>$course), '*', MUST_EXIST);
    require_login($course);

    // There is no page for this in the navigation. The closest we'll have is the course section.
    // If the course section isn't displayed on the navigation this will fall back to the course which
    // will be the closest match we have.
    navigation_node::override_active_url(course_get_url($course, $section));

    // MDL-69431 Validate that $section (url param) does not exceed the maximum for this course / format.
    // If too high (e.g. section *id* not number) non-sequential sections inserted in course_sections table.
    // Then on import, backup fills 'gap' with empty sections (see restore_rebuild_course_cache). Avoid this.
    $courseformat = course_get_format($course);
    $maxsections = $courseformat->get_max_sections();
    if ($section > $maxsections) {
        throw new \moodle_exception('maxsectionslimit', 'moodle', '', $maxsections);
    }

    list($module, $context, $cw, $cm, $data) = prepare_new_moduleinfo_data($course, $add, $section);
    $data->return = 0;
    $data->sr = $sectionreturn;
    $data->add = $add;
    if (!empty($type)) { //TODO: hopefully will be removed in 2.0
        $data->type = $type;
    }

    $sectionname = get_section_name($course, $cw);
    $fullmodulename = get_string('modulename', $module->name);

    if ($data->section && $course->format != 'site') {
        $heading = new stdClass();
        $heading->what = $fullmodulename;
        $heading->to   = $sectionname;
        $pageheading = get_string('addinganewto', 'moodle', $heading);
    } else {
        $pageheading = get_string('addinganew', 'moodle', $fullmodulename);
    }
    $navbaraddition = $pageheading;

} else if (!empty($update)) {

    $url->param('update', $update);
    $PAGE->set_url($url);

    // Select the "Edit settings" from navigation.
    navigation_node::override_active_url(new moodle_url('/local/backup_course/bank/modedit.php', array('update'=>$update, 'return'=>1)));

    // Check the course module exists.
    $cm = get_coursemodule_from_id('', $update, 0, false, MUST_EXIST);

    // Check the course exists.
    $course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);

    // require_login
    require_login($course, false, $cm); // needed to setup proper $COURSE

    list($cm, $context, $module, $data, $cw) = get_moduleinfo_data($cm, $course);
    $data->return = $return;
    $data->sr = $sectionreturn;
    $data->update = $update;

    $sectionname = get_section_name($course, $cw);
    $fullmodulename = get_string('modulename', $module->name);

    if ($data->section && $course->format != 'site') {
        $heading = new stdClass();
        $heading->what = $fullmodulename;
        $heading->in   = $sectionname;
        $pageheading = get_string('updatingain', 'moodle', $heading);
    } else {
        $pageheading = get_string('updatinga', 'moodle', $fullmodulename);
    }
    $navbaraddition = null;

} else {
    require_login();
    throw new \moodle_exception('invalidaction');
}

$pagepath = 'mod-' . $module->name . '-';
if (!empty($type)) { //TODO: hopefully will be removed in 2.0
    $pagepath .= $type;
} else {
    $pagepath .= 'mod';
}
$PAGE->set_pagetype($pagepath);
$PAGE->set_pagelayout('admin');
$PAGE->add_body_class('limitedwidth');


$modmoodleform = "$CFG->dirroot/mod/$module->name/mod_form.php";
if (file_exists($modmoodleform)) {
    require_once($modmoodleform);
} else {
    throw new \moodle_exception('noformdesc');
}

/////////////////////////////////////////////////////////////////////
// Crear una instancia del formulario original.
$mformclassname = 'mod_'.$module->name.'_mod_form';
$mform = new $mformclassname($data, $cw->section, $cm, $course);

// Usar reflexión para acceder a la estructura del formulario y modificarla.
$reflection = new ReflectionClass($mform);
$formField = $reflection->getProperty('_form');
$formField->setAccessible(true); // Permitir acceso al campo protegido '_form'.
$form = $formField->getValue($mform); // Obtener el formulario real.

$incluirBankDef = !empty($update)? 0:'';
$privacyBankDef = 0;
if(!empty($update)){
    $Activi = searchAct($data);
    if(!empty($Activi)) {
        $privacyBankDef = $Activi->private;
        $incluirBankDef = $Activi->retirar == 0? 1:0;
    }
}else{}
// Añadir el colapsable "Banco"
$colapse = $form->createElement('header', 'bancoheader', "Banco");
$form->insertElementBefore($colapse, 'buttonar'); // Insertar el campo heder antes de los btns
$form->setExpanded('bancoheader', true); //desplegar el header para hacer visible los select
// Añadir el campo de selección "incluir" dentro del colapsable "Banco"
$attemptoptions2 = array('' => '',    '1' => 'SI',    '0' => 'NO');
$selectelement2 = $form->createElement('select', 'incluirBank', 'Incluir en el Banco de actividades', $attemptoptions2);
$form->setDefault('incluirBank', $incluirBankDef); // Valor por defecto

// Añadir el campo de selección "Privacidad" dentro del colapsable "Banco"
$attemptoptions = array(    '0' => 'Público',    '1' => 'Privado');
$selectelement = $form->createElement('select', 'privacyBank', 'Privacidad de actividad en el Banco', $attemptoptions);
$form->setDefault('privacyBank', $privacyBankDef); // Valor por defecto

// Insertar ambos select dentro del colapsable antes de los botones de guardado.
$form->insertElementBefore($selectelement2, 'buttonar'); // Insertar el campo select 'Incluir en Banco'
$form->insertElementBefore($selectelement, 'buttonar'); // Insertar el campo select 'Privacidad'
$form->addRule('incluirBank', 'Debes seleccionar si la actividad se incluirá en el Banco.', 'required', null, 'client');

// Establecer los datos en el formulario como antes.
$mform->set_data($data);
echo '<script src="'.$CFG->wwwroot.'/local/backup_course/bank/js/index.js'.'"></script>';
//////////////////////////////////////////////////////////////////////////////////////////////////////////////



if ($mform->is_cancelled()) {
    if ($return && !empty($cm->id)) {
        $urlparams = [
            'id' => $cm->id, // We always need the activity id.
            'forceview' => 1, // Stop file downloads in resources.
        ];
        $activityurl = new moodle_url("/mod/$module->name/view.php", $urlparams);
        redirect($activityurl);
    } else {
        redirect(course_get_url($course, $cw->section, array('sr' => $sectionreturn)));
    }
} else if ($fromform = $mform->get_data()) { 
////////////////////////////////// guardar los datos de la actividad en el bank
    if(!empty($fromform->add) || !empty($fromform->update)){
        require_once 'mbz.php';
        $dataA = prepareActBanco($fromform);
    }
        

    // Verifica si es una actualización o una inserción
    if (!empty($fromform->update)) {
        list($cm, $fromform) = update_moduleinfo($cm, $fromform, $course, $mform);
        //////////////////////
        // Actualizar el registro en la tabla bc_own_activities
        $act = searchAct($fromform);
        $backup = new ActivityBackup(); //crear MBZ de la actividad
        $urlS3 = $backup->generarS3($cm->id, $course->id);
        if(!empty($act)) $dataA->id = $act->id; // Pasar el ID correcto de la actividad
        else{
            $actCre = createActBanco($dataA, $fromform, $course);
            $dataA = $actCre[0];
            $urlS3 = $actCre[1];
            $act = searchAct($fromform);
            if ($act && property_exists($act, 'id') && !empty($act->id))
                $dataA->id = $act->id; // Pasar el ID correcto de la actividad
        }
        $dataA->retirar = ($fromform->incluirBank == 1) ? 0: 1;
        $dataA->url = $urlS3;
        if ($dataA && property_exists($dataA, 'id') && !empty($dataA->id))  $DB->update_record('bc_own_activities', $dataA);
        
    } else if (!empty($fromform->add)) {
        $fromform = add_moduleinfo($fromform, $course, $mform);
        //////////////////
        $actCre = createActBanco($dataA, $fromform, $course);
        $dataA = $actCre[0];
        $urlS3 = $actCre[1];
    } else {
        throw new \moodle_exception('invaliddata');
    }

    ///////////////////////////////actualizar para guardar url
    if((!empty($fromform->add) || !empty($fromform->update)) && ($fromform->incluirBank == 1) )
        if($urlS3 != null) 
            $DB->update_record('bc_own_activities', array('id'=>$dataA->id, 'url'=>$urlS3));
    


    if (isset($fromform->submitbutton)) {
        $url = new moodle_url("/mod/$module->name/view.php", array('id' => $fromform->coursemodule, 'forceview' => 1));
        if (empty($fromform->showgradingmanagement)) {
            // ver actividad
            redirect($url);
        } else { 
            redirect($fromform->gradingman->get_management_url($url));
        }
    } else { //retornar al curso
        redirect(course_get_url($course, $cw->section, array('sr' => $sectionreturn)));
    }
    exit;

} else {

    $streditinga = get_string('editinga', 'moodle', $fullmodulename);
    $strmodulenameplural = get_string('modulenameplural', $module->name);

    if (!empty($cm->id)) {
        $context = context_module::instance($cm->id);
    } else {
        $context = context_course::instance($course->id);
    }

    $PAGE->set_heading($course->fullname);
    $PAGE->set_title($streditinga);
    $PAGE->set_cacheable(false);

    if (isset($navbaraddition)) {
        $PAGE->navbar->add($navbaraddition);
    }
    $PAGE->activityheader->disable();

    echo $OUTPUT->header();

    if (get_string_manager()->string_exists('modulename_help', $module->name)) {
        echo $OUTPUT->heading_with_help($pageheading, 'modulename', $module->name, 'monologo');
    } else {
        echo $OUTPUT->heading_with_help($pageheading, '', $module->name, 'monologo');
    }

    $mform->display();
    echo $OUTPUT->footer();
}

function prepareActBanco($fromform){
    global $DB, $USER;
    // Preparar los datos para guardar/actualizar en la tabla bc_own_activities
    $nodo = $DB->get_record('bc_rel_padre_hijo', array('courseid_sh'=>$fromform->course));

    $dataA = new stdClass();
    $dataA->idnodo = $nodo->registroid_nodo; // ID del curso
    $dataA->idnumber_teacher = (string)$USER->idnumber;
    $dataA->email_teacher = $USER->email; // Pasa el email del profesor
    $dataA->fullname_teacher = $USER->firstname.' '.$USER->lastname; // Nombre completo del profesor
    $dataA->idcourse_p = $nodo->courseid_sp; // ID del curso padre
    $dataA->idcourse_h = $fromform->course; // ID del curso hijo (puede ser el mismo)
    $dataA->name_activity = $fromform->name; // Nombre de la actividad
    $dataA->intro_activity = $fromform->introeditor['text']; // Descripción de la actividad
    $dataA->idsection_h = $fromform->section; // ID de la sección
    $dataA->timemodified = time(); // Timestamp de modificación
    $dataA->url = ''; // Si tienes una URL específica para la actividad
    $dataA->private = $fromform->privacyBank; // Estado de privacidad (Público o Privado)
    $dataA->type_activity = $fromform->modulename; // Tipo de actividad (p. ej., "page")
    return $dataA;
}
function createActBanco($dataA, $fromform, $course){
    global $DB, $USER;
    $urlS3 = null;
    // Insertar un nuevo registro en la tabla bc_own_activities
    $dataA->timecreate = time(); // Timestamp de creación
    $dataA->cant_owner = 0; // Inicializar la cantidad de actividades propias
    $dataA->cant_others = 0; // Inicializar la cantidad de otras actividades
    $dataA->retirar = 0; // Dejar la actividad en el banco
    $dataA->idactivity_h = $fromform->instance; // ID de la actividad
    if($fromform->incluirBank == 1){ 
        $dataA->id= $DB->insert_record('bc_own_activities', $dataA); //solo guardar si incluir en el banco es si
        $backup = new ActivityBackup();//crear MBZ de la actividad
        $urlS3 = $backup->generarS3($fromform->coursemodule, $course->id);
    }
    return [$dataA, $urlS3];
}

function searchAct($fromform){
    global $DB, $USER;
    return $DB->get_record('bc_own_activities', 
                        array('idnumber_teacher'=>$USER->idnumber,
                              'idactivity_h'    =>$fromform->instance,
                              'type_activity'   =>$fromform->modulename));
}


