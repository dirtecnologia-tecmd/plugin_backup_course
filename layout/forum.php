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

use core_grades\component_gradeitems;

class mod_forum_mod_form_uvd extends moodleform_mod_uvd
{

    function definition()
    {
        global $CFG, $COURSE, $DB;

        $mform    = &$this->_form;

        //-------------------------------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('forumname', 'forum'), array('size' => '64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $this->standard_intro_elements(get_string('forumintro', 'forum'));

        $forumtypes = forum_get_forum_types();
        core_collator::asort($forumtypes, core_collator::SORT_STRING);
        $mform->addElement('select', 'type', get_string('forumtype', 'forum'), $forumtypes);
        $mform->addHelpButton('type', 'forumtype', 'forum');
        $mform->setDefault('type', 'general');

        $mform->addElement('header', 'availability', get_string('availability', 'forum'));

        $name = get_string('duedate', 'forum');
        $mform->addElement('date_time_selector', 'duedate', $name, array('optional' => true));
        $mform->addHelpButton('duedate', 'duedate', 'forum');

        $name = get_string('cutoffdate', 'forum');
        $mform->addElement('date_time_selector', 'cutoffdate', $name, array('optional' => true));
        $mform->addHelpButton('cutoffdate', 'cutoffdate', 'forum');

        // Attachments and word count.
        $mform->addElement('header', 'attachmentswordcounthdr', get_string('attachmentswordcount', 'forum'));

        $choices = get_max_upload_sizes($CFG->maxbytes, $COURSE->maxbytes, 0, $CFG->forum_maxbytes);
        $choices[1] = get_string('uploadnotallowed');
        $mform->addElement('select', 'maxbytes', get_string('maxattachmentsize', 'forum'), $choices);
        $mform->addHelpButton('maxbytes', 'maxattachmentsize', 'forum');
        $mform->setDefault('maxbytes', $CFG->forum_maxbytes);

        $choices = array(
            0 => 0,
            1 => 1,
            2 => 2,
            3 => 3,
            4 => 4,
            5 => 5,
            6 => 6,
            7 => 7,
            8 => 8,
            9 => 9,
            10 => 10,
            20 => 20,
            50 => 50,
            100 => 100
        );
        $mform->addElement('select', 'maxattachments', get_string('maxattachments', 'forum'), $choices);
        $mform->addHelpButton('maxattachments', 'maxattachments', 'forum');
        $mform->setDefault('maxattachments', $CFG->forum_maxattachments);

        $mform->addElement('selectyesno', 'displaywordcount', get_string('displaywordcount', 'forum'));
        $mform->addHelpButton('displaywordcount', 'displaywordcount', 'forum');
        $mform->setDefault('displaywordcount', 0);

        // Subscription and tracking.
        $mform->addElement('header', 'subscriptionandtrackinghdr', get_string('subscriptionandtracking', 'forum'));

        $options = forum_get_subscriptionmode_options();
        $mform->addElement('select', 'forcesubscribe', get_string('subscriptionmode', 'forum'), $options);
        $mform->addHelpButton('forcesubscribe', 'subscriptionmode', 'forum');
        if (isset($CFG->forum_subscription)) {
            $defaultforumsubscription = $CFG->forum_subscription;
        } else {
            $defaultforumsubscription = FORUM_CHOOSESUBSCRIBE;
        }
        $mform->setDefault('forcesubscribe', $defaultforumsubscription);

        $options = array();
        $options[FORUM_TRACKING_OPTIONAL] = get_string('trackingoptional', 'forum');
        $options[FORUM_TRACKING_OFF] = get_string('trackingoff', 'forum');
        if ($CFG->forum_allowforcedreadtracking) {
            $options[FORUM_TRACKING_FORCED] = get_string('trackingon', 'forum');
        }
        $mform->addElement('select', 'trackingtype', get_string('trackingtype', 'forum'), $options);
        $mform->addHelpButton('trackingtype', 'trackingtype', 'forum');
        $default = $CFG->forum_trackingtype;
        if ((!$CFG->forum_allowforcedreadtracking) && ($default == FORUM_TRACKING_FORCED)) {
            $default = FORUM_TRACKING_OPTIONAL;
        }
        $mform->setDefault('trackingtype', $default);

        if ($CFG->enablerssfeeds && isset($CFG->forum_enablerssfeeds) && $CFG->forum_enablerssfeeds) {
            //-------------------------------------------------------------------------------
            $mform->addElement('header', 'rssheader', get_string('rss'));
            $choices = array();
            $choices[0] = get_string('none');
            $choices[1] = get_string('discussions', 'forum');
            $choices[2] = get_string('posts', 'forum');
            $mform->addElement('select', 'rsstype', get_string('rsstype'), $choices);
            $mform->addHelpButton('rsstype', 'rsstype', 'forum');
            if (isset($CFG->forum_rsstype)) {
                $mform->setDefault('rsstype', $CFG->forum_rsstype);
            }

            $choices = array();
            $choices[0] = '0';
            $choices[1] = '1';
            $choices[2] = '2';
            $choices[3] = '3';
            $choices[4] = '4';
            $choices[5] = '5';
            $choices[10] = '10';
            $choices[15] = '15';
            $choices[20] = '20';
            $choices[25] = '25';
            $choices[30] = '30';
            $choices[40] = '40';
            $choices[50] = '50';
            $mform->addElement('select', 'rssarticles', get_string('rssarticles'), $choices);
            $mform->addHelpButton('rssarticles', 'rssarticles', 'forum');
            $mform->disabledIf('rssarticles', 'rsstype', 'eq', '0');
            if (isset($CFG->forum_rssarticles)) {
                $mform->setDefault('rssarticles', $CFG->forum_rssarticles);
            }
        }

        $mform->addElement('header', 'discussionlocking', get_string('discussionlockingheader', 'forum'));
        $options = [
            0               => get_string('discussionlockingdisabled', 'forum'),
            1   * DAYSECS   => get_string('numday', 'core', 1),
            1   * WEEKSECS  => get_string('numweek', 'core', 1),
            2   * WEEKSECS  => get_string('numweeks', 'core', 2),
            30  * DAYSECS   => get_string('nummonth', 'core', 1),
            60  * DAYSECS   => get_string('nummonths', 'core', 2),
            90  * DAYSECS   => get_string('nummonths', 'core', 3),
            180 * DAYSECS   => get_string('nummonths', 'core', 6),
            1   * YEARSECS  => get_string('numyear', 'core', 1),
        ];
        $mform->addElement('select', 'lockdiscussionafter', get_string('lockdiscussionafter', 'forum'), $options);
        $mform->addHelpButton('lockdiscussionafter', 'lockdiscussionafter', 'forum');
        $mform->disabledIf('lockdiscussionafter', 'type', 'eq', 'single');

        //-------------------------------------------------------------------------------
        $mform->addElement('header', 'blockafterheader', get_string('blockafter', 'forum'));
        $options = array();
        $options[0] = get_string('blockperioddisabled', 'forum');
        $options[60 * 60 * 24]   = '1 ' . get_string('day');
        $options[60 * 60 * 24 * 2] = '2 ' . get_string('days');
        $options[60 * 60 * 24 * 3] = '3 ' . get_string('days');
        $options[60 * 60 * 24 * 4] = '4 ' . get_string('days');
        $options[60 * 60 * 24 * 5] = '5 ' . get_string('days');
        $options[60 * 60 * 24 * 6] = '6 ' . get_string('days');
        $options[60 * 60 * 24 * 7] = '1 ' . get_string('week');
        $mform->addElement('select', 'blockperiod', get_string('blockperiod', 'forum'), $options);
        $mform->addHelpButton('blockperiod', 'blockperiod', 'forum');

        $mform->addElement('text', 'blockafter', get_string('blockafter', 'forum'));
        $mform->setType('blockafter', PARAM_INT);
        $mform->setDefault('blockafter', '0');
        $mform->addRule('blockafter', null, 'numeric', null, 'client');
        $mform->addHelpButton('blockafter', 'blockafter', 'forum');
        $mform->disabledIf('blockafter', 'blockperiod', 'eq', 0);

        $mform->addElement('text', 'warnafter', get_string('warnafter', 'forum'));
        $mform->setType('warnafter', PARAM_INT);
        $mform->setDefault('warnafter', '0');
        $mform->addRule('warnafter', null, 'numeric', null, 'client');
        $mform->addHelpButton('warnafter', 'warnafter', 'forum');
        $mform->disabledIf('warnafter', 'blockperiod', 'eq', 0);

        $coursecontext = context_course::instance($COURSE->id);
        plagiarism_get_form_elements_module($mform, $coursecontext, 'mod_forum');

        //-------------------------------------------------------------------------------
        // Add the whole forum grading options.
        //$this->add_forum_grade_settings($mform, 'forum');

        $this->standard_coursemodule_elements();

        //$this->standard_grading_coursemodule_elements();

        //$this->standard_coursemodule_elements();
        //-------------------------------------------------------------------------------
        // buttons
        $this->add_action_buttons();
    }

    /**
     * Add the whole forum grade settings to the mform.
     *
     * @param   \mform $mform
     * @param   string $itemname
     */
    public function add_forum_grade_settings($mform, string $itemname)
    {
        global $COURSE;

        $component = "mod_{$this->_modname}";
        $defaultgradingvalue = 0;

        $itemnumber = component_gradeitems::get_itemnumber_from_itemname($component, $itemname);
        $gradefieldname = component_gradeitems::get_field_name_for_itemnumber($component, $itemnumber, 'grade');
        $gradecatfieldname = component_gradeitems::get_field_name_for_itemnumber($component, $itemnumber, 'gradecat');
        $gradepassfieldname = component_gradeitems::get_field_name_for_itemnumber($component, $itemnumber, 'gradepass');
        $sendstudentnotificationsfieldname = component_gradeitems::get_field_name_for_itemnumber(
            $component,
            $itemnumber,
            'sendstudentnotifications'
        );

        // The advancedgradingmethod is different in that it is suffixed with an area name... which is not the
        // itemnumber.
        $methodfieldname = "advancedgradingmethod_{$itemname}";

        $headername = "{$gradefieldname}_header";
        $mform->addElement('header', $headername, get_string("grade_{$itemname}_header", $component));

        $isupdate = !empty($this->_cm);
        $gradeoptions = [
            'isupdate' => $isupdate,
            'currentgrade' => false,
            'hasgrades' => false,
            'canrescale' => false,
            'useratings' => false,
        ];

        if ($isupdate) {
            $gradeitem = grade_item::fetch([
                'itemtype' => 'mod',
                'itemmodule' => $this->_cm->modname,
                'iteminstance' => $this->_cm->instance,
                'itemnumber' => $itemnumber,
                'courseid' => $COURSE->id,
            ]);
            if ($gradeitem) {
                $gradeoptions['currentgrade'] = $gradeitem->grademax;
                $gradeoptions['currentgradetype'] = $gradeitem->gradetype;
                $gradeoptions['currentscaleid'] = $gradeitem->scaleid;
                $gradeoptions['hasgrades'] = $gradeitem->has_grades();
            }
        }
        $mform->addElement(
            'modgrade',
            $gradefieldname,
            get_string("{$gradefieldname}_title", $component),
            $gradeoptions
        );
        $mform->addHelpButton($gradefieldname, 'modgrade', 'grades');
        $mform->setDefault($gradefieldname, $defaultgradingvalue);

        if (!empty($this->current->_advancedgradingdata['methods']) && !empty($this->current->_advancedgradingdata['areas'])) {
            $areadata = $this->current->_advancedgradingdata['areas'][$itemname];
            $mform->addElement(
                'select',
                $methodfieldname,
                get_string('gradingmethod', 'core_grading'),
                $this->current->_advancedgradingdata['methods']
            );
            $mform->addHelpButton($methodfieldname, 'gradingmethod', 'core_grading');
            $mform->hideIf($methodfieldname, "{$gradefieldname}[modgrade_type]", 'eq', 'none');
        }

        // Grade category.
        $mform->addElement(
            'select',
            $gradecatfieldname,
            get_string('gradecategoryonmodform', 'grades'),
            grade_get_categories_menu($COURSE->id, $this->_outcomesused)
        );
        $mform->addHelpButton($gradecatfieldname, 'gradecategoryonmodform', 'grades');
        $mform->hideIf($gradecatfieldname, "{$gradefieldname}[modgrade_type]", 'eq', 'none');

        // Grade to pass.
        $mform->addElement('text', $gradepassfieldname, get_string('gradepass', 'grades'));
        $mform->addHelpButton($gradepassfieldname, 'gradepass', 'grades');
        $mform->setDefault($gradepassfieldname, '');
        $mform->setType($gradepassfieldname, PARAM_RAW);
        $mform->hideIf($gradepassfieldname, "{$gradefieldname}[modgrade_type]", 'eq', 'none');

        $mform->addElement(
            'selectyesno',
            $sendstudentnotificationsfieldname,
            get_string('sendstudentnotificationsdefault', 'forum')
        );
        $mform->addHelpButton($sendstudentnotificationsfieldname, 'sendstudentnotificationsdefault', 'forum');
        $mform->hideIf($sendstudentnotificationsfieldname, "{$gradefieldname}[modgrade_type]", 'eq', 'none');
    }

    function definition_after_data()
    {
        parent::definition_after_data();
        $mform     = &$this->_form;
        $type      = &$mform->getElement('type');
        $typevalue = $mform->getElementValue('type');

        //we don't want to have these appear as possible selections in the form but
        //we want the form to display them if they are set.
        if ($typevalue[0] == 'news') {
            $type->addOption(get_string('namenews', 'forum'), 'news');
            $mform->addHelpButton('type', 'namenews', 'forum');
            $type->freeze();
            $type->setPersistantFreeze(true);
        }
        if ($typevalue[0] == 'social') {
            $type->addOption(get_string('namesocial', 'forum'), 'social');
            $type->freeze();
            $type->setPersistantFreeze(true);
        }
    }

    function data_preprocessing(&$default_values)
    {
        parent::data_preprocessing($default_values);

        // Set up the completion checkboxes which aren't part of standard data.
        // We also make the default value (if you turn on the checkbox) for those
        // numbers to be 1, this will not apply unless checkbox is ticked.
        $default_values['completiondiscussionsenabled'] =
            !empty($default_values['completiondiscussions']) ? 1 : 0;
        if (empty($default_values['completiondiscussions'])) {
            $default_values['completiondiscussions'] = 1;
        }
        $default_values['completionrepliesenabled'] =
            !empty($default_values['completionreplies']) ? 1 : 0;
        if (empty($default_values['completionreplies'])) {
            $default_values['completionreplies'] = 1;
        }
        // Tick by default if Add mode or if completion posts settings is set to 1 or more.
        if (empty($this->_instance) || !empty($default_values['completionposts'])) {
            $default_values['completionpostsenabled'] = 1;
        } else {
            $default_values['completionpostsenabled'] = 0;
        }
        if (empty($default_values['completionposts'])) {
            $default_values['completionposts'] = 1;
        }
    }

    /**
     * Add custom completion rules.
     *
     * @return array Array of string IDs of added items, empty array if none
     */
    public function add_completion_rules()
    {
        $mform = &$this->_form;

        $group = array();
        $group[] = &$mform->createElement('checkbox', 'completionpostsenabled', '', get_string('completionposts', 'forum'));
        $group[] = &$mform->createElement('text', 'completionposts', '', array('size' => 3));
        $mform->setType('completionposts', PARAM_INT);
        $mform->addGroup($group, 'completionpostsgroup', get_string('completionpostsgroup', 'forum'), array(' '), false);
        $mform->disabledIf('completionposts', 'completionpostsenabled', 'notchecked');

        $group = array();
        $group[] = &$mform->createElement('checkbox', 'completiondiscussionsenabled', '', get_string('completiondiscussions', 'forum'));
        $group[] = &$mform->createElement('text', 'completiondiscussions', '', array('size' => 3));
        $mform->setType('completiondiscussions', PARAM_INT);
        $mform->addGroup($group, 'completiondiscussionsgroup', get_string('completiondiscussionsgroup', 'forum'), array(' '), false);
        $mform->disabledIf('completiondiscussions', 'completiondiscussionsenabled', 'notchecked');

        $group = array();
        $group[] = &$mform->createElement('checkbox', 'completionrepliesenabled', '', get_string('completionreplies', 'forum'));
        $group[] = &$mform->createElement('text', 'completionreplies', '', array('size' => 3));
        $mform->setType('completionreplies', PARAM_INT);
        $mform->addGroup($group, 'completionrepliesgroup', get_string('completionrepliesgroup', 'forum'), array(' '), false);
        $mform->disabledIf('completionreplies', 'completionrepliesenabled', 'notchecked');

        return array('completiondiscussionsgroup', 'completionrepliesgroup', 'completionpostsgroup');
    }

    function completion_rule_enabled($data)
    {
        return (!empty($data['completiondiscussionsenabled']) && $data['completiondiscussions'] != 0) ||
            (!empty($data['completionrepliesenabled']) && $data['completionreplies'] != 0) ||
            (!empty($data['completionpostsenabled']) && $data['completionposts'] != 0);
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
        // Turn off completion settings if the checkboxes aren't ticked
        if (!empty($data->completionunlocked)) {
            $autocompletion = !empty($data->completion) && $data->completion == COMPLETION_TRACKING_AUTOMATIC;
            if (empty($data->completiondiscussionsenabled) || !$autocompletion) {
                $data->completiondiscussions = 0;
            }
            if (empty($data->completionrepliesenabled) || !$autocompletion) {
                $data->completionreplies = 0;
            }
            if (empty($data->completionpostsenabled) || !$autocompletion) {
                $data->completionposts = 0;
            }
        }
    }
}




$id_course = optional_param('id_curso', 0, PARAM_INT);
$id_actividad = optional_param('id_act', 0, PARAM_INT);
$url = new moodle_url('/local/backup_course/layout/forum.php');
$url->param('update', $id_actividad);
$PAGE->set_url($url);

// Select the "Edit settings" from navigation.
navigation_node::override_active_url(new moodle_url('/local/backup_course/layout/forum.php', array('id_act' => $id_actividad, 'return' => 1, 'id_curso' => $id_course)));

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
        #id_groupsubmissionsettings, #block-region-side-pre ,#page-footer, #id_notifications, #id_modstandardgrade, #id_tagshdr, #id_competenciessection, #id_availabilityconditionsheader, 
        #id_activitycompletionheader, #id_feedbackhdr, #id_aftersubmithdr, #id_submitbutton, #id_cancel,
        #nav-drawer, .mt-5.mb-1.activity-navigation, #soporte-bar, #page-navbar, #theme_boost-drawers-courseindex, #theme_boost-drawers-blocks,
        #fitem_id_cmidnumber, #fitem_id_groupingid, #fitem_id_restrictgroupbutton, 
        #fitem_id_displaywordcount, #id_subscriptionandtrackinghdr, #fitem_id_groupmode, #page-navbar,
        #id_modstandardratings,
        #fitem_id_assessed, #fgroup_id_scale, #id_discussionlocking, #id_blockafterheader,
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
