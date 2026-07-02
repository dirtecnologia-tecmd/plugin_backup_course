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
 * Forms for updating/adding attendance
 *
 * @package    mod_attendance
 * @copyright  2011 Artem Andreev <andreev.artem@gmail.com>
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
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/lib/datalib.php');

require_once($CFG->libdir . '/filelib.php');
require_once($CFG->libdir . '/gradelib.php');
require_once($CFG->libdir . '/completionlib.php');
require_once($CFG->libdir . '/plagiarismlib.php');
require_once($CFG->dirroot . '/course/modlib.php');

require_once($CFG->dirroot . '/local/backup_course/layout/moodleform_mod.php');

/**
 * class for displaying add/update form.
 *
 * @copyright  2011 Artem Andreev <andreev.artem@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_attendance_mod_form extends moodleform_mod_uvd
{

    /**
     * Called to define this moodle form
     *
     * @return void
     */
    public function definition()
    {
        $attendanceconfig = get_config('attendance');
        if (!isset($attendanceconfig->subnet)) {
            $attendanceconfig->subnet = '';
        }
        $mform    = &$this->_form;

        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('name'), array('size' => '64'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->setDefault('name', get_string('modulename', 'attendance'));

        $this->standard_intro_elements();

        // Grade settings.
        $this->standard_grading_coursemodule_elements();

        $this->standard_coursemodule_elements(true);

        // IP address.
        if (get_config('attendance', 'subnetactivitylevel')) {
            $mform->addElement('header', 'security', get_string('extrarestrictions', 'attendance'));
            $mform->addElement('text', 'subnet', get_string('defaultsubnet', 'attendance'), array('size' => '164'));
            $mform->setType('subnet', PARAM_TEXT);
            $mform->addHelpButton('subnet', 'defaultsubnet', 'attendance');
            $mform->setDefault('subnet', $attendanceconfig->subnet);
        } else {
            $mform->addElement('hidden', 'subnet', '');
            $mform->setType('subnet', PARAM_TEXT);
        }

        $this->add_action_buttons();
    }
}

$id_course = optional_param('id_curso', 0, PARAM_INT);
$id_actividad = optional_param('id_act', 0, PARAM_INT);
$url = new moodle_url('/local/backup_course/layout/attendance.php');
$url->param('update', $id_actividad);
$PAGE->set_url($url);

// Select the "Edit settings" from navigation.
navigation_node::override_active_url(new moodle_url('/local/backup_course/layout/attendance.php', array('id_act' => $id_actividad, 'return' => 1, 'id_curso' => $id_course)));


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
        #id_groupsubmissionsettings, #block-region-side-pre ,#page-footer, #id_notifications, #id_modstandardgrade, #id_tagshdr, #id_competenciessection,#id_availabilityconditionsheader, 
        #id_activitycompletionheader, #id_submitbutton, #id_cancel, #fitem_id_restrictgroupbutton, #id_flowcontrol,
        #nav-drawer, .mt-5.mb-1.activity-navigation, #soporte-bar, #fitem_id_groupmode,
        #id_entrieshdr, #id_appearancehdr, #id_entrieshdr, #fitem_id_rolewarning, #fitem_id_assessed, #fgroup_id_scale,
        #fitem_id_cmidnumber, #fitem_id_groupingid, .moreless-actions, #fitem_id_timelimit, #id_security, #page-navbar,
        .botones-navegacion-actividades, #recursos-uniminuto-format, .btn-chatbot, .help-button
        {
            display: none !important;
        }
        #region-main-course-format{
            width: 100%!important}
            body.drawer-open-left {
                margin-left: 0 !important;
            }

        .secondary-navigation{
            display:block !important;
        }    

        .nav-item.dropdown.dropdownmoremenu{
            display: none;
        }

        #fgroup_id_buttonar{
            display: flex !important;
        }

    </style>';
?>
<script type="text/javascript">
    window.onload = function() {
        document.getElementById("overlay-loader_block").style.display = "block";
        document.getElementsByTagName("header") && document.getElementsByTagName("header")[0] ? document.getElementsByTagName("header")[0].style.display = "none" : "";
        document.getElementsByTagName("footer") && document.getElementsByTagName("footer")[0] ? document.getElementsByTagName("footer")[0].style.display = "none" : "";
        document.getElementById("overlay-loader_block").style.display = "none";
        const navItem = document.querySelectorAll("li.nav-item>a.nav-link");
        if (navItem != null) {
            navItem.forEach(element => {
                const tag = element.href;

                if (tag.indexOf("/course/modedit.php?") != -1) {
                    // Obtener la URL actual
                    const urlParams = new URLSearchParams(window.location.search);

                    // Obtener el valor del parámetro 
                    const id_act = urlParams.get("id_act");

                    // Obtener el valor del parámetro 
                    const id_curso = urlParams.get("id_curso");

                    // Modificar la URL
                    element.href = tag.replace("/course/modedit.php?update=" + id_act + "&return=1", "/local/backup_course/layout/attendance.php?id_act=" + id_act + "&id_curso=" + id_curso);

                    element.style.display = "flex";

                }
                if (tag.indexOf("/attendance/report.php?") != -1) {
                    element.remove();
                }
                if (tag.indexOf("/attendance/import.php?") != -1) {
                    element.remove();
                }
                if (tag.indexOf("/attendance/export.php?") != -1) {
                    element.remove();
                }

                if (tag.indexOf("/attendance/view.php?") != -1) {
                    // Obtener la URL actual
                    const urlParams = new URLSearchParams(window.location.search);

                    // Obtener el valor del parámetro 
                    const id_act = urlParams.get("id_act");

                    // Obtener el valor del parámetro 
                    const id_curso = urlParams.get("id_curso");

                    // Modificar la URL
                    element.href = tag.replace("/attendance/view.php?id=" + id_act, "/attendance/view.php?id=" + id_act + "&id_curso=" + id_curso);

                }
            });
        }
    }
</script>