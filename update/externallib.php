<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once($CFG->dirroot . '/lib/externallib.php');

class local_update_external extends external_api
{

    /* 
     * Notificar al nodo de una actualización
     * Peticion del padre al nodo 
     * Estructura de los parametros que recibe el WS 
     */
    public static function find_notificateUpdate_parameters()
    {
        return new external_function_parameters(
            array(
                'url_padre' => new external_value(PARAM_RAW, 'url_padre'),
                'token' => new external_value(PARAM_CLEAN, 'token'),
                'idCourse_p' => new external_value(PARAM_CLEAN, 'idCourse_p'),
                'cant_courses' => new external_value(PARAM_CLEAN, 'cant_courses'),
            )
        );
    }

    /*
     * Notificar al nodo de una actualización
     * Peticion del padre al nodo
     * @params {string} $url_padre
     * @params {string} $token
     * @params {int} $idCourse_p
     * @params {int} $cant_courses_p
     * Retorna objeto con la cantidad de cursos del nodo relacionados con el curso padre
     * return {objet}
     */
    public static function find_notificateUpdate($url_padre, $token, $idCourse_p, $cant_courses_p)
    {
        global $DB;
        $cant_courses_h = $DB->get_records('bc_rel_padre_hijo', array('courseid_sp' => $idCourse_p));
        $params = array(
            'url_padre' => $url_padre,
            'token' => $token,
            'idCourse_p' => $idCourse_p,
        );

        $resp = new stdClass();
        /* $resp->obj_act_p = json_encode($cant_courses_h); */
        $resp->response = count($cant_courses_h);
        if (count($cant_courses_h) == $cant_courses_p) {
            $resp->ack = 1;
        } else {
            $resp->ack = 0;
        }

        return $resp;
    }

    /* 
     * Notificar al nodo de una actualización
     * Método para estructurar la respuesta
     */
    public static function find_notificateUpdate_returns()
    {
        return
            new external_single_structure(
                array(
                    'ack'  => new external_value(PARAM_RAW, 'respuesta 1 o 0'),
                    'response'  => new external_value(PARAM_RAW, 'respuesta objet de los cursos que coinciden'),
                    /* 'obj_act_p'  => new external_value(PARAM_RAW, 'Objeto para devolver al padre'), */
                )
            );
    }



    /* 
     * Notificar al nodo de una actualización
     * Peticion del padre al nodo 
     * Estructura de los parametros que recibe el WS 
     */
    public static function find_empezarUpdate_parameters()
    {
        return new external_function_parameters(
            array(
                'function' => new external_value(PARAM_CLEAN, 'function'),
                'url_padre' => new external_value(PARAM_RAW, 'url_padre'),
                'token' => new external_value(PARAM_RAW, 'token'),
                'idCourse_p' => new external_value(PARAM_CLEAN, 'idCourse_p'),
                'cant_courses' => new external_value(PARAM_CLEAN, 'cant_courses'),
                'id_updates_nodos' => new external_value(PARAM_CLEAN, 'id_updates_nodos'),
                'id_nodo_rel' => new external_value(PARAM_CLEAN, 'id_nodo_rel'),
                'id_updates_log' => new external_value(PARAM_CLEAN, 'id_updates_log'),
                'obj_act' => new external_value(PARAM_RAW, 'obj_act'),
            )
        );
    }

    /*
     * Notificar al nodo de una actualización
     * Peticion del padre al nodo
     * @params {string} $function
     * @params {string} $url_padre
     * @params {string} $token
     * @params {int} $idCourse_p
     * @params {int} $id_updates_nodos
     * @params {obj} $obj_act
     * @params {int} $cant_courses_p
     * Retorna objeto con la cantidad de cursos del nodo relacionados con el curso padre
     * return {objet}
     */
    public static function find_empezarUpdate($function, $url_padre, $token, $idCourse_p, $cant_courses_p, $id_updates_nodos, $id_nodo_rel, $id_updates_log, $obj_act)
    {
        global $DB, $CFG;
        $cant_courses_h = $DB->get_records('bc_rel_padre_hijo', array('courseid_sp' => $idCourse_p));
        //Function c11
        $params = array(
            'function' => $function,
            'url_padre' => $url_padre,
            'token' => $token,
            'idCourse_p' => $idCourse_p,
            'id_updates_nodos' => $id_updates_nodos,
            'cant_courses' => $cant_courses_p,
            'id_nodo_rel' => $id_nodo_rel,
            'id_updates_log' => $id_updates_log,
            'obj_act_p' => $obj_act,
            'obj_act_h' => $cant_courses_h
        );

        $resp = new stdClass();
        $resp->response = count($cant_courses_h);

        require_once($CFG->dirroot . '/local/backup_course/update/methods/ws_access.php');
        $permis = new ws_accessUpdate();
        $respuesta = $permis->perms($params);
        $resp->response = json_encode($respuesta);
        $resp->ack = $respuesta->cant;
        $resp->object_p = json_encode($respuesta->object_p);

        return $resp;
    }

    /* 
     * Notificar al nodo de una actualización
     * Método para estructurar la respuesta
     */
    public static function find_empezarUpdate_returns()
    {
        return
            new external_single_structure(
                array(
                    'ack'  => new external_value(PARAM_RAW, 'respuesta 1 o 0'),
                    'response'  => new external_value(PARAM_RAW, 'respuesta cantidad de cursos que coinciden'),
                    'object_p'  => new external_value(PARAM_RAW, 'Objeto para devolver al padre'),
                )
            );
    }


    /* 
     * Recibe la notificacion del nodo sobre la actualización de un curso
     * Peticion del nodo al hijo
     * Estructura de los parametros que recibe el WS 
     */
    public static function find_recibir_notifi_parameters()
    {
        return new external_function_parameters(
            array(
                'ack' => new external_value(PARAM_CLEAN, 'ack'),
                'response' => new external_value(PARAM_RAW, 'response'),
            )
        );
    }

    /*
     * Recibe la notificacion del nodo sobre la actualización de un curso
     * Peticion del nodo al padre
     * @params {int} $ack
     * @params {obj} $response
     * Retorna id de la inserción de la informacion del curso
     * return {objet}
     */
    public static function find_recibir_notifi($ack, $response)
    {
        global $DB, $CFG;
        $resp = new stdClass();
        $resp->ack = 0;

        $response = json_decode($response);
        $datos = $response->datosInsert;
        $params = array(
            'function' => 'U02',
            'datos' => $datos
        );
        if ($datos->estado == 3) {
            require_once($CFG->dirroot . '/local/backup_course/update/methods/ws_access.php');
            $permis = new ws_accessUpdate();
            $res = $permis->perms($params);
            if ($res) {
                $id_update_hijo = $DB->insert_record('updates_nodos_course', $datos);
                if (is_int($id_update_hijo)) {
                    if (!empty($response->objRel)) {
                        $params = array(
                            'function' => 'U03',
                            'datos' => $response->objRel,
                            'id_curso_sh' => $datos->id_curso_sh,
                            'id_nodo_rel' => $datos->id_nodo_rel,
                        );
                        $permis->perms($params);
                    }
                    $resp->ack = 1;
                    $resp->response = $id_update_hijo;
                } else {
                    $resp->response = 'No se insertó el curso en updates_nodos_course';
                }
            } else {
                $resp->response = json_encode($res);
            }
        } else {
            $resp->response = 'La actividad en el curso: ' . $datos->id_curso_sh . ' no se actualizó';
        }
        return $resp;
    }

    /* 
     * Recibe la notificacion del nodo sobre la actualización de un curso
     * Método para estructurar la respuesta
     */
    public static function find_recibir_notifi_returns()
    {
        return
            new external_single_structure(
                array(
                    'ack'  => new external_value(PARAM_RAW, 'respuesta 1 o 0'),
                    'response'  => new external_value(PARAM_RAW, 'respuesta id de insertar en updates_nodos_course'),
                )
            );
    }


    /* 
     * Notificar al nodo de una actualización
     * Peticion del padre al nodo 
     * Estructura de los parametros que recibe el WS 
     */
    public static function find_empezarUpdateCourse_parameters()
    {
        return new external_function_parameters(
            array(
                'function' => new external_value(PARAM_CLEAN, 'function'),
                'url_padre' => new external_value(PARAM_RAW, 'url_padre'),
                'token' => new external_value(PARAM_CLEAN, 'token'),
                'idCourse_p' => new external_value(PARAM_CLEAN, 'idCourse_p'),
                'cant_courses' => new external_value(PARAM_CLEAN, 'cant_courses'),
                'id_updates_nodos' => new external_value(PARAM_CLEAN, 'id_updates_nodos'),
                'id_nodo_rel' => new external_value(PARAM_CLEAN, 'id_nodo_rel'),
                'id_updates_log' => new external_value(PARAM_CLEAN, 'id_updates_log'),
                'obj_act' => new external_value(PARAM_RAW, 'obj_act'),
                'format_options' => new external_value(PARAM_RAW, 'format_options'),
                'block_options' => new external_value(PARAM_RAW, 'block_options'),
            )
        );
    }

    /*
     * Notificar al nodo de una actualización
     * Peticion del padre al nodo
     * @params {string} $function
     * @params {string} $url_padre
     * @params {string} $token
     * @params {int} $idCourse_p
     * @params {int} $id_updates_nodos
     * @params {obj} $obj_act
     * @params {int} $cant_courses_p
     * Retorna objeto con la cantidad de cursos del nodo relacionados con el curso padre
     * return {objet}
     */
    public static function find_empezarUpdateCourse($function, $url_padre, $token, $idCourse_p, $cant_courses_p, $id_updates_nodos, $id_nodo_rel, $id_updates_log, $obj_act, $format_options, $block_options)
    {
        global $DB, $CFG;

        $cant_courses_h = $DB->get_records('bc_rel_padre_hijo', array('courseid_sp' => $idCourse_p));

        $params = array(
            'function' => $function,
            'url_padre' => $url_padre,
            'token' => $token,
            'idCourse_p' => $idCourse_p,
            'id_updates_nodos' => $id_updates_nodos,
            'cant_courses' => $cant_courses_p,
            'id_nodo_rel' => $id_nodo_rel,
            'id_updates_log' => $id_updates_log,
            'obj_act_p' => $obj_act,
            'obj_act_h' => $cant_courses_h,
            'format_options' => $format_options,
            'block_options' => $block_options
        );

        $resp = new stdClass();
        $resp->response = count($cant_courses_h);
        if (count($cant_courses_h) == $cant_courses_p) {
            $resp->ack = 1;
            require_once($CFG->dirroot . '/local/backup_course/update/methods/ws_access.php');
            $permis = new ws_accessUpdate();
            $respuesta = $permis->perms($params);
            $resp->response = json_encode($respuesta);

            $resp->ack = $respuesta->cant;
        } else {
            $resp->ack = 0;
        }
        return $resp;
    }

    /* 
     * Notificar al nodo de una actualización
     * Método para estructurar la respuesta
     */
    public static function find_empezarUpdateCourse_returns()
    {
        return
            new external_single_structure(
                array(
                    'ack'  => new external_value(PARAM_RAW, 'respuesta 1 o 0'),
                    'response'  => new external_value(PARAM_RAW, 'respuesta cantidad de cursos que coinciden'),
                )
            );
    }

    public static function deleteQuestionQuiz_parameters()
    {

        return new external_function_parameters(
            array(
                'slot' => new external_value(PARAM_CLEAN, 'slot'),
                'idQuiz' => new external_value(PARAM_RAW, 'idQuiz'),
                'course' => new external_value(PARAM_RAW, 'course'),
            )
        );
    }

    public static function deleteQuestionQuiz($slotID, $idQuiz, $course)
    {
        global $DB, $CFG;

        $curso = $DB->get_records('bc_rel_padre_hijo', array('courseid_sp' => $course));

        $resp = new stdClass();

        $resp->slot = $slotID;

        foreach ($curso as $k => $v) {

            $obj = json_decode($v->objet_ph);

            foreach ($obj->sectionAndActi->sections as $ke => $va) {

                if (property_exists($va, 'activities')) {

                    foreach ($va->activities as $key => $val) {

                        foreach ($val as $key => $value) {

                            if ($value->table == "quiz" && $idQuiz == $value->id_acti_p) {

                                $idQuiz = $value->id_acti;

                                $resp->idQuiz = $idQuiz;

                                $quiz = $DB->get_record_sql('SELECT id FROM {quiz_slots} where quizid = ' . $idQuiz . ' AND slot =' . $slotID . '');

                                $quizId = intval($quiz->id);

                                $slot = $DB->get_records_sql('SELECT * FROM {quiz_slots} WHERE quizid = ' . $idQuiz . ' AND slot =' . $slotID . ' ORDER BY id ASC');

                                foreach ($slot as $key => $value) {

                                    $quices = $DB->get_record_sql('SELECT sumgrades FROM {quiz} WHERE id = ' . $idQuiz . '');

                                    $maxmark = ($quices->sumgrades - $value->maxmark);

                                    $table = 'quiz';
                                    $data = new stdClass();
                                    $data->id = $idQuiz;
                                    $data->sumgrades = $maxmark;

                                    $DB->update_record($table, $data);
                                }

                                $DB->delete_records_select('question_references', 'itemid =' . $quizId . '');

                                $DB->delete_records_select('quiz_slots', 'quizid = ' . $idQuiz . ' AND slot = ' . $slotID . '');

                                $slots = $DB->get_records_sql('SELECT * FROM {quiz_slots} WHERE quizid = ' . $idQuiz . ' ORDER BY id ASC');

                                $cont = 1;

                                foreach ($slots as $keys => $values) {

                                    $table = 'quiz_slots';
                                    $data = new stdClass();
                                    $data->id = $values->id; // ID del usuario que se va a actualizar
                                    $data->slot = $cont;

                                    $DB->update_record($table, $data);

                                    $cont++;
                                }

                                $resp->course = "Ok";
                            }
                        }
                    }
                }
            }
        }

        return $resp;
    }

    public static function deleteQuestionQuiz_returns()
    {

        return
            new external_single_structure(
                array(
                    'slot'  => new external_value(PARAM_RAW, 'respuesta 1 o 0'),
                    'idQuiz'  => new external_value(PARAM_RAW, 'parametros'),
                    'course'  => new external_value(PARAM_RAW, 'parametros'),
                )
            );
    }


    public static function mover_actividades_parameters()
    {
        return new external_function_parameters(

            array(
                'idCurso' => new external_value(PARAM_CLEAN, 'idCurso'),
                'course_sections' => new external_value(PARAM_CLEAN, 'course_sections'),
                'course_sections2' => new external_value(PARAM_CLEAN, 'course_sections2'),
            )

        );
    }

    public static function mover_actividades($idCurso, $course_sections, $course_sections2)
    {
        global $DB, $CFG;

        /* require_once(__DIR__ . '/config.php'); */
        require_once('../../config.php');
        require_once('../../group/lib.php');
        /* require_once('../../lib/setup.php'); */
        require_once($CFG->dirroot . "/cache/classes/helper.php");

        $course_sections = json_decode($course_sections);
        $course_sections2 = json_decode($course_sections2);

        $course_sections_p = clone $course_sections;

        $course_sections_clone = clone $course_sections2;

        $cursos = $DB->get_records('bc_rel_padre_hijo', array('courseid_sp' => $idCurso));

        $resp = new stdClass();

        foreach ($cursos as $kc => $vc) {

            $cursoH = $vc->courseid_sh;

            $course_sections_hijo = $DB->get_records('course_sections', array('course' => $cursoH), 'section ASC');

            $course_sections_hijo = (object) $course_sections_hijo;

            $course_sections_hijo_clone = clone $course_sections_hijo;

            $cantidadSectPadre = count(get_object_vars($course_sections));

            $cantidadSectHijo = count(get_object_vars($course_sections_hijo));

            if ($cantidadSectPadre ==  $cantidadSectHijo) {

                $cont = 10000;

                $ordenSections = new stdClass();

                foreach ($course_sections_p as $ksp => $vsp) {

                    foreach ($course_sections_hijo as $ksh => $vsh) {
                        $vsp->id = $vsh->id;
                        $vsp->course = $vsh->course;
                        $vsp->section = $cont;
                        /* $DB->update_record('course_sections', $vsp); */
                        $cont++;
                        unset($course_sections_hijo->$ksh);
                        break;
                    }
                }

                $rel = $DB->get_record('bc_rel_padre_hijo', array('courseid_sp' => $idCurso, 'courseid_sh' => $cursoH));

                $obj_ph = json_decode($rel->objet_ph);

                $sectiAndaci = $obj_ph->sectionAndActi->sections;

                foreach ($course_sections_clone as $ksp2 => $vsp2) {

                    foreach ($course_sections_hijo_clone as $ksh2 => $vsh2) {

                        if ($vsp2->section == $vsh2->section) {

                            $idpad = $vsp2->id;
                            $vsp2->id = $vsh2->id;
                            $vsp2->course = $vsh2->course;

                            $sequence = array();
                            $cont = 0;
                            foreach ($sectiAndaci as $kact => $vact) {
                                $cont2 = 0;

                                if (property_exists($vact, 'activities')) {

                                    foreach ($vact->activities as $knact => $vknact) {

                                        $array_sq = explode(",", $vsp2->sequence);

                                        $array_sq = (object) $array_sq;

                                        foreach ($array_sq as $key => $val) {
                                            foreach ($vknact as $ke => $va) {

                                                if ($va->id_como_p == $val) {
                                                    array_push($sequence, $va->id_como);
                                                    $ordenSections->{$vsp2->section}[$cont] = $vknact[$ke];
                                                    $cont++;
                                                }
                                            }
                                        }
                                    }
                                }
                            }

                            $string = implode(',', $sequence);

                            $vsp2->sequence = $string;

                            $DB->update_record('course_sections', $vsp2);
                        }
                    }
                }

                foreach ($sectiAndaci as $ksac => $vasc) {
                    foreach ($ordenSections as $kos => $vos) {
                        if ($kos == $ksac) {
                            $vAct = $vasc->activities[0];
                            $obj_ph->sectionAndActi->sections[$ksac]->activities[0] = $vos;
                        }
                    }
                }

                $rel->objet_ph =  json_encode($obj_ph);
            } else {

                return $resp->response = "La cantidad de secciones o actividades no coincide, realice la importación en el hijo";
            }

            rebuild_course_cache($cursoH);
        }

        $resp->response = "Hola mundo desde el hijo";

        return $resp;
    }

    public static function mover_actividades_returns()
    {
        return
            new external_single_structure(

                array(
                    'response'  => new external_value(PARAM_RAW, 'respuesta 1 o 0'),
                )

            );
    }
}
