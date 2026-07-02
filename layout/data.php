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

class mod_data_mod_form_uvd extends moodleform_mod_uvd
{

    function definition()
    {
        global $CFG, $DB, $OUTPUT;

        $mform = &$this->_form;

        //-------------------------------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('name'), array('size' => '64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $this->standard_intro_elements(get_string('intro', 'data'));

        // ----------------------------------------------------------------------
        $mform->addElement('header', 'entrieshdr', get_string('entries', 'data'));

        $mform->addElement('selectyesno', 'approval', get_string('requireapproval', 'data'));
        $mform->addHelpButton('approval', 'requireapproval', 'data');

        $mform->addElement('selectyesno', 'manageapproved', get_string('manageapproved', 'data'));
        $mform->addHelpButton('manageapproved', 'manageapproved', 'data');
        $mform->setDefault('manageapproved', 1);
        $mform->disabledIf('manageapproved', 'approval', 'eq', 0);

        $mform->addElement('selectyesno', 'comments', get_string('allowcomments', 'data'));

        $countoptions = array(0 => get_string('none')) +
            (array_combine(
                range(1, DATA_MAX_ENTRIES), // Keys.
                range(1, DATA_MAX_ENTRIES)
            )); // Values.
        /*only show fields if there are legacy values from
         *before completionentries was added*/
        if (!empty($this->current->requiredentries)) {
            $group = array();
            $group[] = $mform->createElement(
                'select',
                'requiredentries',
                get_string('requiredentries', 'data'),
                $countoptions
            );
            $mform->addGroup($group, 'requiredentriesgroup', get_string('requiredentries', 'data'), array(''), false);
            $mform->addHelpButton('requiredentriesgroup', 'requiredentries', 'data');
            $mform->addElement('html', $OUTPUT->notification(get_string('requiredentrieswarning', 'data')));
        }

        $mform->addElement('select', 'requiredentriestoview', get_string('requiredentriestoview', 'data'), $countoptions);
        $mform->addHelpButton('requiredentriestoview', 'requiredentriestoview', 'data');

        $mform->addElement('select', 'maxentries', get_string('maxentries', 'data'), $countoptions);
        $mform->addHelpButton('maxentries', 'maxentries', 'data');

        // ----------------------------------------------------------------------
        $mform->addElement('header', 'availibilityhdr', get_string('availability'));

        $mform->addElement(
            'date_time_selector',
            'timeavailablefrom',
            get_string('availablefromdate', 'data'),
            array('optional' => true)
        );

        $mform->addElement(
            'date_time_selector',
            'timeavailableto',
            get_string('availabletodate', 'data'),
            array('optional' => true)
        );

        $mform->addElement(
            'date_time_selector',
            'timeviewfrom',
            get_string('viewfromdate', 'data'),
            array('optional' => true)
        );

        $mform->addElement(
            'date_time_selector',
            'timeviewto',
            get_string('viewtodate', 'data'),
            array('optional' => true)
        );

        // ----------------------------------------------------------------------
        if ($CFG->enablerssfeeds && $CFG->data_enablerssfeeds) {
            $mform->addElement('header', 'rsshdr', get_string('rss'));
            $mform->addElement('select', 'rssarticles', get_string('numberrssarticles', 'data'), $countoptions);
        }

        $this->standard_grading_coursemodule_elements();

        $this->standard_coursemodule_elements();

        //-------------------------------------------------------------------------------
        // buttons
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
        $errors = parent::validation($data, $files);

        // Check open and close times are consistent.
        if (
            $data['timeavailablefrom'] && $data['timeavailableto'] &&
            $data['timeavailableto'] < $data['timeavailablefrom']
        ) {
            $errors['timeavailableto'] = get_string('availabletodatevalidation', 'data');
        }
        if (
            $data['timeviewfrom'] && $data['timeviewto'] &&
            $data['timeviewto'] < $data['timeviewfrom']
        ) {
            $errors['timeviewto'] = get_string('viewtodatevalidation', 'data');
        }

        return $errors;
    }

    /**
     * Display module-specific activity completion rules.
     * Part of the API defined by moodleform_mod
     * @return array Array of string IDs of added items, empty array if none
     */
    public function add_completion_rules()
    {
        $mform = &$this->_form;
        $group = array();
        $group[] = $mform->createElement(
            'checkbox',
            'completionentriesenabled',
            '',
            get_string('completionentriescount', 'data')
        );
        $group[] = $mform->createElement(
            'text',
            'completionentries',
            get_string('completionentriescount', 'data'),
            array('size' => '1')
        );

        $mform->addGroup(
            $group,
            'completionentriesgroup',
            get_string('completionentries', 'data'),
            array(' '),
            false
        );
        $mform->disabledIf('completionentries', 'completionentriesenabled', 'notchecked');
        $mform->setDefault('completionentries', 1);
        $mform->setType('completionentries', PARAM_INT);
        /* This ensures the elements are disabled unless completion rules are enabled */
        return array('completionentriesgroup');
    }

    /**
     * Called during validation. Indicates if a module-specific completion rule is selected.
     *
     * @param array $data
     * @return bool True if one or more rules is enabled, false if none are.
     */
    public function completion_rule_enabled($data)
    {
        return ($data['completionentries'] != 0);
    }

    /**
     * Set up the completion checkbox which is not part of standard data.
     *
     * @param array $defaultvalues
     *
     */
    public function data_preprocessing(&$defaultvalues)
    {
        parent::data_preprocessing($defaultvalues);
        $defaultvalues['completionentriesenabled'] = !empty($defaultvalues['completionentries']) ? 1 : 0;
        if (empty($defaultvalues['completionentries'])) {
            $defaultvalues['completionentries'] = 1;
        }
    }

    /**
     * Allows modules to modify the data returned by form get_data().
     * This method is also called in the bulk activity completion form.
     *
     * Only available on moodleform_mod.
     *
     * @param stdClass $data the form data to be modified.
     */
    public function data_postprocessing($data)
    {
        parent::data_postprocessing($data);
        if (!empty($data->completionunlocked)) {
            $autocompletion = !empty($data->completion) && $data->completion == COMPLETION_TRACKING_AUTOMATIC;
            if (empty($data->completionentriesenabled) || !$autocompletion) {
                $data->completionentries = 0;
            }
        }
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
        #id_groupsubmissionsettings, #block-region-side-pre ,#page-footer, #id_notifications, #id_modstandardgrade, #id_tagshdr, #id_competenciessection,#id_availabilityconditionsheader, #id_activitycompletionheader,
        #id_entrieshdr, #fitem_id_rolewarning, #fitem_id_assessed, #fgroup_id_scale, #id_submitbutton, #id_cancel,
        #nav-drawer, .mt-5.mb-1.activity-navigation, #soporte-bar, #page-navbar, #theme_boost-drawers-courseindex, #theme_boost-drawers-blocks,
        #fitem_id_cmidnumber, #fitem_id_groupingid, #fitem_id_restrictgroupbutton, #id_modstandardratings, #fitem_id_groupmode,
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
