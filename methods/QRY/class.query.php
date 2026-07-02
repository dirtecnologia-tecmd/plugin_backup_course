<?php

require_once('../../../../config.php');
require_once("$CFG->libdir/filelib.php");
require_once("$CFG->libdir/pagelib.php");
require_once("$CFG->libdir/blocklib.php");
require_once '../CRE/excepcions_errors.php';

class queryNodo extends excepcions_errors
{

    public static function run()
    {
        $obj = new self();
        $idfunc = $_POST['key'];

        switch ($idfunc) {
            case 'Q01':
                $resp = $obj->listaNodos();
                break;
            case 'Q02':
                $resp = $obj->listaCursos();
                break;
            case 'Q03':
                $resp = $obj->listaSections();
                break;
            case 'Q06':
                $resp = $obj->allInfoCourse();
                break;
            case 'Q07':
                $resp = $obj->getBanckPreguntas();
                break;
            case 'Q08':
                $resp = $obj->gettype_activity($_POST['id_cm'], $_POST['courseid'], $_POST['nombre_modulo']);
                break;
            case 'Q09':
                $resp = $obj->getRubric();
                break;
            case 'Q10':
                $resp = $obj->getSectionsNew($_POST['courseid']);
                break;
            case 'Q11':
                $resp = $obj->countAtivities($_POST['courseid'], $_POST['section']);
                break;
            case 'Q12':
                $resp = $obj->misActividades($_POST['courseid']);
                break;
            case 'Q13':
                $resp = $obj->miActividad($_POST['moduleid']);
                break;
            case 'Q100':
                $resp = $obj->getBanckContenido();
                break;
            case 'Q101':
                $resp = $obj->getFilesH5P();
                break;
            case 'Q102':
                $resp = $obj->getFilesResource();
                break;
        }
        echo json_encode($resp);
    }

    private function gettype_activity($id, $courseid, $nombre_modulo)
    {
        global $DB;

        $actividad = new stdClass();

        $actividad = $DB->get_record_sql('SELECT a.id, g.activemethod, cm.id as id_module,
                                     (SELECT section FROM {course_sections} WHERE id = cm.section) AS sect
                                      FROM {course_modules} cm 
                                      LEFT JOIN {' . $nombre_modulo . '} a ON a.id = cm.instance
                                      LEFT JOIN {context} c ON c.instanceid = cm.id AND c.contextlevel = 70
                                      LEFT JOIN {grading_areas} g ON g.contextid = c.id
                                      WHERE cm.id = :id', array('id' => $id));

        if (!$DB->get_manager()->table_exists('ec_actividades_propuestas')) {
            $actividad->estado = 'No';
        } else {
            if (!empty($actividad)) {

                $obj_act = $DB->get_record_sql("SELECT * FROM {ec_actividades_propuestas} 
                WHERE id_course_hijo = $courseid 
                AND id_section_hijo = $actividad->sect 
                AND id_activity_hijo = $actividad->id");

                if (!empty($obj_act)) {

                    $actividad_propuesta = $DB->get_record_sql("SELECT * FROM {ec_plantilla_apoyo_propuesta}  AS plantilla WHERE id_act_propuesta = " . $obj_act->id . " AND plantilla.state_relation = '' AND plantilla.type_activity <> 'plantilla_programa' AND plantilla.type_activity <> 'plantilla_micro' ");

                    if ($actividad_propuesta) {
                        $actividad->id_plantilla_actividad = $actividad_propuesta->id_plantilla_actividad;
                        $actividad->id_ec_plantilla_grupo = $actividad_propuesta->id_ec_plantilla_grupo;
                    }

                    $actividad->estado = $obj_act->estado;
                } else {
                    $actividad->estado = 0;
                }
            } else {
                $actividad->estado = 0;
            }
        }

        $clave = "reemplazoActividades2024*";
        $encriptado = $this->encrypt($actividad->id_module, $clave);
        $actividad->id_module = urlencode($encriptado);
        return $actividad;
    }

    /*
     * List Tokens -> listaTokens
     * Lista los nodos unidos al padre
     * return {array};
     */

    private function listaNodos()
    {
        global $DB;
        $options_tokens = $DB->get_records_sql('SELECT id, url_padre, nombre, token, /* ip, */ url_hijo, estado, edition, FROM_UNIXTIME(startdate) AS startdate, FROM_UNIXTIME(enddate8) AS enddate8, FROM_UNIXTIME(enddate16) AS enddate16 FROM {bc_registro_pc}');
        return $options_tokens;
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

        if ($url_https == 'https') {

            $url_http = str_replace("s", "", $url_https);

            return $url_http . ":" . $url_expl[1];
        } else {

            return $url;
        }
    }

    /*
     * List Cursos -> listaCursos
     * Lista los cursos encontrados en el padre 
     * return {array}; 
     */

    private function listaCursos()
    {
        global $DB, $CFG;
        $res = new stdClass();
        $tb_reg = $DB->get_records('bc_registro_pc', array('nombre' => "Soy Hijo"));
        $tb_wl = $DB->get_record_sql('SELECT * FROM {bc_white_list} limit 1');
        if (!empty($tb_reg) && !empty($tb_wl)) {
            $url_p = $tb_wl;
            if ($url_p->estado == 1) {
                $registro = (object) $_POST;
                $tok = '2017.UVD_TokeN_noDos';
                $url = $this->formatHttp($url_p->url) . '/webservice/rest/server.php?wstoken=' . sha1($tok) . '&wsfunction=local_backup_list_courses&moodlewsrestformat=json';
                $params = array(
                    'function' => $registro->key,
                    'search' => $registro->search,
                    'token' => $url_p->token,
                    /* 'ip' => $_SERVER['SERVER_ADDR'], */
                    'url_hijo' => $this->formatHttp($CFG->wwwroot),
                    'estado' => $url_p->estado,
                );
                $curl = new curl;
                $results = json_decode($curl->post($url, $params));
                return $results;
            } else {
                $res->ack = 0;
                $res->response = 'Estás inactivo';
            }
        } else {
            $res->ack = 0;
            $res->response = 'No está configurado como hijo';
        }
        return $res;
    }

    /*
     * List Sections -> listaSections
     * Información de las secciones con las actividades del curso seleccionado en el padre
     * return {obj};
     */

    private function listaSections()
    {
        global $DB, $CFG;
        $registro = (object) $_POST;
        $tb_reg = $DB->get_records('bc_registro_pc', array('nombre' => "Soy Hijo"));
        $tb_wl = $DB->get_record_sql('SELECT * FROM {bc_white_list} LIMIT 1');
        if (!empty($tb_reg) && !empty($tb_wl)) {
            //$url_p = each($tb_wl);
            $url_p = $tb_wl;
            if ($url_p->estado == '1') {
                $registro = (object) $_POST;
                $tok = '2017.UVD_TokeN_noDos';
                $url = $this->formatHttp($url_p->url) . '/webservice/rest/server.php?wstoken=' . sha1($tok) . '&wsfunction=local_backup_list_coursesAct&moodlewsrestformat=json';
                $params = array(
                    'url_hijo' => $this->formatHttp($CFG->wwwroot),
                    'id_nodo' => $registro->id_course,
                    'id_course' => $registro->id_nodo,
                );
                $curl = new curl();
                //echo '<pre>'; print_r($curl->post($url, $params)); echo '</pre>'; die();
                $results = json_decode($curl->post($url, $params));
                $this->setFormatOption($results->format_options, $registro->id_course);
                return $results;
            }
            return $tb_wl;
        } else {
            $res = new stdClass();
            $res->ack = 0;
            $res->response = 'No se puede importar';
            return $res;
        }
    }

    /*
     * Objet de la información del curso -> allInfoCourse
     * Busca en el padre la información y la crea en el hijo, devuelve un objeto con la relacion entre estos
     * return {objet};
     */

    private function allInfoCourse()
    {
        global $DB, $CFG, $USER, $PAGE;
        $registro = (object) $_POST;
        $tb_reg = $DB->get_records('bc_registro_pc', array('nombre' => "Soy Hijo"));
        $tb_wl = $DB->get_record_sql('SELECT * FROM {bc_white_list} LIMIT 1');
        if (!empty($tb_reg) && !empty($tb_wl)) {
            //$url_p = each($tb_wl);
            $url_p = ($tb_wl);
            if ($url_p->estado == '1') {
                $tok = '2017.UVD_TokeN_noDos';
                $url = $url_p->url . '/webservice/rest/server.php?wstoken=' . sha1($tok) . '&wsfunction=local_remoter_bloques_node&moodlewsrestformat=json';
                $params = array('id_course' => $registro->id_course);
                $curl = new curl;
                $results = json_decode($curl->post($this->formatHttp($url), $params));
                $resp = new stdClass();
                $info_course = json_decode($results->response);

                foreach (json_decode($info_course) as $key => $value) {
                    if ($key == 'bloques') {
                        $url = $CFG->wwwroot . '/course/view.php?id=' . $registro->id_nodo;
                        $context = context_course::instance($registro->id_nodo);
                        $PAGE->set_url($url);
                        $PAGE->set_context($context);
                        $PAGE->blocks->add_region(BLOCK_POS_LEFT); //side-post
                        $position = 0;
                        foreach ($value as $k => $v) {
                            $block_exist = $DB->get_record('block', ['name' => $v->blockname]);
                            if ($block_exist) {
                                $PAGE->blocks->add_block($v->blockname, BLOCK_POS_LEFT, $position, FALSE, $v->pagetypepattern);
                                $position = $position + 2;
                                $bloques = $DB->get_record('block_instances', array('pagetypepattern' => $v->pagetypepattern, 'parentcontextid' => $context->id, 'blockname' => $v->blockname));
                                $bloques->configdata = $v->configdata;
                                $bloques->showinsubcontexts = $v->showinsubcontexts;
                                $bloques->subpagepattern = $v->subpagepattern;
                                $ac = $DB->update_record('block_instances', $bloques);
                            }
                        }
                    } else if ($key == 'grade_categories' || $key == 'grade_items') {

                        if (!empty($value)) {
                            if ($key == 'grade_categories') {
                                $nodos = array();
                                $id_cat_p = array();
                                $id_cat_h = array();
                                $j = 0;
                                foreach ($value as $k => $v) {
                                    $nodos[$j] = (array) $v;
                                    $id_cat_p[$j] = $v->id;
                                    $j = $j + 1;
                                }
                                if ($j == count($nodos)) {
                                    $id_parent = null;
                                    $nodos[0]['courseid'] = $registro->id_nodo;
                                    $nodos[0]['parent'] = NULL;

                                    $id_cate = $DB->insert_record($key, $nodos[0]);

                                    $id_cat_h[0] = $id_cate;

                                    $nodos[0]['id'] = $id_cate;
                                    $nodos[0]['path'] = '/' . $id_cate . '/';
                                    $ac = $DB->update_record($key, $nodos[0]);

                                    for ($i = 1; $i < count($nodos); $i++) {
                                        $nodos[$i]['courseid'] = $registro->id_nodo;
                                        if ($id_parent = $DB->insert_record($key, $nodos[$i])) {
                                            $id_cat_h[$i] = $id_parent;
                                            $path = explode('/', $nodos[$i]['path']);
                                            $newpath = array();

                                            for ($a = 0; $a < count($path); $a++) {
                                                $pos = array_search($path[$a], $id_cat_p);
                                                if (is_int($pos)) {
                                                    $newpath[] = $id_cat_h[$pos];
                                                }
                                            }
                                            $pos2 = array_search($nodos[$i]['parent'], $id_cat_p);
                                            if (is_int($pos2)) {
                                                $nodos[$i]['parent'] = $id_cat_h[$pos2];
                                            }
                                            $path2 = implode('/', $newpath);
                                            $nodos[$i]['path'] = '/' . $path2 . '/';
                                            $nodos[$i]['id'] = $id_parent;
                                            $tmp = explode('/', $nodos[$i]['path']);
                                            if (count($path) == count($tmp)) {
                                                if (array_key_exists('path', $nodos[$i]) && array_key_exists('parent', $nodos[$i])) {
                                                    $ac = $DB->update_record($key, $nodos[$i]);
                                                    $newpath = array();
                                                }
                                            }
                                        }
                                    }
                                    $resp->cat_p = $id_cat_p;
                                    $resp->cat_h = $id_cat_h;
                                }
                            } else {
                                $resp->grade_items = $value;
                            }
                        }
                    } else if ($key == 'groups' || $key == 'groupings' || $key == 'groupings_groups') {
                        if ($key == 'groups') {
                            $groups = array();
                            $id_groups_p = array();
                            $id_groups_h = array();
                            $j = 0;
                            foreach ($value as $k => $v) {
                                $groups[$j] = (array) $v;
                                $id_groups_p[$j] = $v->id;
                                $j = $j + 1;
                            }
                            for ($i = 0; $i < count($groups); $i++) {
                                $groups[$i]['courseid'] = $registro->id_nodo;
                                $id_groups_h[] = $DB->insert_record($key, $groups[$i]);
                            }
                            $resp->groups_p = $id_groups_p;
                            $resp->groups_h = $id_groups_h;
                        } else if ($key == 'groupings') {
                            $groupings = array();
                            $id_groupings_p = array();
                            $id_groupings_h = array();
                            $j = 0;
                            foreach ($value as $k => $v) {
                                $groupings[$j] = (array) $v;
                                $id_groupings_p[$j] = $v->id;
                                $j = $j + 1;
                            }
                            for ($i = 0; $i < count($groupings); $i++) {
                                $groupings[$i]['courseid'] = $registro->id_nodo;
                                $id_groupings_h[] = $DB->insert_record($key, $groupings[$i]);
                            }
                            $resp->groupings_p = $id_groupings_p;
                            $resp->groupings_h = $id_groupings_h;
                        } else {
                            $resp->groupings_groups = $value;
                        }
                    } else {
                        foreach ($value as $k => $v) {
                            $v->courseid = $registro->id_nodo;
                            $v->userid = $USER->id;
                            $DB->insert_record($key, $v);
                        }
                    }
                }
            }
        }

        return $resp;
    }


    private function getBanckPreguntas()
    {
        global $DB, $CFG, $USER;

        $registro = (object) $_POST;

        $tb_wl = $DB->get_record_sql('SELECT * FROM {bc_white_list} LIMIT 1');
        $id_question_usages = array();
        $id_question_versions = array();

        if (!empty($tb_wl)) {

            $url_p = ($tb_wl);
            if ($url_p->estado == '1') {

                $tok = '2017.UVD_TokeN_noDos';
                $url = $this->formatHttp($url_p->url) . '/webservice/rest/server.php?wstoken=' . sha1($tok) . '&wsfunction=local_remoter_banco_preguntas&moodlewsrestformat=json';
                $params = array(
                    'function' => $registro->key,
                    'id_course' => $registro->id_course,
                    'id_nodo' => $registro->id_nodo,
                );
                $curl = new curl;
                $results = json_decode($curl->post($url, $params));
                $id_question_categories = array();
                $id_question = array();
                $id_question_bank_entries = array();
                $id_question_versions = array();

                if (!empty($results) && property_exists($results, 'response')) {
                    $context = array();
                    $all_context = $DB->get_record('context', array('instanceid' => $registro->id_nodo, 'contextlevel' => 50));
                    $banck = array();
                    $info = json_decode($results->response);

                    foreach ($info as $k => $v) {

                        if ((is_array($v) || is_object($v)) && strpos($k, 'uestion')) {
                            //
                            foreach ($v as $key => $val) {
                                $val = (array) $val;

                                if ($k == 'question_categories') {
                                    $val['contextid'] = $all_context->id;
                                    $id_question_categories[$key]['p'] = $key;
                                    $id_cat = $DB->insert_record($k, $val);
                                    $id_question_categories[$key]['h'] = $id_cat;

                                    if ($val['parent']) {
                                        $id_question_categories = array_values($id_question_categories);
                                        $position = array_search($val['parent'], array_column($id_question_categories, 'p'));
                                        if (is_int($position)) {
                                            $val['parent'] = $id_question_categories[$position]['h'];
                                            $val['id'] = $id_cat;
                                            $DB->update_record($k, $val);
                                        }
                                    }
                                }

                                if (is_array($val) || is_object($val) && !empty($val)) {
                                    foreach ($val as $keye => $value) {
                                        if (is_array($value) || is_object($value)) {
                                            $value = (array) $value;

                                            if ($k == 'question_bank_entries') {
                                                $id_question_categories = array_values($id_question_categories);
                                                $position = array_search($value['questioncategoryid'], array_column($id_question_categories, 'p'));
                                                if (is_int($position)) {
                                                    $value['questioncategoryid'] = $id_question_categories[$position]['h'];
                                                    $id_question_bank_entries[$keye]['p'] = (int) $keye;
                                                    $value['ownerid'] = $USER->id;
                                                    $id_que_bank_entries = $DB->insert_record($k, $value);
                                                    $id_question_bank_entries[$keye]['h'] = $id_que_bank_entries;
                                                }
                                            }

                                            if ($k == 'question_versions') {
                                                $id_question_bank_entries = array_values($id_question_bank_entries);
                                                $position = array_search($value['questionbankentryid'], array_column($id_question_bank_entries, 'p'));
                                                if (is_int($position)) {
                                                    $value['questionbankentryid'] = $id_question_bank_entries[$position]['h'];
                                                    $id_question_versions[$keye]['p'] = (int) $keye;
                                                    $id_question = array_values($id_question);

                                                    //ESTÁ BIEN !!!
                                                    $position2 = array_search($value['questionid'], array_column($id_question, 'p'));

                                                    if (is_int($position2)) {
                                                        $value['questionid'] = $id_question[$position2]['h'];
                                                        $id_que_versions = $DB->insert_record($k, $value);
                                                        $id_question_versions[$keye]['h'] = $id_que_versions;
                                                    }
                                                }
                                            }

                                            if ($k == 'question_usages') {
                                                $position = array_search($value['contextid'], array_column($context, 'p'));
                                                if (is_int($position)) {
                                                    $value['contextid'] = $context[$position]['h'];
                                                    $id_question_usages[$keye]['p'] = $keye;
                                                    $id_question_usages[$keye]['h'] = $DB->insert_record($k, $value);
                                                }
                                            }

                                            if ($k == 'question') {

                                                $id_que = $DB->insert_record($k, $value);

                                                $id_question[$keye]['p'] = (int) $value['id'];
                                                $id_question[$keye]['h'] = $id_que;

                                                if ($value['parent'] != 0) {
                                                    $id_question2 = array_values($id_question);
                                                    $position = array_search($value['parent'], array_column($id_question2, 'p'));
                                                    if (is_int($position)) {
                                                        $value['parent'] = $id_question2[$position]['h'];
                                                        $value['id'] = $id_que;
                                                        $DB->update_record($k, $value);
                                                    }
                                                }
                                            }

                                            if ($k == 'question_answers') {
                                                $id_question = array_values($id_question);
                                                $position = array_search($value['question'], array_column($id_question, 'p'));
                                                if (is_int($position)) {
                                                    $banck[$k][$keye]['p'] = $value['id'];
                                                    $value['question'] = $id_question[$position]['h'];
                                                    $idQA = $DB->insert_record($k, $value);
                                                    $banck[$k][$keye]['h'] = $idQA;
                                                    $id_question_answers[$keye]['p'] = $value['id'];
                                                    $id_question_answers[$keye]['h'] = $idQA;
                                                }
                                            }

                                            if ($k == 'question_truefalse') {
                                                $position = array_search($value['question'], array_column($id_question, 'p'));
                                                if (is_int($position)) {
                                                    $value['question'] = $id_question[$position]['h'];
                                                    $id_question_answers = array_values($id_question_answers);
                                                    $position = array_search($value['trueanswer'], array_column($id_question_answers, 'p'));
                                                    if (is_int($position)) {
                                                        $value['trueanswer'] = $id_question_answers[$position]['h'];
                                                        $position2 = array_search($value['falseanswer'], array_column($id_question_answers, 'p'));
                                                        if (is_int($position2)) {
                                                            $value['falseanswer'] = $id_question_answers[$position2]['h'];
                                                            $banck[$k][$value['id']]['p'] = $value['id'];
                                                            $banck[$k][$value['id']]['h'] = $DB->insert_record($k, $value);
                                                        }
                                                    }
                                                }
                                            }

                                            if ($k == 'question_attempts') {
                                                $id_question_usages = array_values($id_question_usages);
                                                $position = array_search($value['questionusageid'], array_column($id_question_usages, 'p'));
                                                if (is_int($position)) {
                                                    $value['questionusageid'] = $id_question_usages[$position]['h'];
                                                    $position2 = array_search($value['questionid'], array_column($id_question, 'p'));
                                                    if (is_int($position2)) {
                                                        $banck[$k][$value['id']]['p'] = $value['id'];
                                                        $value['questionid'] = $id_question[$position2]['h'];
                                                        $banck[$k][$value['id']]['h'] = $DB->insert_record($k, $value);
                                                    }
                                                }
                                            }

                                            if ($k == 'question_multianswer') {
                                                $parti = explode(',', $value['sequence']);
                                                $sequence = array();
                                                for ($g = 0; $g < count($parti); $g++) {
                                                    $position1 = array_search($parti[$g], array_column($id_question, 'p'));
                                                    if (is_int($position1)) {
                                                        $sequence[$g] = $id_question[$position1]['h'];
                                                    }
                                                }
                                                $position = array_search($value['question'], array_column($id_question, 'p'));
                                                if (is_int($position)) {
                                                    $value['question'] = $id_question[$position]['h'];
                                                    $value['sequence'] = implode(",", $sequence);
                                                    $banck[$k][$value['id']]['p'] = $value['id'];
                                                    $banck[$k][$value['id']]['h'] = $DB->insert_record($k, $value);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        if ((is_array($v) || is_object($v)) && strpos($k, 'type_')) {
                            //TODOS LOS TIPOS
                            foreach ($v as $key => $val) {
                                $val = (array) $val;
                                if (is_array($val) || is_object($val) && !empty($val)) {
                                    foreach ($val as $keye => $value) {
                                        if (is_array($value) || is_object($value)) {
                                            $value = (array) $value;
                                            if (
                                                $k == 'qtype_ddimageortext' || $k == 'qtype_ddimageortext_drags' || $k == 'qtype_ddimageortext_drops'
                                                || $k == 'qtype_ddmarker' || $k == 'qtype_ddmarker_drags' || $k == 'qtype_ddmarker_drops'
                                                || $k == 'qtype_essay_options' || $k == 'qtype_match_options' || $k == 'qtype_match_subquestions'
                                                || $k == 'qtype_multichoice_options' || $k == 'qtype_randomsamatch_options' || $k == 'qtype_shortanswer_options'
                                            ) {
                                                $id_question2 = array_values($id_question);
                                                $position = array_search($value['questionid'], array_column($id_question2, 'p'));

                                                if (is_int($position)) {
                                                    $value['questionid'] = $id_question2[$position]['h'];
                                                    $banck[$k][$value['id']]['p'] = $value['id'];
                                                    $banck[$k][$value['id']]['h'] = $value['id'] = $DB->insert_record($k, $value);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                } else print_r($results);

                $banck['question_categories'] = $id_question_categories;
                $banck['question_bank_entries'] = $id_question_bank_entries;
                $banck['question_versions'] = $id_question_versions;
                $banck['question'] = $id_question;

                return $banck;
            }
            return $tb_wl;
        }
    }

    private function getBanckContenido()
    {
        global $DB, $CFG, $USER;

        $registro = (object) $_POST;

        $tb_wl = $DB->get_record_sql('SELECT * FROM {bc_white_list} LIMIT 1');


        if (!empty($tb_wl)) {

            $url_p = ($tb_wl);
            if ($url_p->estado == '1') {

                $tok = '2017.UVD_TokeN_noDos';
                $url = $this->formatHttp($url_p->url) . '/webservice/rest/server.php?wstoken=' . sha1($tok) . '&wsfunction=local_remoter_banco_preguntas&moodlewsrestformat=json';
                $params = array(
                    'function' => $registro->key,
                    'id_course' => $registro->id_course,
                    'id_nodo' => $registro->id_nodo,
                );
                $curl = new curl;
                $results = json_decode($curl->post($url, $params));
                $contentbank_files = array();
                $contentbank = array();
                $banck = array();
                //echo '<pre>getBanckContenido: ';  var_dump($results); echo '</pre>'; die();
                if (!empty($results) && property_exists($results, 'response')) {
                    $context = array();
                    $all_context = $DB->get_record('context', array('instanceid' => $registro->id_nodo, 'contextlevel' => 50));
                    //$all_context = each($all_context);

                    $location = $CFG->dataroot . '/temp/';
                    $extracion = $CFG->dataroot . '/temp/course_' . $registro->id_nodo . '/';
                    $info = json_decode($results->response);

                    foreach ($info as $k => $v) {
                        /////////comprobar que exista un item que crea el zip
                        if ($k == 'contentbank_files' && !empty($v)) {

                            require_once '../../folder_S3/controlador2_m.php';
                            $s3 = new Controlador2_m(); //hacer transfer
                            $s3->run('transfer', 'course_' . $registro->id_nodo . '.zip', $location, $registro->id_nodo);
                            $s3->run('delete', null, 'course_' . $registro->id_nodo . '.zip', $registro->id_nodo);

                            if (file_exists($location . 'course_' . $registro->id_nodo . '.zip')) {
                                $zip = new ZipArchive;
                                if ($zip->open($location . 'course_' . $registro->id_nodo . '.zip') === TRUE) {
                                    $zip->extractTo($extracion);
                                    $zip->close();
                                } else echo 'failed descomprimiendo';
                                //unlink($location . 'course_' . $registro->id_nodo . '.zip');
                            }
                            break;
                        }
                    }
                    foreach ($info as $k => $v) {
                        if ($k == 'contentbank') {
                            if (!empty($v)) {
                                foreach ($v as $keey => $valuee) {
                                    $valuee = (array) $valuee;
                                    $valuee['contextid'] = $all_context->id;
                                    $valuee['usercreated'] = $USER->id;
                                    $valuee['usermodified'] = $USER->id;
                                    $contentbank[$keey] = array();
                                    $contentbank[$keey]['p'] = $valuee['id'];
                                    $id_con_ba = $DB->insert_record('contentbank_content', $valuee);
                                    $contentbank[$keey]['h'] = $id_con_ba;
                                }
                            }
                        }
                        if ($k == 'contentbank_files') {
                            if (!empty($v)) {
                                $cate_bank = array_values($contentbank);


                                foreach ($v as $keey => $valuee) {
                                    $valuee = (array) $valuee;
                                    $kId = $valuee['itemid'];
                                    $position = array_search($kId, array_column($cate_bank, 'p'));
                                    if (is_int($position)) $valuee['itemid'] = $cate_bank[$position]['h'];
                                    $valuee['contextid'] = $all_context->id;
                                    $valuee['userid'] = $USER->id;
                                    $contentbank_files[$keey] = array();
                                    $contentbank_files[$keey]['p'] = $valuee['id'];
                                    $archivo = $extracion . $valuee['filename'];
                                    if (file_exists($archivo)) {
                                        $fs = get_file_storage();
                                        if ($id = $fs->create_file_from_pathname($valuee, $archivo)) {
                                            $contentbank_files[$keey]['h'] = $id;
                                            //unlink($archivo);
                                        } //else throw new Exception('No se pudo crear el file ' . $valuee['filename']);
                                    }
                                }
                            }
                        }
                    }
                } else print_r($results);

                $banck['contentbank_files'] = $contentbank_files;
                $banck['contentbank'] = $contentbank;

                return $banck;
            }
            return $tb_wl;
        }
    }

    private function getFilesH5P()
    {
        global $DB, $CFG, $USER;

        $registro = (object) $_POST;

        $tb_wl = $DB->get_record_sql('SELECT * FROM {bc_white_list} LIMIT 1');


        if (!empty($tb_wl)) {

            $url_p = ($tb_wl);
            if ($url_p->estado == '1') {

                $tok = '2017.UVD_TokeN_noDos';
                $url = $this->formatHttp($url_p->url) . '/webservice/rest/server.php?wstoken=' . sha1($tok) . '&wsfunction=local_remoter_banco_preguntas&moodlewsrestformat=json';
                $params = array(
                    'function' => $registro->key,
                    'id_course' => $registro->id_course,
                    'id_nodo' => $registro->id_nodo,
                );
                $curl = new curl;
                $results = json_decode($curl->post($url, $params));
                $banck = array();
                if (!empty($results) && property_exists($results, 'response')) {
                    $context = array();
                    $all_context = $DB->get_record('context', array('instanceid' => $registro->id_nodo, 'contextlevel' => 50));
                    //$all_context = each($all_context);

                    $location = $CFG->dataroot . '/temp/';
                    $extracion = $CFG->dataroot . '/temp/courseH5P_' . $registro->id_nodo . '/';
                    $info = json_decode($results->response);

                    foreach ($info as $k => $v) {
                        /////////comprobar que exista un item que crea el zip
                        if (($k == 'h5p_files' || $k == 'hvp_files') && !empty($v)) {

                            require_once '../../folder_S3/controlador2_m.php';
                            $s3 = new Controlador2_m(); //hacer transfer
                            $s3->run('transfer', 'courseH5P_' . $registro->id_nodo . '.zip', $location, $registro->id_nodo);
                            //$s3->run('delete', null, 'courseH5P_' . $registro->id_nodo . '.zip', $registro->id_nodo);

                            if (file_exists($location . 'courseH5P_' . $registro->id_nodo . '.zip')) {
                                $zip = new ZipArchive;
                                if ($zip->open($location . 'courseH5P_' . $registro->id_nodo . '.zip') === TRUE) {
                                    $zip->extractTo($extracion);
                                    $zip->close();
                                } else echo 'failed descomprimiendo';
                                unlink($location . 'courseH5P_' . $registro->id_nodo . '.zip');
                            }
                            break;
                        }
                    }
                    foreach ($info as $k => $v) {
                        if ($k == 'h5p_files') {
                            if (!empty($v)) $banck['h5p_files'] = $v;
                        }

                        if ($k == 'hvp_files') {
                            if (!empty($v)) $banck['hvp_files'] = $v;
                        }
                    }
                } else print_r($results);

                return $banck;
            }
            return $tb_wl;
        }
    }

    private function getFilesResource()
    {
        global $DB, $CFG, $USER;

        $registro = (object) $_POST;

        $tb_wl = $DB->get_record_sql('SELECT * FROM {bc_white_list} LIMIT 1');


        if (!empty($tb_wl)) {

            $url_p = ($tb_wl);
            if ($url_p->estado == '1') {

                $tok = '2017.UVD_TokeN_noDos';
                $url = $this->formatHttp($url_p->url) . '/webservice/rest/server.php?wstoken=' . sha1($tok) . '&wsfunction=local_remoter_banco_preguntas&moodlewsrestformat=json';
                $params = array(
                    'function' => $registro->key,
                    'id_course' => $registro->id_course,
                    'id_nodo' => $registro->id_nodo,
                );
                $curl = new curl;
                $results = json_decode($curl->post($url, $params));

                $banck = array();
                if (!empty($results) && property_exists($results, 'response')) {
                    $context = array();
                    $all_context = $DB->get_record('context', array('instanceid' => $registro->id_nodo, 'contextlevel' => 50));
                    //$all_context = each($all_context);

                    $location = $CFG->dataroot . '/temp/';
                    $extracion = $CFG->dataroot . '/temp/courseResource_' . $registro->id_nodo . '/';
                    $info = json_decode($results->response);
                    foreach ($info as $k => $v) {
                        /////////comprobar que exista un item que crea el zip
                        if ($k == 'resource_files' && !empty($v)) {

                            require_once '../../folder_S3/controlador2_m.php';
                            $s3 = new Controlador2_m(); //hacer transfer
                            $s3->run('transfer', 'courseResource_' . $registro->id_nodo . '.zip', $location, $registro->id_nodo);
                            $s3->run('delete', null, 'courseResource_' . $registro->id_nodo . '.zip', $registro->id_nodo);

                            if (file_exists($location . 'courseResource_' . $registro->id_nodo . '.zip')) {
                                $zip = new ZipArchive;
                                if ($zip->open($location . 'courseResource_' . $registro->id_nodo . '.zip') === TRUE) {
                                    $zip->extractTo($extracion);
                                    $zip->close();
                                } else echo 'failed descomprimiendo';
                                //unlink($location . 'courseResource_' . $registro->id_nodo . '.zip');
                            }
                            break;
                        }
                    }
                    foreach ($info as $k => $v) {
                        if ($k == 'resource_files') {
                            if (!empty($v)) $banck['resource_files'] = $v;
                        }
                    }
                } else print_r($results);

                return $banck;
            }
            return $tb_wl;
        }
    }

    private function getRubric()
    {
        global $DB, $CFG, $USER;

        $registro = (object) $_POST;

        $tb_wl = $DB->get_record_sql('SELECT * FROM {bc_white_list} LIMIT 1');

        if (!empty($tb_wl)) {

            $url_p = ($tb_wl);

            if ($url_p->estado == '1') {

                $tok = '2017.UVD_TokeN_noDos';
                $url = $this->formatHttp($url_p->url) . '/webservice/rest/server.php?wstoken=' . sha1($tok) . '&wsfunction=local_remoter_rubrica&moodlewsrestformat=json';

                $params = array(
                    'function' => $registro->key,
                    'id_course' => $registro->id_course,
                    'id_nodo' => $registro->id_nodo,
                );

                $curl = new curl;
                $results = json_decode($curl->post($url, $params));

                if (!empty($results) && property_exists($results, 'response')) {

                    $info = json_decode($results->response);

                    return $info;
                }
            }
        }
    }

    private function getSectionsNew($courseid)
    {
        global $DB;
        $sections = $DB->get_record('bc_add_sections_activities', array('courseid' => $courseid));
        return ($sections);
    }

    private function countAtivities($courseid, $section)
    {
        global $DB,  $USER;
        $res = [];
        $log = [];

        $section = $DB->get_record('course_sections', array('course' => $courseid, 'section' => $section));
        if ($section) {
            $modules = $DB->get_records('course_modules', array('section' => $section->id, 'deletioninprogress' => 0));
            foreach ($modules as $module) {
                $context = $DB->get_record('context', array('instanceid' => $module->id, 'contextlevel' => 70));
                if ($context) $log += $DB->get_records('logstore_standard_log', array('action' => 'created', 'userid' => $USER->id, 'contextid' => $context->id));
            }
        }

        $permisos = $DB->get_record_sql(
            'SELECT p.id, r.courseid_sp, r.courseid_sh
                                ,p.reemplazar, p.permiso, p.cant_secciones, p.cant_actividades,p.cant_recursos
                              FROM {bc_permisos_aplicados} p
                              LEFT JOIN {bc_rel_padre_hijo} r ON r.courseid_sp = p.idcourse
                              WHERE r.courseid_sh = :idcourse1
                                 OR (r.courseid_sh IS NULL AND p.idcourse = 0)
                              ORDER BY p.idcourse DESC, p.fecha DESC LIMIT 1',
            array('idcourse1' => $courseid)
        );
        $res[0] = $log; // log de creación de actividades      
        $res[1] = 0;   // cant_recursos   
        $res[2] = 0;   //cant_actividades         
        //cuando el permiso es completamente restringido 2, se deja en 0 para no permitir crear 
        if (!empty($permisos)) {
            $res[1] = $permisos->permiso == 2 ? 0 : $permisos->cant_recursos;
            $res[2] = $permisos->permiso == 2 ? 0 : $permisos->cant_actividades;
        }

        return $res;
    }

    private function misActividades($courseid)
    {
        global $DB, $USER;
        $res = [];
        $log = [];

        $sections = $DB->get_records('course_sections', array('course' => $courseid));
        if ($sections) {
            foreach ($sections as $section) {
                //print_r($section);
                $sequence = (!strpos($section->sequence, ',')) ?  $section->sequence : explode(",", $section->sequence);
                //print_r($sequence);
                if (gettype($sequence) == 'string') {
                    $context = $DB->get_record('context', array('instanceid' => $sequence, 'contextlevel' => 70));
                    if ($context) $log += $DB->get_records('logstore_standard_log', array('action' => 'created', 'userid' => $USER->id, 'contextid' => $context->id));
                } else {
                    foreach ($sequence as $key => $value) {
                        $context = $DB->get_record('context', array('instanceid' => $value, 'contextlevel' => 70));
                        if ($context) $log += $DB->get_records('logstore_standard_log', array('action' => 'created', 'userid' => $USER->id, 'contextid' => $context->id));
                    }
                }
            }
        }
        $permisos = $DB->get_record_sql(
            'SELECT p.id, r.courseid_sp, r.courseid_sh
                                            ,p.reemplazar, p.permiso, p.cant_secciones, p.cant_actividades,p.cant_recursos
                                          FROM {bc_permisos_aplicados} p
                                          LEFT JOIN {bc_rel_padre_hijo} r ON r.courseid_sp = p.idcourse
                                          WHERE r.courseid_sh = :idcourse1
                                             OR (r.courseid_sh IS NULL AND p.idcourse = 0)
                                          ORDER BY p.idcourse DESC, p.fecha DESC LIMIT 1 ',
            array('idcourse1' => $courseid)
        );
        $res[0] = $log;
        $res[1] = 2;
        //sin ningún permiso
        if (!empty($permisos)) {
            $res[1] = $permisos->permiso == 2 ? 0 : $permisos->cant_actividades;
        }

        return $res;
    }

    private function miActividad($moduleid)
    {
        global $DB, $USER;

        $context = $DB->get_record('context', array('instanceid' => $moduleid, 'contextlevel' => 70));
        $log = ($context) ? $DB->get_records('logstore_standard_log', array('action' => 'created', 'userid' => $USER->id, 'contextid' => $context->id)) : null;
        return $log;
    }

    private function encrypt($texto, $clave)
    {
        // Generar un vector de inicialización (IV) aleatorio
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));

        // Cifrar el texto utilizando AES-256-CBC
        $texto_cifrado = openssl_encrypt($texto, 'aes-256-cbc', $clave, 0, $iv);

        // Retornar el texto cifrado y el IV como una cadena codificada en base64
        return base64_encode($iv . $texto_cifrado);
    }

    private function setFormatOption($format_options, $id_course)
    {
        global $DB;
        $format_options = json_decode($format_options);
        if (!empty($format_options)) {

            $DB->delete_records('course_format_options', array('courseid' => $id_course));

            foreach ($format_options as $key => $value) {

                $options = [
                    'courseid' => $id_course,
                    'format' => $value->format,
                    'sectionid' => $value->sectionid,
                    'name' => $value->name,
                    'value' => $value->value
                ];

                $DB->insert_record('course_format_options', $options);
            }
        }
    }
}

queryNodo::run();
