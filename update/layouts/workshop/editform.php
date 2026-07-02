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
 * Edit grading form in for a particular instance of workshop
 *
 * @package    mod_workshop
 * @copyright  2009 David Mudrak <david.mudrak@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../../../../config.php');
require_once('locallib.php');

$cmid       = required_param('cmid', PARAM_INT);

$cm         = get_coursemodule_from_id('workshop', $cmid, 0, false, MUST_EXIST);
$course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

require_login($course, false, $cm);
require_capability('mod/workshop:editdimensions', $PAGE->context);

$workshop   = $DB->get_record('workshop', array('id' => $cm->instance), '*', MUST_EXIST);
$workshop   = new workshop($workshop, $cm, $course);

// todo: check if there already is some assessment done and do not allowed the change of the form
// once somebody already used it to assess

$PAGE->set_url($workshop->editform_url());
$PAGE->set_title($workshop->name);
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add(get_string('editingassessmentform', 'workshop'));

// load the grading strategy logic
$strategy = $workshop->grading_strategy_instance();

// load the form to edit the grading strategy dimensions
$mform = $strategy->get_edit_strategy_form($PAGE->url);
require_once '../loader.php';
echo '<link href="../../../css/style.css" rel="stylesheet" type="text/css" />'.
     '<script src="../../../../../lib/jquery/jquery-3.6.1.js"></script>'.
     '<script src="../../../update/js/buttons.js"></script>';
if ($mform->is_cancelled()) {
    redirect($workshop->view_url());
} elseif ($data = $mform->get_data()) {
    echo html_writer::script('document.getElementById("overlay-loader_block_modedit").style.display = "block";');
    if (($data->workshopid != $workshop->id) or ($data->strategy != $workshop->strategy)) {
        // this may happen if someone changes the workshop setting while the user had the
        // editing form opened
        throw new invalid_parameter_exception('Invalid workshop ID or the grading strategy has changed.');
    }
    $strategy->save_edit_strategy_form($data);
    
    if (isset($data->saveandclose)) {
        $url = $workshop->view_url();
    } elseif (isset($data->saveandpreview)) {
        $url = $workshop->previewform_url();
    } else {
        // save and continue - redirect to self to prevent data being re-posted by pressing "Reload"
        $url = $PAGE->url;
    }
    echo '<link href="'.$CFG->wwwroot .'/local/backup_course/css/style.css" rel="stylesheet" type="text/css" />'.
        '<link href="'.$CFG->wwwroot .'/local/backup_course/css/jquery-confirm.css" rel="stylesheet" type="text/css" />'.
        '<link href="'.$CFG->wwwroot .'/local/backup_course/css/tostadas.css" rel="stylesheet" type="text/css" />'.
            '<script src="'.$CFG->wwwroot .'/lib/jquery/jquery-3.6.1.js"></script>'.
             '<script src="'.$CFG->wwwroot .'/local/backup_course/update/js/buttons.js"></script>'.
             '<script src="'.$CFG->wwwroot .'/local/backup_course/update/js/objetsUpdates/updateObjet.js"></script>'.
             '<script src="'.$CFG->wwwroot .'/local/backup_course/update/js/objetsUpdates/saveLog.js"></script>   '.
             '<script src="'.$CFG->wwwroot .'/local/backup_course/update/js/objetsUpdates/CRE.js"></script>'.
             '<script src="'.$CFG->wwwroot .'/local/backup_course/js/objetos/QRY.js"></script>'.
             '<script src="'.$CFG->wwwroot .'/local/backup_course/js/jquery-confirm.js"></script>'.
             '<script src="'.$CFG->wwwroot .'/local/backup_course/js/mensajes.js"></script>'.
             '<script src="'.$CFG->wwwroot .'/local/backup_course/update/js/objetsUpdates/UPD.js"></script>'.
             '<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>'.
             '<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>';
    
    echo '<pre>';
    print_r($data);
    echo '</pre>';
    $course_modules = $DB->get_record('course_modules', array('id' => $cmid), '*', MUST_EXIST);
    $sectionnum     = $DB->get_record('course_sections', array('id' => $course_modules->section), '*', MUST_EXIST);
    echo '<div id="snackbar"></div>';
    echo html_writer::script('SLog.confir_nodos_actu('.$course->id.', '.$cm->id.', "'.$cm->modname.'", '.$USER->id.',"'.addslashes(json_encode($data)).'", '.$sectionnum->section.', "../../../", "'.$url.'");'     );
    die();
}

// Output starts here

echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($workshop->name));
echo $OUTPUT->heading(get_string('pluginname', 'workshopform_' . $workshop->strategy), 3);

$mform->display();

echo $OUTPUT->footer();
