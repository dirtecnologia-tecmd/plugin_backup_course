<?php
require_once('../../../../../config.php');
require_once("$CFG->libdir/filelib.php");

class updateUpdate
{

    public static function run()
    {
        $obj = new self();
        $idfunc = $_POST['key'];
        switch ($idfunc) {
            case 'U01':
                $resp = $obj->updateLibroCalificaciones();
                break;
            case 'U02':
                $resp = $obj->update_actividad_propuesta_en_padre();
                break;
            case 'U03':
                $resp = $obj->update_actividad_propuesta_en_hijos();
                break;
        }

        //redirect($FULLME);
    }

    /* 
     * Actualizar la actividad en los hijos
     */
    private function update_actividad_propuesta_en_hijos() {}


    /* 
     * Actualizar la actividad en el servidosr padre
     */
    private function update_actividad_propuesta_en_padre()
    {
        global $DB;
        $objUPD = new self();
        $registro = (object)$_POST;
        $objeto = json_decode($_POST['objeto']);
        $actividad = json_decode($_POST['actividad']);
        if ($DB->update_record($registro->type, $actividad)) {
            $ob = new stdClass();
            $ob->id = $objeto->id_ob;
            $ob->obj = $objeto->obj;
            if ($DB->update_record('act_obj_create', $ob)) {
                $ac = new stdClass();
                $ac->id = $objeto->id_pro;
                $ac->actividad = $objeto->actividad;
                if ($DB->update_record('act_activity_propuesta', $ac)) {
                    if ($DB->update_record('act_activities_uvd', array('id' => $objeto->id, 'name' => $objeto->name))) {
                        rebuild_course_cache($objeto->courseid_h);
                        $result = $DB->get_record_sql(
                            'SELECT co.* 
                                                        FROM {course_modules} co
                                                        LEFT JOIN {modules} mo ON mo.id = co.module
                                                        WHERE co.course = :course AND co.instance = :instance AND mo.name = :module',
                            array('course' => $objeto->courseid_h, 'instance' => $actividad->id, 'module' => $registro->type)
                        );

                        $objUPD->enviar_actualizacion_acti_propuesta($result->id, $actividad, $objeto->courseid_h);
                    } else {
                        echo 'act_activities_uvd no actualizado';
                        echo '<br>$objeto: ';
                        print_r($objeto);
                        echo '<br>$actividad: ';
                        print_r($actividad);
                    }
                } else {
                    echo 'act_activity_propuesta no actualizado';
                    echo '<br>$objeto: ';
                    print_r($objeto);
                    echo '<br>$actividad: ';
                    print_r($actividad);
                }
            } else {
                echo 'act_obj_create no actualizado';
                echo '<br>$objeto: ';
                print_r($objeto);
                echo '<br>$actividad: ';
                print_r($actividad);
            }
        } else {
            echo 'Actividad ' . $registro->type . ' no actualizada';
            echo '<br>$objeto: ';
            print_r($objeto);
            echo '<br>$actividad: ';
            print_r($actividad);
        }
    }

    private function enviar_actualizacion_acti_propuesta($id_como, $actividad, $courseid)
    {
        global $DB, $USER, $CFG;
        $cm  = get_coursemodule_from_id('', $id_como, 0, true, MUST_EXIST);
        $myHTML = addslashes(json_encode($actividad));
        $converted = strtr($myHTML, array_flip(get_html_translation_table(HTML_ENTITIES, ENT_QUOTES)));
        $data = trim($converted, chr(0xC2) . chr(0xA0));
        echo json_encode(array($courseid, $cm->id, $cm->modname, $USER->id, $cm->sectionnum, $CFG->wwwroot . '/course/view.php?id=' . $courseid . '#section=' . $cm->sectionnum));
        //echo '<script>SLog.confir_nodos_actu('.$courseid.', '.$cm->id.', "'.$cm->modname.'", '.$USER->id.',"'.addslashes(json_encode($actividad)).'", '.$cm->sectionnum.', "../../", "'.$CFG->wwwroot.'/course/view.php?id='.$courseid.'#section='.$cm->sectionnum.'");</script>';
        //echo html_writer::script('SLog.confir_nodos_actu('.$courseid.', '.$cm->id.', "'.$cm->modname.'", '.$USER->id.',"'.addslashes(json_encode($actividad)).'", '.$cm->sectionnum.', "../../", "'.$CFG->wwwroot.'/course/view.php?id='.$courseid.'#section='.$cm->sectionnum.'");'     );


    }

    /* 
     * Create Relation in course_modules and sections sequence -> saveSequence
     * Actualiza la secuencia de las actividades creadas en el hijo, para las secciones
     */
    private function updateLibroCalificaciones()
    {
        global $DB;
        $registro = (object)$_POST;
        $deleteItem = null;

        if (property_exists($registro, 'deleteItem')) {
            $deleteItem = $registro->deleteItem;
            $registro->obCourse['grade_items_delete'] = ["delete" => $deleteItem];
        }

        $objUPD = new self();

        if (array_key_exists('grade_items', $registro->obCourse)) {
            echo $registro->idCourse_p . ' <br>';
            $result = $DB->get_records_sql(
                'SELECT id,itemname,itemtype,itemmodule,categoryid,grademax,grademin,gradepass,multfactor,plusfactor,aggregationcoef,aggregationcoef2,sortorder,weightoverride 
                                            FROM {grade_items} 
                                            WHERE courseid = :courseid',
                array('courseid' => $registro->idCourse_p)
            );

            if (!empty($result)) {

                $registro->obCourse['grade_items'] = $result;
            }
            //Nota se está es enviando a grade_categories
        }

        if (array_key_exists('grade_categories', $registro->obCourse)) {
            $result = $DB->get_records('grade_categories', array('courseid' => $registro->idCourse_p));
            if (!empty($result)) {

                $idReg = $registro->obCourse['grade_categories']['id'];

                $registro->obCourse['grade_categories'] = $result;

                $res = $DB->get_records('grade_items', ['itemtype' => 'category', 'iteminstance' => $idReg]);
                if (!empty($res)) {
                    $registro->obCourse['grade_items'] = $res;
                }
            }
        }

        if (array_key_exists('groups', $registro->obCourse)) {
            $result = $DB->get_records('groups', array('courseid' => $registro->idCourse_p));
            if (!empty($result)) {
                $registro->obCourse['groups'] = $result;
            }
        }
        if (array_key_exists('groupings_groups', $registro->obCourse)) {
            $result = $DB->get_records('groupings_groups', array('groupingid' => $registro->obCourse['groupings']['id']));
            if (!empty($result)) {
                $registro->obCourse['groupings_groups'] = $result;
                unset($registro->obCourse['groupings']);
            }
        }
        if (array_key_exists('groupings', $registro->obCourse)) {
            $result = $DB->get_records('groupings', array('courseid' => $registro->idCourse_p));
            if (!empty($result)) {
                $registro->obCourse['groupings'] = $result;
            }
        }

        if (array_key_exists('question_categories', $registro->obCourse)) {

            $result = $DB->get_records_sql('SELECT q_cat.id, q_cat.name, q_cat.info, q_cat.infoformat, q_cat.stamp, q_cat.parent, q_cat.sortorder
                                                FROM {question_categories} q_cat
                                                LEFT JOIN {context} cont ON cont.id = q_cat.contextid
                                                WHERE cont.contextlevel = 50 AND cont.instanceid = :instanceid ', array('instanceid' => $registro->idCourse_p));

            if (!empty($result)) {
                $registro->obCourse['question_categories'] = $result;
            }
        }

        if (array_key_exists('question_in_quiz', $registro->obCourse)) {
            if (array_key_exists('id', $registro->obCourse['question_in_quiz']) && $registro->obCourse['question_in_quiz']['id']) {
                $result = $DB->get_record('quiz_slots', array('id' => $registro->obCourse['question_in_quiz']['id']));
                if (!empty($result)) {
                    $registro->obCourse['slots'] = $result;
                }
            } else {

                //$result = $DB->get_record('quiz_slots',array('questionid'=>$registro->obCourse['question_in_quiz']['questionid'], 'quizid' => $registro->obCourse['question_in_quiz']['quizid']));
                $result = $DB->get_records('quiz_slots', array('quizid' => $registro->obCourse['question_in_quiz']['quizid']));

                if (!empty($result)) {
                    $registro->obCourse['slots'] = $result;



                    $registro->obCourse['question']['id'] = $registro->obCourse['question_in_quiz']['questionid'];
                }
            }
        }

        if (array_key_exists('quiz_slots', $registro->obCourse)) {

            $result = $DB->get_record('quiz_slots', array('id' => $registro->obCourse['quiz_slots']['lotid']));
            if (!empty($result)) {
                $registro->obCourse['quiz_slots'] = $result;
            }
        }

        if (array_key_exists('question', $registro->obCourse)) {

            $question = $DB->get_record('question', array('id' => $registro->obCourse['question']['id']));

            $versi = $DB->get_record('question_versions', array('questionid' => $registro->obCourse['question']['id']));

            if (!empty($question)) {

                $qcat = $DB->get_record_sql("SELECT q_cat.id as id_categoria, q_cat.name, q_cat.contextid, q_cat.info, q_cat.infoformat, 
                q_cat.stamp, q_cat.parent, q_cat.sortorder, entries.id as bank_entry
                FROM {question} as question
                INNER JOIN {question_versions} as versions ON versions.questionid = question.id
                INNER JOIN {question_bank_entries} as entries ON entries.id = versions.questionbankentryid
                INNER JOIN {question_categories} as q_cat ON q_cat.id = entries.questioncategoryid
                INNER JOIN {context} as cont ON cont.id = q_cat.contextid
                WHERE cont.contextlevel = 50
                AND cont.instanceid = $registro->idCourse_p
                AND question.id = $question->id
                AND q_cat.name != 'top' ");

                $registro->obCourse['question']['question'] = $question;

                $registro->obCourse['question']['question_categories'] = $qcat;

                $idBankEntries = $DB->get_record_sql("SELECT id FROM {question_bank_entries} WHERE questioncategoryid = $qcat->id_categoria AND id = $qcat->bank_entry");

                $registro->obCourse['question']['idBankEntries'] = (object) ["id" => $idBankEntries->id];

                $registro->obCourse['question']['question_bank_entries'] = $DB->get_record_sql("SELECT * FROM {question_bank_entries} WHERE questioncategoryid = $qcat->id_categoria AND id = $qcat->bank_entry ");

                $registro->obCourse['question']['question_versions'] = $versi;

                $registro->obCourse['question']['question_bank_entries2'] = $DB->get_record_sql("SELECT * FROM {question_bank_entries} WHERE questioncategoryid = $qcat->id_categoria AND id = $qcat->bank_entry ");

                $id_question = $question->id;

                $registro->obCourse['question']['question_answers']            = $DB->get_records('question_answers', array('question' => $id_question));
                $registro->obCourse['question']['question_truefalse']          = $DB->get_records('question_truefalse', array('question' => $id_question));
                $registro->obCourse['question']['question_multianswer']        = $DB->get_records('question_multianswer', array('question' => $id_question));
                $registro->obCourse['question']['qtype_ddimageortext']         = $DB->get_records('qtype_ddimageortext', array('questionid' => $id_question));
                $registro->obCourse['question']['qtype_ddimageortext_drags']   = $DB->get_records('qtype_ddimageortext_drags', array('questionid' => $id_question));
                $registro->obCourse['question']['qtype_ddimageortext_drops']   = $DB->get_records('qtype_ddimageortext_drops', array('questionid' => $id_question));
                $registro->obCourse['question']['qtype_ddmarker']              = $DB->get_records('qtype_ddmarker', array('questionid' => $id_question));
                $registro->obCourse['question']['qtype_ddmarker_drags']        = $DB->get_records('qtype_ddmarker_drags', array('questionid' => $id_question));
                $registro->obCourse['question']['qtype_ddmarker_drops']        = $DB->get_records('qtype_ddmarker_drops', array('questionid' => $id_question));
                $registro->obCourse['question']['qtype_essay_options']         = $DB->get_records('qtype_essay_options', array('questionid' => $id_question));
                $registro->obCourse['question']['qtype_match_options']         = $DB->get_records('qtype_match_options', array('questionid' => $id_question));
                $registro->obCourse['question']['qtype_match_subquestions']    = $DB->get_records('qtype_match_subquestions', array('questionid' => $id_question));
                $registro->obCourse['question']['qtype_multichoice_options']   = $DB->get_records('qtype_multichoice_options', array('questionid' => $id_question));
                $registro->obCourse['question']['qtype_randomsamatch_options'] = $DB->get_records('qtype_randomsamatch_options', array('questionid' => $id_question));
                $registro->obCourse['question']['qtype_shortanswer_options']   = $DB->get_records('qtype_shortanswer_options', array('questionid' => $id_question));
            }
        }

        $table_questions_bank = $DB->get_records_sql("SELECT q.questionid FROM {question_versions} q 
            INNER JOIN {question_bank_entries} bank ON bank.id = q.questionbankentryid
            INNER JOIN {quiz_slots} slots  ON slots.id = q.questionbankentryid");

        foreach ($table_questions_bank as $ktqb => $vtqb) {

            $registro->obCourse["bancoPregu"]["question"][$vtqb->questionid]["p"] = $vtqb->questionid;
            $registro->obCourse["bancoPregu"]["question"][$vtqb->questionid]["h"] = "";

            $table_question_answers_bank = $DB->get_records_sql("SELECT id FROM {question_answers} WHERE question = $vtqb->questionid ");

            foreach ($table_question_answers_bank as $ktqas => $vtqas) {
                $registro->obCourse["bancoPregu"]["question_answers"][$vtqas->id]["p"] = $vtqas->id;
                $registro->obCourse["bancoPregu"]["question_answers"][$vtqas->id]["h"] = "";
            }

            $table_question_truefalse_bank = $DB->get_records_sql("SELECT id FROM {question_truefalse} WHERE question = $vtqb->questionid ");

            foreach ($table_question_truefalse_bank as $ktqtb => $vtqtb) {
                $registro->obCourse["bancoPregu"]["question_truefalse"][$vtqtb->id]["p"] = $vtqtb->id;
                $registro->obCourse["bancoPregu"]["question_truefalse"][$vtqtb->id]["h"] = "";
            }

            /* $res->bancoPregu->question_bank_entries */
        }

        $contPos = 0;
        $contPos2 = 0;

        $table_context_bank = $DB->get_records_sql("SELECT id FROM {context} WHERE instanceid = $registro->idCourse_p AND contextlevel = 50 ");
        /* $res->bancoPregu->question_categories[$contPos] = $res->idInstance; */

        foreach ($table_context_bank as $ktcb => $vtcb) {

            $table_question_categories_bank = $DB->get_records_sql("SELECT * FROM {question_categories} WHERE contextid = $vtcb->id ");

            foreach ($table_question_categories_bank as $ktqcb => $vtcqcb) {
                $registro->obCourse["bancoPregu"]["question_categories"][$contPos]["p"] = $vtcqcb->id;
                $registro->obCourse["bancoPregu"]["question_categories"][$contPos]["h"] = "";
                $contPos++;
                if ($vtcqcb->parent != 0) {
                    $table_question_bank_entries_bank = $DB->get_records_sql("SELECT id FROM {question_bank_entries} WHERE questioncategoryid = $vtcqcb->id ");
                    foreach ($table_question_bank_entries_bank as $ktqbeb => $vtqbeb) {
                        $registro->obCourse["bancoPregu"]["question_bank_entries"][$contPos2]["p"] = $vtqbeb->id;
                        $registro->obCourse["bancoPregu"]["question_bank_entries"][$contPos2]["h"] = "";
                        $contPos2++;
                        $table_question_versions_bank = $DB->get_records_sql("SELECT id FROM {question_versions} WHERE questionbankentryid = $vtqbeb->id ");
                        foreach ($table_question_versions_bank as $ktqvb => $vtqvb) {
                            $registro->obCourse["bancoPregu"]["question_versions"][$vtqvb->id]["p"] = $vtqvb->id;
                            $registro->obCourse["bancoPregu"]["question_versions"][$vtqvb->id]["h"] = "";
                        }
                    }
                }
            }
        }

        //$registro->obCourse["bancoPregu"]["padre_prueba"] = "dato desde el padre";

        $array_check = implode(",", $registro->array_check);

        $tb_reg = $DB->get_records_sql(
            'SELECT rel.registroid, COUNT(rel.registroid) AS tot_courses, reg.url_hijo, reg.token
                                        FROM {bc_rel_padre_hijo} rel
                                        LEFT JOIN {bc_registro_pc} reg ON reg.id = rel.registroid 
                                        WHERE rel.courseid_sp = :id_course 
                                        AND reg.url_hijo is not null 
                                        AND reg.estado = 1
                                        AND reg.id IN(' . $array_check . ')
                                        GROUP BY   reg.url_hijo, rel.registroid, reg.token',
            array('id_course' => $registro->idCourse_p)
        );

        $DB->update_record('updates_courses', array('id' => $registro->id_updates_courses, 'obj_act' => json_encode($registro->obCourse)));

        $update_nodos = new stdClass();
        $nodos = new stdClass();

        if (!empty($tb_reg) && count($tb_reg) > 0) {
            $tok = sha1('2017.UVD_TokeN_noDos');
            $url_actual = explode('/course/', $_SERVER['HTTP_REFERER']);
            if (strpos($_SERVER['HTTP_REFERER'], '/mod/lesson/')) {
                $url_actual = explode('/mod/', $_SERVER['HTTP_REFERER']);
            }
            $update_nodos->id_course_sp = $registro->idCourse_p;
            $update_nodos->id_log = $registro->id_updates_log;

            $update_nodos->estado = 1;
            $update_nodos->cant_courses_terminados = 0;
            echo '<ul>';

            $contador = 0;

            foreach ($tb_reg as $key => $value) {

                $update_nodos->id_nodo_rel = $key;
                $update_nodos->cant_courses_actual = $value->tot_courses;
                $update_nodos->cant_courses_enhijo = 0;
                $update_nodos->id = $DB->insert_record('updates_nodos', $update_nodos);

                $url = $value->url_hijo . '/webservice/rest/server.php?wstoken=' . $tok . '&wsfunction=local_update_notificar_nodos&moodlewsrestformat=json';
                $params = array(
                    'url_padre' => $url_actual[0],
                    'token' => $value->token,
                    'idCourse_p' => $registro->idCourse_p,
                    'cant_courses' => $value->tot_courses,
                );
                $curl = new curl;
                $results = json_decode($curl->post($url, $params)); //cantidad de cursos
                $update_nodos_update = new stdClass;
                $update_nodos_update->id = $update_nodos->id;

                if (property_exists($results, 'ack')) {

                    if ($results->ack == 1 && $update_nodos->cant_courses_actual == $results->response) {
                        $context = context_course::instance($registro->idCourse_p);
                        $format_options = $DB->get_records('course_format_options', ['courseid' => $registro->idCourse_p]);
                        $block_options = $DB->get_record('block_instances', ['parentcontextid' => $context->id, 'blockname' => 'bloque_recursos']);

                        $params = array(
                            'function' => 'U04',
                            'url_padre' => $url_actual[0],
                            'token' => $value->token,
                            'idCourse_p' => $registro->idCourse_p,
                            'cant_courses' => $value->tot_courses,
                            'id_updates_nodos' => $update_nodos->id,
                            'id_nodo_rel' => $key,
                            'id_updates_log' => $registro->id_updates_log,
                            'obj_act' =>  json_encode($registro->obCourse),
                            'format_options' => json_encode($format_options),
                            'block_options' => json_encode($block_options)
                        );

                        $update_nodos_update->cant_courses_enhijo = $results->response;
                        $update_nodos_update->estado = 2;
                        $DB->update_record('updates_nodos', $update_nodos_update);

                        $ca = $objUPD->ordenEmpezarCurso($value->url_hijo, $params, $update_nodos_update->cant_courses_enhijo, $value->tot_courses);
                        $contador++;
                        $update_nodos->cant_courses_terminados += is_int($ca) ? $ca : 0;
                        $update_nodos_update->cant_courses_terminados = $update_nodos->cant_courses_terminados;
                        if ($update_nodos->cant_courses_terminados > 0) {
                            $DB->update_record('updates_nodos', $update_nodos_update);
                        }
                    } else {
                        echo ' $results ';
                        print_r($results);
                        echo ' $value->url_hijo ';
                        print_r($value->url_hijo);
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
            $nodos->mjs = 'No hay nodos para notificar';
        }
    }


    /*
     * Da la orden a los nodos de empezar
     * @param {string} $url_hijo
     * @param {array} $params
     * return {};
     */
    private function ordenEmpezarCurso($url_hijo, $params, $cant_h, $cant_p)
    {
        global $DB;
        $tok = sha1('2017.UVD_TokeN_noDos');
        $url = $url_hijo . '/webservice/rest/server.php?wstoken=' . $tok . '&wsfunction=local_update_empezar_cursos&moodlewsrestformat=json';
        $curl = new curl;
        $results = json_decode($curl->post($url, $params));

        echo '<li><h3><strong>URL: ' . $url_hijo . '</strong></h3></li>';
        echo 'URL DE EJECUCIÓN: update\methods\UPD\class.update.php' . '<br>';
        echo 'Cantidad de cursos registrados en el padre: ' . $cant_p . '<br>';
        echo 'Cantidad de cursos registrados en el hijo: ' . $cant_h . '<br>';

        if ((is_array($results) || is_object($results)) && property_exists($results, 'ack')) {

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
}
updateUpdate::run();
