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
require_once($CFG->dirroot . '/mod/lesson/locallib.php');

class mod_lesson_mod_form_uvd extends moodleform_mod_uvd
{

    protected $course = null;

    public function __construct($current, $section, $cm, $course)
    {
        $this->course = $course;
        parent::__construct($current, $section, $cm, $course);
    }

    /**
     * Old syntax of class constructor. Deprecated in PHP7.
     *
     * @deprecated since Moodle 3.1
     */
    public function mod_lesson_mod_form($current, $section, $cm, $course)
    {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($current, $section, $cm, $course);
    }

    function definition()
    {
        global $CFG, $COURSE, $DB, $OUTPUT;

        $mform    = $this->_form;

        $lessonconfig = get_config('mod_lesson');

        $mform->addElement('header', 'general', get_string('general', 'form'));

        /** Legacy slideshow width element to maintain backwards compatibility */
        $mform->addElement('hidden', 'width');
        $mform->setType('width', PARAM_INT);
        $mform->setDefault('width', $lessonconfig->slideshowwidth);

        /** Legacy slideshow height element to maintain backwards compatibility */
        $mform->addElement('hidden', 'height');
        $mform->setType('height', PARAM_INT);
        $mform->setDefault('height', $lessonconfig->slideshowheight);

        /** Legacy slideshow background color element to maintain backwards compatibility */
        $mform->addElement('hidden', 'bgcolor');
        $mform->setType('bgcolor', PARAM_TEXT);
        $mform->setDefault('bgcolor', $lessonconfig->slideshowbgcolor);

        /** Legacy media popup width element to maintain backwards compatibility */
        $mform->addElement('hidden', 'mediawidth');
        $mform->setType('mediawidth', PARAM_INT);
        $mform->setDefault('mediawidth', $lessonconfig->mediawidth);

        /** Legacy media popup height element to maintain backwards compatibility */
        $mform->addElement('hidden', 'mediaheight');
        $mform->setType('mediaheight', PARAM_INT);
        $mform->setDefault('mediaheight', $lessonconfig->mediaheight);

        /** Legacy media popup close button element to maintain backwards compatibility */
        $mform->addElement('hidden', 'mediaclose');
        $mform->setType('mediaclose', PARAM_BOOL);
        $mform->setDefault('mediaclose', $lessonconfig->mediaclose);

        $mform->addElement('text', 'name', get_string('name'), array('size' => '64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $this->standard_intro_elements();

        // Appearance.
        $mform->addElement('header', 'appearancehdr', get_string('appearance'));

        $filemanageroptions = array();
        $filemanageroptions['filetypes'] = '*';
        $filemanageroptions['maxbytes'] = $this->course->maxbytes;
        $filemanageroptions['subdirs'] = 0;
        $filemanageroptions['maxfiles'] = 1;

        $mform->addElement('filemanager', 'mediafile', get_string('mediafile', 'lesson'), null, $filemanageroptions);
        $mform->addHelpButton('mediafile', 'mediafile', 'lesson');
        $mform->setAdvanced('mediafile', $lessonconfig->mediafile_adv);

        $mform->addElement('selectyesno', 'progressbar', get_string('progressbar', 'lesson'));
        $mform->addHelpButton('progressbar', 'progressbar', 'lesson');
        $mform->setDefault('progressbar', $lessonconfig->progressbar);
        $mform->setAdvanced('progressbar', $lessonconfig->progressbar_adv);

        $mform->addElement('selectyesno', 'ongoing', get_string('ongoing', 'lesson'));
        $mform->addHelpButton('ongoing', 'ongoing', 'lesson');
        $mform->setDefault('ongoing', $lessonconfig->ongoing);
        $mform->setAdvanced('ongoing', $lessonconfig->ongoing_adv);

        $mform->addElement('selectyesno', 'displayleft', get_string('displayleftmenu', 'lesson'));
        $mform->addHelpButton('displayleft', 'displayleftmenu', 'lesson');
        $mform->setDefault('displayleft', $lessonconfig->displayleftmenu);
        $mform->setAdvanced('displayleft', $lessonconfig->displayleftmenu_adv);

        $options = array();
        for ($i = 100; $i >= 0; $i--) {
            $options[$i] = $i . '%';
        }
        $mform->addElement('select', 'displayleftif', get_string('displayleftif', 'lesson'), $options);
        $mform->addHelpButton('displayleftif', 'displayleftif', 'lesson');
        $mform->setDefault('displayleftif', $lessonconfig->displayleftif);
        $mform->setAdvanced('displayleftif', $lessonconfig->displayleftif_adv);

        $mform->addElement('selectyesno', 'slideshow', get_string('slideshow', 'lesson'));
        $mform->addHelpButton('slideshow', 'slideshow', 'lesson');
        $mform->setDefault('slideshow', $lessonconfig->slideshow);
        $mform->setAdvanced('slideshow', $lessonconfig->slideshow_adv);

        $numbers = array();
        for ($i = 20; $i > 1; $i--) {
            $numbers[$i] = $i;
        }

        $mform->addElement('select', 'maxanswers', get_string('maximumnumberofanswersbranches', 'lesson'), $numbers);
        $mform->setDefault('maxanswers', $lessonconfig->maxanswers);
        $mform->setAdvanced('maxanswers', $lessonconfig->maxanswers_adv);
        $mform->setType('maxanswers', PARAM_INT);
        $mform->addHelpButton('maxanswers', 'maximumnumberofanswersbranches', 'lesson');

        $mform->addElement('selectyesno', 'feedback', get_string('displaydefaultfeedback', 'lesson'));
        $mform->addHelpButton('feedback', 'displaydefaultfeedback', 'lesson');
        $mform->setDefault('feedback', $lessonconfig->defaultfeedback);
        $mform->setAdvanced('feedback', $lessonconfig->defaultfeedback_adv);

        // Get the modules.
        if ($mods = get_course_mods($COURSE->id)) {
            $modinstances = array();
            foreach ($mods as $mod) {
                // Get the module name and then store it in a new array.
                if ($module = get_coursemodule_from_instance($mod->modname, $mod->instance, $COURSE->id)) {
                    // Exclude this lesson, if it's already been saved.
                    if (!isset($this->_cm->id) || $this->_cm->id != $mod->id) {
                        $modinstances[$mod->id] = $mod->modname . ' - ' . $module->name;
                    }
                }
            }
            asort($modinstances); // Sort by module name.
            $modinstances = array(0 => get_string('none')) + $modinstances;

            $mform->addElement('select', 'activitylink', get_string('activitylink', 'lesson'), $modinstances);
            $mform->addHelpButton('activitylink', 'activitylink', 'lesson');
            $mform->setDefault('activitylink', 0);
            $mform->setAdvanced('activitylink', $lessonconfig->activitylink_adv);
        }

        // Availability.
        $mform->addElement('header', 'availabilityhdr', get_string('availability'));

        $mform->addElement('date_time_selector', 'available', get_string('available', 'lesson'), array('optional' => true));
        $mform->setDefault('available', 0);

        $mform->addElement('date_time_selector', 'deadline', get_string('deadline', 'lesson'), array('optional' => true));
        $mform->setDefault('deadline', 0);

        // Time limit.
        $mform->addElement(
            'duration',
            'timelimit',
            get_string('timelimit', 'lesson'),
            array('optional' => true)
        );
        $mform->addHelpButton('timelimit', 'timelimit', 'lesson');
        $mform->setAdvanced('timelimit', $lessonconfig->timelimit_adv);
        $mform->setDefault('timelimit', $lessonconfig->timelimit);

        $mform->addElement('selectyesno', 'usepassword', get_string('usepassword', 'lesson'));
        $mform->addHelpButton('usepassword', 'usepassword', 'lesson');
        $mform->setDefault('usepassword', $lessonconfig->password);
        $mform->setAdvanced('usepassword', $lessonconfig->password_adv);

        $mform->addElement('passwordunmask', 'password', get_string('password', 'lesson'));
        $mform->setDefault('password', '');
        $mform->setAdvanced('password', $lessonconfig->password_adv);
        $mform->setType('password', PARAM_RAW);
        $mform->disabledIf('password', 'usepassword', 'eq', 0);
        $mform->disabledIf('passwordunmask', 'usepassword', 'eq', 0);

        // Dependent on.
        if ($this->current && isset($this->current->dependency) && $this->current->dependency) {
            $mform->addElement('header', 'dependencyon', get_string('prerequisitelesson', 'lesson'));
            $mform->addElement(
                'static',
                'warningobsolete',
                get_string('warning', 'lesson'),
                get_string('prerequisiteisobsolete', 'lesson')
            );
            $options = array(0 => get_string('none'));
            if ($lessons = get_all_instances_in_course('lesson', $COURSE)) {
                foreach ($lessons as $lesson) {
                    if ($lesson->id != $this->_instance) {
                        $options[$lesson->id] = format_string($lesson->name, true);
                    }
                }
            }
            $mform->addElement('select', 'dependency', get_string('dependencyon', 'lesson'), $options);
            $mform->addHelpButton('dependency', 'dependencyon', 'lesson');
            $mform->setDefault('dependency', 0);

            $mform->addElement('text', 'timespent', get_string('timespentminutes', 'lesson'));
            $mform->setDefault('timespent', 0);
            $mform->setType('timespent', PARAM_INT);
            $mform->disabledIf('timespent', 'dependency', 'eq', 0);

            $mform->addElement('checkbox', 'completed', get_string('completed', 'lesson'));
            $mform->setDefault('completed', 0);
            $mform->disabledIf('completed', 'dependency', 'eq', 0);

            $mform->addElement('text', 'gradebetterthan', get_string('gradebetterthan', 'lesson'));
            $mform->setDefault('gradebetterthan', 0);
            $mform->setType('gradebetterthan', PARAM_INT);
            $mform->disabledIf('gradebetterthan', 'dependency', 'eq', 0);
        } else {
            $mform->addElement('hidden', 'dependency', 0);
            $mform->setType('dependency', PARAM_INT);
            $mform->addElement('hidden', 'timespent', 0);
            $mform->setType('timespent', PARAM_INT);
            $mform->addElement('hidden', 'completed', 0);
            $mform->setType('completed', PARAM_INT);
            $mform->addElement('hidden', 'gradebetterthan', 0);
            $mform->setType('gradebetterthan', PARAM_INT);
            $mform->setConstants(array(
                'dependency' => 0, 'timespent' => 0,
                'completed' => 0, 'gradebetterthan' => 0
            ));
        }

        // Allow to enable offline lessons only if the Mobile services are enabled.
        if ($CFG->enablemobilewebservice) {
            $mform->addElement('selectyesno', 'allowofflineattempts', get_string('allowofflineattempts', 'lesson'));
            $mform->addHelpButton('allowofflineattempts', 'allowofflineattempts', 'lesson');
            $mform->setDefault('allowofflineattempts', 0);
            $mform->setAdvanced('allowofflineattempts');
            $mform->disabledIf('allowofflineattempts', 'timelimit[number]', 'neq', 0);

            $mform->addElement(
                'static',
                'allowofflineattemptswarning',
                '',
                $OUTPUT->notification(get_string('allowofflineattempts_help', 'lesson'), 'warning')
            );
            $mform->setAdvanced('allowofflineattemptswarning');
        } else {
            $mform->addElement('hidden', 'allowofflineattempts', 0);
            $mform->setType('allowofflineattempts', PARAM_INT);
        }

        // Flow control.
        $mform->addElement('header', 'flowcontrol', get_string('flowcontrol', 'lesson'));

        $mform->addElement('selectyesno', 'modattempts', get_string('modattempts', 'lesson'));
        $mform->addHelpButton('modattempts', 'modattempts', 'lesson');
        $mform->setDefault('modattempts', $lessonconfig->modattempts);
        $mform->setAdvanced('modattempts', $lessonconfig->modattempts_adv);

        $mform->addElement('selectyesno', 'review', get_string('displayreview', 'lesson'));
        $mform->addHelpButton('review', 'displayreview', 'lesson');
        $mform->setDefault('review', $lessonconfig->displayreview);
        $mform->setAdvanced('review', $lessonconfig->displayreview_adv);

        $numbers = array();
        for ($i = 10; $i > 0; $i--) {
            $numbers[$i] = $i;
        }
        $mform->addElement('select', 'maxattempts', get_string('maximumnumberofattempts', 'lesson'), $numbers);
        $mform->addHelpButton('maxattempts', 'maximumnumberofattempts', 'lesson');
        $mform->setDefault('maxattempts', $lessonconfig->maximumnumberofattempts);
        $mform->setAdvanced('maxattempts', $lessonconfig->maximumnumberofattempts_adv);

        $defaultnextpages = array();
        $defaultnextpages[0] = get_string('normal', 'lesson');
        $defaultnextpages[LESSON_UNSEENPAGE] = get_string('showanunseenpage', 'lesson');
        $defaultnextpages[LESSON_UNANSWEREDPAGE] = get_string('showanunansweredpage', 'lesson');
        $mform->addElement('select', 'nextpagedefault', get_string('actionaftercorrectanswer', 'lesson'), $defaultnextpages);
        $mform->addHelpButton('nextpagedefault', 'actionaftercorrectanswer', 'lesson');
        $mform->setDefault('nextpagedefault', $lessonconfig->defaultnextpage);
        $mform->setAdvanced('nextpagedefault', $lessonconfig->defaultnextpage_adv);

        $numbers = array();
        for ($i = 100; $i >= 0; $i--) {
            $numbers[$i] = $i;
        }
        $mform->addElement('select', 'maxpages', get_string('numberofpagestoshow', 'lesson'), $numbers);
        $mform->addHelpButton('maxpages', 'numberofpagestoshow', 'lesson');
        $mform->setDefault('maxpages', $lessonconfig->numberofpagestoshow);
        $mform->setAdvanced('maxpages', $lessonconfig->numberofpagestoshow_adv);

        // Grade.
        $this->standard_grading_coursemodule_elements();

        // No header here, so that the following settings are displayed in the grade section.

        $mform->addElement('selectyesno', 'practice', get_string('practice', 'lesson'));
        $mform->addHelpButton('practice', 'practice', 'lesson');
        $mform->setDefault('practice', $lessonconfig->practice);
        $mform->setAdvanced('practice', $lessonconfig->practice_adv);

        $mform->addElement('selectyesno', 'custom', get_string('customscoring', 'lesson'));
        $mform->addHelpButton('custom', 'customscoring', 'lesson');
        $mform->setDefault('custom', $lessonconfig->customscoring);
        $mform->setAdvanced('custom', $lessonconfig->customscoring_adv);

        $mform->addElement('selectyesno', 'retake', get_string('retakesallowed', 'lesson'));
        $mform->addHelpButton('retake', 'retakesallowed', 'lesson');
        $mform->setDefault('retake', $lessonconfig->retakesallowed);
        $mform->setAdvanced('retake', $lessonconfig->retakesallowed_adv);

        $options = array();
        $options[0] = get_string('usemean', 'lesson');
        $options[1] = get_string('usemaximum', 'lesson');
        $mform->addElement('select', 'usemaxgrade', get_string('handlingofretakes', 'lesson'), $options);
        $mform->addHelpButton('usemaxgrade', 'handlingofretakes', 'lesson');
        $mform->setDefault('usemaxgrade', $lessonconfig->handlingofretakes);
        $mform->setAdvanced('usemaxgrade', $lessonconfig->handlingofretakes_adv);
        $mform->disabledIf('usemaxgrade', 'retake', 'eq', '0');

        $numbers = array();
        for ($i = 100; $i >= 0; $i--) {
            $numbers[$i] = $i;
        }
        $mform->addElement('select', 'minquestions', get_string('minimumnumberofquestions', 'lesson'), $numbers);
        $mform->addHelpButton('minquestions', 'minimumnumberofquestions', 'lesson');
        $mform->setDefault('minquestions', $lessonconfig->minimumnumberofquestions);
        $mform->setAdvanced('minquestions', $lessonconfig->minimumnumberofquestions_adv);

        //-------------------------------------------------------------------------------
        $this->standard_coursemodule_elements();
        //-------------------------------------------------------------------------------
        // buttons
        $this->add_action_buttons();
    }

    /**
     * Enforce defaults here
     *
     * @param array $defaultvalues Form defaults
     * @return void
     **/
    public function data_preprocessing(&$defaultvalues)
    {
        if (isset($defaultvalues['conditions'])) {
            $conditions = unserialize($defaultvalues['conditions']);
            $defaultvalues['timespent'] = $conditions->timespent;
            $defaultvalues['completed'] = $conditions->completed;
            $defaultvalues['gradebetterthan'] = $conditions->gradebetterthan;
        }

        // Set up the completion checkbox which is not part of standard data.
        $defaultvalues['completiontimespentenabled'] =
            !empty($defaultvalues['completiontimespent']) ? 1 : 0;

        if ($this->current->instance) {
            // Editing existing instance - copy existing files into draft area.
            $draftitemid = file_get_submitted_draft_itemid('mediafile');
            file_prepare_draft_area($draftitemid, $this->context->id, 'mod_lesson', 'mediafile', 0, array('subdirs' => 0, 'maxbytes' => $this->course->maxbytes, 'maxfiles' => 1));
            $defaultvalues['mediafile'] = $draftitemid;
        }
    }

    /**
     * Enforce validation rules here
     *
     * @param object $data Post data to validate
     * @return array
     **/
    function validation($data, $files)
    {
        $errors = parent::validation($data, $files);

        // Check open and close times are consistent.
        if (
            $data['available'] != 0 && $data['deadline'] != 0 &&
            $data['deadline'] < $data['available']
        ) {
            $errors['deadline'] = get_string('closebeforeopen', 'lesson');
        }

        if (!empty($data['usepassword']) && empty($data['password'])) {
            $errors['password'] = get_string('emptypassword', 'lesson');
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
        $mform = $this->_form;

        $mform->addElement(
            'checkbox',
            'completionendreached',
            get_string('completionendreached', 'lesson'),
            get_string('completionendreached_desc', 'lesson')
        );
        // Enable this completion rule by default.
        $mform->setDefault('completionendreached', 1);

        $group = array();
        $group[] = &$mform->createElement(
            'checkbox',
            'completiontimespentenabled',
            '',
            get_string('completiontimespent', 'lesson')
        );
        $group[] = &$mform->createElement('duration', 'completiontimespent', '', array('optional' => false));
        $mform->addGroup($group, 'completiontimespentgroup', get_string('completiontimespentgroup', 'lesson'), array(' '), false);
        $mform->disabledIf('completiontimespent[number]', 'completiontimespentenabled', 'notchecked');
        $mform->disabledIf('completiontimespent[timeunit]', 'completiontimespentenabled', 'notchecked');

        return array('completionendreached', 'completiontimespentgroup');
    }

    /**
     * Called during validation. Indicates whether a module-specific completion rule is selected.
     *
     * @param array $data Input data (not yet validated)
     * @return bool True if one or more rules is enabled, false if none are.
     */
    public function completion_rule_enabled($data)
    {
        return !empty($data['completionendreached']) || $data['completiontimespent'] > 0;
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
        // Turn off completion setting if the checkbox is not ticked.
        if (!empty($data->completionunlocked)) {
            $autocompletion = !empty($data->completion) && $data->completion == COMPLETION_TRACKING_AUTOMATIC;
            if (empty($data->completiontimespentenabled) || !$autocompletion) {
                $data->completiontimespent = 0;
            }
            if (empty($data->completionendreached) || !$autocompletion) {
                $data->completionendreached = 0;
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
        #id_groupsubmissionsettings, #block-region-side-pre ,#page-footer, #id_notifications, #id_modstandardgrade, #id_tagshdr, #id_competenciessection,#id_availabilityconditionsheader, 
        #id_activitycompletionheader, #id_submitbutton, #id_cancel, #fitem_id_restrictgroupbutton, #id_flowcontrol,
        #nav-drawer, .mt-5.mb-1.activity-navigation, #soporte-bar, #fitem_id_groupmode, #page-navbar, #theme_boost-drawers-courseindex, #theme_boost-drawers-blocks,
        #id_entrieshdr, #id_appearancehdr, #id_entrieshdr, #fitem_id_rolewarning, #fitem_id_assessed, #fgroup_id_scale,
        #fitem_id_cmidnumber, #fitem_id_groupingid, .moreless-actions, #fitem_id_timelimit,
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
