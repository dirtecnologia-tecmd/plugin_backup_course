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
 * Rubric editor page
 *
 * @package    gradingform_rubric
 * @copyright  2011 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../../../config.php');
require_once($CFG->dirroot . '/grade/grading/form/rubric/lib.php');
require_once($CFG->dirroot . '/grade/grading/form/rubric/edit_form.php');
require_once($CFG->dirroot . '/grade/grading/lib.php');

require_once '../loader.php';
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

$areaid = required_param('areaid', PARAM_INT);

$manager = get_grading_manager($areaid);

list($context, $course, $cm) = get_context_info_array($manager->get_context()->id);

$areaName = "";

if ($cm->modname == 'assign') {
    $areaName = 'submissions';
} else {
    $areaName = $cm->modname;
}

$redi = "$CFG->wwwroot/local/backup_course/update/layouts/rubric/manage.php?contextid=$context->id&component=mod_$cm->modname&area=$areaName";

require_login($course, true, $cm);
require_capability('moodle/grade:managegradingforms', $context);

$controller = $manager->get_controller('rubric');

$PAGE->set_url(new moodle_url('/local/backup_course/update/layouts/rubric/edit.php', array('areaid' => $areaid)));
$PAGE->set_title(get_string('definerubric', 'gradingform_rubric'));
$PAGE->set_heading(get_string('definerubric', 'gradingform_rubric'));

$mform = new gradingform_rubric_editrubric(null, array('areaid' => $areaid, 'context' => $context, 'allowdraft' => !$controller->has_active_instances()), 'post', '', array('class' => 'gradingform_rubric_editform'));
$data = $controller->get_definition_for_editing(true);
$returnurl = optional_param('returnurl', $manager->get_management_url(), PARAM_LOCALURL);
$data->returnurl = $returnurl;
$mform->set_data($data);
if ($mform->is_cancelled()) {
    redirect($returnurl);
} else if ($mform->is_submitted() && $mform->is_validated() && !$mform->need_confirm_regrading($controller)) {

    // Everything ok, validated, re-grading confirmed if needed. Make changes to the rubric.
    $data = $mform->get_data();
    $controller->update_definition($data);

    // If we do not go back to management url and the minscore warning needs to be displayed, display it during redirection.
    $warning = null;
    if (!empty($data->returnurl) && $data->returnurl !== $manager->get_management_url()->out(false)) {
        if (empty($data->rubric['options']['lockzeropoints']) && ($scores = $controller->get_min_max_score()) && $scores['minscore'] <> 0) {
            $warning = get_string('zerolevelsabsent', 'gradingform_rubric') . '<br>' .
                html_writer::link($manager->get_management_url(), get_string('back'));
        }
    }
    
    echo html_writer::script('SLog.confir_nodos_actu(' . $course->id . ', ' . $cm->id . ', "' . $cm->modname . '", ' . $USER->id . ',"' . addslashes(json_encode($data)) . '", "' . null . '", "../../../", "' . $redi . '");');
    die();

}

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();
