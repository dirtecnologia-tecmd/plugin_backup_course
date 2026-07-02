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

require_once($CFG->libdir . '/gradelib.php');
require_once($CFG->libdir . '/completionlib.php');
require_once($CFG->libdir . '/plagiarismlib.php');
require_once($CFG->dirroot . '/course/modlib.php');

require_once($CFG->dirroot . '/local/backup_course/layout/moodleform_mod.php');
require_once($CFG->dirroot . '/mod/workshop/locallib.php');
require_once($CFG->libdir . '/filelib.php');
/**
 * Module settings form for Workshop instances
 */
class mod_workshop_mod_form_uvd extends moodleform_mod_uvd
{

    /** @var object the course this instance is part of */
    protected $course = null;

    /**
     * Constructor
     */
    public function __construct($current, $section, $cm, $course)
    {
        $this->course = $course;
        parent::__construct($current, $section, $cm, $course);
    }

    /**
     * Defines the workshop instance configuration form
     *
     * @return void
     */
    public function definition()
    {
        global $CFG, $PAGE, $DB;

        $workshopconfig = get_config('workshop');
        $mform = $this->_form;

        // General --------------------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Workshop name
        $label = get_string('workshopname', 'workshop');
        $mform->addElement('text', 'name', $label, array('size' => '64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        // Introduction
        $this->standard_intro_elements(get_string('introduction', 'workshop'));

        // Grading settings -----------------------------------------------------------
        $mform->addElement('header', 'gradingsettings', get_string('gradingsettings', 'workshop'));
        $mform->setExpanded('gradingsettings');

        $label = get_string('strategy', 'workshop');
        $mform->addElement('select', 'strategy', $label, workshop::available_strategies_list());
        $mform->setDefault('strategy', $workshopconfig->strategy);
        $mform->addHelpButton('strategy', 'strategy', 'workshop');

        $grades = workshop::available_maxgrades_list();
        $current_gradecat_id = 0;

        if (moodleform_mod_uvd::get_current() && property_exists(moodleform_mod_uvd::get_current(), 'gradecat')) {
            $current_gradecat_id = moodleform_mod_uvd::get_current()->gradecat;
        }

        $gradecategories = $DB->get_record("grade_categories", array("id" => $current_gradecat_id), "id, fullname");

        $categorias = [];

        if ($gradecategories && $gradecategories->fullname != '?') {
            $categorias = [$gradecategories->id => $gradecategories->fullname];
        } else {
            $categorias = grade_get_categories_menu($this->course->id);
        }
        // Acceder a los valores
        $label = get_string('submissiongrade', 'workshop');
        $mform->addGroup(array(
            $mform->createElement('select', 'grade', '', $grades),
            $mform->createElement('select', 'gradecategory', '', $categorias),
        ), 'submissiongradegroup', $label, ' ', false);
        $mform->setDefault('grade', $workshopconfig->grade);
        $mform->addHelpButton('submissiongradegroup', 'submissiongrade', 'workshop');

        $mform->addElement('text', 'submissiongradepass', get_string('gradetopasssubmission', 'workshop'));
        $mform->addHelpButton('submissiongradepass', 'gradepass', 'grades');
        $mform->setDefault('submissiongradepass', '');
        $mform->setType('submissiongradepass', PARAM_RAW);

        $label = get_string('gradinggrade', 'workshop');
        $mform->addGroup(array(
            $mform->createElement('select', 'gradinggrade', '', $grades),
            $mform->createElement('select', 'gradinggradecategory', '', $categorias),
        ), 'gradinggradegroup', $label, ' ', false);
        $mform->setDefault('gradinggrade', $workshopconfig->gradinggrade);
        $mform->addHelpButton('gradinggradegroup', 'gradinggrade', 'workshop');

        $mform->addElement('text', 'gradinggradepass', get_string('gradetopassgrading', 'workshop'));
        $mform->addHelpButton('gradinggradepass', 'gradepass', 'grades');
        $mform->setDefault('gradinggradepass', '');
        $mform->setType('gradinggradepass', PARAM_RAW);

        $options = array();
        for ($i = 5; $i >= 0; $i--) {
            $options[$i] = $i;
        }
        $label = get_string('gradedecimals', 'workshop');
        $mform->addElement('select', 'gradedecimals', $label, $options);
        $mform->setDefault('gradedecimals', $workshopconfig->gradedecimals);

        // Submission settings --------------------------------------------------------
        $mform->addElement('header', 'submissionsettings', get_string('submissionsettings', 'workshop'));

        $label = get_string('instructauthors', 'workshop');
        $mform->addElement(
            'editor',
            'instructauthorseditor',
            $label,
            null,
            workshop::instruction_editors_options($this->context)
        );

        $options = array();
        for ($i = 7; $i >= 0; $i--) {
            $options[$i] = $i;
        }
        $label = get_string('nattachments', 'workshop');
        $mform->addElement('select', 'nattachments', $label, $options);
        $mform->setDefault('nattachments', 1);

        $label = get_string('allowedfiletypesforsubmission', 'workshop');
        $mform->addElement('filetypes', 'submissionfiletypes', $label);
        $mform->addHelpButton('submissionfiletypes', 'allowedfiletypesforsubmission', 'workshop');
        $mform->disabledIf('submissionfiletypes', 'nattachments', 'eq', 0);

        $options = get_max_upload_sizes($CFG->maxbytes, $this->course->maxbytes, 0, $workshopconfig->maxbytes);
        $mform->addElement('select', 'maxbytes', get_string('maxbytes', 'workshop'), $options);
        $mform->setDefault('maxbytes', $workshopconfig->maxbytes);
        $mform->disabledIf('maxbytes', 'nattachments', 'eq', 0);

        /*  $label = get_string('latesubmissions', 'workshop');
        $text = get_string('latesubmissions_desc', 'workshop');
        $mform->addElement('checkbox', 'latesubmissions', $label, $text);
        $mform->addHelpButton('latesubmissions', 'latesubmissions', 'workshop'); */

        // Assessment settings --------------------------------------------------------
        $mform->addElement('header', 'assessmentsettings', get_string('assessmentsettings', 'workshop'));

        $label = get_string('instructreviewers', 'workshop');
        $mform->addElement(
            'editor',
            'instructreviewerseditor',
            $label,
            null,
            workshop::instruction_editors_options($this->context)
        );

        $label = get_string('useselfassessment', 'workshop');
        $text = get_string('useselfassessment_desc', 'workshop');
        $mform->addElement('checkbox', 'useselfassessment', $label, $text);
        $mform->addHelpButton('useselfassessment', 'useselfassessment', 'workshop');

        // Feedback -------------------------------------------------------------------
        $mform->addElement('header', 'feedbacksettings', get_string('feedbacksettings', 'workshop'));

        $mform->addElement('select', 'overallfeedbackmode', get_string('overallfeedbackmode', 'mod_workshop'), array(
            0 => get_string('overallfeedbackmode_0', 'mod_workshop'),
            1 => get_string('overallfeedbackmode_1', 'mod_workshop'),
            2 => get_string('overallfeedbackmode_2', 'mod_workshop')
        ));
        $mform->addHelpButton('overallfeedbackmode', 'overallfeedbackmode', 'mod_workshop');
        $mform->setDefault('overallfeedbackmode', 1);

        $options = array();
        for ($i = 7; $i >= 0; $i--) {
            $options[$i] = $i;
        }
        $mform->addElement('select', 'overallfeedbackfiles', get_string('overallfeedbackfiles', 'workshop'), $options);
        $mform->setDefault('overallfeedbackfiles', 0);
        $mform->disabledIf('overallfeedbackfiles', 'overallfeedbackmode', 'eq', 0);

        $label = get_string('allowedfiletypesforoverallfeedback', 'workshop');
        $mform->addElement('filetypes', 'overallfeedbackfiletypes', $label);
        $mform->addHelpButton('overallfeedbackfiletypes', 'allowedfiletypesforoverallfeedback', 'workshop');
        $mform->disabledIf('overallfeedbackfiletypes', 'overallfeedbackfiles', 'eq', 0);

        $options = get_max_upload_sizes($CFG->maxbytes, $this->course->maxbytes);
        $mform->addElement('select', 'overallfeedbackmaxbytes', get_string('overallfeedbackmaxbytes', 'workshop'), $options);
        $mform->setDefault('overallfeedbackmaxbytes', $workshopconfig->maxbytes);
        $mform->disabledIf('overallfeedbackmaxbytes', 'overallfeedbackmode', 'eq', 0);
        $mform->disabledIf('overallfeedbackmaxbytes', 'overallfeedbackfiles', 'eq', 0);

        $label = get_string('conclusion', 'workshop');
        $mform->addElement(
            'editor',
            'conclusioneditor',
            $label,
            null,
            workshop::instruction_editors_options($this->context)
        );
        $mform->addHelpButton('conclusioneditor', 'conclusion', 'workshop');

        // Example submissions --------------------------------------------------------
        $mform->addElement('header', 'examplesubmissionssettings', get_string('examplesubmissions', 'workshop'));

        $label = get_string('useexamples', 'workshop');
        $text = get_string('useexamples_desc', 'workshop');
        $mform->addElement('checkbox', 'useexamples', $label, $text);
        $mform->addHelpButton('useexamples', 'useexamples', 'workshop');

        $label = get_string('examplesmode', 'workshop');
        $options = workshop::available_example_modes_list();
        $mform->addElement('select', 'examplesmode', $label, $options);
        $mform->setDefault('examplesmode', $workshopconfig->examplesmode);
        $mform->disabledIf('examplesmode', 'useexamples');


        // Availability ---------------------------------------------------------------
        $mform->addElement('header', 'accesscontrol', get_string('availability', 'core'));

        $label = get_string('submissionstart', 'workshop');
        $mform->addElement('date_time_selector', 'submissionstart', $label, array('optional' => true));

        $label = get_string('submissionend', 'workshop');
        $mform->addElement('date_time_selector', 'submissionend', $label, array('optional' => true));

        $label = get_string('submissionendswitch', 'mod_workshop');
        $mform->addElement('checkbox', 'phaseswitchassessment', $label);
        $mform->hideIf('phaseswitchassessment', 'submissionend[enabled]');
        $mform->addHelpButton('phaseswitchassessment', 'submissionendswitch', 'mod_workshop');

        $label = get_string('assessmentstart', 'workshop');
        $mform->addElement('date_time_selector', 'assessmentstart', $label, array('optional' => true));

        $label = get_string('assessmentend', 'workshop');
        $mform->addElement('date_time_selector', 'assessmentend', $label, array('optional' => true));

        $coursecontext = context_course::instance($this->course->id);
        // To be removed (deprecated) with MDL-67526.
        plagiarism_get_form_elements_module($mform, $coursecontext, 'mod_workshop');

        // Common module settings, Restrict availability, Activity completion etc. ----
        $features = array(
            'groups' => true,
            'groupings' => true,
            'outcomes' => true,
            'gradecat' => false,
            'idnumber' => false
        );

        $this->standard_coursemodule_elements();

        // Standard buttons, common to all modules ------------------------------------
        $this->add_action_buttons();

        $PAGE->requires->js_call_amd('mod_workshop/modform', 'init');
    }

    /**
     * Prepares the form before data are set
     *
     * Additional wysiwyg editor are prepared here, the introeditor is prepared automatically by core.
     * Grade items are set here because the core modedit supports single grade item only.
     *
     * @param array $data to be set
     * @return void
     */
    public function data_preprocessing(&$data)
    {
        if ($this->current->instance) {
            // editing an existing workshop - let us prepare the added editor elements (intro done automatically)
            $draftitemid = file_get_submitted_draft_itemid('instructauthors');
            $data['instructauthorseditor']['text'] = file_prepare_draft_area(
                $draftitemid,
                $this->context->id,
                'mod_workshop',
                'instructauthors',
                0,
                workshop::instruction_editors_options($this->context),
                $data['instructauthors']
            );
            $data['instructauthorseditor']['format'] = $data['instructauthorsformat'];
            $data['instructauthorseditor']['itemid'] = $draftitemid;

            $draftitemid = file_get_submitted_draft_itemid('instructreviewers');
            $data['instructreviewerseditor']['text'] = file_prepare_draft_area(
                $draftitemid,
                $this->context->id,
                'mod_workshop',
                'instructreviewers',
                0,
                workshop::instruction_editors_options($this->context),
                $data['instructreviewers']
            );
            $data['instructreviewerseditor']['format'] = $data['instructreviewersformat'];
            $data['instructreviewerseditor']['itemid'] = $draftitemid;

            $draftitemid = file_get_submitted_draft_itemid('conclusion');
            $data['conclusioneditor']['text'] = file_prepare_draft_area(
                $draftitemid,
                $this->context->id,
                'mod_workshop',
                'conclusion',
                0,
                workshop::instruction_editors_options($this->context),
                $data['conclusion']
            );
            $data['conclusioneditor']['format'] = $data['conclusionformat'];
            $data['conclusioneditor']['itemid'] = $draftitemid;
        } else {
            // adding a new workshop instance
            $draftitemid = file_get_submitted_draft_itemid('instructauthors');
            file_prepare_draft_area($draftitemid, null, 'mod_workshop', 'instructauthors', 0);    // no context yet, itemid not used
            $data['instructauthorseditor'] = array('text' => '', 'format' => editors_get_preferred_format(), 'itemid' => $draftitemid);

            $draftitemid = file_get_submitted_draft_itemid('instructreviewers');
            file_prepare_draft_area($draftitemid, null, 'mod_workshop', 'instructreviewers', 0);    // no context yet, itemid not used
            $data['instructreviewerseditor'] = array('text' => '', 'format' => editors_get_preferred_format(), 'itemid' => $draftitemid);

            $draftitemid = file_get_submitted_draft_itemid('conclusion');
            file_prepare_draft_area($draftitemid, null, 'mod_workshop', 'conclusion', 0);    // no context yet, itemid not used
            $data['conclusioneditor'] = array('text' => '', 'format' => editors_get_preferred_format(), 'itemid' => $draftitemid);
        }
    }

    /**
     * Set the grade item categories when editing an instance
     */
    public function definition_after_data()
    {

        /*         $mform = &$this->_form;

        if ($id = $mform->getElementValue('update')) {
            $instance   = $mform->getElementValue('instance');

            $gradeitems = grade_item::fetch_all(array(
                'itemtype'      => 'mod',
                'itemmodule'    => 'workshop',
                'iteminstance'  => $instance,
                'courseid'      => $this->course->id
            ));

            if (!empty($gradeitems)) {
                foreach ($gradeitems as $gradeitem) {
                    // here comes really crappy way how to set the value of the fields
                    // gradecategory and gradinggradecategory - grrr QuickForms
                    $decimalpoints = $gradeitem->get_decimals();
                    if ($gradeitem->itemnumber == 0) {
                        $submissiongradepass = $mform->getElement('submissiongradepass');
                        $submissiongradepass->setValue(format_float($gradeitem->gradepass, $decimalpoints));
                        $group = $mform->getElement('submissiongradegroup');
                        $elements = $group->getElements();
                        foreach ($elements as $element) {
                            if ($element->getName() == 'gradecategory') {
                                $element->setValue($gradeitem->categoryid);
                            }
                        }
                    } else if ($gradeitem->itemnumber == 1) {
                        $gradinggradepass = $mform->getElement('gradinggradepass');
                        $gradinggradepass->setValue(format_float($gradeitem->gradepass, $decimalpoints));
                        $group = $mform->getElement('gradinggradegroup');
                        $elements = $group->getElements();
                        foreach ($elements as $element) {
                            if ($element->getName() == 'gradinggradecategory') {
                                $element->setValue($gradeitem->categoryid);
                            }
                        }
                    }
                }
            }
        }

        parent::definition_after_data(); */
    }

    /**
     * Validates the form input
     *
     * @param array $data submitted data
     * @param array $files submitted files
     * @return array eventual errors indexed by the field name
     */
    public function validation($data, $files)
    {
        $errors = parent::validation($data, $files);

        // check the phases borders are valid
        if ($data['submissionstart'] > 0 and $data['submissionend'] > 0 and $data['submissionstart'] >= $data['submissionend']) {
            $errors['submissionend'] = get_string('submissionendbeforestart', 'mod_workshop');
        }
        if ($data['assessmentstart'] > 0 and $data['assessmentend'] > 0 and $data['assessmentstart'] >= $data['assessmentend']) {
            $errors['assessmentend'] = get_string('assessmentendbeforestart', 'mod_workshop');
        }

        // check the phases do not overlap
        if (max($data['submissionstart'], $data['submissionend']) > 0 and max($data['assessmentstart'], $data['assessmentend']) > 0) {
            $phasesubmissionend = max($data['submissionstart'], $data['submissionend']);
            $phaseassessmentstart = min($data['assessmentstart'], $data['assessmentend']);
            if ($phaseassessmentstart == 0) {
                $phaseassessmentstart = max($data['assessmentstart'], $data['assessmentend']);
            }
            if ($phasesubmissionend > 0 and $phaseassessmentstart > 0 and $phaseassessmentstart < $phasesubmissionend) {
                foreach (array('submissionend', 'submissionstart', 'assessmentstart', 'assessmentend') as $f) {
                    if ($data[$f] > 0) {
                        $errors[$f] = get_string('phasesoverlap', 'mod_workshop');
                        break;
                    }
                }
            }
        }

        /*         // Check that the submission grade pass is a valid number.
        if (!empty($data['submissiongradepass'])) {
            $submissiongradefloat = unformat_float($data['submissiongradepass'], true);
            if ($submissiongradefloat === false) {
                $errors['submissiongradepass'] = get_string('err_numeric', 'form');
            } else {
                if ($submissiongradefloat > $data['grade']) {
                    $errors['submissiongradepass'] = get_string('gradepassgreaterthangrade', 'grades', $data['grade']);
                }
            }
        }

        // Check that the grade pass is a valid number.
        if (!empty($data['gradinggradepass'])) {
            $gradepassfloat = unformat_float($data['gradinggradepass'], true);
            if ($gradepassfloat === false) {
                $errors['gradinggradepass'] = get_string('err_numeric', 'form');
            } else {
                if ($gradepassfloat > $data['gradinggrade']) {
                    $errors['gradinggradepass'] = get_string('gradepassgreaterthangrade', 'grades', $data['gradinggrade']);
                }
            }
        } */

        return $errors;
    }
}

$id_course = optional_param('id_curso', 0, PARAM_INT);
$id_actividad = optional_param('id_act', 0, PARAM_INT);
$url = new moodle_url('/local/backup_course/layout/workshop.php');
$url->param('update', $id_actividad);
$PAGE->set_url($url);

// Select the "Edit settings" from navigation.
navigation_node::override_active_url(new moodle_url('/local/backup_course/layout/workshop.php', array('id_act' => $id_actividad, 'return' => 1, 'id_curso' => $id_course)));

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

//#id_gradingsettings,
echo '<style>
        .navbar.navbar-fixed-top.moodle-has-zindex, .fixed-top.navbar.navbar-light, .action-menu.moodle-actionmenu.d-inline , 
        .desktop-first-column.block-region,#page-footer, #course-header, #id_general, #id_submissiontypes,
        #id_groupsubmissionsettings, #block-region-side-pre, #id_notifications, #id_modstandardgrade, #id_tagshdr, #id_competenciessection,#id_availabilityconditionsheader, 
        #id_activitycompletionheader, #id_submitbutton, #id_cancel, #id_feedbacksettings, #id_gradingsettings,
        #id_assessmentsettings, #id_examplesubmissionssettings, #fitem_id_maxbytes, #page-navbar, #theme_boost-drawers-courseindex, #theme_boost-drawers-blocks,
        #nav-drawer, .mt-5.mb-1.activity-navigation, #soporte-bar, #fgroup_id_submissiontypes, #fitem_fgroup_id_submissionfiletypes,
        #fitem_id_cmidnumber, #fitem_id_groupingid, #fitem_id_restrictgroupbutton, #fitem_id_groupmode, #fitem_id_instructauthorseditor,
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
