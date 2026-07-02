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

require_once($CFG->dirroot.'/local/backup_course/layout/moodleform_mod.php');

class mod_feedback_mod_form_uvd extends moodleform_mod_uvd {

    public function definition() {
        global $CFG, $DB;

        $editoroptions = feedback_get_editor_options();

        $mform    =& $this->_form;

        //-------------------------------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('name', 'feedback'), array('size'=>'64'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $this->standard_intro_elements(get_string('description', 'feedback'));

        //-------------------------------------------------------------------------------
        $mform->addElement('header', 'timinghdr', get_string('availability'));

        $mform->addElement('date_time_selector', 'timeopen', get_string('feedbackopen', 'feedback'),
            array('optional' => true));

        $mform->addElement('date_time_selector', 'timeclose', get_string('feedbackclose', 'feedback'),
            array('optional' => true));

        //-------------------------------------------------------------------------------
        $mform->addElement('header', 'feedbackhdr', get_string('questionandsubmission', 'feedback'));

        $options=array();
        $options[1]  = get_string('anonymous', 'feedback');
        $options[2]  = get_string('non_anonymous', 'feedback');
        $mform->addElement('select',
                           'anonymous',
                           get_string('anonymous_edit', 'feedback'),
                           $options);

        // check if there is existing responses to this feedback
        if (is_numeric($this->_instance) AND
                    $this->_instance AND
                    $feedback = $DB->get_record("feedback", array("id"=>$this->_instance))) {

            $completed_feedback_count = feedback_get_completeds_group_count($feedback);
        } else {
            $completed_feedback_count = false;
        }

        if ($completed_feedback_count) {
            $multiple_submit_value = $feedback->multiple_submit ? get_string('yes') : get_string('no');
            $mform->addElement('text',
                               'multiple_submit_static',
                               get_string('multiplesubmit', 'feedback'),
                               array('size'=>'4',
                                    'disabled'=>'disabled',
                                    'value'=>$multiple_submit_value));
            $mform->setType('multiple_submit_static', PARAM_RAW);

            $mform->addElement('hidden', 'multiple_submit', '');
            $mform->setType('multiple_submit', PARAM_INT);
            $mform->addHelpButton('multiple_submit_static', 'multiplesubmit', 'feedback');
        } else {
            $mform->addElement('selectyesno',
                               'multiple_submit',
                               get_string('multiplesubmit', 'feedback'));

            $mform->addHelpButton('multiple_submit', 'multiplesubmit', 'feedback');
        }

        $mform->addElement('selectyesno', 'email_notification', get_string('email_notification', 'feedback'));
        $mform->addHelpButton('email_notification', 'email_notification', 'feedback');

        $mform->addElement('selectyesno', 'autonumbering', get_string('autonumbering', 'feedback'));
        $mform->addHelpButton('autonumbering', 'autonumbering', 'feedback');

        //-------------------------------------------------------------------------------
        $mform->addElement('header', 'aftersubmithdr', get_string('after_submit', 'feedback'));

        $mform->addElement('selectyesno', 'publish_stats', get_string('show_analysepage_after_submit', 'feedback'));

        $mform->addElement('editor',
                           'page_after_submit_editor',
                           get_string("page_after_submit", "feedback"),
                           null,
                           $editoroptions);

        $mform->setType('page_after_submit_editor', PARAM_RAW);

        $mform->addElement('text',
                           'site_after_submit',
                           get_string('url_for_continue', 'feedback'),
                           array('size'=>'64', 'maxlength'=>'255'));

        $mform->setType('site_after_submit', PARAM_TEXT);
        $mform->addHelpButton('site_after_submit', 'url_for_continue', 'feedback');
        //-------------------------------------------------------------------------------
        $this->standard_coursemodule_elements();
        //-------------------------------------------------------------------------------
        // buttons
        $this->add_action_buttons();
    }

    public function data_preprocessing(&$default_values) {

        $editoroptions = feedback_get_editor_options();

        if ($this->current->instance) {
            // editing an existing feedback - let us prepare the added editor elements (intro done automatically)
            $draftitemid = file_get_submitted_draft_itemid('page_after_submit');
            $default_values['page_after_submit_editor']['text'] =
                                    file_prepare_draft_area($draftitemid, $this->context->id,
                                    'mod_feedback', 'page_after_submit', false,
                                    $editoroptions,
                                    $default_values['page_after_submit']);

            $default_values['page_after_submit_editor']['format'] = $default_values['page_after_submitformat'];
            $default_values['page_after_submit_editor']['itemid'] = $draftitemid;
        } else {
            // adding a new feedback instance
            $draftitemid = file_get_submitted_draft_itemid('page_after_submit_editor');

            // no context yet, itemid not used
            file_prepare_draft_area($draftitemid, null, 'mod_feedback', 'page_after_submit', false);
            $default_values['page_after_submit_editor']['text'] = '';
            $default_values['page_after_submit_editor']['format'] = editors_get_preferred_format();
            $default_values['page_after_submit_editor']['itemid'] = $draftitemid;
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
    public function data_postprocessing($data) {
        parent::data_postprocessing($data);
        if (isset($data->page_after_submit_editor)) {
            $data->page_after_submitformat = $data->page_after_submit_editor['format'];
            $data->page_after_submit = $data->page_after_submit_editor['text'];

            if (!empty($data->completionunlocked)) {
                // Turn off completion settings if the checkboxes aren't ticked
                $autocompletion = !empty($data->completion) &&
                    $data->completion == COMPLETION_TRACKING_AUTOMATIC;
                if (!$autocompletion || empty($data->completionsubmit)) {
                    $data->completionsubmit=0;
                }
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
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // Check open and close times are consistent.
        if ($data['timeopen'] && $data['timeclose'] &&
                $data['timeclose'] < $data['timeopen']) {
            $errors['timeclose'] = get_string('closebeforeopen', 'feedback');
        }
        return $errors;
    }

    public function add_completion_rules() {
        $mform =& $this->_form;

        $mform->addElement('checkbox',
                           'completionsubmit',
                           '',
                           get_string('completionsubmit', 'feedback'));
        // Enable this completion rule by default.
        $mform->setDefault('completionsubmit', 1);
        return array('completionsubmit');
    }

    public function completion_rule_enabled($data) {
        return !empty($data['completionsubmit']);
    }
}


$id_course = optional_param('id_curso', 0, PARAM_INT);
$id_actividad = optional_param('id_act', 0, PARAM_INT);
$url = new moodle_url('/local/backup_course/layout/assign.php');
$url->param('update', $id_actividad);
$PAGE->set_url($url);

// Select the "Edit settings" from navigation.
navigation_node::override_active_url(new moodle_url('/local/backup_course/layout/assign.php', array('id_act' => $id_actividad, 'return' => 1, 'id_curso'=>$id_course)));

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
$mformclassname = 'mod_'.$module->name.'_mod_form_uvd';
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
        #id_activitycompletionheader, #id_feedbackhdr, #id_aftersubmithdr, #id_submitbutton, #id_cancel, #page-navbar,
        #nav-drawer, .mt-5.mb-1.activity-navigation, #soporte-bar,
        #id_activitycompletionheader, #id_feedbackhdr, #id_aftersubmithdr, #id_submitbutton, #id_cancel,
        #nav-drawer, .mt-5.mb-1.activity-navigation, #soporte-bar, #page-navbar, #theme_boost-drawers-courseindex, #theme_boost-drawers-blocks,
        #fitem_id_cmidnumber, #fitem_id_groupingid, #fitem_id_restrictgroupbutton,
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



    