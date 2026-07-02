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
 * Take Attendance
 *
 * @package    mod_attendance
 * @copyright  2011 Artem Andreev <andreev.artem@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot . '/mod/attendance/locallib.php');

$pageparams = new mod_attendance_take_page_params();

$id                     = required_param('id', PARAM_INT);
$pageparams->sessionid  = required_param('sessionid', PARAM_INT);
$pageparams->grouptype  = required_param('grouptype', PARAM_INT);
$pageparams->sort       = optional_param('sort', ATT_SORT_DEFAULT, PARAM_INT);
$pageparams->copyfrom   = optional_param('copyfrom', null, PARAM_INT);
$pageparams->viewmode   = optional_param('viewmode', null, PARAM_INT);
$pageparams->gridcols   = optional_param('gridcols', null, PARAM_INT);
$pageparams->page       = optional_param('page', 1, PARAM_INT);
$pageparams->perpage    = optional_param('perpage', get_config('attendance', 'resultsperpage'), PARAM_INT);

$cm             = get_coursemodule_from_id('attendance', $id, 0, false, MUST_EXIST);
$course         = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$att            = $DB->get_record('attendance', array('id' => $cm->instance), '*', MUST_EXIST);
// Check this is a valid session for this attendance.
$session        = $DB->get_record(
    'attendance_sessions',
    array('id' => $pageparams->sessionid, 'attendanceid' => $att->id),
    '*',
    MUST_EXIST
);

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/attendance:takeattendances', $context);

$pageparams->group = groups_get_activity_group($cm, true);

$pageparams->init($course->id);
$att = new mod_attendance_structure($att, $cm, $course, $PAGE->context, $pageparams);

$allowedgroups = groups_get_activity_allowed_groups($cm);
if (!empty($pageparams->grouptype) && !array_key_exists($pageparams->grouptype, $allowedgroups)) {
    $group = groups_get_group($pageparams->grouptype);
    throw new moodle_exception('cannottakeforgroup', 'attendance', '', $group->name);
}

if (($formdata = data_submitted()) && confirm_sesskey()) {
    $att->take_from_form_data($formdata);

    $group = 0;
    if ($att->pageparams->grouptype != mod_attendance_structure::SESSION_COMMON) {
        $group = $att->pageparams->grouptype;
    } else {
        if ($att->pageparams->group) {
            $group = $att->pageparams->group;
        }
    }

    $totalusers = count_enrolled_users(context_module::instance($cm->id), 'mod/attendance:canbelisted', $group);
    $usersperpage = $att->pageparams->perpage;

    if (!empty($att->pageparams->page) && $att->pageparams->page && $totalusers && $usersperpage) {
        $numberofpages = ceil($totalusers / $usersperpage);
        if ($att->pageparams->page < $numberofpages) {
            $params = array(
                'sessionid' => $att->pageparams->sessionid,
                'grouptype' => $att->pageparams->grouptype
            );
            $params['page'] = $att->pageparams->page + 1;
            redirect($att->url_take($params), get_string('moreattendance', 'attendance'));
        }
    }

    redirect($att->url_manage(), get_string('attendancesuccess', 'attendance'));
}

$PAGE->set_url($att->url_take());
$PAGE->set_title($course->shortname . ": " . $att->name);
$PAGE->set_heading($course->fullname);
$PAGE->set_cacheable(true);
$PAGE->navbar->add($att->name);

$output = $PAGE->get_renderer('mod_attendance');
$sesstable = new mod_attendance\output\take_data($att);


echo '<style>
        .navbar.navbar-fixed-top.moodle-has-zindex, .fixed-top.navbar.navbar-light, .action-menu.moodle-actionmenu.d-inline , .desktop-first-column.block-region, #course-header, #id_general, #id_submissiontypes,  #id_submissionsettings, 
        #id_groupsubmissionsettings, #block-region-side-pre ,#page-footer, #id_notifications, #id_modstandardgrade, #id_tagshdr, #id_competenciessection,#id_availabilityconditionsheader, 
        #id_activitycompletionheader, #id_submitbutton, #id_cancel, #fitem_id_restrictgroupbutton, #id_flowcontrol,
        #nav-drawer, .mt-5.mb-1.activity-navigation, #soporte-bar, #fitem_id_groupmode, #page-navbar, #theme_boost-drawers-courseindex, #theme_boost-drawers-blocks,
        #id_entrieshdr, #id_appearancehdr, #id_entrieshdr, #fitem_id_rolewarning, #fitem_id_assessed, #fgroup_id_scale,
        #fitem_id_cmidnumber, #fitem_id_groupingid, .moreless-actions, #fitem_id_timelimit, #id_security,
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
            display:none !important;
        }    

        .nav-item.dropdown.dropdownmoremenu{
            display: none;
        }

        #fgroup_id_buttonar{
            display: flex !important;
        }

    </style>';


echo '<script type="text/javascript">

        window.onload = function() {

            document.getElementById("overlay-loader_block").style.display = "block";
            document.getElementsByTagName("header") && document.getElementsByTagName("header")[0] ? document.getElementsByTagName("header")[0].style.display = "none" : "";
            document.getElementsByTagName("footer") && document.getElementsByTagName("footer")[0] ? document.getElementsByTagName("footer")[0].style.display = "none" : "";
            document.getElementById("overlay-loader_block").style.display = "none";

            const singlebutton = document.querySelector(".singlebutton>form");
            singlebutton.action = singlebutton.action.replace("/mod/attendance/import/marksessions.php", "/local/backup_course/layout/marksessions.php");

        }

    </script>';



// Output starts here.

echo $output->header();

echo $output->render($sesstable);

echo $output->footer();
