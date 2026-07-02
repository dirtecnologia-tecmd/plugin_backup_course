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
 * The main mod_h5pactivity configuration form.
 *
 * @package     mod_h5pactivity
 * @copyright   2020 Ferran Recio <ferran@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_h5pactivity\local\manager;

global $CFG;
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
require_once($CFG->dirroot . '/lib/datalib.php');

require_once($CFG->libdir . '/filelib.php');
require_once($CFG->libdir . '/gradelib.php');
require_once($CFG->libdir . '/completionlib.php');
require_once($CFG->libdir . '/plagiarismlib.php');
require_once($CFG->dirroot . '/course/modlib.php');

require_once($CFG->dirroot . '/local/backup_course/layout/moodleform_mod.php');


/**
 * Module instance settings form.
 *
 * @package    mod_h5pactivity
 * @copyright  2020 Ferran Recio <ferran@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_h5pactivity_mod_form extends moodleform_mod_uvd
{

    /**
     * Defines forms elements
     */
    public function definition(): void
    {
        global $CFG, $OUTPUT;

        $mform = &$this->_form;

        // Adding the "general" fieldset, where all the common settings are shown.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('name'), ['size' => '64']);

        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }

        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $this->standard_intro_elements();

        // Adding the rest of mod_h5pactivity settings, spreading all them into this fieldset.
        $options = [];
        $options['accepted_types'] = ['.h5p'];
        $options['maxbytes'] = 0;
        $options['maxfiles'] = 1;
        $options['subdirs'] = 0;

        $mform->addElement('filemanager', 'packagefile', get_string('package', 'mod_h5pactivity'), null, $options);
        $mform->addHelpButton('packagefile', 'package', 'mod_h5pactivity');
        $mform->addRule('packagefile', null, 'required');

        // Add a link to the Content Bank if the user can access.
        $course = $this->get_course();
        $coursecontext = context_course::instance($course->id);
        if (has_capability('moodle/contentbank:access', $coursecontext)) {
            $msg = null;
            $context = $this->get_context();
            if ($context instanceof \context_module) {
                // This is an existing activity. If the H5P file it's a referenced file from the content bank, a link for
                // displaying this specific content will be used instead of the generic link to the main page of the content bank.
                $fs = get_file_storage();
                $files = $fs->get_area_files($context->id, 'mod_h5pactivity', 'package', 0, 'sortorder, itemid, filepath,
                    filename', false);
                $file = reset($files);
                if ($file && $file->get_reference() != null) {
                    $referencedfile = \repository::get_moodle_file($file->get_reference());
                    if ($referencedfile->get_component() == 'contentbank') {
                        // If the attached file is a referencedfile in the content bank, display a link to open this content.
                        $url = new moodle_url('/contentbank/view.php', ['id' => $referencedfile->get_itemid()]);
                        $msg = get_string('opencontentbank', 'mod_h5pactivity', $url->out());
                        $msg .= ' ' . $OUTPUT->help_icon('contentbank', 'mod_h5pactivity');
                    }
                }
            }
            if (!isset($msg)) {
                $url = new moodle_url('/contentbank/index.php', ['contextid' => $coursecontext->id]);
                $msg = get_string('usecontentbank', 'mod_h5pactivity', $url->out());
                $msg .= ' ' . $OUTPUT->help_icon('contentbank', 'mod_h5pactivity');
            }

            $mform->addElement('static', 'contentbank', '', $msg);
        }

        // H5P displaying options.
        $factory = new \core_h5p\factory();
        $core = $factory->get_core();
        $displayoptions = (array) \core_h5p\helper::decode_display_options($core);
        $mform->addElement('header', 'h5pdisplay', get_string('h5pdisplay', 'mod_h5pactivity'));
        foreach ($displayoptions as $key => $value) {
            $name = get_string('display' . $key, 'mod_h5pactivity');
            $fieldname = "displayopt[$key]";
            $mform->addElement('checkbox', $fieldname, $name);
            $mform->setType($fieldname, PARAM_BOOL);
        }

        // Add standard grading elements.
        $this->standard_grading_coursemodule_elements();

        // Attempt options.
        $mform->addElement('header', 'h5pattempts', get_string('h5pattempts', 'mod_h5pactivity'));

        $mform->addElement('static', 'trackingwarning', '', get_string('tracking_messages', 'mod_h5pactivity'));

        $options = [1 => get_string('yes'), 0 => get_string('no')];
        $mform->addElement('select', 'enabletracking', get_string('enabletracking', 'mod_h5pactivity'), $options);
        $mform->setDefault('enabletracking', 1);

        $options = manager::get_grading_methods();
        $mform->addElement('select', 'grademethod', get_string('grade_grademethod', 'mod_h5pactivity'), $options);
        $mform->setType('grademethod', PARAM_INT);
        $mform->hideIf('grademethod', 'enabletracking', 'neq', 1);
        $mform->disabledIf('grademethod', 'grade[modgrade_type]', 'neq', 'point');
        $mform->addHelpButton('grademethod', 'grade_grademethod', 'mod_h5pactivity');

        $options = manager::get_review_modes();
        $mform->addElement('select', 'reviewmode', get_string('review_mode', 'mod_h5pactivity'), $options);
        $mform->setType('reviewmode', PARAM_INT);
        $mform->hideIf('reviewmode', 'enabletracking', 'neq', 1);

        // Add standard elements.
        $this->standard_coursemodule_elements();

        // Add standard buttons.
        $this->add_action_buttons();
    }

    /**
     * Enforce validation rules here
     *
     * @param array $data array of ("fieldname"=>value) of submitted data
     * @param array $files array of uploaded files "element_name"=>tmp_file_path
     * @return array
     **/
    public function validation($data, $files)
    {
        global $USER;
        $errors = parent::validation($data, $files);

        if (empty($data['packagefile'])) {
            $errors['packagefile'] = get_string('required');
        } else {
            $draftitemid = file_get_submitted_draft_itemid('packagefile');

            file_prepare_draft_area(
                $draftitemid,
                $this->context->id,
                'mod_h5pactivity',
                'packagefilecheck',
                null,
                ['subdirs' => 0, 'maxfiles' => 1]
            );

            // Get file from users draft area.
            $usercontext = context_user::instance($USER->id);
            $fs = get_file_storage();
            $files = $fs->get_area_files($usercontext->id, 'user', 'draft', $draftitemid, 'id', false);

            if (count($files) < 1) {
                $errors['packagefile'] = get_string('required');
                return $errors;
            }
            $file = reset($files);
            if (!$file->is_external_file() && !empty($data['updatefreq'])) {
                // Make sure updatefreq is not set if using normal local file.
                $errors['updatefreq'] = get_string('updatefreq_error', 'mod_h5pactivity');
            }
        }

        return $errors;
    }

    /**
     * Enforce defaults here.
     *
     * @param array $defaultvalues Form defaults
     * @return void
     **/
    public function data_preprocessing(&$defaultvalues)
    {
        // H5P file.
        $draftitemid = file_get_submitted_draft_itemid('packagefile');
        file_prepare_draft_area(
            $draftitemid,
            $this->context->id,
            'mod_h5pactivity',
            'package',
            0,
            ['subdirs' => 0, 'maxfiles' => 1]
        );
        $defaultvalues['packagefile'] = $draftitemid;

        // H5P display options.
        $factory = new \core_h5p\factory();
        $core = $factory->get_core();
        if (isset($defaultvalues['displayoptions'])) {
            $currentdisplay = $defaultvalues['displayoptions'];
            $displayoptions = (array) \core_h5p\helper::decode_display_options($core, $currentdisplay);
        } else {
            $displayoptions = (array) \core_h5p\helper::decode_display_options($core);
        }
        foreach ($displayoptions as $key => $value) {
            $fieldname = "displayopt[$key]";
            $defaultvalues[$fieldname] = $value;
        }
    }

    /**
     * Allows modules to modify the data returned by form get_data().
     * This method is also called in the bulk activity completion form.
     *
     * Only available on moodleform_mod.
     *
     * @param stdClass $data passed by reference
     */
    public function data_postprocessing($data)
    {
        parent::data_postprocessing($data);

        $factory = new \core_h5p\factory();
        $core = $factory->get_core();
        if (isset($data->displayopt)) {
            $config = (object) $data->displayopt;
        } else {
            $config = \core_h5p\helper::decode_display_options($core);
        }
        $data->displayoptions = \core_h5p\helper::get_display_options($core, $config);

        if (!isset($data->enabletracking)) {
            $data->enabletracking = 0;
        }
    }
}

$id_course = optional_param('id_curso', 0, PARAM_INT);
$id_actividad = optional_param('id_act', 0, PARAM_INT);
$url = new moodle_url('/local/backup_course/layout/h5pactivity.php');
$url->param('update', $id_actividad);
$PAGE->set_url($url);

// Select the "Edit settings" from navigation.
navigation_node::override_active_url(new moodle_url('/local/backup_course/layout/h5pactivity.php', array('id_act' => $id_actividad, 'return' => 1, 'id_curso' => $id_course)));

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
/* $mformclassname = 'mod_' . $module->name . '_mod_form_uvd'; */

$mformclassname = 'mod_' . $module->name . '_mod_form';
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
        #id_groupsubmissionsettings, #block-region-side-pre ,#page-footer, #id_notifications, #id_modstandardgrade, #id_tagshdr, #id_competenciessection,#id_availabilityconditionsheader, 
        #id_activitycompletionheader, #id_submitbutton, #id_cancel, #nav-drawer, .mt-5.mb-1.activity-navigation, #soporte-bar,
        #id_entrieshdr, #id_appearancehdr, #id_entrieshdr, #fitem_id_rolewarning, #fitem_id_assessed, #fgroup_id_scale, #id_h5pdisplay, #id_h5pattempts,
        #fitem_id_groupmode, #page-navbar, #theme_boost-drawers-courseindex, #theme_boost-drawers-blocks,
        #fitem_id_cmidnumber, #fitem_id_groupingid, #fitem_id_restrictgroupbutton, #id_modstandardratings,
        .botones-navegacion-actividades, #recursos-uniminuto-format, .btn-chatbot, .help-button
        {
            display: none !important;
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

