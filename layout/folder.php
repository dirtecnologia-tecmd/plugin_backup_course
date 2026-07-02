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
 * Folder configuration form
 *
 * @package   mod_folder
 * @copyright 2009 Petr Skoda  {@link http://skodak.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/lib/formslib.php');
require_once($CFG->libdir . '/gdlib.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/user/editadvanced_form.php');
require_once($CFG->dirroot . '/user/editlib.php');
require_once($CFG->dirroot . '/user/profile/lib.php');
require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->dirroot . '/lib/pear/HTML/QuickForm.php');
require_once($CFG->libdir . '/form/datetimeselector.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/lib/datalib.php');

require_once($CFG->libdir . '/filelib.php');
require_once($CFG->libdir . '/gradelib.php');
require_once($CFG->libdir . '/completionlib.php');
require_once($CFG->libdir . '/plagiarismlib.php');
require_once($CFG->dirroot . '/course/modlib.php');

require_once($CFG->dirroot.'/local/backup_course/layout/moodleform_mod.php');
//require_once($CFG->dirroot . '/mod/folder/locallib.php');
use core_grades\component_gradeitems;

class mod_folder_mod_form_uvd extends moodleform_mod_uvd {

	function definition() {
 
        global $CFG;
        $mform = $this->_form;

        $config = get_config('folder');

        //-------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->addElement('text', 'name', get_string('name'), array('size'=>'48'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $this->standard_intro_elements();

        //-------------------------------------------------------
        $mform->addElement('header', 'content', get_string('contentheader', 'folder'));
        $mform->addElement('filemanager', 'files', get_string('files'), null, array('subdirs'=>1, 'accepted_types'=>'*'));
        $mform->addElement('select', 'display', get_string('display', 'mod_folder'),
                array(FOLDER_DISPLAY_PAGE => get_string('displaypage', 'mod_folder'),
                    FOLDER_DISPLAY_INLINE => get_string('displayinline', 'mod_folder')));
        $mform->addHelpButton('display', 'display', 'mod_folder');
        if (!$this->courseformat->has_view_page()) {
            $mform->setConstant('display', FOLDER_DISPLAY_PAGE);
            $mform->hardFreeze('display');
        }
        $mform->setExpanded('content');

        // Adding option to show sub-folders expanded or collapsed by default.
        $mform->addElement('advcheckbox', 'showexpanded', get_string('showexpanded', 'folder'));
        $mform->addHelpButton('showexpanded', 'showexpanded', 'mod_folder');
        $mform->setDefault('showexpanded', $config->showexpanded);

        // Adding option to enable downloading archive of folder.
        $mform->addElement('advcheckbox', 'showdownloadfolder', get_string('showdownloadfolder', 'folder'));
        $mform->addHelpButton('showdownloadfolder', 'showdownloadfolder', 'mod_folder');
        $mform->setDefault('showdownloadfolder', true);

        // Adding option to enable viewing of individual files.
        $mform->addElement('advcheckbox', 'forcedownload', get_string('forcedownload', 'folder'));
        $mform->addHelpButton('forcedownload', 'forcedownload', 'mod_folder');
        $mform->setDefault('forcedownload', true);

        //-------------------------------------------------------
        $this->standard_coursemodule_elements();

        //-------------------------------------------------------
        $this->add_action_buttons();

        //-------------------------------------------------------
        $mform->addElement('hidden', 'revision');
        $mform->setType('revision', PARAM_INT);
        $mform->setDefault('revision', 1);
    }

    function data_preprocessing(&$default_values) {
        if ($this->current->instance) {
            // editing existing instance - copy existing files into draft area
            $draftitemid = file_get_submitted_draft_itemid('files');
            file_prepare_draft_area($draftitemid, $this->context->id, 'mod_folder', 'content', 0, array('subdirs'=>true));
            $default_values['files'] = $draftitemid;
        }
    }

    function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // Completion: Automatic on-view completion can not work together with
        // "display inline" option
        if (empty($errors['completion']) &&
                array_key_exists('completion', $data) &&
                $data['completion'] == COMPLETION_TRACKING_AUTOMATIC &&
                !empty($data['completionview']) &&
                $data['display'] == FOLDER_DISPLAY_INLINE) {
            $errors['completion'] = get_string('noautocompletioninline', 'mod_folder');
        }

        return $errors;
    }
}


$id_course = optional_param('id_curso', 0, PARAM_INT);
$id_actividad = optional_param('id_act', 0, PARAM_INT);
$url = new moodle_url('/local/backup_course/layout/folder.php');
$url->param('update', $id_actividad);
$PAGE->set_url($url);

// Select the "Edit settings" from navigation.
navigation_node::override_active_url(new moodle_url('/local/backup_course/layout/folder.php', array('id_act' => $id_actividad, 'return' => 1, 'id_curso' => $id_course)));

$cm = get_coursemodule_from_id('', $id_actividad, 0, false, MUST_EXIST);

$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

// require_login
require_login($course, false, $cm); // needed to setup proper $COURSE

list($cm, $context, $module, $data, $cw) = get_moduleinfo_data($cm, $course);
$data->return = 0;
$data->sr = $data->section;
$data->update = $id_actividad;
$data->id_curso = $id_course;
$data->id_act = $id_actividad;

$sectionname = get_section_name($course, $cw);
$fullmodulename = get_string('modulename', $module->name);

if ($data->section && $course->format != 'site') {
    $heading = new stdClass();
    $heading->what = $fullmodulename;
    $heading->in = $sectionname;
    $pageheading = get_string('updatingain', 'moodle', $heading);
} else {
    $pageheading = get_string('updatinga', 'moodle', $fullmodulename);
}

list($cm, $context, $module, $data, $cw) = get_moduleinfo_data($cm, $course);
//echo '<pre>$cm: '; print_r($cm);  echo '</pre>';
$mformclassname = 'mod_' . $module->name . '_mod_form_uvd';
$mform = new $mformclassname($data, $cw->section, $cm, $course);
$mform->set_data($data);
//$class->__construct();
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
echo $OUTPUT->heading_with_help($pageheading, 'modulename', $module->name, 'icon');

//echo '<pre>'; print_r($mform->current);die();
if ($mform->is_cancelled()) {
    echo "Datos NO actualizados";
} else if ($fromform = $mform->get_data()) {

    list($cm, $fromform) = update_moduleinfo($cm, $fromform, $course, $mform);
    rebuild_course_cache($course->id);
    echo "Datos actualizados";

    echo '<script type="text/javascript">
            var href = window.parent.location.href;
            window.parent.location.reload();
        </script>';
} else {
    $mform->display();
    echo $OUTPUT->footer();
}
echo '<style>
        .navbar.navbar-fixed-top.moodle-has-zindex, .fixed-top.navbar.navbar-light, .action-menu.moodle-actionmenu.d-inline , .desktop-first-column.block-region, #course-header, #id_general, #id_submissiontypes,  #id_submissionsettings, 
        #id_groupsubmissionsettings, #block-region-side-pre,#page-footer, #id_notifications, #id_tagshdr, #id_competenciessection,#id_availabilityconditionsheader, 
        #id_activitycompletionheader, #id_submitbutton, #id_cancel, #id_layouthdr, #id_interactionhdr, #id_reviewoptionshdr, #id_display,
        #id_security, #id_overallfeedbackhdr, #fitem_id_groupmode, #page-navbar, #theme_boost-drawers-courseindex, #theme_boost-drawers-blocks,
        #nav-drawer, .mt-5.mb-1.activity-navigation, #soporte-bar, #fitem_id_overduehandling, #fitem_id_graceperiod,
        #fitem_id_cmidnumber, #fitem_id_groupingid, #fitem_id_restrictgroupbutton, #fitem_id_gradecat, #fitem_id_gradepass, #fitem_id_grademethod,
        .botones-navegacion-actividades, #recursos-uniminuto-format, .btn-chatbot, .help-button
        {
            display: none !important;
        }
        #region-main-course-format{
            width: 100%!important
        }
        body.drawer-open-left {
            margin-left: 0 !important;
        }
        #fgroup_id_buttonar{
            display: flex !important;
        }
    </style>';
    echo '<script type="text/javascript">
    window.onload = function() {
        document.getElementById("overlay-loader_block").style.display = "block";
        document.getElementsByTagName("header") && document.getElementsByTagName("header")[0]? document.getElementsByTagName("header")[0].style.display = "none": "";
        document.getElementsByTagName("footer") && document.getElementsByTagName("footer")[0]? document.getElementsByTagName("footer")[0].style.display = "none": "";
        document.getElementById("overlay-loader_block").style.display = "none";
    }
</script>';