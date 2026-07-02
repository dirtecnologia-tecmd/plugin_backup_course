<?php
require_once('../../../../config.php');
require_once("$CFG->libdir/filelib.php");
require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
require_once($CFG->dirroot . '/backup/moodle2/backup_plan_builder.class.php');
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');
require_once($CFG->dirroot . '/backup/util/ui/import_extensions.php');


class delete{
    
    public static function run(){
        $obj = new self();
        $idfunc = $_POST['key'];
        //print_r($_POST);
        switch($idfunc){ 
            case 'D01': 
                $resp = $obj->deleteToken($_POST['data_child']['node_id']);
                break;   
            case 'D02': 
                $resp = $obj->deleteSection();
                break;   

        }
        echo json_encode($resp);
    } 

    /*
     * Https to Http -> formatHttp
     * identifica si la cadena ingresada contiene HTTPS y lo convierte a HTTP
     * Retorna un string con la cadena formateada
     * return {string};
    */

    private function formatHttp($url)
    {
        $url = trim($url);

        $url_expl = explode(":", $url);

        $url_https = $url_expl[0];

        $url_https = strtolower($url_https);

        if( $url_https == 'https'){

            $url_http = str_replace("s", "", $url_https);
        
            return $url_http.":".$url_expl[1];

        }else{

            return $url;

        }
 
    }
    
    /*
     * Eliminar Tokens -> deleteToken
     * return {array};
     */
    private function deleteToken($idnodo){
        global $DB, $CFG;
        $registro = (object)$_POST;
        /* var_dump($registro); */
        $tok = sha1('2017.UVD_TokeN_noDos');
        $url_actual = explode('/local/', $_SERVER['HTTP_REFERER']);
        $url = $this->formatHttp($registro->data_child['node_domain']).'/webservice/rest/server.php?wstoken='.$tok.'&wsfunction=local_remoter_register_node&moodlewsrestformat=json';
        $params = array('function'=>$registro->key,
                        'url' =>$this->formatHttp($url_actual[0]),
                        'nombre' => '',
                        'token' => $registro->data_child['node_token'],
                        /* 'ip' => $_SERVER['SERVER_ADDR'], */
                        'url_hijo' => $this->formatHttp($registro->data_child['node_domain']),
                        'edition_acti'=>'',
                        'estado' => '',
                        /*'server' => '',
                        'port' => '',
                        'username' => '',
                        'password' => '',*/
                        'startdate' => '',
                        'enddate8' => '',
                        'enddate16' => ''
                );

             /*    var_dump($params); */

        $curl = new curl;
        $results = json_decode($curl->post($url,$params));
        if(!empty($results) ){
            foreach ($results as $arr) {
                if(property_exists($arr,'ack')){
                    if($arr->ack == 1 && $idnodo != 0){
                        $DB->delete_records('bc_registro_pc',array('id'=>$idnodo));
                        /*$archivos = '/home/www/sincronizacion/tmp_bck_p/'.$idnodo;
                        rmdir($archivos);*/
                    }
                }else{
                    print_r($results);
                }
                
            }
        }
        return $results;
    }
    
    /*
     * Eliminar section -> saveToken
     * @params {objet} 
     * return {id};
     */
    private function deleteSection(){
        global $DB,$CFG, $OUTPUT;;
        $registro = (object)$_POST;
        //restore_dbops::delete_course_content($registro->node_id);
        require_once($CFG->libdir.'/badgeslib.php');
        require_once($CFG->libdir.'/completionlib.php');
        require_once($CFG->libdir.'/questionlib.php');
        require_once($CFG->libdir.'/gradelib.php');
        require_once($CFG->dirroot.'/group/lib.php');
        require_once($CFG->dirroot.'/comment/lib.php');
        require_once($CFG->dirroot.'/rating/lib.php');
        require_once($CFG->dirroot.'/notes/lib.php');
        
        $courseid = $registro->node_id;
        
        $showfeedback = false;
        $options = null;
        // Handle course badges.
        badges_handle_course_deletion($courseid);

        // NOTE: these concatenated strings are suboptimal, but it is just extra info...
        $strdeleted = get_string('deleted').' - ';

        // Some crazy wishlist of stuff we should skip during purging of course content.
        $options = (array)$options;

        $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
        
        $coursecontext = context_course::instance($courseid);
        $fs = get_file_storage();

        // Delete course completion information, this has to be done before grades and enrols.
        $cc = new completion_info($course);
        $cc->clear_criteria();
        if ($showfeedback) {
            echo $OUTPUT->notification($strdeleted.get_string('completion', 'completion'), 'notifysuccess');
        }

        // Remove all data from gradebook - this needs to be done before course modules
        // because while deleting this information, the system may need to reference
        // the course modules that own the grades.
        remove_course_grades($courseid, $showfeedback);
        remove_grade_letters($coursecontext, $showfeedback);

        // Delete course blocks in any all child contexts,
        // they may depend on modules so delete them first.
        $childcontexts = $coursecontext->get_child_contexts(); // Returns all subcontexts since 2.2.
        foreach ($childcontexts as $childcontext) {
            blocks_delete_all_for_context($childcontext->id);
        }

        unset($childcontexts);
        blocks_delete_all_for_context($coursecontext->id);
        if ($showfeedback) {
            echo $OUTPUT->notification($strdeleted.get_string('type_block_plural', 'plugin'), 'notifysuccess');
        }
        
        $sections = $DB->get_records('course_sections', array('course' => $courseid));
        foreach ($sections as $key => $value) {
            if(empty($value->sequence)){
                $DB->delete_records('course_modules', array('section' => $value->id, 'course' => $courseid));
                // Delete course sections.
                $DB->delete_records('course_sections', array('id' => $value->id));
            }
        }
        
        $DB->delete_records('forum', array('course' => $courseid));
        $DB->delete_records('forum_discussions', array('course' => $courseid));
              
        // Delete every instance of every module,
        // this has to be done before deleting of course level stuff.
        $locations = core_component::get_plugin_list('mod');
        foreach ($locations as $modname => $moddir) {
            if ($modname === 'NEWMODULE') {
                continue;
            }
            if ($module = $DB->get_record('modules', array('name' => $modname))) {

                include_once("$moddir/lib.php");                 // Shows php warning only if plugin defective.
                $moddelete = $modname .'_delete_instance';       // Delete everything connected to an instance.
                $moddeletecourse = $modname .'_delete_course';   // Delete other stray stuff (uncommon).

                if ($instances = $DB->get_records($modname, array('course' => $course->id))) {

                    foreach ($instances as $instance) {
                        if ($cm = get_coursemodule_from_instance($modname, $instance->id, $course->id)) {
                            try {
                                // Delete activity context questions and question categories.
                                question_delete_activity($cm,  $showfeedback);
                                // Notify the competency subsystem.
                                \core_competency\api::hook_course_module_deleted($cm);
                            } catch (Exception $e) {
                                echo 'Excepción capturada: No se eliminó $cm'. json_encode($cm).  $e->getMessage();
                            }
                            
                        }
                        if (function_exists($moddelete)&& property_exists($instance,'id')) {
                            // This purges all module data in related tables, extra user prefs, settings, etc.
                            try {
                                $moddelete($instance->id);
                            } catch (Exception $e) {
                                //echo 'Excepción capturada: No se eliminó $instance ', $modname,' -> '. json_encode($instance).  $e->getMessage();
                                $DB->delete_records($modname, array('id' => $instance->id));
                            }
                            
                        } else {
                            // NOTE: we should not allow installation of modules with missing delete support!
                            debugging("Defective module '$modname' detected when deleting course contents: missing function $moddelete()!");
                            try {
                                $DB->delete_records($modname, array('id' => $instance->id));
                            } catch (Exception $e) {
                                echo 'Excepción capturada: No se eliminó ',$modname, json_encode($instance),  $e->getMessage();
                            }
                                
                        }

                        if ($cm) {
                            // Delete cm and its context - orphaned contexts are purged in cron in case of any race condition.
                            context_helper::delete_instance(CONTEXT_MODULE, $cm->id);
                            try {
                                $DB->delete_records('course_modules', array('id' => $cm->id));
                            } catch (Exception $e) {
                                echo 'Excepción capturada: No se eliminó'. json_encode($cm).  $e->getMessage();
                            }
                            
                        }
                    }
                }
                if (function_exists($moddeletecourse)) {
                    // Execute ptional course cleanup callback.
                    $moddeletecourse($course, $showfeedback);
                }
                if ($instances and $showfeedback) {
                    echo $OUTPUT->notification($strdeleted.get_string('pluginname', $modname), 'notifysuccess');
                }
            } else {
                // Ooops, this module is not properly installed, force-delete it in the next block.
            }
        }
        
        // We have tried to delete everything the nice way - now let's force-delete any remaining module data.

        // Remove all data from availability and completion tables that is associated
        // with course-modules belonging to this course. Note this is done even if the
        // features are not enabled now, in case they were enabled previously.
        try {
            $DB->delete_records_select('course_modules_completion',
               'coursemoduleid IN (SELECT id from {course_modules} WHERE course=?)',
               array($courseid));
        } catch (Exception $e) {
            echo 'Excepción capturada: No se eliminó  course_modules_completion';
        }
        

        // Remove course-module data.
        $cms = $DB->get_records('course_modules', array('course' => $course->id));
        foreach ($cms as $cm) {
            if ($module = $DB->get_record('modules', array('id' => $cm->module))) {
                try {
                    $DB->delete_records($module->name, array('id' => $cm->instance));
                    context_helper::delete_instance(CONTEXT_MODULE, $cm->id);
                    $DB->delete_records('course_modules', array('id' => $cm->id));
                } catch (Exception $e) {
                    echo 'Excepción capturada: No se eliminó '. json_encode($cm).  $e->getMessage();
                    // Ignore weird or missing table problems.
                }
            }
            
        }
        if ($showfeedback) {
            echo $OUTPUT->notification($strdeleted.get_string('type_mod_plural', 'plugin'), 'notifysuccess');
        }

        // Cleanup the rest of plugins.
        $cleanuplugintypes = array('report', 'coursereport', 'format');
        $callbacks = get_plugins_with_function('delete_course', 'lib.php');
        foreach ($cleanuplugintypes as $type) {
            if (!empty($callbacks[$type])) {
                foreach ($callbacks[$type] as $pluginfunction) {
                    $pluginfunction($course->id, $showfeedback);
                }
            }
            if ($showfeedback) {
                echo $OUTPUT->notification($strdeleted.get_string('type_'.$type.'_plural', 'plugin'), 'notifysuccess');
            }
        }

        // Delete questions and question categories.
        question_delete_course($course, $showfeedback);
        if ($showfeedback) {
            echo $OUTPUT->notification($strdeleted.get_string('questions', 'question'), 'notifysuccess');
        }

        // Make sure there are no subcontexts left - all valid blocks and modules should be already gone.
        $childcontexts = $coursecontext->get_child_contexts(); // Returns all subcontexts since 2.2.
        foreach ($childcontexts as $childcontext) {
            $childcontext->delete();
        }
        unset($childcontexts);

        // Remove all roles and enrolments by default.
        /*if (empty($options['keep_roles_and_enrolments'])) {
            // This hack is used in restore when deleting contents of existing course.
            role_unassign_all(array('contextid' => $coursecontext->id, 'component' => ''), true);
            enrol_course_delete($course);
            if ($showfeedback) {
                echo $OUTPUT->notification($strdeleted.get_string('type_enrol_plural', 'plugin'), 'notifysuccess');
            }
        }*/

        // Delete any groups, removing members and grouping/course links first.
        if (empty($options['keep_groups_and_groupings'])) {
            groups_delete_groupings($course->id, $showfeedback);
            groups_delete_groups($course->id, $showfeedback);
        }

        // Filters be gone!
        filter_delete_all_for_context($coursecontext->id);

        // Notes, you shall not pass!
        note_delete_all($course->id);

        // Die comments!
        comment::delete_comments($coursecontext->id);

        // Ratings are history too.
        $delopt = new stdclass();
        $delopt->contextid = $coursecontext->id;
        $rm = new rating_manager();
        $rm->delete_ratings($delopt);

        // Delete course tags.
        core_tag_tag::remove_all_item_tags('core', 'course', $course->id);

        // Notify the competency subsystem.
        \core_competency\api::hook_course_deleted($course);

        // Delete calendar events.
        $DB->delete_records('event', array('courseid' => $course->id));
        $fs->delete_area_files($coursecontext->id, 'calendar');

        // Delete all related records in other core tables that may have a courseid
        // This array stores the tables that need to be cleared, as
        // table_name => column_name that contains the course id.
        $tablestoclear = array(
            'backup_courses' => 'courseid',  // Scheduled backup stuff.
            'user_lastaccess' => 'courseid', // User access info.
        );
        foreach ($tablestoclear as $table => $col) {
            $DB->delete_records($table, array($col => $course->id));
        }

        // Delete all course backup files.
        $fs->delete_area_files($coursecontext->id, 'backup');

        // Cleanup course record - remove links to deleted stuff.
        $oldcourse = new stdClass();
        $oldcourse->id               = $course->id;
        $oldcourse->summary          = '';
        $oldcourse->cacherev         = 0;
        $oldcourse->legacyfiles      = 0;
        if (!empty($options['keep_groups_and_groupings'])) {
            $oldcourse->defaultgroupingid = 0;
        }
        /*Eliminar la configuracion de pestañas*/
        if($DB->get_records('course_format_options', array('courseid' => $course->id))){
            $DB->delete_records('course_format_options', array('courseid' => $course->id));
        }
        // Delete questions and question categories.
        question_delete_course($course);

        // Delete content bank contents.
        $cb = new \core_contentbank\contentbank();
        $cb->delete_contents($coursecontext);
        $DB->delete_records('question_categories', array('contextid' => $coursecontext->id));
        
        
        $DB->update_record('course', $oldcourse);
        // Delete course sections.
        $DB->delete_records('course_sections', array('course' => $course->id));
        // Delete legacy, section and any other course files.
        $fs->delete_area_files($coursecontext->id, 'course'); // Files from summary and section.
        /*
        // Delete all remaining stuff linked to context such as files, comments, ratings, etc.
        if (empty($options['keep_roles_and_enrolments']) and empty($options['keep_groups_and_groupings'])) {
            // Easy, do not delete the context itself...
            $coursecontext->delete_content();
        } else {
            // Hack alert!!!!
            // We can not drop all context stuff because it would bork enrolments and roles,
            // there might be also files used by enrol plugins...
        }*/

        // Delete legacy files - just in case some files are still left there after conversion to new file api,
        // also some non-standard unsupported plugins may try to store something there.
        fulldelete($CFG->dataroot.'/'.$course->id);
        // Delete from cache to reduce the cache size especially makes sense in case of bulk course deletion.
        $cachemodinfo = cache::make('core', 'coursemodinfo');
        $cachemodinfo->delete($courseid);

        // Trigger a course content deleted event.
        $event = \core\event\course_content_deleted::create(array(
            'objectid' => $course->id,
            'context' => $coursecontext,
            'other' => array('shortname' => $course->shortname,
                             'fullname' => $course->fullname,
                             'options' => $options) // Passing this for legacy reasons.
        ));
        $event->add_record_snapshot('course', $course);
        $event->trigger();
        $DB->delete_records('grade_categories', array('courseid' => $courseid));
        $DB->delete_records('grade_items', array('courseid' => $courseid));
        $DB->delete_records('question_categories', array('contextid' => $coursecontext->id));
        $DB->delete_records('files', array('contextid' => $coursecontext->id));
        $DB->delete_records('contentbank_content', array('contextid' => $coursecontext->id));
        
        if($DB->get_manager()->table_exists('bc_add_sections_activities')) $DB->delete_records('bc_add_sections_activities', array('courseid' => $courseid));
        return (int)$registro->node_id;
    }
    
}
delete::run();
