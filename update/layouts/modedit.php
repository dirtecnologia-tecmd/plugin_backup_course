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

require_once('../../../../config.php');
require_once("../../../../course/lib.php");
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->libdir . '/gradelib.php');
require_once($CFG->libdir . '/completionlib.php');
require_once($CFG->libdir . '/plagiarismlib.php');
require_once($CFG->dirroot . '/course/modlib.php');

$add    = optional_param('add', '', PARAM_ALPHA);     // module name
$update = optional_param('update', 0, PARAM_INT);
$return = optional_param('return', 0, PARAM_BOOL);    //return to course/view.php if false or mod/modname/view.php if true
$type   = optional_param('type', '', PARAM_ALPHANUM); //TODO: hopefully will be removed in 2.0
$sectionreturn = optional_param('sr', null, PARAM_INT);
$url = new moodle_url('/local/backup_course/update/layouts/modedit.php');
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

    $course = $DB->get_record('course', array('id' => $course), '*', MUST_EXIST);
    require_login($course);

    // There is no page for this in the navigation. The closest we'll have is the course section.
    // If the course section isn't displayed on the navigation this will fall back to the course which
    // will be the closest match we have.
    navigation_node::override_active_url(course_get_url($course, $section));

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
    navigation_node::override_active_url(new moodle_url('/course/modedit.php', array('update' => $update, 'return' => 1)));

    // Check the course module exists.
    $cm = get_coursemodule_from_id('', $update, 0, false, MUST_EXIST);

    // Check the course exists.
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

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
    echo '<link href="' . $CFG->wwwroot . '/local/backup_course/css/style.css" rel="stylesheet" type="text/css" />' .
        '<link href="' . $CFG->wwwroot . '/local/backup_course/css/jquery-confirm.css" rel="stylesheet" type="text/css" />' .
        '<link href="' . $CFG->wwwroot . '/local/backup_course/css/tostadas.css" rel="stylesheet" type="text/css" />' .
        '<script src="' . $CFG->wwwroot . '/lib/jquery/jquery-3.6.1.js"></script>' .
        '<script src="' . $CFG->wwwroot . '/local/backup_course/update/js/buttons.js"></script>' .
        '<script src="' . $CFG->wwwroot . '/local/backup_course/update/js/objetsUpdates/updateObjet.js"></script>' .
        '<script src="' . $CFG->wwwroot . '/local/backup_course/update/js/objetsUpdates/saveLog.js"></script>   ' .
        '<script src="' . $CFG->wwwroot . '/local/backup_course/update/js/objetsUpdates/CRE.js"></script>' .
        '<script src="' . $CFG->wwwroot . '/local/backup_course/js/objetos/QRY.js"></script>' .
        '<script src="' . $CFG->wwwroot . '/local/backup_course/js/jquery-confirm.js"></script>' .
        '<script src="' . $CFG->wwwroot . '/local/backup_course/js/mensajes.js"></script>' .
        '<script src="' . $CFG->wwwroot . '/local/backup_course/update/js/objetsUpdates/UPD.js"></script>' .
        '<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>' .
        '<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>';
} else {
    require_login();
    print_error('invalidaction');
}

$pagepath = 'mod-' . $module->name . '-';
if (!empty($type)) { //TODO: hopefully will be removed in 2.0
    $pagepath .= $type;
} else {
    $pagepath .= 'mod';
}
$PAGE->set_pagetype($pagepath);
$PAGE->set_pagelayout('admin');


////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////OJO añadí a las wikis este form new              //
/**/
if ($module->name == 'wiki') {                                                          //
    /**/
    $modmoodleform = "$CFG->dirroot/local/backup_course/update/layouts/mod_form.php"; //
    /**/
} else {                                                                                //
    /**/
    $modmoodleform = "$CFG->dirroot/mod/$module->name/mod_form.php";                  //
    /**/
}                                                                                     //
////////////////////////////////////////////////////////////////////////////////////////



if (file_exists($modmoodleform)) {
    require_once($modmoodleform);
} else {
    print_error('noformdesc');
}

$mformclassname = 'mod_' . $module->name . '_mod_form';
$mform = new $mformclassname($data, $cw->section, $cm, $course);
$mform->set_data($data);

if ($mform->is_cancelled()) {
    if ($return && !empty($cm->id)) {
        redirect("$CFG->wwwroot/mod/$module->name/view.php?id=$cm->id");
    } else {
        redirect(course_get_url($course, $cw->section, array('sr' => $sectionreturn)));
    }
} else if ($fromform = $mform->get_data()) {
    // Convert the grade pass value - we may be using a language which uses commas,
    // rather than decimal points, in numbers. These need to be converted so that
    // they can be added to the DB.
    if (isset($fromform->gradepass)) {
        $fromform->gradepass = unformat_float($fromform->gradepass);
    }

    if (!empty($fromform->update)) {
        list($cm, $fromform) = update_moduleinfo($cm, $fromform, $course, $mform);
        include '../../util.php';
        echo '
            <style>
                #overlay-loader_block_modedit {
                    position: fixed;
                    display: block;
                    width: 100%;
                    height: 100%;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background-color: rgba(0,0,0,0.5);
                    z-index: 2;
                    cursor: pointer;
                } 


                #overlay-loader_block_modedit .sk-cube-grid {
                    width: 40px;
                    height: 40px;
                    top: 50%;
                    left: 50%;
                    margin-top: -5em; /*set to a negative number 1/2 of your height*/
                    margin-left: 0em; /*set to a negative number 1/2 of your width*/
                    position:fixed;
                }

                #overlay-loader_block_modedit .sk-cube-grid .sk-cube {
                    width: 33%;
                    height: 33%;
                    background-color: #fff;
                    float: left;
                    -webkit-animation: sk-cubeGridScaleDelay 1.3s infinite ease-in-out;
                    animation: sk-cubeGridScaleDelay 1.3s infinite ease-in-out; 
                }
                #overlay-loader_block_modedit .sk-cube-grid .sk-cube1 {
                  -webkit-animation-delay: 0.2s;
                          animation-delay: 0.2s; }
                #overlay-loader_block_modedit .sk-cube-grid .sk-cube2 {
                  -webkit-animation-delay: 0.3s;
                          animation-delay: 0.3s; }
                #overlay-loader_block_modedit .sk-cube-grid .sk-cube3 {
                  -webkit-animation-delay: 0.4s;
                          animation-delay: 0.4s; }
                #overlay-loader_block_modedit .sk-cube-grid .sk-cube4 {
                  -webkit-animation-delay: 0.1s;
                          animation-delay: 0.1s; }
                #overlay-loader_block_modedit .sk-cube-grid .sk-cube5 {
                  -webkit-animation-delay: 0.2s;
                          animation-delay: 0.2s; }
                #overlay-loader_block_modedit .sk-cube-grid .sk-cube6 {
                  -webkit-animation-delay: 0.3s;
                          animation-delay: 0.3s; }
                #overlay-loader_block_modedit .sk-cube-grid .sk-cube7 {
                  -webkit-animation-delay: 0s;
                          animation-delay: 0s; }
                #overlay-loader_block_modedit .sk-cube-grid .sk-cube8 {
                  -webkit-animation-delay: 0.1s;
                          animation-delay: 0.1s; }
                #overlay-loader_block_modedit .sk-cube-grid .sk-cube9 {
                  -webkit-animation-delay: 0.2s;
                          animation-delay: 0.2s; }

                @-webkit-keyframes sk-cubeGridScaleDelay {
                  0%, 70%, 100% {
                    -webkit-transform: scale3D(1, 1, 1);
                            transform: scale3D(1, 1, 1);
                  } 35% {
                    -webkit-transform: scale3D(0, 0, 1);
                            transform: scale3D(0, 0, 1); 
                  }
                }
            </style>';

        $cm2     = get_coursemodule_from_id('', $cm->id, 0, true, MUST_EXIST);
        //'../../../../course/view.php?id='+id_course+'#section='+section
        if ($module->name == 'lti') {
            $result = $DB->get_record(
                'grade_items',
                array('courseid' => $fromform->course, 'iteminstance' => $fromform->instance, 'itemtype' => 'mod', 'itemmodule' => $fromform->modulename)
            );
            if (!empty($result)) {
                $fromform->itemname = $result->itemname;
                $fromform->gradetype = $result->gradetype;
                $fromform->grademax = $result->grademax;
                $fromform->grademin = $result->grademin;
                $fromform->scaleid = $result->scaleid;
            }
        }
        
        $datos = addslashes(json_encode($fromform));

        $url = new moodle_url("/mod/$module->name/view.php", array('id' => $fromform->coursemodule, 'forceview' => 1));
        if (empty($fromform->showgradingmanagement)) {
        } else {
            $url = ($fromform->gradingman->get_management_url($url));
        }
        echo '<div id="snackbar"></div>';
        //echo html_writer::script('SLog.confir_nodos_actu('.$CFG->wwwroot.','.$course->id.', '.$cm->id.', "'.$cm->modname.'", '.$USER->id.',"'.$datos.'", '.$cm2->sectionnum.', "../../", "'.$url.'");'     );
        echo html_writer::script('SLog.confir_nodos_actu(' . $course->id . ', ' . $cm->id . ', "' . $cm->modname . '", ' . $USER->id . ',"' . $datos . '", ' . $cm2->sectionnum . ', "../../", "' . $url . '");');
    } else if (!empty($fromform->add)) {
        $fromform = add_moduleinfo($fromform, $course, $mform);
    } else {
        print_error('invaliddata');
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

    echo $OUTPUT->header();

    if (get_string_manager()->string_exists('modulename_help', $module->name)) {
        echo $OUTPUT->heading_with_help($pageheading, 'modulename', $module->name, 'icon');
    } else {
        echo $OUTPUT->heading_with_help($pageheading, '', $module->name, 'icon');
    }

    $mform->display();

    echo $OUTPUT->footer();
    if ($DB->get_manager()->table_exists('act_activities_uvd') && !empty($update)) {
        if ($module->name == 'assign' || $module->name == 'forum' || $module->name == 'url') { ///saber si es una actividad propuesta

            $propuesta = $DB->get_records_sql(
                'SELECT ac.*, ob.id AS id_ob, ob.obj, ob.id_como_p, 
                                                (SELECT id FROM {act_activity_propuesta} WHERE activitiesid = ac.id ORDER BY id DESC LIMIT 1) AS id_pro, 
                                                (SELECT actividad FROM {act_activity_propuesta} WHERE activitiesid = ac.id ORDER BY id DESC LIMIT 1) AS actividad
                                                FROM {act_activities_uvd} ac 
                                                LEFT JOIN {act_obj_create} ob ON ob.activitiesid = ac.acti_p
                                                WHERE  ac.estado >= 4 AND ac.courseid_h = :course  AND ac.sectionid_h = :section AND ac.relationid > 0',
                array('course' => $course->id, 'section' => $data->section)
            );
            if (!empty($propuesta)) {
                $acti = null;
                $id_tag = null;
                switch ($module->name) {
                    case 'assign':
                        $acti = 'activity';
                        $id_tag = 'list_frm_plantillas_creadas_';
                        break;
                    case 'forum':
                        $acti = 'rel_activity';
                        $id_tag = 'rel_list_frm_plantillas_creadas_';
                        break;
                    case 'url':
                        $acti = 'urls';
                        $id_tag = 'list_frm_recursos_creadas_';
                        break;
                }

                foreach ($propuesta as $key => $value) {
                    $obj = json_decode($value->obj);
                    foreach ($obj as $k => $v) {
                        $rubrica = 0;
                        if ((is_array($v) || is_object($v))) {
                            if (property_exists($v, 'area')) {
                                $rubrica = 1;
                            }
                            if ($acti == $k && property_exists($v, 'id') && $data->id == $v->id) {
                                echo '<script src="' . $CFG->wwwroot . '/local/backup_course/update/js/update_activities_uvd.js"></script>';
                                echo '<link rel="stylesheet" href="' . $CFG->wwwroot . '/local/activities_uvd/css/styles.css">';
                                $value->actividad = str_replace("angrytext", "", $value->actividad);
                                $value->actividad = str_replace('<button type="button" class="btn btn-primary btn-sm btn-flat" id="btn_modal_rec" onclick="adAcP.addRecurso()">Bibliografía básica</button>', "", $value->actividad);
                                $value->actividad = str_replace('<button type="button" class="btn btn-primary btn-sm btn-flat" id="btn_modal_ext" onclick="adAcP.addExterno()">Añadir Recursos complementarios</button>', "", $value->actividad);
                                $value->actividad = str_replace('adAcP.onkeypressDivEdit(this)', "", $value->actividad);
                                include '../../util.php';

                                //echo html_writer::script('u_act_uvd.redirec_template("'.addslashes(json_encode($value)).'", "'.$acti.'", "'.$id_tag.'", "'.addslashes(json_encode($v)).'" , "'.$CFG->wwwroot.'", "'.$module->name.'", '.$rubrica.')');
                                die();
                            } else if ($k == 'urls' && $acti == $k) {
                                foreach ($v as $ke => $va) {
                                    if (property_exists($va, 'id') && $data->id == $va->id) {
                                        echo '<script src="' . $CFG->wwwroot . '/local/backup_course/update/js/update_activities_uvd.js"></script>';
                                        echo '<link rel="stylesheet" href="' . $CFG->wwwroot . '/local/activities_uvd/css/styles.css">';
                                        $value->actividad = str_replace("angrytext", "", $value->actividad);
                                        $value->actividad = str_replace('<button type="button" class="btn btn-primary btn-sm btn-flat" id="btn_modal_rec" onclick="adAcP.addRecurso()">Bibliografía básica</button>', "", $value->actividad);
                                        $value->actividad = str_replace('<button type="button" class="btn btn-primary btn-sm btn-flat" id="btn_modal_ext" onclick="adAcP.addExterno()">Añadir Recursos complementarios</button>', "", $value->actividad);
                                        $value->actividad = str_replace('adAcP.onkeypressDivEdit(this)', "", $value->actividad);
                                        //$value->actividad = str_replace('style="display: none;"', "", $value->actividad);
                                        include '../../util.php';
                                        //echo html_writer::script('u_act_uvd.redirec_template("'.addslashes(json_encode($value)).'", "'.$acti.'", "'.$id_tag.'", "'.addslashes(json_encode($va)).'", "'.$CFG->wwwroot.'" , "'.$module->name.'", '.$rubrica.')');
                                        die();
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
