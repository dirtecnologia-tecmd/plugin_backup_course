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
 * Group self selection instance configuration
 *
 * @package   mod_groupselect
 * @copyright 2018 HTW Chur Roger Barras
 * @copyright  2008-2011 Petr Skoda (http://skodak.org)
 * @copyright  2014 Tampere University of Technology, P. Pyykkönen (pirkka.pyykkonen ÄT tut.fi)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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
require_once($CFG->dirroot . '/lib/datalib.php');

require_once($CFG->libdir . '/filelib.php');
require_once($CFG->libdir . '/gradelib.php');
require_once($CFG->libdir . '/completionlib.php');
require_once($CFG->libdir . '/plagiarismlib.php');
require_once($CFG->dirroot . '/course/modlib.php');

require_once($CFG->dirroot . '/local/backup_course/layout/moodleform_mod.php');

/**
 * Groupselect settings form.
 *
 * @copyright 2018 HTW Chur Roger Barras
 * @copyright  2008-2011 Petr Skoda (http://skodak.org)
 * @copyright  2014 Tampere University of Technology, P. Pyykkönen (pirkka.pyykkonen ÄT tut.fi)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_groupselect_mod_form extends moodleform_mod_uvd {

    /**
     * Definition of the form
     */
    public function definition() {
        global $CFG, $COURSE, $DB; // TODO: get rid of the sloppy $COURSE.

        $mform = $this->_form;

        $config = get_config('groupselect');

        $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->addElement('text', 'name', get_string('name'), array('size' => '48'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');

        $this->standard_intro_elements();

        // -------------------------------------------------------

        $options = array();
        $options[0] = get_string('fromallgroups', 'mod_groupselect');
        if ($groupings = groups_get_all_groupings($COURSE->id)) {
            foreach ($groupings as $grouping) {
                $options[$grouping->id] = format_string($grouping->name);
            }
        }

        $roles = $DB->get_records("role");
        $supervisionroles = role_get_names(context_system::instance(), ROLENAME_ALIAS, true);

        $mform->addElement('date_time_selector', 'timeavailable', get_string('timeavailable', 'mod_groupselect'),
                            array('optional' => true));
        $mform->setDefault('timeavailable', 0);
        $mform->addElement('date_time_selector', 'timedue', get_string('timedue', 'mod_groupselect'), array('optional' => true));
        $mform->setDefault('timedue', 0);
        $mform->addElement('select', 'targetgrouping', get_string('targetgrouping', 'mod_groupselect'), $options);
        // Min. members.
        $mform->addElement('text', 'minmembers', get_string('minmembers', 'mod_groupselect'), array('size' => '4'));
        $mform->setType('minmembers', PARAM_INT);
        $mform->setDefault('minmembers', $config->minmembers);
        $mform->addHelpButton('minmembers', 'minmembers', 'mod_groupselect');
        // Max. members.
        $mform->addElement('text', 'maxmembers', get_string('maxmembers', 'mod_groupselect'), array('size' => '4'));
        $mform->setType('maxmembers', PARAM_INT);
        $mform->setDefault('maxmembers', $config->maxmembers);
        $mform->addHelpButton('maxmembers', 'maxmembers', 'mod_groupselect');
        // Multi group selection.
        $mform->addElement('text', 'maxgroupmembership', get_string('maxgroupmembership', 'mod_groupselect'), array('size' => '4'));
        $mform->setType('maxgroupmembership', PARAM_INT);
        $mform->setDefault('maxgroupmembership', $config->maxgroupmembership);
        $mform->addHelpButton('maxgroupmembership', 'maxgroupmembership', 'mod_groupselect');

        // Enable Permissions.

        $mform->addElement('header', 'permissions', get_string('enablepermissions', 'mod_groupselect'));
        $mform->setExpanded('permissions', true);
        // Join.
        $mform->addElement('advcheckbox', 'studentcanjoin', get_string('studentcanjoin', 'mod_groupselect'), '',
                array('optional' => true, 'group' => null), array(0, 1));
        $mform->addHelpButton('studentcanjoin', 'studentcanjoin', 'mod_groupselect');
        $mform->setDefault('studentcanjoin', $config->studentcanjoin);
        // Leave.
        $mform->addElement('advcheckbox', 'studentcanleave', get_string('studentcanleave', 'mod_groupselect'), '',
                array('optional' => true, 'group' => null), array(0, 1));
        $mform->addHelpButton('studentcanleave', 'studentcanleave', 'mod_groupselect');
        $mform->setDefault('studentcanleave', $config->studentcanleave);
        // Create.
        $mform->addElement('advcheckbox', 'studentcancreate', get_string('studentcancreate', 'mod_groupselect'), '',
                array('optional' => true, 'group' => null), array(0, 1));
        $mform->addHelpButton('studentcancreate', 'studentcancreate', 'mod_groupselect');
        $mform->setDefault('studentcancreate', $config->studentcancreate);
        // Group name.
        $mform->addElement('advcheckbox', 'studentcansetgroupname', get_string('studentcansetgroupname', 'mod_groupselect'), '',
                array('optional' => true, 'group' => null), array(0, 1));
        $mform->addHelpButton('studentcansetgroupname', 'studentcansetgroupname', 'mod_groupselect');
        $mform->setDefault('studentcansetgroupname', $config->studentcansetgroupname);
        $mform->disabledIf('studentcansetgroupname', 'studentcancreate', 'notchecked');
        // Group description.
        $mform->addElement('advcheckbox', 'studentcansetdesc', get_string('studentcansetdesc', 'mod_groupselect'), '',
                            array('optional' => true, 'group' => null), array(0, 1));
        $mform->addHelpButton('studentcansetdesc', 'studentcansetdesc', 'mod_groupselect');
        $mform->setDefault('studentcansetdesc', $config->studentcansetdesc);
        // Enroll password.
        $mform->addElement('advcheckbox', 'studentcansetenrolmentkey', get_string('studentcansetenrolmentkey', 'mod_groupselect'),
                            '', array('optional' => true, 'group' => null), array(0, 1));
        $mform->addHelpButton('studentcansetenrolmentkey', 'studentcansetenrolmentkey', 'mod_groupselect');
        $mform->setDefault('studentcansetenrolmentkey', $config->studentcansetenrolmentkey);
        $mform->disabledIf('studentcansetenrolmentkey', 'studentcancreate', 'notchecked');

        // Miscellaneous.

        $mform->addElement('header', 'miscellaneous', get_string('miscellaneoussettings', 'mod_groupselect'));
        $mform->setExpanded('miscellaneous', true);
        // Assign supervisor.
        $mform->addElement('advcheckbox', 'assignteachers', get_string('assigngroup', 'mod_groupselect'), '',
                array('optional' => true, 'group' => null), array(0, 1));
        $mform->addHelpButton('assignteachers', 'assigngroup', 'mod_groupselect');
        $mform->setDefault('assignteachers', $config->assignteachers);
        // Supervisor role.
        $mform->addElement('select', 'supervisionrole', get_string('supervisionrole', 'mod_groupselect'), $supervisionroles);
        $mform->setDefault('supervisionrole', $config->supervisionrole);
        $mform->addHelpButton('supervisionrole', 'supervisionrole', 'mod_groupselect');
        // Show supervisor.
        $mform->addElement('advcheckbox', 'showassignedteacher', get_string('showassignedteacher', 'mod_groupselect'), '',
                array('optional' => true, 'group' => null), array(0, 1));
        $mform->addHelpButton('showassignedteacher', 'showassignedteacher', 'mod_groupselect');
        $mform->setDefault('showassignedteacher', $config->showassignedteacher);
        $mform->disabledIf('showassignedteacher', 'assignteachers', 'notchecked');
        // Hide full group.
        $mform->addElement('advcheckbox', 'hidefullgroups', get_string('hidefullgroups', 'mod_groupselect'), '',
                array('optional' => true, 'group' => null), array(0, 1));
        $mform->addHelpButton('hidefullgroups', 'hidefullgroups', 'mod_groupselect');
        $mform->setDefault('hidefullgroups', $config->hidefullgroups);
        // Hide suspended students.
        $mform->addElement('advcheckbox', 'hidesuspendedstudents', get_string('hidesuspendedstudents', 'mod_groupselect'), '',
                array('optional' => true, 'group' => null), array(0, 1));
        $mform->addHelpButton('hidesuspendedstudents', 'hidesuspendedstudents', 'mod_groupselect');
        $mform->setDefault('hidesuspendedstudents', $config->hidesuspendedstudents);
        // Hide group members.
        $mform->addElement('advcheckbox', 'hidegroupmembers', get_string('hidegroupmembers', 'mod_groupselect'), '',
                array('optional' => true, 'group' => null), array(0, 1));
        $mform->addHelpButton('hidegroupmembers', 'hidegroupmembers', 'mod_groupselect');
        $mform->setDefault('hidegroupmembers', $config->hidegroupmembers);
        // Notify expired group selection.
        $mform->addElement('advcheckbox', 'notifyexpiredselection', get_string('notifyexpiredselection', 'mod_groupselect'), '',
                array('optional' => true, 'group' => null), array(0, 1));
        $mform->addHelpButton('notifyexpiredselection', 'notifyexpiredselection', 'mod_groupselect');
        $mform->setDefault('notifyexpiredselection', $config->notifyexpiredselection);
        // Delete empty groups.
        $mform->addElement('advcheckbox', 'deleteemptygroups', get_string('deleteemptygroups', 'mod_groupselect'), '',
                array('optional' => true, 'group' => null), array(0, 1));
        $mform->addHelpButton('deleteemptygroups', 'deleteemptygroups', 'mod_groupselect');
        $mform->setDefault('deleteemptygroups', $config->deleteemptygroups);

        // Buttons.

        $this->standard_coursemodule_elements();

        // -------------------------------------------------------
        $this->add_action_buttons();
    }

    /**
     * Validation of the form
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        $maxgroupmembership = $data['maxgroupmembership'];
        $maxmembers = $data['maxmembers'];
        $minmembers = $data['minmembers'];
        $timeavailable = $data['timeavailable'];
        $timedue = $data['timedue'];

        if ($maxmembers < 0) {
            $errors['maxmembers'] = get_string('maxmembers_error_low', 'mod_groupselect');
        }
        if ($minmembers < 0) {
            $errors['minmembers'] = get_string('minmembers_error_low', 'mod_groupselect');
        }
        if ($minmembers > $maxmembers && $maxmembers != 0) {
            $errors['minmembers'] = get_string('minmembers_error_bigger_maxmembers', 'mod_groupselect');
            $errors['maxmembers'] = get_string('maxmembers_error_smaller_minmembers', 'mod_groupselect');
        }
        if ($timeavailable >= $timedue && $timeavailable > 0 && $timedue > 0 ) {
            $errors['timeavailable'] = get_string('timeavailable_error_past_timedue', 'mod_groupselect');
            $errors['timedue'] = get_string('timedue_error_pre_timeavailable', 'mod_groupselect');
        }
        if ($maxgroupmembership < 1) {
            $errors['maxgroupmembership'] = get_string('maxgroupmembership_error_low', 'mod_groupselect');
        }

        return $errors;
    }
}


$id_course = optional_param('id_curso', 0, PARAM_INT);
$id_actividad = optional_param('id_act', 0, PARAM_INT);
$url = new moodle_url('/local/backup_course/layout/groupselect.php');
$url->param('update', $id_actividad);
$PAGE->set_url($url);

// Select the "Edit settings" from navigation.
navigation_node::override_active_url(new moodle_url('/local/backup_course/layout/groupselect.php', array('id_act' => $id_actividad, 'return' => 1, 'id_curso' => $id_course)));

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
        .navbar.navbar-fixed-top.moodle-has-zindex, .fixed-top.navbar.navbar-light, .action-menu.moodle-actionmenu.d-inline , .desktop-first-column.block-region, #course-header, #id_submissiontypes,  #id_submissionsettings, 
        #id_groupsubmissionsettings, #block-region-side-pre ,#page-footer, #id_notifications, #id_modstandardgrade,
        #id_submitbutton, #id_cancel, #nav-drawer, .mt-5.mb-1.activity-navigation, #soporte-bar,
        #id_entrieshdr, #id_appearancehdr, #id_entrieshdr, #fitem_id_rolewarning, #fitem_id_assessed, #fgroup_id_scale, #id_h5pdisplay, #id_h5pattempts,
        #fitem_id_groupmode, #fitem_id_introeditor, #page-navbar, #theme_boost-drawers-courseindex, #theme_boost-drawers-blocks,
        #fitem_id_cmidnumber, #fitem_id_groupingid, #fitem_id_restrictgroupbutton, #id_modstandardratings, #id_privacy, .moreless-actions, #id_options, #fitem_id_name 
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
            //Se oculta el check Muestra la descripción en la página del curso
            document.querySelectorAll(".col-md-9.checkbox")[0].style.display = "none";
        }
     </script>';



