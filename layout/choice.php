<?php

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

class mod_choice_mod_form_uvd extends moodleform_mod_uvd
{

    function definition()
    {
        global $CFG, $CHOICE_SHOWRESULTS, $CHOICE_PUBLISH, $CHOICE_DISPLAY, $DB;

        $mform    = &$this->_form;

        //-------------------------------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('choicename', 'choice'), array('size' => '64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $this->standard_intro_elements(get_string('description', 'choice'));

        $mform->addElement('select', 'display', get_string("displaymode", "choice"), $CHOICE_DISPLAY);

        //-------------------------------------------------------------------------------
        $mform->addElement('header', 'optionhdr', get_string('options', 'choice'));

        $mform->addElement('selectyesno', 'allowupdate', get_string("allowupdate", "choice"));

        $mform->addElement('selectyesno', 'allowmultiple', get_string('allowmultiple', 'choice'));
        if ($this->_instance) {
            if ($DB->count_records('choice_answers', array('choiceid' => $this->_instance)) > 0) {
                // Prevent user from toggeling the number of allowed answers once there are submissions.
                $mform->freeze('allowmultiple');
            }
        }

        $mform->addElement('selectyesno', 'limitanswers', get_string('limitanswers', 'choice'));
        $mform->addHelpButton('limitanswers', 'limitanswers', 'choice');

        $repeatarray = array();
        $repeatarray[] = $mform->createElement('text', 'option', get_string('optionno', 'choice'));
        $repeatarray[] = $mform->createElement('text', 'limit', get_string('limitno', 'choice'));
        $repeatarray[] = $mform->createElement('hidden', 'optionid', 0);

        if ($this->_instance) {
            $repeatno = $DB->count_records('choice_options', array('choiceid' => $this->_instance));
            $repeatno += 2;
        } else {
            $repeatno = 5;
        }

        $repeateloptions = array();
        $repeateloptions['limit']['default'] = 0;
        $repeateloptions['limit']['disabledif'] = array('limitanswers', 'eq', 0);
        $repeateloptions['limit']['rule'] = 'numeric';
        $repeateloptions['limit']['type'] = PARAM_INT;

        $repeateloptions['option']['helpbutton'] = array('choiceoptions', 'choice');
        $mform->setType('option', PARAM_CLEANHTML);

        $mform->setType('optionid', PARAM_INT);

        $this->repeat_elements(
            $repeatarray,
            $repeatno,
            $repeateloptions,
            'option_repeats',
            'option_add_fields',
            3,
            null,
            true
        );

        // Make the first option required
        if ($mform->elementExists('option[0]')) {
            $mform->addRule('option[0]', get_string('atleastoneoption', 'choice'), 'required', null, 'client');
        }

        //-------------------------------------------------------------------------------
        $mform->addElement('header', 'availabilityhdr', get_string('availability'));
        $mform->addElement(
            'date_time_selector',
            'timeopen',
            get_string("choiceopen", "choice"),
            array('optional' => true)
        );

        $mform->addElement(
            'date_time_selector',
            'timeclose',
            get_string("choiceclose", "choice"),
            array('optional' => true)
        );

        $mform->addElement('advcheckbox', 'showpreview', get_string('showpreview', 'choice'));
        $mform->addHelpButton('showpreview', 'showpreview', 'choice');
        $mform->disabledIf('showpreview', 'timeopen[enabled]');

        //-------------------------------------------------------------------------------
        $mform->addElement('header', 'resultshdr', get_string('results', 'choice'));

        $mform->addElement('select', 'showresults', get_string("publish", "choice"), $CHOICE_SHOWRESULTS);

        $mform->addElement('select', 'publish', get_string("privacy", "choice"), $CHOICE_PUBLISH);
        $mform->disabledIf('publish', 'showresults', 'eq', 0);

        $mform->addElement('selectyesno', 'showunanswered', get_string("showunanswered", "choice"));

        $mform->addElement('selectyesno', 'includeinactive', get_string('includeinactive', 'choice'));
        $mform->setDefault('includeinactive', 0);

        //-------------------------------------------------------------------------------
        $this->standard_coursemodule_elements();
        //-------------------------------------------------------------------------------
        $this->add_action_buttons();
    }

    function data_preprocessing(&$default_values)
    {
        global $DB;
        if (
            !empty($this->_instance) && ($options = $DB->get_records_menu('choice_options', array('choiceid' => $this->_instance), 'id', 'id,text'))
            && ($options2 = $DB->get_records_menu('choice_options', array('choiceid' => $this->_instance), 'id', 'id,maxanswers'))
        ) {
            $choiceids = array_keys($options);
            $options = array_values($options);
            $options2 = array_values($options2);

            foreach (array_keys($options) as $key) {
                $default_values['option[' . $key . ']'] = $options[$key];
                $default_values['limit[' . $key . ']'] = $options2[$key];
                $default_values['optionid[' . $key . ']'] = $choiceids[$key];
            }
        }
    }

    /**
     * Allows module to modify the data returned by form get_data().
     * This method is also called in the bulk activity completion form.
     *
     * Only available on moodleform_mod.
     *
     * @param stdClass $data the form data to be modified.
     */
    public function data_postprocessing($data)
    {
        parent::data_postprocessing($data);
        // Set up completion section even if checkbox is not ticked
        if (!empty($data->completionunlocked)) {
            if (empty($data->completionsubmit)) {
                $data->completionsubmit = 0;
            }
        }
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
        $errors = parent::validation($data, $files);

        // Check open and close times are consistent.
        if (
            $data['timeopen'] && $data['timeclose'] &&
            $data['timeclose'] < $data['timeopen']
        ) {
            $errors['timeclose'] = get_string('closebeforeopen', 'choice');
        }

        return $errors;
    }

    function add_completion_rules()
    {
        $mform = &$this->_form;

        $mform->addElement('checkbox', 'completionsubmit', '', get_string('completionsubmit', 'choice'));
        // Enable this completion rule by default.
        $mform->setDefault('completionsubmit', 1);
        return array('completionsubmit');
    }

    function completion_rule_enabled($data)
    {
        return !empty($data['completionsubmit']);
    }
}



$id_course = optional_param('id_curso', 0, PARAM_INT);
$id_actividad = optional_param('id_act', 0, PARAM_INT);
$url = new moodle_url('/local/backup_course/layout/assign.php');
$url->param('update', $id_actividad);
$PAGE->set_url($url);

// Select the "Edit settings" from navigation.
navigation_node::override_active_url(new moodle_url('/local/backup_course/layout/assign.php', array('id_act' => $id_actividad, 'return' => 1, 'id_curso' => $id_course)));

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
        #id_groupsubmissionsettings, #block-region-side-pre, #page-footer, #id_notifications, #id_modstandardgrade, #id_tagshdr, #id_competenciessection,#id_availabilityconditionsheader, #id_activitycompletionheader,
        #id_submitbutton, #id_cancel, #id_optionhdr, #id_resultshdr,
        #nav-drawer, .mt-5.mb-1.activity-navigation, #soporte-bar,
        #fitem_id_cmidnumber, #fitem_id_groupingid, #fitem_id_restrictgroupbutton, #page-navbar, #theme_boost-drawers-courseindex, #theme_boost-drawers-blocks,
        .botones-navegacion-actividades, #recursos-uniminuto-format, .btn-chatbot, .help-button
        {
            display: none !important;
        }
        #region-main-course-format{
        width: 100%!important}
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
