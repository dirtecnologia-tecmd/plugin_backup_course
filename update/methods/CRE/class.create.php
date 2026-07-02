<?php
require_once('../../../../../config.php');
require_once("$CFG->libdir/filelib.php");

class createUpdate
{

    public static function run()
    {
        $objCRE = new self();
        $idfunc = $_POST['key'];
        switch ($idfunc) {
            case 'C01':
                $resp = $objCRE->saveAndReturnActivities();
                break;
            case 'C02':
                $resp = $objCRE->saveUpdate_Nodos();
                break;
            case 'Q01':
                $resp = $objCRE->searchData();
                break;
                /*case 'C03': 
                $resp = $objCRE->emailError();
                break;*/
        }
        if (!empty($resp)) {
            echo json_encode($resp);
        }
    }


    /*
     * Create updates_courses y updates_log -> saveAndReturnActivities
     * Guarda en las tablas el id del curso, de la actividad, el tipo de actividad y el objeto de esta
     * Retorna id de las inserciones 
     * @param {int} $registro->id_course_sp
     * @param {int} $registro->id_act_sp
     * @param {string} $registro->type_act
     * @param {obj} $registro->obj_act
     * @param {int} $registro->id_user
     * return {objet};
     */
    private function saveAndReturnActivities()
    {

        global $DB;
        $registro = (object)$_POST;

        if (property_exists($registro, 'obj_act')) {
            $registro->obj_act = json_encode($registro->obj_act);
        }

        $res = new stdClass();
        $res->id_updates_courses = $DB->insert_record('updates_courses', $registro);
        $registro->id_update = $res->id_updates_courses;
        $registro->time_update = time();
        $res->id_updates_log = $DB->insert_record('updates_log', $registro);

        return $res;
    }

    /*
     * Create updates_nodos -> saveUpdate_Nodos
     * Enviar las notificaciones a los nodos
     * Retorna el id creado 
     * return {id};
     */
    private function saveUpdate_Nodos()
    {
        global $DB, $CFG;
        $objCRE = new self();
        $registro = (object)$_POST;

        if (array_key_exists('assign_plugin_config', $registro->obj_act)) {
            if (empty($registro->propuesta)) {
                $result = $DB->get_records('assign_plugin_config', array('assignment' => $registro->obj_act['assign']['id']));
                if (!empty($result)) {
                    $registro->obj_act['assign_plugin_config'] = $result;
                }
            } else {
                unset($registro->obj_act['course_modules'], $registro->obj_act['assign_plugin_config'], $registro->obj_act['grade_items']);
            }
        }
        if (array_key_exists('choice_options', $registro->obj_act)) {
            $result = $DB->get_records('choice_options', array('choiceid' => $registro->obj_act['choice']['id']));
            if (!empty($result)) {
                $registro->obj_act['choice_options'] = $result;
            }
        }
        if (array_key_exists('choicegroup_options', $registro->obj_act)) { //choicegroup_options
            $result = $DB->get_records('choicegroup_options', array('choicegroupid' => $registro->obj_act['choicegroup']['id']));
            if (!empty($result)) {
                $registro->obj_act['choicegroup_options'] = $result;
            }
        }
        if (array_key_exists('feedback_item', $registro->obj_act)) { //
            $result = $DB->get_records('feedback_item', array('feedback' => $registro->obj_act['feedback_item']['feedback']));
            if (!empty($result)) {
                $registro->obj_act['feedback_item'] = $result;
            }
        }

        if (array_key_exists('lesson_pages', $registro->obj_act)) { //


            $result = $DB->get_records('lesson_pages', array('lessonid' => $registro->obj_act['lesson_pages']['lessonid']));
            if (!empty($result)) {
                $registro->obj_act['lesson_pages'] = $result;
            }
        }

        if (array_key_exists('lesson_answers', $registro->obj_act)) { //

            $result = $DB->get_records('lesson_answers', array('lessonid' => $registro->obj_act['lesson_answers']['lessonid']));

            if (!empty($result)) {
                $registro->obj_act['lesson_answers'] = $result;
                unset($registro->obj_act['course_modules'], $registro->obj_act['grade_items']);
            }
        }


        if (array_key_exists('quiz_feedback', $registro->obj_act)) { //quiz_feedback

            $result = $DB->get_records('quiz_feedback', array('quizid' => $registro->obj_act['quiz']['id']));
            if (!empty($result)) {
                $registro->obj_act['quiz_feedback'] = $result;
            }
        }
        if (array_key_exists('quiz_slots', $registro->obj_act)) { //quiz_feedback
            $result = $DB->get_records('quiz_slots', array('quizid' => $registro->obj_act['quiz']['id']));
            if (!empty($result)) {
                $registro->obj_act['quiz_slots'] = $result;
            }
        }

        if (array_key_exists('question', $registro->obj_act)) {
            if (array_key_exists('quiz', $registro->obj_act) && array_key_exists('cmid', $registro->obj_act['quiz'])) {
                $result = $DB->get_record('course_modules', array('id' => $registro->obj_act['quiz']['cmid']));

                if (!empty($result)) {

                    $idquiz = ($result);
                    $registro->obj_act['quiz']['id'] = $idquiz->instance;
                }
            }

            $question = $DB->get_record('question', array('id' => $registro->obj_act['question']['id']));

            $versi = $DB->get_record('question_versions', array('questionid' => $registro->obj_act['question']['id']));

            if (!empty($question)) {

                /*                 $qcat = $DB->get_record_sql('SELECT q_cat.id as id_categoria, q_cat.name, q_cat.contextid, q_cat.info, q_cat.infoformat, q_cat.stamp, q_cat.parent, q_cat.sortorder
                FROM {question_categories} q_cat
                LEFT JOIN {context} cont ON cont.id = q_cat.contextid
                WHERE cont.contextlevel = 50 AND cont.instanceid = :instanceid 
                AND q_cat.name != :name_cat
                ', array('instanceid' => $registro->id_course_sp, 'name_cat' => 'top')); */

                $qcat = $DB->get_record_sql("SELECT q_cat.id as id_categoria, q_cat.name, q_cat.contextid, q_cat.info, q_cat.infoformat, 
                q_cat.stamp, q_cat.parent, q_cat.sortorder, entries.id as bank_entry
                FROM {question} as question
                INNER JOIN {question_versions} as versions ON versions.questionid = question.id
                INNER JOIN {question_bank_entries} as entries ON entries.id = versions.questionbankentryid
                INNER JOIN {question_categories} as q_cat ON q_cat.id = entries.questioncategoryid
                INNER JOIN {context} as cont ON cont.id = q_cat.contextid
                WHERE cont.contextlevel = 50
                AND cont.instanceid = $registro->id_course_sp
                AND question.id = $question->id
                AND q_cat.name != 'top'");

                $registro->obj_act['question'] = $question;

                $registro->obj_act['question_categories'] = $qcat;

                $idBankEntries = $DB->get_record_sql("SELECT id FROM {question_bank_entries} WHERE questioncategoryid = $qcat->id_categoria AND id = $qcat->bank_entry");

                $registro->obj_act['idBankEntries'] = (object) ["id" => $idBankEntries->id];

                $registro->obj_act['question_bank_entries'] = $DB->get_record_sql("SELECT * FROM {question_bank_entries} WHERE questioncategoryid = $qcat->id_categoria AND id = $qcat->bank_entry ");

                $registro->obj_act['question_versions'] = $versi;

                $registro->obj_act['question_bank_entries2'] = $DB->get_record_sql("SELECT * FROM {question_bank_entries} WHERE questioncategoryid = $qcat->id_categoria AND id = $qcat->bank_entry ");

                $id_question = $question->id;

                $registro->obj_act['question_answers']            = $DB->get_records('question_answers', array('question' => $id_question));
                $registro->obj_act['question_truefalse']          = $DB->get_records('question_truefalse', array('question' => $id_question));
                $registro->obj_act['question_multianswer']        = $DB->get_records('question_multianswer', array('question' => $id_question));
                $registro->obj_act['qtype_ddimageortext']         = $DB->get_records('qtype_ddimageortext', array('questionid' => $id_question));
                $registro->obj_act['qtype_ddimageortext_drags']   = $DB->get_records('qtype_ddimageortext_drags', array('questionid' => $id_question));
                $registro->obj_act['qtype_ddimageortext_drops']   = $DB->get_records('qtype_ddimageortext_drops', array('questionid' => $id_question));
                $registro->obj_act['qtype_ddmarker']              = $DB->get_records('qtype_ddmarker', array('questionid' => $id_question));
                $registro->obj_act['qtype_ddmarker_drags']        = $DB->get_records('qtype_ddmarker_drags', array('questionid' => $id_question));
                $registro->obj_act['qtype_ddmarker_drops']        = $DB->get_records('qtype_ddmarker_drops', array('questionid' => $id_question));
                $registro->obj_act['qtype_essay_options']         = $DB->get_records('qtype_essay_options', array('questionid' => $id_question));
                $registro->obj_act['qtype_match_options']         = $DB->get_records('qtype_match_options', array('questionid' => $id_question));
                $registro->obj_act['qtype_match_subquestions']    = $DB->get_records('qtype_match_subquestions', array('questionid' => $id_question));
                $registro->obj_act['qtype_multichoice_options']   = $DB->get_records('qtype_multichoice_options', array('questionid' => $id_question));
                $registro->obj_act['qtype_randomsamatch_options'] = $DB->get_records('qtype_randomsamatch_options', array('questionid' => $id_question));
                $registro->obj_act['qtype_shortanswer_options']   = $DB->get_records('qtype_shortanswer_options', array('questionid' => $id_question));
                /* $registro->obj_act['quiz_slots']  = $DB->get_records('quiz_slots', array('quizid' => $idquiz->instance)); */
            }
        }
        //Rubrica del foro o la tarea
        $tipoAct = "";
        if (array_key_exists('forum', $registro->obj_act)) {
            $tipoAct = 'forum';
        }
        if (array_key_exists('assign', $registro->obj_act)) {
            $tipoAct = 'assign';
        }

        if (array_key_exists($tipoAct, $registro->obj_act)) {

            if (array_key_exists('rubric', $registro->obj_act[$tipoAct])) {

                $areaId = $registro->obj_act[$tipoAct]['areaid'];

                $areasId = $DB->get_record('grading_areas', array('id' => $areaId));

                $contextId = $DB->get_record('context', array('id' => $areasId->contextid));

                $courseId = $contextId->instanceid;

                $registro->obj_act[$tipoAct]['courseId'] = $courseId;

                /* $registro->obj_act["cantidadCriteria"] = $DB-> */

                //Propiedades de la rubrica
                $registro->obj_act['grading_definitions'] = $DB->get_record('grading_definitions', array('areaid' => $areaId));

                $definitionId = $registro->obj_act['grading_definitions']->id;

                $registro->obj_act['grading_definitions']->$definitionId['p'] = $definitionId;
                $registro->obj_act['grading_definitions']->$definitionId['h'] = "";
                /* $registro2->lesson_answers[$res->idInstance]["h"] */

                //Criterios de calificación de la rubrica
                $registro->obj_act['gradingform_rubric_criteria'] = $DB->get_records('gradingform_rubric_criteria', array('definitionid' => $definitionId));

                $levelsR = array();

                foreach ($registro->obj_act['gradingform_rubric_criteria'] as $keyR => $valR) {

                    $idR = $valR->id;

                    array_push($levelsR, $DB->get_records('gradingform_rubric_levels', array('criterionid' => $idR)));
                }

                //Niveles de calificación de la rubrica
                $registro->obj_act['gradingform_rubric_levels'] = $levelsR;
            }
        }

        //ENVIAR AQUI LOS DATOS DE LA CARPETA HACIA EL HIJO 

        if ($registro->type_act == 'folder') {

            $folderId = $registro->obj_act['folder']['id'];

            $array_check = implode(",", $registro->array_check);

            $array_url = array();

            $array_files_dir = array();

            $array_moodle_data = array();

            $array_name_archive = array();

            $array_rel = array();

            $array_hashed = array();

            require_once '../../../folder_S3/controlador2_m.php';

            $s3 = new Controlador2_m();

            $tb_reg = $DB->get_records_sql('SELECT rel.registroid, COUNT(rel.registroid) AS tot_courses, reg.url_hijo, reg.token, GROUP_CONCAT(courseid_sh) as list
            FROM {bc_rel_padre_hijo} rel
            LEFT JOIN {bc_registro_pc} reg ON reg.id = rel.registroid 
            WHERE rel.courseid_sp = :id_course 
            AND reg.url_hijo is not null 
            AND reg.estado = 1
            AND reg.id IN(' . $array_check . ')
            GROUP BY  reg.url_hijo, rel.registroid, reg.token', array('id_course' => $registro->id_course_sp));

            foreach ($tb_reg as $key => $value) {

                $id_course = $registro->id_course_sp;

                $rel = $DB->get_record('bc_registro_pc', array('url_hijo' => $value->url_hijo));

                array_push($array_rel, $rel->id);

                $folder = $DB->get_record('folder', array('id' => $folderId));

                $course_mod = $DB->get_record('course_modules', array('instance' => $folder->id, 'course' => $folder->course));

                $context_fold = $DB->get_record('context', array('instanceid' => $course_mod->id));

                $files_fold = $DB->get_records_select(
                    'files',
                    'contextid = :contextid AND component = :component AND filesize > 0',
                    array(
                        'contextid' => $context_fold->id,
                        'component' => 'mod_folder',
                        'filearea' => 'content'
                    )
                );

                $registro->obj_act['folder']['files_folder'] = $files_fold;

                foreach ($files_fold as $kf => $vkf) {

                    $archivo = $vkf->contenthash;

                    $ext = pathinfo($vkf->filename, PATHINFO_EXTENSION);

                    $name_archive = "";

                    if (!empty($ext)) {

                        $timestamp = time();

                        $uniqueString = 'Unimin2023' . $timestamp;

                        $hashedValue = hash('sha256', $uniqueString);

                        array_push($array_hashed, $hashedValue);

                        $name_archive = $archivo . '_' . $hashedValue . '_' . $id_course . '_' . $rel->id . "." . $ext;
                        array_push($array_name_archive, $name_archive);

                        $cr1 = substr($archivo, 0, 2);
                        $cr2 = substr($archivo, 2, 2);

                        $file_dir = '/filedir/' . $cr1 . '/' . $cr2 . '/' . $archivo;

                        array_push($array_files_dir, $file_dir);

                        $moodle_data = $CFG->dataroot . '/filedir/' . $cr1 . '/' . $cr2 . '/' . $archivo;

                        array_push($array_moodle_data, $moodle_data);

                        array_push($array_url, $s3->run('create', $moodle_data, $name_archive, $id_course));
                    }
                }
            }

            $registro->obj_act['folder']['urls_file'] = $array_url;
            $registro->obj_act['folder']['moodle_data_folder'] = $array_moodle_data;
            $registro->obj_act['folder']['name_archive_folder'] = $array_name_archive;
            $registro->obj_act['folder']['files_dir'] = $array_files_dir;
            $registro->obj_act['folder']['hashed'] = $array_hashed;
            $registro->obj_act['folder']['rel'] = $array_rel;
        }

        if ($registro->type_act == 'scorm') {

            $array_check = implode(",", $registro->array_check);

            $tb_reg = $DB->get_records_sql('SELECT rel.registroid, COUNT(rel.registroid) AS tot_courses, reg.url_hijo, reg.token, GROUP_CONCAT(courseid_sh) as list
            FROM {bc_rel_padre_hijo} rel
            LEFT JOIN {bc_registro_pc} reg ON reg.id = rel.registroid 
            WHERE rel.courseid_sp = :id_course 
            AND reg.url_hijo is not null 
            AND reg.estado = 1
            AND reg.id IN(' . $array_check . ')
            GROUP BY  reg.url_hijo, rel.registroid, reg.token', array('id_course' => $registro->id_course_sp));

            foreach ($tb_reg as $key => $value) {

                $scorm = $DB->get_record('scorm', array('id' => $registro->obj_act["scorm"]["id"]));

                $id_course = $registro->id_course_sp;

                $archivo = $scorm->sha1hash;

                $rel = $DB->get_record('bc_registro_pc', array('url_hijo' => $value->url_hijo));

                $cr1 = substr($archivo, 0, 2);
                $cr2 = substr($archivo, 2, 2);
                $name_archive = $archivo . '_' . $id_course . '_' . $rel->id . '.zip';
                $moodle_data = $CFG->dataroot . '/filedir/' . $cr1 . '/' . $cr2 . '/' . $archivo;

                require_once '../../../folder_S3/controlador2_m.php';

                $s3 = new Controlador2_m();

                $registro->obj_act["scorm"]['url_scorm'] = $s3->run('create', $moodle_data, $name_archive, $id_course);

                $registro->obj_act["scorm"]['id_nodo'] = $id_course;
                $registro->obj_act["scorm"]['rel_id'] = $rel->id;
                $registro->obj_act["scorm"]['archivo'] = $archivo;
                $registro->obj_act["scorm"]['reference'] = $scorm->reference;
                $registro->obj_act["scorm"]['version'] = $scorm->version;

                $registro->obj_act["scorm"]['scorm_scoes_table'] = $DB->get_records('scorm_scoes', array('scorm' => $scorm->id));

                $array_scorm_scoes_data_table = array();

                foreach ($registro->obj_act["scorm"]['scorm_scoes_table'] as $ksst => $vsst) {

                    $ins = $DB->get_records('scorm_scoes_data', array('scoid' => $vsst->id));

                    if (!empty($ins)) {

                        array_push($array_scorm_scoes_data_table, $ins);
                    }
                }

                $registro->obj_act["scorm"]['scorm_scoes_data_table'] = (object)$array_scorm_scoes_data_table;

                $registro->obj_act["scorm"]['file_dir'] = '/filedir/' . $cr1 . '/' . $cr2 . '/' . $archivo;
            }
        }

        if ($registro->type_act == 'h5pactivity') {
        }

        if ($registro->type_act == 'workshop') {
            $workshop = $DB->get_record('workshop', array('id' => $registro->obj_act['workshop']['id']));
            $registro->obj_act['workshop']['name'] = $workshop->name;
            if ($workshop->strategy == 'accumulative') {
                $result = $DB->get_records('workshopform_accumulative', array('workshopid' => $registro->obj_act['workshop']['id']));
                if (!empty($result)) {
                    $registro->obj_act['workshopform_accumulative'] = $result;
                    unset($registro->obj_act['workshopform_comments'], $registro->obj_act['workshopform_numerrors'], $registro->obj_act['workshopform_rubric']);
                }
            } else if ($workshop->strategy == 'comments') {
                $result = $DB->get_records('workshopform_comments', array('workshopid' => $registro->obj_act['workshop']['id']));
                if (!empty($result)) {
                    $registro->obj_act['workshopform_comments'] = $result;
                    unset($registro->obj_act['workshopform_accumulative'], $registro->obj_act['workshopform_numerrors'], $registro->obj_act['workshopform_rubric']);
                }
            } else if ($workshop->strategy == 'numerrors') {
                $result = $DB->get_records('workshopform_numerrors', array('workshopid' => $registro->obj_act['workshop']['id']));
                if (!empty($result)) {
                    $registro->obj_act['workshopform_numerrors'] = $result;
                    unset($registro->obj_act['workshopform_accumulative'], $registro->obj_act['workshopform_comments'], $registro->obj_act['workshopform_rubric']);
                }
            } else if ($workshop->strategy == 'rubric') {
                $result = $DB->get_records('workshopform_rubric', array('workshopid' => $registro->obj_act['workshop']['id']));
                if (!empty($result)) {
                    $registro->obj_act['workshopform_rubric'] = $result;
                    $confg = $DB->get_records('workshopform_rubric_config', array('workshopid' => $registro->obj_act['workshop']['id']));
                    if (!empty($confg)) {
                        $registro->obj_act['workshopform_rubric_config'] = $confg;
                    }
                    foreach ($result as $k_ey => $v_alue) {
                        $levels = $DB->get_records('workshopform_rubric_levels', array('dimensionid' => $v_alue->id));
                        if (!empty($levels)) {
                            $registro->obj_act['workshopform_rubric_levels'][$k_ey] = $levels;
                        }
                    }
                    unset($registro->obj_act['workshopform_accumulative'], $registro->obj_act['workshopform_comments'], $registro->obj_act['workshopform_numerrors']);
                }
            }
        }

        if (array_key_exists('lti', $registro->obj_act) && array_key_exists('typeid', $registro->obj_act['lti'])) {
            $registro->obj_act['lti_types'] = $DB->get_record('lti_types', array('id' => $registro->obj_act['lti']['typeid']));
        }

        if (array_key_exists('grade_items', $registro->obj_act)) {

            if (!array_key_exists('categoryid', $registro->obj_act['grade_items'])) {

                unset($registro->obj_act['grade_items']);
            } else {

                $result = $DB->get_record(
                    'grade_items',
                    array(
                        "iteminstance" => $registro->obj_act['grade_items']['iteminstance'],
                        "itemmodule" => $registro->obj_act['grade_items']['itemmodule']
                    )
                );

                $registro->obj_act['grade_items'] = $result;
            }
        }

        $array_check = implode(",", $registro->array_check);

        $tb_reg = $DB->get_records_sql(
            'SELECT rel.registroid, COUNT(rel.registroid) AS tot_courses, reg.url_hijo, reg.token
                                        FROM {bc_rel_padre_hijo} rel
                                        LEFT JOIN {bc_registro_pc} reg ON reg.id = rel.registroid 
                                        WHERE rel.courseid_sp = :id_course 
                                        AND reg.url_hijo is not null 
                                        AND reg.estado = 1
                                        AND reg.id IN(' . $array_check . ')
                                        GROUP BY reg.url_hijo, rel.registroid, reg.token',
            array('id_course' => $registro->id_course_sp)
        );

        /* reg.ip, */

        $DB->update_record('updates_courses', array('id' => $registro->id_updates_courses, 'obj_act' => json_encode($registro->obj_act)));

        $update_nodos = new stdClass();
        $nodos = new stdClass();
        if (!empty($tb_reg) && count($tb_reg) > 0) {
            $tok = sha1('2017.UVD_TokeN_noDos');
            $url_actual = explode('/course/', $_SERVER['HTTP_REFERER']);
            if (strpos($_SERVER['HTTP_REFERER'], '/mod/lesson/')) {
                $url_actual = explode('/mod/', $_SERVER['HTTP_REFERER']);
            }
            $update_nodos->id_course_sp = $registro->id_course_sp;
            $update_nodos->id_log = $registro->id_updates_log;

            $update_nodos->estado = 1;
            $update_nodos->cant_courses_terminados = 0;
            echo '<ul>';
            foreach ($tb_reg as $key => $value) {
                $update_nodos->id_nodo_rel = $key;
                $update_nodos->cant_courses_actual = $value->tot_courses;
                $update_nodos->cant_courses_enhijo = 0;
                $update_nodos->id = $DB->insert_record('updates_nodos', $update_nodos);

                $url = $value->url_hijo . '/webservice/rest/server.php?wstoken=' . $tok . '&wsfunction=local_update_notificar_nodos&moodlewsrestformat=json';
                $params = array(
                    'url_padre' => $url_actual[0],
                    'token' => $value->token,
                    'idCourse_p' => $registro->id_course_sp,
                    'cant_courses' => $value->tot_courses,
                );
                $curl = new curl;
                $results = json_decode($curl->post($url, $params));
                $update_nodos_update = new stdClass;
                $update_nodos_update->id = $update_nodos->id;
                /*                 print_r($params);
                print_r($results); */
                if ((is_array($results) || is_object($results)) && property_exists($results, 'ack')) {

                    //if($results->ack == 1 && $update_nodos->cant_courses_actual == $results->response){
                    if (property_exists($results, 'response') && is_int($results->response)) {

                        if ($results->response != $update_nodos->cant_courses_actual) {
                            echo '<br><div style="color: blue"> La cantidad de cursos es diferente en ' . $value->url_hijo . ' </div><br> ';
                        }

                        $params = array(
                            'function' => 'U01',
                            'url_padre' => $url_actual[0],
                            'token' => $value->token,
                            'idCourse_p' => $registro->id_course_sp,
                            'cant_courses' => $value->tot_courses,
                            'id_updates_nodos' => $update_nodos->id,
                            'id_nodo_rel' => $key,
                            'id_updates_log' => $registro->id_updates_log,
                            'obj_act' => json_encode($registro->obj_act),
                        );

                        $update_nodos_update->cant_courses_enhijo = $results->response;
                        $update_nodos_update->estado = 2;
                        $DB->update_record('updates_nodos', $update_nodos_update);

                        $ca = $objCRE->ordenEmpezar($value->url_hijo, $params, $update_nodos_update->cant_courses_enhijo, $value->tot_courses);
                        $update_nodos->cant_courses_terminados += is_int($ca) ? $ca : 0;
                        $update_nodos_update->cant_courses_terminados = $update_nodos->cant_courses_terminados;
                        if ($update_nodos->cant_courses_terminados > 0) {
                            $DB->update_record('updates_nodos', $update_nodos_update);
                        }
                    } else {
                        echo '<h3 style="color: red"> Url_hijo: ';
                        print_r($value->url_hijo);
                        echo '</h3>$results: ';
                        print_r($results);
                        echo '<br> Cantidad de cursos en padre: ';
                        print_r($update_nodos->cant_courses_actual);
                        if (property_exists($results, 'response')) {
                            echo '<br> Cantidad de cursos en hijo: ';
                            print_r($results->response);
                        }
                        $update_nodos_update->estado = 0;
                        $DB->update_record('updates_nodos', $update_nodos_update);
                    }
                } else {
                    print_r($results);
                    $update_nodos_update->estado = 0;
                    $DB->update_record('updates_nodos', $update_nodos_update);
                }
            }
            echo '</ul>';
        } else {
            echo 'No hay nodos para notificar<br>';
        }
        //return $nodos;
    }

    /*
     * Da la orden a los nodos de empezar
     * @param {string} $url_hijo
     * @param {array} $params
     * return {};
     */
    private function ordenEmpezar($url_hijo, $params, $cant_h, $cant_p)
    {
        global $DB;
        $tok = sha1('2017.UVD_TokeN_noDos');
        $url = $url_hijo . '/webservice/rest/server.php?wstoken=' . $tok . '&wsfunction=local_update_empezar_nodos&moodlewsrestformat=json';
        $curl = new curl;
        $results = json_decode($curl->post($url, $params));
        echo "Este es el objeto que se retorna en la ruta update\methods\CRE\class.create.php: " . print_r($results, true);
        echo '<li><h3><strong>URL: ' . $url_hijo . '</strong></h3></li>';
        echo 'URL DE EJECUCIÓN: update\methods\CRE\class.create.php' . '<br>';
        echo 'Cantidad de cursos registrados en el padre: ' . $cant_p . '<br>';
        echo 'Cantidad de cursos registrados en el hijo: ' . $cant_h . '<br>';
        echo 'Hola soy el resultado => ' . print_r(json_encode($results), true) . '<br>';
        echo 'Hola soy el resultado 2 => ' . print_r($results, true) . '<br>';

        if (is_object($results) && property_exists($results, 'ack')) {

            $resp = json_decode($results->response);
            if ((is_array($resp) || is_object($resp)) && property_exists($resp, 'cursos_total')) {
                $arr_no_actua = array_values(array_diff($resp->cursos_total, $resp->cursos_actualizados));
                echo '<div data-toggle="collapse" data-target="#demo1_' . $params['id_nodo_rel'] . '">'
                    . 'Lista de cursos NO actualizados en el hijo: ' . count($arr_no_actua)
                    . '</div>'
                    . '<div class="collapse" id="demo1_' . $params['id_nodo_rel'] . '">' .
                    '-> ' . json_encode($arr_no_actua)
                    . '</div>';

                echo '<div data-toggle="collapse" data-target="#demo2_' . $params['id_nodo_rel'] . '">'
                    . 'Lista de cursos actualizados en el hijo: ' . $results->ack
                    . '</div>'
                    . '<div class="collapse" id="demo2_' . $params['id_nodo_rel'] . '">' .
                    '-> ' . json_encode($resp->cursos_actualizados)
                    . '</div>';
                echo '<div data-toggle="collapse" data-target="#demo3_' . $params['id_nodo_rel'] . '">'
                    . 'Lista de cursos NO actualizados por actividad reemplazada en el hijo: ' . count($resp->cursos_act_propues)
                    . '</div>'
                    . '<div class="collapse" id="demo3_' . $params['id_nodo_rel'] . '">' .
                    '-> ' . json_encode($resp->cursos_act_propues)
                    . '</div><br><br>';
            } else {
                echo ' $results->response ';
                print_r($results->response);
            }

            return $results->ack;
        } else {
            print_r($results);
            return 0;
        }
    }
    /*
     * Da la orden a los nodos de empezar
     * @param {string} $url_hijo
     * @param {array} $params
     * return {};
     */
    private function searchData()
    {
        global $DB, $USER;
        $objCRE = new self();
        $registro = (object)$_POST;

        $result = $DB->get_record_sql('SELECT slo.*, qui.course
                                            FROM {quiz_slots} slo
                                            LEFT JOIN {quiz} qui ON qui.id = slo.quizid 
                                            WHERE slo.id = :slot LIMIT 1', array('slot' => $registro->slot));
        if (!empty($result)) {

            echo json_encode(array(
                'quizid' => $result->quizid,
                'questionid' => $result->questionid,
                'id' => $registro->slot,
                'courseid' => $result->course,
                'userid' => $USER->id
            ));
        }
    }
}
createUpdate::run();
