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

class mod_glossary_mod_form_uvd extends moodleform_mod_uvd {

    function definition() {
        global $CFG, $COURSE, $DB;

        $mform = &$this->_form;

//-------------------------------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('name'), array('size'=>'64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $this->standard_intro_elements();

        if (has_capability('mod/glossary:manageentries', context_system::instance())) {
            $mform->addElement('checkbox', 'globalglossary', get_string('isglobal', 'glossary'));
            $mform->addHelpButton('globalglossary', 'isglobal', 'glossary');

        }else{
            $mform->addElement('hidden', 'globalglossary');
            $mform->setType('globalglossary', PARAM_INT);
        }

        $options = array(1=>get_string('mainglossary', 'glossary'), 0=>get_string('secondaryglossary', 'glossary'));
        $mform->addElement('select', 'mainglossary', get_string('glossarytype', 'glossary'), $options);
        $mform->addHelpButton('mainglossary', 'glossarytype', 'glossary');
        $mform->setDefault('mainglossary', 0);

        // ----------------------------------------------------------------------
        $mform->addElement('header', 'entrieshdr', get_string('entries', 'glossary'));

        $mform->addElement('selectyesno', 'defaultapproval', get_string('defaultapproval', 'glossary'));
        $mform->setDefault('defaultapproval', $CFG->glossary_defaultapproval);
        $mform->addHelpButton('defaultapproval', 'defaultapproval', 'glossary');

        $mform->addElement('selectyesno', 'editalways', get_string('editalways', 'glossary'));
        $mform->setDefault('editalways', 0);
        $mform->addHelpButton('editalways', 'editalways', 'glossary');

        $mform->addElement('selectyesno', 'allowduplicatedentries', get_string('allowduplicatedentries', 'glossary'));
        $mform->setDefault('allowduplicatedentries', $CFG->glossary_dupentries);
        $mform->addHelpButton('allowduplicatedentries', 'allowduplicatedentries', 'glossary');

        $mform->addElement('selectyesno', 'allowcomments', get_string('allowcomments', 'glossary'));
        $mform->setDefault('allowcomments', $CFG->glossary_allowcomments);
        $mform->addHelpButton('allowcomments', 'allowcomments', 'glossary');

        $mform->addElement('selectyesno', 'usedynalink', get_string('usedynalink', 'glossary'));
        $mform->setDefault('usedynalink', $CFG->glossary_linkbydefault);
        $mform->addHelpButton('usedynalink', 'usedynalink', 'glossary');

        // ----------------------------------------------------------------------
        $mform->addElement('header', 'appearancehdr', get_string('appearance'));

        // Get and update available formats.
        $recformats = glossary_get_available_formats();
        $formats = array();
        foreach ($recformats as $format) {
           $formats[$format->name] = get_string('displayformat'.$format->name, 'glossary');
        }
        asort($formats);
        $mform->addElement('select', 'displayformat', get_string('displayformat', 'glossary'), $formats);
        $mform->setDefault('displayformat', 'dictionary');
        $mform->addHelpButton('displayformat', 'displayformat', 'glossary');

        $displayformats['default'] = get_string('displayformatdefault', 'glossary');
        $displayformats = array_merge($displayformats, $formats);
        $mform->addElement('select', 'approvaldisplayformat', get_string('approvaldisplayformat', 'glossary'), $displayformats);
        $mform->setDefault('approvaldisplayformat', 'default');
        $mform->addHelpButton('approvaldisplayformat', 'approvaldisplayformat', 'glossary');

        $mform->addElement('text', 'entbypage', get_string('entbypage', 'glossary'));
        $mform->setDefault('entbypage', $this->get_default_entbypage());
        $mform->addRule('entbypage', null, 'numeric', null, 'client');
        $mform->setType('entbypage', PARAM_INT);

        $mform->addElement('selectyesno', 'showalphabet', get_string('showalphabet', 'glossary'));
        $mform->setDefault('showalphabet', 1);
        $mform->addHelpButton('showalphabet', 'showalphabet', 'glossary');

        $mform->addElement('selectyesno', 'showall', get_string('showall', 'glossary'));
        $mform->setDefault('showall', 1);
        $mform->addHelpButton('showall', 'showall', 'glossary');

        $mform->addElement('selectyesno', 'showspecial', get_string('showspecial', 'glossary'));
        $mform->setDefault('showspecial', 1);
        $mform->addHelpButton('showspecial', 'showspecial', 'glossary');

        $mform->addElement('selectyesno', 'allowprintview', get_string('allowprintview', 'glossary'));
        $mform->setDefault('allowprintview', 1);
        $mform->addHelpButton('allowprintview', 'allowprintview', 'glossary');

        if ($CFG->enablerssfeeds && isset($CFG->glossary_enablerssfeeds) && $CFG->glossary_enablerssfeeds) {
//-------------------------------------------------------------------------------
            $mform->addElement('header', 'rssheader', get_string('rss'));
            $choices = array();
            $choices[0] = get_string('none');
            $choices[1] = get_string('withauthor', 'glossary');
            $choices[2] = get_string('withoutauthor', 'glossary');
            $mform->addElement('select', 'rsstype', get_string('rsstype'), $choices);
            $mform->addHelpButton('rsstype', 'rsstype', 'glossary');

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
            $mform->addHelpButton('rssarticles', 'rssarticles', 'glossary');
            $mform->disabledIf('rssarticles', 'rsstype', 'eq', 0);
        }

//-------------------------------------------------------------------------------

        $this->standard_grading_coursemodule_elements();

        $this->standard_coursemodule_elements();

//-------------------------------------------------------------------------------
        // buttons
        $this->add_action_buttons();
    }

    function definition_after_data() {
        global $COURSE, $DB;

        parent::definition_after_data();
        $mform    =& $this->_form;
        $mainglossaryel =& $mform->getElement('mainglossary');
        $mainglossary = $DB->get_record('glossary', array('mainglossary'=>1, 'course'=>$COURSE->id));
        if ($mainglossary && ($mainglossary->id != $mform->getElementValue('instance'))){
            //secondary glossary, a main one already exists in this course.
            $mainglossaryel->setValue(0);
            $mainglossaryel->freeze();
            $mainglossaryel->setPersistantFreeze(true);
        } else {
            $mainglossaryel->unfreeze();
            $mainglossaryel->setPersistantFreeze(false);

        }
    }

    function data_preprocessing(&$default_values){
        parent::data_preprocessing($default_values);

        // Fallsback on the default setting if 'Entries shown per page' has been left blank.
        // This prevents the field from being required and expand its section which should not
        // be the case if there is a default value defined.
        if (empty($default_values['entbypage']) || $default_values['entbypage'] < 0) {
            $default_values['entbypage'] = $this->get_default_entbypage();
        }

        // Set up the completion checkboxes which aren't part of standard data.
        // Tick by default if Add mode or if completion entries settings is set to 1 or more.
        if (empty($this->_instance) || !empty($default_values['completionentries'])) {
            $default_values['completionentriesenabled'] = 1;
        } else {
            $default_values['completionentriesenabled'] = 0;
        }
        if (empty($default_values['completionentries'])) {
            $default_values['completionentries']=1;
        }
    }

    function add_completion_rules() {
        $mform =& $this->_form;

        $group=array();
        $group[] =& $mform->createElement('checkbox', 'completionentriesenabled', '', get_string('completionentries','glossary'));
        $group[] =& $mform->createElement('text', 'completionentries', '', array('size'=>3));
        $mform->setType('completionentries', PARAM_INT);
        $mform->addGroup($group, 'completionentriesgroup', get_string('completionentriesgroup','glossary'), array(' '), false);
        $mform->disabledIf('completionentries','completionentriesenabled','notchecked');

        return array('completionentriesgroup');
    }

    function completion_rule_enabled($data) {
        return (!empty($data['completionentriesenabled']) && $data['completionentries']!=0);
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
        if (!empty($data->completionunlocked)) {
            // Turn off completion settings if the checkboxes aren't ticked
            $autocompletion = !empty($data->completion) && $data->completion==COMPLETION_TRACKING_AUTOMATIC;
            if (empty($data->completionentriesenabled) || !$autocompletion) {
                $data->completionentries = 0;
            }
        }
    }

    /**
     * Returns the default value for 'Entries shown per page'.
     *
     * @return int default for number of entries per page.
     */
    protected function get_default_entbypage() {
        global $CFG;
        return !empty($CFG->glossary_entbypage) ? $CFG->glossary_entbypage : 10;
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
        #id_activitycompletionheader, #id_submitbutton, #id_cancel, #nav-drawer, .mt-5.mb-1.activity-navigation, #soporte-bar,
        #id_entrieshdr, #id_appearancehdr, #id_entrieshdr, #fitem_id_rolewarning, #fitem_id_assessed, #fgroup_id_scale, #page-navbar, #theme_boost-drawers-courseindex, #theme_boost-drawers-blocks,
        #fitem_id_cmidnumber, #fitem_id_groupingid, #fitem_id_restrictgroupbutton, #id_modstandardratings,
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



    