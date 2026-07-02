<?php
require_once('../../../../config.php');
require_once("$CFG->libdir/filelib.php");

class create
{

    public static function run()
    {
        $objCRE = new self();
        $idfunc = $_POST['key'];
        switch ($idfunc) {
            case 'C01':
                $resp = $objCRE->create_actividad();
                break;
        }
        //echo json_encode($resp);
        //redirect($FULLME);
    }

    private function qryInfoCourses($id_course)
    {
        global $DB;
        $info_course_padre = array();

        $bloques = $DB->get_records_sql('SELECT DISTINCT 
                        blo.id AS id_block_ins, blo.blockname, blo.defaultregion, blo.defaultweight, blo.configdata,
                        cont.id AS id_context, cont.contextlevel, cont.depth
                    FROM {course} c
                    LEFT JOIN {context} cont ON cont.instanceid = c.id
                    LEFT JOIN {block_instances} blo ON blo.parentcontextid = cont.id
                    WHERE c.id = :id_course  
                    AND blo.pagetypepattern = "course-view-*"', array('id_course' => $id_course));

        $scale = $DB->get_records('scale', array('courseid' => $id_course));
        $format_options = $DB->get_records('course_format_options', array('courseid' => $id_course));
        $grade_settings = $DB->get_records('grade_settings', array('courseid' => $id_course));

        $groups = $DB->get_records('groups', array('courseid' => $id_course));
        $groupings = $DB->get_records('groupings', array('courseid' => $id_course));

        $groupings_groups = $DB->get_records_sql('SELECT gr_gr.* 
                                                    FROM {groupings} gr
                                                    LEFT JOIN {groupings_groups} gr_gr ON gr_gr.groupingid = gr.id
                                                    WHERE gr.courseid = :courseid ', array('courseid' => $id_course));

        $periodos = $DB->get_records('grade_categories', array('courseid' => $id_course), $sort = 'depth');
        $grade_items = $DB->get_records('grade_items', array('courseid' => $id_course));

        $info_course_padre['bloques'] = $bloques;
        $info_course_padre['scale'] = $scale;
        $info_course_padre['course_format_options'] = $format_options;
        $info_course_padre['grade_settings'] = $grade_settings;

        $info_course_padre['groups'] = $groups;
        $info_course_padre['groupings'] = $groupings;
        $info_course_padre['groupings_groups'] = $groupings_groups;

        $info_course_padre['grade_categories'] = $periodos;
        $info_course_padre['grade_items'] = $grade_items;


        return json_encode($info_course_padre);
    }

    /*
     * Create token -> saveToken
     * Se une al hijo y crea un token correspondiente
     * Retorna la respuesta exitosa o inválida
     * return {objet};
     */
    private function create_actividad()
    {
        global $DB, $CFG;
        $registro = (object)$_POST;

        $objCRE = new self();
        $data = json_decode($registro->fromform);

        $sav = $objCRE->saveAndReturnActivities($data);

        $registro2 = json_decode($registro->fromform);
        $registro2->coursemodule = $sav->idActividad;
        $registro2->instance = $sav->idInstance;

        if ($sav->modulename == 'assign') {
            $registro2->idAssignPluginConfigP = $sav->idAssignPluginConfigP;
            $registro2->gradingAreasPH = $sav->gradingAreasPH;
            $registro2->contextId = $sav->contextId;
        }

        if ($sav->modulename == 'quiz') {

            $registro2->quiz_sections = $sav->quiz_sections;
            $registro2->quiz_feedback = $sav->quiz_feedback;
            $registro2->table_quiz_feedback = $sav->table_quiz_feedback;
        }

        if ($sav->modulename == 'lti') {
            $registro2->lti_types = $sav->lti_types;
            $registro2->lti_name = $sav->lti_name;
            $registro2->lti_types_config = $sav->lti_types_config;
            $registro2->table_lti_types = $sav->table_lti_types;
        }

        if ($sav->modulename == 'lesson') {
            $registro2->lesson_pages = $sav->lesson_pages;
            $registro2->lesson_answers = $sav->lesson_answers;
        }

        if ($sav->modulename != 'feedback') {
            $registro2->gradeItemPH = $sav->gradeItemPH;
        }

        if ($sav->modulename == 'feedback') {
            $registro2->feedback_item = $sav->feedback_item;
        }

        if ($sav->modulename == 'scorm') {

            $registro2->url_scorm  = $sav->url_scorm;
            $registro2->archivo = $sav->archivo;
            $registro2->id_nodo = $sav->id_nodo;
            $registro2->rel_id = $sav->rel_id;
            $registro2->reference = $sav->reference;
            $registro2->version = $sav->version;
            $registro2->scorm_scoes_table = $sav->scorm_scoes_table;
            $registro2->scorm_scoes_data_table = $sav->scorm_scoes_data_table;
            $registro2->scoes = $sav->scoes;
            $registro2->scoes_data = $sav->scoes_data;
            $registro2->file_dir = $sav->file_dir;
        }

        if ($sav->modulename == 'folder') {

            $registro2->files_fold = $sav->files_fold;
            $registro2->urls_file = $sav->urls_file;
            $registro2->moodle_data_folder = $sav->moodle_data_folder;
            $registro2->name_archive_folder = $sav->name_archive_folder;
            $registro2->files_dir = $sav->files_dir;
            $registro2->files_folder = $sav->files_folder;
            $registro2->rel = $sav->rel;
            $registro2->id_course = $sav->id_course;
        }

        $registro->fromform = json_encode($registro2);

        $array_check = implode(",", $registro->array_check);

        $tb_reg = $DB->get_records_sql('SELECT rel.registroid, COUNT(rel.registroid) AS tot_courses, reg.url_hijo, reg.token, GROUP_CONCAT(courseid_sh) as list
                                        FROM {bc_rel_padre_hijo} rel
                                        LEFT JOIN {bc_registro_pc} reg ON reg.id = rel.registroid 
                                        WHERE rel.courseid_sp = :id_course 
                                        AND reg.url_hijo is not null 
                                        AND reg.estado = 1
                                        AND reg.id IN(' . $array_check . ')
                                        GROUP BY  reg.url_hijo, rel.registroid, reg.token', array('id_course' => $registro->course));

        $update_nodos = new stdClass();
        if (!empty($tb_reg) && count($tb_reg) > 0) {
            $tok = sha1('2017.UVD_TokeN_noDos');

            $update_nodos->id_course_sp = $sav->idActividad;

            $update_nodos->id_log = $sav->id_updates_log;

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
                    'url_padre' => $_SERVER['DOCUMENT_ROOT'],
                    'token' => $value->token,
                    'idCourse_p' => $registro->course,
                    'cant_courses' => $value->tot_courses,
                );
                $curl = new curl;
                $results = json_decode($curl->post($url, $params));
                $update_nodos_update = new stdClass;
                $update_nodos_update->id = $update_nodos->id;

                if ((is_object($results)) && property_exists($results, 'ack')) {

                    if (property_exists($results, 'response') && is_int($results->response)) {

                        if ($results->response != $update_nodos->cant_courses_actual) {
                            echo '<br><div style="color: blue"> La cantidad de cursos es diferente en ' . $value->url_hijo . ' </div><br> ';
                        }
                        $params = array(
                            'function' => 'C11',
                            'url_padre' => $_SERVER['DOCUMENT_ROOT'],
                            'token' => $value->token,
                            'idCourse_p' => $registro->course,
                            'cant_courses' => $value->tot_courses,
                            'id_updates_nodos' => $update_nodos->id,
                            'id_nodo_rel' => $key,
                            'id_updates_log' => $sav->id_updates_log,
                            'obj_act' => ($registro->fromform),
                        );
                        $update_nodos_update->cant_courses_enhijo = $results->response;
                        $update_nodos_update->estado = 2;
                        $DB->update_record('updates_nodos', $update_nodos_update);
                        $ca = $objCRE->ordenEmpezar($value->url_hijo, $params, $update_nodos_update->cant_courses_enhijo, $value->tot_courses, $value->list);
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
        }

        return $results;
    }
    /*
     * Create updates_courses y updates_log -> saveAndReturnActivities
     * Guarda en las tablas el id del curso, de la actividad, el tipo de actividad y el objeto de esta
     * Retorna id de las inserciones 
     * @param {obj} $data
     * return {objet};
     */
    /* Este método permite crear el objeto JSON que se insertará en la tabla de datos bc_rel_padre_hijo */
    private function saveAndReturnActivities($data)
    {
        //Variables Globales
        global $DB, $USER;
        //Datos desde el formulario
        $registro = (object)$_POST;

        $res = new stdClass();
        $updates_courses = new stdClass();
        $updates_courses->id_course_sp = $data->course;

        $idActividad = $DB->get_records_sql("SELECT id, instance FROM {course_modules} where course = $data->course order by id desc limit 1 ");

        foreach ($idActividad as $k => $v) {
            $updates_courses->id_act_sp = $v->id;
            $res->idInstance = $v->instance;
            $res->instance =  $v->id;
        }

        $res->idActividad = $updates_courses->id_act_sp;
        $updates_courses->type_act = $data->modulename . '_create';
        $res->modulename = $data->modulename;

        $registro2 = json_decode($registro->fromform);
        $registro2->coursemodule = $updates_courses->id_act_sp;
        $registro2->instance = $res->idInstance;

        if ($data->modulename == 'assign') {

            $idAssignPluginConfigP = $DB->get_records_sql("SELECT * FROM {assign_plugin_config} WHERE assignment = $res->idInstance");

            foreach ($idAssignPluginConfigP as $key => $value) {
                $registro2->idAssignPluginConfigP[$value->id]["p"] = $value->id;
                $registro2->idAssignPluginConfigP[$value->id]["h"] = "";
            }

            $context = $DB->get_records_sql("SELECT id FROM {context} WHERE instanceid = $res->instance");

            $idContext = 0;

            foreach ($context as $x => $z) {
                $idContext = $z->id;
            }

            $gradingAreas = $DB->get_records_sql("SELECT id FROM {grading_areas} WHERE contextid = $idContext");

            foreach ($gradingAreas as $a => $b) {
                $registro2->gradingAreasPH[$b->id]["p"] = $b->id;
                $registro2->gradingAreasPH[$b->id]["h"] = "";
            }

            /*  fclose($archi); */

            $res->idAssignPluginConfigP = $registro2->idAssignPluginConfigP;

            $res->gradingAreasPH = $registro2->gradingAreasPH;

            $res->contextId = $res->instance;
        }

        if ($data->modulename == 'quiz') {

            $table_quiz = $DB->get_records_sql("SELECT id FROM {quiz} WHERE id = $res->idInstance AND course = $data->course ");

            foreach ($table_quiz as $kq => $vq) {

                $table_quiz_sections = $DB->get_records_sql("SELECT id FROM {quiz_sections} WHERE quizid = $vq->id");
                $table_quiz_feedback = $DB->get_records_sql("SELECT id FROM {quiz_feedback} WHERE quizid = $vq->id");
                $table_quiz_feedback2 = $DB->get_records_sql("SELECT * FROM {quiz_feedback} WHERE quizid = $vq->id");
                $table_quiz_slots = $DB->get_records_sql("SELECT id FROM {quiz_slots} WHERE quizid = $vq->id ");
                $table_questions = $DB->get_records_sql("SELECT q.id FROM {question} q INNER JOIN {quiz_slots} slots ON slots.id = q.id WHERE slots.quizid = $vq->id  ");
            }

            foreach ($table_quiz_sections as $ks => $vqs) {
                $registro2->quiz_sections[$vqs->id]["p"] = $vqs->id;
                $registro2->quiz_sections[$vqs->id]["h"] = "";
            }

            foreach ($table_quiz_feedback as $kf => $vqf) {
                $registro2->quiz_feedback[$vqf->id]["p"] = $vqf->id;
                $registro2->quiz_feedback[$vqf->id]["h"] = "";
            }

            $res->quiz_sections = $registro2->quiz_sections;
            $res->quiz_feedback = $registro2->quiz_feedback;



            $res->table_quiz_feedback = $table_quiz_feedback2;
            $res->table_questions = $table_questions;

            /* $table_quiz_bank = $DB->get_records_sql("SELECT id FROM {quiz} WHERE course = $data->course "); */

            /* foreach ($table_quiz_bank as $kqb => $vqb) { */

            $table_questions_bank = $DB->get_records_sql("SELECT q.questionid FROM {question_versions} q 
            INNER JOIN {question_bank_entries} bank ON bank.id = q.questionbankentryid
            INNER JOIN {quiz_slots} slots  ON slots.id = q.questionbankentryid");



            foreach ($table_questions_bank as $ktqb => $vtqb) {

                $res->bancoPregu->question[$vtqb->questionid]["p"] = $vtqb->questionid;
                $res->bancoPregu->question[$vtqb->questionid]["h"] = "";

                $table_question_answers_bank = $DB->get_records_sql("SELECT id FROM {question_answers} WHERE question = $vtqb->questionid ");

                foreach ($table_question_answers_bank as $ktqas => $vtqas) {
                    $res->bancoPregu->question_answers[$vtqas->id]["p"] = $vtqas->id;
                    $res->bancoPregu->question_answers[$vtqas->id]["h"] = "";
                }

                $table_question_truefalse_bank = $DB->get_records_sql("SELECT id FROM {question_truefalse} WHERE question = $vtqb->questionid ");

                foreach ($table_question_truefalse_bank as $ktqtb => $vtqtb) {
                    $res->bancoPregu->question_truefalse[$vtqtb->id]["p"] = $vtqtb->id;
                    $res->bancoPregu->question_truefalse[$vtqtb->id]["h"] = "";
                }

                /* $res->bancoPregu->question_bank_entries */
            }

            $contPos = 0;
            $contPos2 = 0;

            $table_context_bank = $DB->get_records_sql("SELECT id FROM {context} WHERE instanceid = $data->course AND contextlevel = 50 ");
            /* $res->bancoPregu->question_categories[$contPos] = $res->idInstance; */
            foreach ($table_context_bank as $ktcb => $vtcb) {

                $table_question_categories_bank = $DB->get_records_sql("SELECT * FROM {question_categories} WHERE contextid = $vtcb->id ");

                foreach ($table_question_categories_bank as $ktqcb => $vtcqcb) {
                    $res->bancoPregu->question_categories[$contPos]["p"] = $vtcqcb->id;
                    $res->bancoPregu->question_categories[$contPos]["h"] = "";
                    $contPos++;
                    if ($vtcqcb->parent != 0) {
                        $table_question_bank_entries_bank = $DB->get_records_sql("SELECT id FROM {question_bank_entries} WHERE questioncategoryid = $vtcqcb->id ");
                        foreach ($table_question_bank_entries_bank as $ktqbeb => $vtqbeb) {
                            $res->bancoPregu->question_bank_entries[$contPos2]["p"] = $vtqbeb->id;
                            $res->bancoPregu->question_bank_entries[$contPos2]["h"] = "";
                            $contPos2++;
                            $table_question_versions_bank = $DB->get_records_sql("SELECT id FROM {question_versions} WHERE questionbankentryid = $vtqbeb->id ");
                            foreach ($table_question_versions_bank as $ktqvb => $vtqvb) {
                                $res->bancoPregu->question_versions[$vtqvb->id]["p"] = $vtqvb->id;
                                $res->bancoPregu->question_versions[$vtqvb->id]["h"] = "";
                            }
                        }
                    }
                }
            }
        }

        if ($data->modulename == 'lti') {

            $table_lti = $DB->get_record('lti', array('id' => $res->idInstance));

            $table_lti_types = $DB->get_record('lti_types', array('id' => $table_lti->typeid));

            $registro2->lti_types->p = $table_lti_types->id;
            $registro2->lti_types->h = "";

            $res->lti_types = $registro2->lti_types;
            $res->lti_name = $table_lti_types->name;

            $res->table_lti_types = $table_lti_types;

            $res->lti_types_config = $DB->get_records('lti_types_config', array('typeid' => $table_lti_types->id));
        }

        if ($data->modulename == 'feedback') {

            $registro2->feedback_item = "";
            $res->feedback_item = "";
        }

        if ($data->modulename == 'lesson') {

            $registro2->lesson_pages[$res->idInstance]["p"] =  "";
            $registro2->lesson_pages[$res->idInstance]["h"] =  "";

            $res->lesson_pages = $registro2->lesson_pages;

            $registro2->lesson_answers[$res->idInstance]["p"] =  "";
            $registro2->lesson_answers[$res->idInstance]["h"] =  "";

            $res->lesson_answers = $registro2->lesson_answers;
        }

        $gradeItemPH = $DB->get_records_sql("SELECT id FROM {grade_items} WHERE iteminstance = $res->idInstance AND itemmodule = '$data->modulename' ");

        $conta = 0;
        foreach ($gradeItemPH as $ka => $val) {
            $registro2->gradeItemPH[$conta][0]["p"] = $val->id;
            $registro2->gradeItemPH[$conta][0]["h"] = "";
            $conta++;
        }

        if (!empty($gradeItemPH)) {
            $res->gradeItemPH = $registro2->gradeItemPH;
        }

        if ($data->modulename == 'folder') {

            global $CFG;

            $array_check = $registro->array_check[0];

            $array_url = array();

            $array_name_archive = array();

            $array_moodle_data = array();

            $array_files_dir = array();

            $array_files_folder = array();

            $array_rel = array();

            $tb_reg = $DB->get_records_sql('SELECT rel.registroid, COUNT(rel.registroid) AS tot_courses, reg.url_hijo, reg.token, GROUP_CONCAT(courseid_sh) as list
                                        FROM {bc_rel_padre_hijo} rel
                                        LEFT JOIN {bc_registro_pc} reg ON reg.id = rel.registroid 
                                        WHERE rel.courseid_sp = :id_course 
                                        AND reg.url_hijo is not null 
                                        AND reg.estado = 1
                                        AND reg.id IN(' . $array_check . ')
                                        GROUP BY  reg.url_hijo, rel.registroid, reg.token', array('id_course' => $registro->course));

            foreach ($tb_reg as $key => $value) {

                $rel = $DB->get_record('bc_registro_pc', array('url_hijo' => $value->url_hijo));

                array_push($array_rel, $rel->id);

                require_once '../../folder_S3/controlador2_m.php';

                $s3 = new Controlador2_m();

                $id_course = $registro->course;

                $res->id_course = $id_course;

                $folder = $DB->get_record('folder', array('id' => $res->idInstance));

                $course_mod = $DB->get_record('course_modules', array('instance' => $folder->id, 'course' => $folder->course));

                $context_fold = $DB->get_record('context', array('instanceid' => $course_mod->id));

                $files_fold = $DB->get_records('files', array(
                    'contextid' => $context_fold->id,
                    'component' => 'mod_folder',
                    'filearea' => 'content'
                ));

                $res->files_fold = $files_fold;

                foreach ($files_fold as $kf => $vkf) {

                    $archivo = $vkf->contenthash;

                    $ext = pathinfo($vkf->filename, PATHINFO_EXTENSION);

                    $name_archive = "";

                    if (!empty($ext)) {
                        $name_archive = $archivo . '_' . $id_course . '_' . $rel->id . "." . $ext;
                        array_push($array_name_archive, $name_archive);
                    } else {
                        $name_archive = $archivo . '_' . $id_course . '_' . $rel->id;
                        array_push($array_name_archive, $name_archive);
                    }

                    $id = $vkf->id;

                    $registro2->files_folder[$id]["p"] = $id;
                    $registro2->files_folder[$id]["h"] = "";

                    $cr1 = substr($archivo, 0, 2);
                    $cr2 = substr($archivo, 2, 2);

                    $file_dir = '/filedir/' . $cr1 . '/' . $cr2 . '/' . $archivo;

                    array_push($array_files_dir, $file_dir);

                    $moodle_data = $CFG->dataroot . '/filedir/' . $cr1 . '/' . $cr2 . '/' . $archivo;

                    array_push($array_moodle_data, $moodle_data);

                    array_push($array_url, $s3->run('create', $moodle_data, $name_archive, $id_course));
                }
            }

            $res->urls_file = $array_url;
            $res->moodle_data_folder = $array_moodle_data;
            $res->name_archive_folder = $array_name_archive;
            $res->files_dir = $array_files_dir;
            $res->files_folder = $registro2->files_folder;
            $res->rel = $array_rel;
        }

        if ($data->modulename == 'scorm') {

            global $CFG;

            $array_check = $registro->array_check[0];

            $tb_reg = $DB->get_records_sql('SELECT rel.registroid, COUNT(rel.registroid) AS tot_courses, reg.url_hijo, reg.token, GROUP_CONCAT(courseid_sh) as list
                                        FROM {bc_rel_padre_hijo} rel
                                        LEFT JOIN {bc_registro_pc} reg ON reg.id = rel.registroid 
                                        WHERE rel.courseid_sp = :id_course 
                                        AND reg.url_hijo is not null 
                                        AND reg.estado = 1
                                        AND reg.id IN(' . $array_check . ')
                                        GROUP BY  reg.url_hijo, rel.registroid, reg.token', array('id_course' => $registro->course));

            foreach ($tb_reg as $key => $value) {

                $scorm = $DB->get_record('scorm', array('id' => $res->idInstance));

                $id_course = $registro->course;

                $archivo = $scorm->sha1hash;

                $rel = $DB->get_record('bc_registro_pc', array('url_hijo' => $value->url_hijo));

                $cr1 = substr($archivo, 0, 2);
                $cr2 = substr($archivo, 2, 2);
                $name_archive = $archivo . '_' . $id_course . '_' . $rel->id . '.zip';
                $moodle_data = $CFG->dataroot . '/filedir/' . $cr1 . '/' . $cr2 . '/' . $archivo;

                require_once '../../folder_S3/controlador2_m.php';
                $s3 = new Controlador2_m();
                $res->url_scorm = $s3->run('create', $moodle_data, $name_archive, $id_course);
                $res->file_dir = '/filedir/' . $cr1 . '/' . $cr2 . '/' . $archivo;
                $res->id_nodo = $id_course;
                $res->rel_id = $rel->id;
                $res->archivo = $archivo;
                $res->reference = $scorm->reference;
                $res->version = $scorm->version;

                $res->scorm_scoes_table = $DB->get_records('scorm_scoes', array('scorm' => $scorm->id));

                $array_scorm_scoes_data_table = array();

                foreach ($res->scorm_scoes_table as $ksst => $vsst) {

                    $registro2->scoes[$vsst->id]["p"] = $vsst->id;

                    $registro2->scoes[$vsst->id]["h"] = "";

                    $ins = $DB->get_records('scorm_scoes_data', array('scoid' => $vsst->id));

                    if (!empty($ins)) {

                        array_push($array_scorm_scoes_data_table, $ins);
                    }
                }

                $res->scorm_scoes_data_table = (object)$array_scorm_scoes_data_table;

                foreach ($res->scorm_scoes_data_table as $ksc => $vsc) {

                    foreach ($vsc as $kvsc => $vvsc) {
                        $registro2->scoes_data[$vvsc->id]["p"] = $vvsc->id;
                        $registro2->scoes_data[$vvsc->id]["h"] = "";
                    }
                }

                $res->scoes = $registro2->scoes;
                $res->scoes_data = $registro2->scoes_data;
            }
        }

        if ($data->modulename == 'h5pactivity') {

            $array_check = $registro->array_check[0];

            $tb_reg = $DB->get_records_sql('SELECT rel.registroid, COUNT(rel.registroid) AS tot_courses, reg.url_hijo, reg.token, GROUP_CONCAT(courseid_sh) as list
                                        FROM {bc_rel_padre_hijo} rel
                                        LEFT JOIN {bc_registro_pc} reg ON reg.id = rel.registroid 
                                        WHERE rel.courseid_sp = :id_course 
                                        AND reg.url_hijo is not null 
                                        AND reg.estado = 1
                                        AND reg.id IN(' . $array_check . ')
                                        GROUP BY  reg.url_hijo, rel.registroid, reg.token', array('id_course' => $registro->course));


            $h5p_files = $DB->get_records_sql(
                'select fil.*,cm.id AS id_como_p from {course_modules} cm
                    inner join {context} c on (c.instanceid = cm.id and c.contextlevel = 70)
                    inner join {files} fil on (fil.contextid = c.id and fil.filename <> ".")
                    where cm.course = :id_course and cm.module = (select m.id from {modules} m where m.name ="h5pactivity") order by fil.contenthash',
                array('id_course' => $registro->course)
            );


            foreach ($tb_reg as $key => $valueTable) {

                if (!empty($h5p_files)) {

                    $rel = $DB->get_record('bc_registro_pc', array('url_hijo' => $valueTable->url_hijo));

                    $zip = new ZipArchive();

                    $filename = $CFG->dataroot . '/temp/course_' . $rel->id . '.zip'; //s3

                    if ($zip->open($filename, ZIPARCHIVE::CREATE) === true) {
                        if (!empty($h5p_files)) {
                            foreach ($h5p_files as $key => $value) {
                                $archivo = $value->contenthash;
                                $cr1 = substr($archivo, 0, 2);
                                $cr2 = substr($archivo, 2, 2);
                                //$name_archive = $value->pathnamehash.'_'.$params['id_nodo'];
                                $moodle_data = $CFG->dataroot . '/filedir/' . $cr1 . '/' . $cr2 . '/' . $archivo;
                                $zip->addFile($moodle_data, $value->filename);
                            }
                        }
                        if (!empty($hvp_files)) {
                            foreach ($hvp_files as $key => $value) {
                                $archivo = $value->contenthash;
                                $cr1 = substr($archivo, 0, 2);
                                $cr2 = substr($archivo, 2, 2);
                                //$name_archive = $value->pathnamehash.'_'.$params['id_nodo'];
                                $moodle_data = $CFG->dataroot . '/filedir/' . $cr1 . '/' . $cr2 . '/' . $archivo;
                                $zip->addFile($moodle_data, $value->filename);
                            }
                        }

                        $zip->close();
                    } else {
                        $activities['h5p_files'] =  'Error creando ' . $filename;
                    }

                    if (file_exists($filename)) {
                        $name_archive = 'course_' . $rel->id . '.zip';
                        require_once $CFG->dirroot . '/local/backup_course/folder_S3/controlador2_m.php';
                        $s3 = new Controlador2_m();
                        $crear = $s3->run('create', $filename, $name_archive, $rel->id);

                        if ($crear) {
                            $activities['h5p_files'] = $h5p_files;
                            $activities['hvp_files'] = $hvp_files;
                            if (file_exists($filename)) {
                                unlink($filename);
                            }
                        }
                    }
                }
            }
        }

        $registro->fromform = json_encode($registro2);
        $updates_courses->obj_act = $registro->fromform;

        $res->id_updates_courses = $DB->insert_record('updates_courses', $updates_courses);
        $updates_courses->id_update = $res->id_updates_courses;
        $updates_courses->time_update = time();
        $updates_courses->id_user = $USER->id;
        $res->id_updates_log = $DB->insert_record('updates_log', $updates_courses);

        return $res;
    }

    /*
     * Da la orden a los nodos de empezar a crear y mostrar reporte
     * @param {string} $url_hijo
     * @param {array} $params
     * @param {int} $cant_h
     * @param {int} $cant_p
     * @param {string} $list
     * return {};
     */
    private function ordenEmpezar($url_hijo, $params, $cant_h, $cant_p, $list)
    {
        
        global $DB;
        $tok = sha1('2017.UVD_TokeN_noDos');
        $url = $url_hijo . '/webservice/rest/server.php?wstoken=' . $tok . '&wsfunction=local_update_empezar_nodos&moodlewsrestformat=json';
        $curl = new curl;
        $results = json_decode($curl->post($url, $params));
        echo '<li><h3><strong>URL: ' . $url_hijo . '</strong></h3></li>';
        echo 'URL DE EJECUCIÓN: create\methods\class.create.php' . '<br>';
        echo 'Cantidad de cursos registrados en el padre: ' . $cant_p . '<br>';
        echo 'Cantidad de cursos registrados en el hijo: ' . $cant_h . '<br>';
        echo '<div data-toggle="collapse" data-target="#demo0_' . $params['id_nodo_rel'] . '">'
            . 'Lista de cursos registrados en el Padre newwww: '
            . '</div>'
            . '<div class="collapse" id="demo0_' . $params['id_nodo_rel'] . '">' .
            '-> ' . json_encode($list)
            . '</div>';

        if ((is_object($results)) && property_exists($results, 'ack')) {

            $resp = json_decode($results->response);
            if ((is_array($resp) || is_object($resp)) && property_exists($resp, 'cursos_total')) {
                $arr_no_actua = array_values(array_diff($resp->cursos_total, $resp->cursos_actualizados));
                echo '<div data-toggle="collapse" data-target="#demo1_' . $params['id_nodo_rel'] . '">'
                    . 'Lista de cursos NO actualizados_create en el hijo: ' . count($arr_no_actua)
                    . '</div>'
                    . '<div class="collapse" id="demo1_' . $params['id_nodo_rel'] . '">' .
                    '-> ' . json_encode($arr_no_actua)
                    . '</div>';

                echo '<div data-toggle="collapse" data-target="#demo2_' . $params['id_nodo_rel'] . '">'
                    . 'Lista de cursos actualizados_create en el hijo: ' . $results->ack
                    . '</div>'
                    . '<div class="collapse" id="demo2_' . $params['id_nodo_rel'] . '">' .
                    '-> ' . json_encode($resp->cursos_actualizados)
                    . '</div>';
                echo '<div data-toggle="collapse" data-target="#demo3_' . $params['id_nodo_rel'] . '">'
                    . 'Lista de cursos NO actualizados_create por actividad reemplazada en el hijo: ' . count($resp->cursos_act_propues)
                    . '</div>'
                    . '<div class="collapse" id="demo3_' . $params['id_nodo_rel'] . '">' .
                    '-> ' . json_encode($resp->cursos_act_propues)
                    . '</div><br><br>';
            } else {
                echo ' $results->response ';
                print_r($results->response);
            }

            $cursosJsonObj = json_decode(($results->object_p));
            $insert = new stdClass();

            foreach ($cursosJsonObj as $k => $v) {
                $insert->id = $v->id;
                $insert->objet_ph = ($v->objet_ph);
            }

            $DB->update_record('bc_rel_padre_hijo', $insert);

            return $results->ack;
        } else {
            echo 'aqqqu';
            print_r($results);
            return 0;
        }
    }
}
create::run();
