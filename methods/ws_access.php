<?php
require_once($CFG->dirroot . '/local/backup_course/methods/query_bl_wl.php');

/**
 * Description of ws_access
 *
 * @author daniela.sierra
 */


class ws_access
{
    /*
     * Run the class -> perms
     * @params -> array(url_padre,token,ip,estado)
     * return {int};
     */
    public function perms($param)
    {
        $obj = new self();
        //if($_SERVER['HTTP_ORIGIN'] === $param['url_padre']){

        if ($obj->validar_datos(json_encode($param))) {
            $res = new stdClass();
            $res->ack = 0;
            $res->response = 'Guardado en negra';
        } else {
            $idfunc = $param['function'];
            switch ($idfunc) {
                case 'C01':
                    $resp = $obj->creNodo($param);
                    break;
                case 'C04':
                    $resp = $obj->creRelationPH($param);
                    break;
                case 'Q02':
                    $resp = $obj->qryCourses($param);
                    break;
                case 'Q07':
                    $resp = $obj->getBanckPreguntas($param);
                    break;
                case 'Q04':
                    $resp = $obj->qryInfoCourses($param['id_course'], $param['infoCourse']);
                    break;
                case 'U01':
                    $resp = $obj->updNodo($param);
                    break;
                case 'D01':
                    $resp = $obj->delNodo($param);
                    break;
                case 'D02':
                    $resp = $obj->delete_rel_padre_hijo($param);
                    break;
                case 'Q09':
                    $resp = $obj->getRubrica($param);
                    break;

                case 'Q100':
                    $resp = $obj->getBanckContenido($param);
                    break;
                case 'Q101':
                    $resp = $obj->getFilesH5P($param);
                    break;
                case 'Q102':
                    $resp = $obj->getFilesResource($param);
                    break;
            }
        }
        /*}else{
            // Lista negra
        }*/

        return $resp;
    }
    /*
     * Validate data -> validar_datos
     * Valida inyección por parametros
     * @params -> array(url_padre,token,ip,estado)
     * return {bool};
     */
    private function validar_datos($data)
    {

        $identifier_syntax = '/^[$_\p{L}][$_\p{L}\p{Mn}\p{Mc}\p{Nd}\p{Pc}\x{200C}\x{200D}]*+$/u';

        $reserved_words = array(
            'break',
            'do',
            'instanceof',
            'typeof',
            'case',
            'else',
            'new',
            'var',
            'catch',
            'finally',
            'return',
            'void',
            'continue',
            'for',
            'switch',
            'while',
            'debugger',
            'function',
            'this',
            'with',
            'default',
            'if',
            'throw',
            'delete',
            'in',
            'try',
            'class',
            'enum',
            'extends',
            'super',
            'const',
            'export',
            'import',
            'implements',
            'let',
            'private',
            'public',
            'yield',
            'interface',
            'package',
            'protected',
            'static',
            'null',
            'true',
            'false'
        );

        return preg_match($identifier_syntax, $data) && in_array(mb_strtolower($data, 'UTF-8'), $reserved_words);
    }
    /*
     * Create Padre -> creNodo
     * Crea en bc_registro_pc el token
     * @params -> array(url_padre,token,ip,estado)
     * return {objet};
     */
    private function creNodo($params)
    {
        global $DB, $CFG;
        $query_bl_wl = new query_bl_wl();
        $resp = $query_bl_wl->run($params);
        if ($resp->response == 'Puede crear') {
            $registro_token = new stdClass();
            $registro_token->nombre = 'Padre';
            /* $registro_token->ip = $params['ip']; */
            $registro_token->url_hijo = $params['url_hijo'];
            $registro_token->token = sha1($params['token']);
            $registro_token->estado = $params['estado'];
            $registro_token->edition = $params['edition'];
            $registro_token->url_padre = $params['url'];
            $registro_token->startdate = $params['startdate'];
            $registro_token->enddate8 = $params['enddate8'];
            $registro_token->enddate16 = $params['enddate16'];
            $DB->insert_record('bc_registro_pc', $registro_token);
        }
        return $resp;
    }
    /*
     * Create relation of padre hijo -> creRelationPH
     * Crea la relación entre el padre y el hijo de las secciones y las actividades
     * @params -> array(url_padre,token,ip,estado)
     * return {objet};
     */
    private function creRelationPH($params)
    {
        global $DB;
        $query_bl_wl = new query_bl_wl();
        $resp = $query_bl_wl->run($params);
        /* $id_reg = $query_bl_wl->QRY_RBC_tok($params['url'], $params['token'], $params['ip']); */
        $id_reg = $query_bl_wl->QRY_RBC_tok($params['url'], $params['token']);
        if ($resp->response == 'Ya existe en la blanca') {
            $tb_rel = $DB->get_record_sql('SELECT * FROM {bc_rel_padre_hijo} b WHERE b.courseid_sh = :courseid_sh AND b.registroid = :registroid ORDER BY b.id DESC LIMIT 1', array('courseid_sh' => $params['id_nodo'], 'registroid' => $id_reg));
            $relation = new stdClass();
            $relation->registroid = $id_reg;
            $relation->registroid_nodo = $params['id_reg'];
            $relation->courseid_sh = $params['id_nodo'];
            $relation->courseid_sp = $params['id_padre'];
            $relation->objet_ph = $params['obj'];
            $relation->estado_update = 0;
            $relation->userid_nodo = $params['id_user'];
            if (empty($tb_rel)) {
                $DB->insert_record('bc_rel_padre_hijo', $relation);
                $resp->ack = 1;
                $resp->response = 'Importación realizada';
            } else {
                //$tb_rel = each($tb_rel); 
                $relation->id = $tb_rel->id;
                $DB->update_record('bc_rel_padre_hijo', $relation);
                $resp->ack = 1;
                $resp->response = 'Se realizó de nuevo la importación';
            }
        } else {
            $resp->ack = 0;
            $resp->response = 'No estas en este padre';
        }

        return $resp;
    }

    /*
     * Search Courses in Padre-> qryCourses
     * Busca las coincidencias de los cursos en el padre
     * @params -> array(url_hijo,token,estado)
     * return {objet};
     */
    private function qryCourses($params)
    {
        global $DB;

        $search = $params['search'];
        $query_bl_wl = new query_bl_wl();
        $resp = $query_bl_wl->run($params);
        $id_reg = $query_bl_wl->QRY_RBC_tok($params['url'], $params['token']/* , $params['ip'] */);

        if (($resp->response == 'Ya existe en la blanca' || $resp->response == 'Puede crear') && !empty($id_reg)) {
            $searchsql    = '';
            $searchparams = array();
            $searchlikes = array();
            $searchfields = array('c.shortname', 'c.fullname');
            for ($i = 0; $i < count($searchfields); $i++) {
                $searchlikes[$i] = $DB->sql_like($searchfields[$i], ":s{$i}", false, false);
                $searchparams["s{$i}"] = '%' . $search . '%';
            }
            // We exclude the front page.
            $searchsql = '(' . implode(' OR ', $searchlikes) . ') AND c.id != 1 AND c.visible = 1';

            // Run query.

            $sql = "SELECT c.*,
                    (SELECT url_padre FROM {bc_registro_pc} LIMIT 1) url_padre
                        FROM {course} c
                        WHERE $searchsql ORDER BY c.shortname ASC";
            $courses = $DB->get_records_sql($sql, $searchparams, 0);

            $resp->ack = 1;
            $resp->response = json_encode($courses);
        } else if (empty($id_reg)) {
            $query_bl_wl->CRE_BL($params);
            $resp->ack = 0;
            $resp->response = 'Su token ha sido bloqueado, comuníquese con el administrador del Padre';
        }

        return $resp;
    }
    /*
     * Search infoCourses in Padre-> qryInfoCourses
     * Busca las toda la información del curso(bloques, grupos, agrupaciones)
     * @params -> {int}$id_course
     * return {objet};
     */
    private function qryInfoCourses($id_course, $infoCourse)
    {
        global $DB;
        $info_course_padre = array();

        $bloques = $DB->get_records_sql('SELECT DISTINCT 
                        blo.id AS id_block_ins, blo.blockname, blo.defaultregion, blo.defaultweight, blo.configdata, blo.pagetypepattern, 
                        blo.showinsubcontexts, blo.subpagepattern,
                        cont.id AS id_context, cont.contextlevel, cont.depth
                    FROM {course} c
                    LEFT JOIN {context} cont ON cont.instanceid = c.id
                    LEFT JOIN {block_instances} blo ON blo.parentcontextid = cont.id
                    WHERE c.id = :id_course  
                    /* AND blo.pagetypepattern = "course-view-*" */', array('id_course' => $id_course));

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
     * Update search -> updNodo
     * Actualizar el token en el nodo
     * @params -> array(url_padre,token,ip,estado)
     * return {objet};
     */
    private function updNodo($params)
    {
        global $DB;
        $query_bl_wl = new query_bl_wl();
        $resp = $query_bl_wl->run($params);
        $id_reg = $query_bl_wl->QRY_RBC($params['url'], $params['nombre']);
        if ($resp->response == 'Ya existe en la blanca' && !empty($id_reg)) {
            $registro_token = new stdClass();
            $registro_token->id = $id_reg;
            $registro_token->nombre = 'Padre';
            $registro_token->url_hijo = $params['url_hijo'];
            $registro_token->startdate = $params['startdate'];
            $registro_token->enddate8 = $params['enddate8'];
            $registro_token->enddate16 = $params['enddate16'];
            $registro_token->estado = $params['estado'];
            $registro_token->edition = $params['edition'];
            if (!empty($params['token'])) {
                $registro_token->token = sha1($params['token']);
            }
            $DB->update_record('bc_registro_pc', $registro_token);
            $registro_token->id = $query_bl_wl->QRY_URLWL($params['url']);
            $registro_token->url = $params['url'];
            $query_bl_wl->UPD_WL($registro_token);
            $resp->ack = 1;
            $resp->response = 'Nodo Actualizado';
        } else if ($resp->response == 'Ya existe en la blanca') {
            $resp->ack = 0;
            $resp->response = 'NO se actualizó';
        }
        return $resp;
    }
    /*
     * Delete Padre -> delNodo
     * Eliminar token en el nodo
     * @params -> array(url_padre,token,ip,estado)
     * return {objet};
     */
    private function delNodo($params)
    {
        global $DB;
        $query_bl_wl = new query_bl_wl();
        $resp = $query_bl_wl->run($params);
        $id_reg = $query_bl_wl->QRY_RBC($params['url'], $params['token']);
        if ($resp->response == 'Ya existe en la blanca' && !empty($id_reg)) {
            $query_bl_wl->DEL_WL($query_bl_wl->QRY_URLWL($params['url']));
            $DB->delete_records('bc_registro_pc', array('id' => $id_reg));
            $DB->delete_records('bc_rel_padre_hijo', array('registroid' => $id_reg));
            $resp->ack = 1;
            $resp->response = 'Se eliminó el hijo';
        } else {
            $resp->ack = 0;
            $resp->response = 'No es posible eliminar el nodo';
        }
        return $resp;
    }

    /*
     * Delete relación del curso padre con el hijo
     * @params -> array(url_padre,token,ip,estado)
     * return {objet};
     */
    private function delete_rel_padre_hijo($param)
    {
        global $DB;
        $resp = new stdClass();
        $query_bl_wl = new query_bl_wl();
        $wl = $DB->get_records('bc_white_list', array('url' => $param['url_hijo']));
        $id_reg = $DB->get_record('bc_registro_pc', array('token' => $param['token']));
        $resp->ack = 0;
        if (!empty($wl) && !empty($id_reg)) {
            $rel_p_h = $DB->get_record('bc_rel_padre_hijo', array('registroid' => $id_reg->id, 'courseid_sp' => $param['enddate8'], 'courseid_sh' => $param['enddate16']));
            if (!empty($rel_p_h)) {
                if ($DB->delete_records('bc_rel_padre_hijo', array('registroid' => $id_reg->id, 'courseid_sp' => $param['enddate8'], 'courseid_sh' => $param['enddate16']))) {
                    $resp->ack = 1;
                    $resp->response = 'Se eliminó la relación entre cursos';
                } else {
                    $resp->response = 'No es posible eliminar la relación entre cursos';
                }
            } else {
                $resp->response = 'No hay registro de curso padre: ' . $param['enddate8'] . ', curso hijo: ' . $param['enddate16'] . ', id_registro: ' . $id_reg->id;
            }
        } else {
            $resp->response = 'Registro en bc_white_list: ' . json_encode($wl) . '   bc_registro_pc: ' . json_encode($id_reg);
        }
        return $resp;
    }

    /*
     * Buscar librerias HVP y el banco de preguntas
     */
    private function getBanckPreguntas($params)
    {
        global $DB, $CFG;

        $activities = array();
        $activities['question_categories'] = $DB->get_records_sql('SELECT q_cat.* ,
                                                                                 cont.id AS id_cont, cont.contextlevel AS contextlevel_cont, cont.instanceid AS instanceid_cont, cont.path AS path_cont, cont.depth AS depth_cont 
                                                                        FROM {question_categories} q_cat
                                                                        LEFT JOIN {context} cont ON cont.id = q_cat.contextid
                                                                        WHERE cont.contextlevel = 50 AND cont.instanceid = :instanceid ', array('instanceid' => $params['id_course']));

        $registro_dep["consulta_iniciadora_question_categories"] = $activities['question_categories'];

        $question_bank = array();

        $question_version = array();

        $question =  array();

        $question_answers =  array();
        $question_attempts =  array();
        $question_multianswer =  array();
        $question_truefalse =  array();
        $qtype_ddimageortext =  array();
        $qtype_ddimageortext_drags =  array();
        $qtype_ddimageortext_drops =  array();
        $qtype_ddmarker =  array();
        $qtype_ddmarker_drags =  array();
        $qtype_ddmarker_drops =  array();
        $qtype_essay_options =  array();
        $qtype_match_options =  array();
        $qtype_match_subquestions =  array();
        $qtype_multichoice_options =  array();
        $qtype_randomsamatch_options =  array();
        $qtype_shortanswer_options =  array();

        foreach ($activities['question_categories'] as $key => $val) {

            $registro_dep["KEY"][$key] = $key;

            $registro_dep["VAL"][$key] = $val;

            $activities['question_usages'][$key] = $DB->get_records('question_usages', array('contextid' => $val->id_cont));

            $registro_dep["activities_question_usages"][$key] = $activities['question_usages'][$key];

            $question_bank[$key] = $DB->get_records('question_bank_entries', array('questioncategoryid' => $val->id));

            $registro_dep["question_bank_key"][$key] = $question_bank[$key];

            if (!empty($question_bank[$key])) {

                $registro_dep["primer_if"][$key] = true;

                $activities['question_bank_entries'][$key] = $question_bank[$key];

                $registro_dep["activities_question_bank_entries_key"][$key] = $activities['question_bank_entries'][$key];

                foreach ($question_bank[$key] as $key3 => $value3) {

                    $question_version[$key3] = $DB->get_records('question_versions', array('questionbankentryid' => $value3->id));

                    $registro_dep["question_version_key3"][$key] = $question_version[$key3];

                    if (!empty($question_version[$key3])) {

                        $registro_dep["segundo_if"][$key] = true;

                        foreach ($question_version[$key3] as $key4 => $value4) {

                            $question[$key4] = $DB->get_records('question', array('id' => $value4->questionid));

                            $registro_dep["question_key4"][$key] = $question[$key4];

                            if (!empty($question[$key4])) {

                                foreach ($question[$key4] as $k => $v) {

                                    $activities['question'] = (array)$question;

                                    $registro_dep["activities_question_final"][$key] = $activities['question'];
                                    $question_answers[] = (array)$DB->get_records('question_answers', array('question' => $v->id));
                                    $question_attempts[] = (array)$DB->get_records('question_attempts', array('questionid' => $v->id));
                                    $question_multianswer[] = (array)$DB->get_records('question_multianswer', array('question' => $v->id));
                                    $question_truefalse[] = (array)$DB->get_records('question_truefalse', array('question' => $v->id));

                                    $qtype_ddimageortext[] = (array)$DB->get_records('qtype_ddimageortext', array('questionid' => $v->id));
                                    $qtype_ddimageortext_drags[] = (array)$DB->get_records('qtype_ddimageortext_drags', array('questionid' => $v->id));
                                    $qtype_ddimageortext_drops[] = (array)$DB->get_records('qtype_ddimageortext_drops', array('questionid' => $v->id));
                                    $qtype_ddmarker[] = (array)$DB->get_records('qtype_ddmarker', array('questionid' => $v->id));
                                    $qtype_ddmarker_drags[] = (array)$DB->get_records('qtype_ddmarker_drags', array('questionid' => $v->id));
                                    $qtype_ddmarker_drops[] = (array)$DB->get_records('qtype_ddmarker_drops', array('questionid' => $v->id));
                                    $qtype_essay_options[] = (array)$DB->get_records('qtype_essay_options', array('questionid' => $v->id));
                                    $qtype_match_options[] = (array)$DB->get_records('qtype_match_options', array('questionid' => $v->id));
                                    $qtype_match_subquestions[] = (array)$DB->get_records('qtype_match_subquestions', array('questionid' => $v->id));
                                    $qtype_multichoice_options[] = (array)$DB->get_records('qtype_multichoice_options', array('questionid' => $v->id));
                                    $qtype_randomsamatch_options[] = (array)$DB->get_records('qtype_randomsamatch_options', array('questionid' => $v->id));
                                    $qtype_shortanswer_options[] = (array)$DB->get_records('qtype_shortanswer_options', array('questionid' => $v->id));
                                }
                            }
                        }
                    }
                }
            }
        }

        $activities['question_answers'] =  $question_answers;
        $activities['question_attempts'] = $question_attempts;
        $activities['question_multianswer'] = $question_multianswer;
        $activities['question_truefalse'] = $question_truefalse;
        $activities['qtype_ddimageortext'] = $qtype_ddimageortext;
        $activities['qtype_ddimageortext_drags'] = $qtype_ddimageortext_drags;
        $activities['qtype_ddimageortext_drops'] = $qtype_ddimageortext_drops;
        $activities['qtype_ddmarker'] = $qtype_ddmarker;
        $activities['qtype_ddmarker_drags'] = $qtype_ddmarker_drags;
        $activities['qtype_ddmarker_drops'] = $qtype_ddmarker_drops;
        $activities['qtype_essay_options'] = $qtype_essay_options;
        $activities['qtype_match_options'] = $qtype_match_options;
        $activities['qtype_match_subquestions'] =  $qtype_match_subquestions;
        $activities['qtype_multichoice_options'] = $qtype_multichoice_options;
        $activities['qtype_randomsamatch_options'] = $qtype_randomsamatch_options;
        $activities['qtype_shortanswer_options'] = $qtype_shortanswer_options;
        $activities['question_versions'] = $question_version;

        $registro_dep = json_encode($registro_dep);

        return json_encode($activities);
    }


    /*
     * Buscar librerias HVP y el banco de preguntas
     */
    private function getFilesH5P($params)
    {
        global $DB, $CFG;

        $activities = array();

        $h5p_files = $DB->get_records_sql(
            'select fil.*,cm.id AS id_como_p from {course_modules} cm
                                            inner join {context} c on (c.instanceid = cm.id and c.contextlevel = 70)
                                            inner join {files} fil on (fil.contextid = c.id and fil.filename <> ".")
                                            where cm.course = :id_course and cm.module = (select m.id from {modules} m where m.name ="h5pactivity") order by fil.contenthash',
            array('id_course' => $params['id_course'])
        );
        //ERROR EN ESTA LINEA
        /* */
        $hvp_files = null;


        if (!empty($h5p_files) || !empty($hvp_files)) {
            $zip = new ZipArchive();
            $filename = $CFG->dataroot . '/temp/courseH5P_' . $params['id_nodo'] . '.zip'; //s3

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
                        $moodle_data = $CFG->dataroot . '/filedir/' . $cr1 . '/' . $cr2 . '/' . $archivo;
                        $zip->addFile($moodle_data, $value->filename);
                    }
                }


                $zip->close();
            } else $activities['h5p_files'] =  'Error creando ' . $filename;

            if (file_exists($filename)) {



                $name_archive = 'courseH5P_' . $params['id_nodo'] . '.zip';
                require_once $CFG->dirroot . '/local/backup_course/folder_S3/controlador2_m.php';
                $s3 = new Controlador2_m();
                if ($crear = $s3->run('create', $filename, $name_archive, $params['id_nodo'])) {
                    $activities['h5p_files']   = $h5p_files;
                    $activities['hvp_files']   = $hvp_files;
                    if (file_exists($filename)) unlink($filename);
                }
            }
        }


        return json_encode($activities);
    }

    private function getFilesResource($params)
    {
        global $DB, $CFG;

        $activities = array();

        $resource_files = $DB->get_records_sql(
            'select fil.*,cm.id AS id_como_p from {course_modules} cm
                                            inner join {context} c on (c.instanceid = cm.id and c.contextlevel = 70)
                                            inner join {files} fil on (fil.contextid = c.id and fil.filename <> ".")
                                            where cm.course = :id_course and cm.module = (select m.id from {modules} m where m.name ="resource") order by fil.contenthash',
            array('id_course' => $params['id_course'])
        );


        if (!empty($resource_files)) {
            $zip = new ZipArchive();
            $filename = $CFG->dataroot . '/temp/courseResource_' . $params['id_nodo'] . '.zip'; //s3

            if ($zip->open($filename, ZIPARCHIVE::CREATE) === true) {

                if (!empty($resource_files)) {
                    foreach ($resource_files as $key => $value) {
                        $archivo = $value->contenthash;
                        $cr1 = substr($archivo, 0, 2);
                        $cr2 = substr($archivo, 2, 2);
                        //$name_archive = $value->pathnamehash.'_'.$params['id_nodo'];
                        $moodle_data = $CFG->dataroot . '/filedir/' . $cr1 . '/' . $cr2 . '/' . $archivo;
                        $zip->addFile($moodle_data, $value->contenthash);
                    }
                }



                $zip->close();
            } else $activities['resource_files'] =  'Error creando ' . $filename;

            if (file_exists($filename)) {
                $name_archive = 'courseResource_' . $params['id_nodo'] . '.zip';
                require_once $CFG->dirroot . '/local/backup_course/folder_S3/controlador2_m.php';
                $s3 = new Controlador2_m();
                $crear = $s3->run('create', $filename, $name_archive, $params['id_nodo']);
                if ($crear) {
                    $activities['resource_files']   = $resource_files;
                    if (file_exists($filename)) unlink($filename);
                }
            }
        }


        return json_encode($activities);
    }

    private function getBanckContenido($params)
    {
        global $DB, $CFG;

        $activities = array();
        /////////buscar archivos del curson del banco de contenido
        $contentbank_files = $DB->get_records_sql(
            'SELECT DISTINCT  f.*
                                                FROM {files} f 
                                                INNER JOIN {context} c ON (c.id = f.contextid AND c.contextlevel = 50 )
                                                INNER JOIN {contentbank_content} cc ON cc.contextid =c.id 
                                                WHERE f.component = "contentbank" AND f.filesize>0 AND c.instanceid = :id_course
                                                order by f.contenthash',
            array('id_course' => $params['id_course'])
        );

        $contentbank = $DB->get_records_sql(
            'SELECT cc.*  FROM {context} c 
                                            INNER JOIN {contentbank_content} cc ON cc.contextid =c.id 
                                            WHERE c.contextlevel = 50 AND c.instanceid = :id_course',
            array('id_course' => $params['id_course'])
        );

        if (!empty($contentbank_files)) {
            $zip = new ZipArchive();
            $filename = $CFG->dataroot . '/temp/course_' . $params['id_nodo'] . '.zip'; //s3

            if ($zip->open($filename, ZIPARCHIVE::CREATE) === true) {
                if (!empty($contentbank_files)) {
                    foreach ($contentbank_files as $key => $value) { ///////buscar cada archivo el el moodledata
                        $archivo = $value->contenthash;
                        $cr1 = substr($archivo, 0, 2);
                        $cr2 = substr($archivo, 2, 2);
                        $moodle_data = $CFG->dataroot . '/filedir/' . $cr1 . '/' . $cr2 . '/' . $archivo;
                        $zip->addFile($moodle_data, $value->filename); //añadir archivos al zip
                    }
                }



                $zip->close();
            }

            if (file_exists($filename)) {
                $name_archive = 'course_' . $params['id_nodo'] . '.zip';
                require_once $CFG->dirroot . '/local/backup_course/folder_S3/controlador2_m.php';
                $s3 = new Controlador2_m();
                $crear = $s3->run('create', $filename, $name_archive, $params['id_nodo']);
                if ($crear) {
                    $activities['contentbank'] = $contentbank; //añadir banco al obj
                    $activities['contentbank_files'] = $contentbank_files; //añadir archivos de banco al obj 
                    if (file_exists($filename)) unlink($filename);
                }
            }
        }


        return json_encode($activities);
    }

    private function getRubrica($params)
    {
        global $DB, $CFG;

        $registro = (object) $params;

        $arrayAct = array('forum', 'assign');

        $return = new stdClass();

        foreach ($arrayAct as $valor) {

            $consultaForos = $DB->get_records("$valor", array('course' => $registro->id_course));

            $idModuleForum = $DB->get_record('modules', array('name' => "$valor"));

            foreach ($consultaForos as $key  => $val) {

                $consultaRubrica = $DB->get_record_sql("SELECT cm.id as moduleid, con.id as contextid, ga.id as areasid, instance as forumid
            
                FROM {course_modules} as cm  
        
                INNER JOIN {context} as con 
        
                ON con.instanceid = cm.id
        
                INNER JOIN {grading_areas} as ga
        
                ON con.id = ga.contextid
        
                WHERE cm.course = '$registro->id_course' AND cm.module = '$idModuleForum->id' AND instance = $val->id ");

                if (!empty($consultaRubrica)) {

                    $valr = $consultaRubrica;

                    $return->idHijo = $registro->id_nodo;

                    $return->$valor[$valr->forumid] = $valr->forumid;

                    $gradingAreas = $DB->get_record('grading_areas', array('id' => $valr->areasid));

                    $return->$valor['gradingAreas'][$valr->areasid] = $gradingAreas;

                    $grading_definitions = $DB->get_record('grading_definitions', array('areaid' => $valr->areasid));

                    $return->$valor['grading_definitions'][$grading_definitions->id] = $grading_definitions;

                    $gradingform_rubric_criteria = $DB->get_records('gradingform_rubric_criteria', array('definitionid' => $grading_definitions->id));

                    foreach ($gradingform_rubric_criteria as $krc => $vcr) {

                        $return->$valor['gradingform_rubric_criteria'][$krc] = $vcr;
                    }

                    foreach ($gradingform_rubric_criteria as $kc => $vc) {

                        $gradingform_rubric_levels = $DB->get_records('gradingform_rubric_levels', array('criterionid' => $vc->id));

                        foreach ($gradingform_rubric_levels as $kcri => $vcri) {

                            $return->$valor['gradingform_rubric_levels'][$kcri] = $vcri;
                        }
                    }
                }
            }
        }

        return json_encode($return, true);
    }
}
