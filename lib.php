<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 * @package    local_remote_backup_provider
 * @copyright  2015 Lafayette College ITS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function local_backup_course_extend_navigation_course($navigation, $course, $context)
{

    $urlself = $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
    global $PAGE, $USER, $OUTPUT, $CFG, $DB;
    echo '<link rel="stylesheet" type="text/css" href="' . $CFG->wwwroot . '/local/backup_course/css/adit_activities.css' . '">';
    if (get_config('local_backup_course', 'instancia') == '1') { //el nodo es hijo
        $conf_hijo = $DB->get_record('bc_registro_pc', array('nombre' => 'Padre')); // unión a un padre

        $course_import_ya = $DB->get_record('bc_rel_padre_hijo', array('courseid_sh' => $course->id)); //el curso se ha importado
        $borrado = 0; //saber si las secciones existen en el curso
        if (!empty($conf_hijo) && !empty($course_import_ya) && property_exists($course_import_ya, 'objet_ph') && !empty($course_import_ya->objet_ph)) { //está unido a un padre, se ha importado el curso, tiene el objeto de importación
            $objet_ph = json_decode($course_import_ya->objet_ph);
            if (property_exists($objet_ph, 'sectionAndActi') && property_exists($objet_ph->sectionAndActi, 'sections')) { //el objeto tiene las secciones
                $sections = $objet_ph->sectionAndActi->sections;
                foreach ($sections as $key => $value) { //recorrer las secciones del objeto de importación
                    if (property_exists($value, 'idsec_hijo')) {
                        $sec = $DB->get_record('course_sections', array('id' => $value->idsec_hijo));
                        if (!empty($sec)) { // existe las secciones que se importaron
                            $borrado++;
                        }
                    }
                }
            }
        }

        if (!is_siteadmin($USER->id)) { //el usuario no es un admin

            echo '<link href="' . $CFG->wwwroot . '/local/backup_course/css/style.css" rel="stylesheet" type="text/css" />';
            echo '<link href="' . $CFG->wwwroot . '/local/backup_course/css/jquery-confirm.css" rel="stylesheet" type="text/css" />';

            $PAGE->requires->js(new moodle_url("https://code.jquery.com/jquery-3.6.3.min.js"));

            /* echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css">'; */

            $PAGE->requires->js(new moodle_url($CFG->wwwroot . '/local/backup_course/js/active_edit.js'));

            if (!empty($conf_hijo) && !empty($course_import_ya) && property_exists($course_import_ya, 'objet_ph') && !empty($course_import_ya->objet_ph)) { //está unido a un padre, se ha importado el curso, tiene el objeto de importación
                $objet_ph = json_decode($course_import_ya->objet_ph);
                if (property_exists($objet_ph, 'sectionAndActi') && property_exists($objet_ph->sectionAndActi, 'sections')) { //el objeto tiene las secciones
                    $sections = $objet_ph->sectionAndActi->sections;
                    foreach ($sections as $key => $value) { //recorrer las secciones del objeto de importación
                        if (property_exists($value, 'idsec_hijo')) {
                            $sec = $DB->get_record('course_sections', array('id' => $value->idsec_hijo));
                            if (!empty($sec)) $borrado++; // existe las secciones que se importaron
                        }
                    }
                }
                $PAGE->requires->js_init_call('inn.no_copy'); // llamar al metodo para no permitir copiar

                if ($borrado != 0) {
                    $context1 = context_course::instance($course->id);
                    $roles = get_user_roles($context1, $USER->id);
                    if (!empty($roles)) {
                        foreach ($roles as $key => &$valor) { // Si es profesor con permisos de edición mostramos boton Editor
                            if ($valor->shortname == 'editingteacher') {
                                $PAGE->requires->js_init_call('inn.showBtnEditor', array('path' => $CFG->wwwroot));
                                break;
                            }

                            if ($valor->shortname == 'student') {
                                //echo $USER->id;
                                $PAGE->requires->js_init_call('inn.no_copy'); // llamar al metodo para no permitir copiar
                                break;
                            }
                        }
                    }
                }


                if ($borrado != 0 && (strpos($urlself, 'backup/import.php') || strpos($urlself, 'backup/backup.php'))) { // se va a importar por moodle o van a realizar una copia de seguridad

                    $context1 = context_course::instance($course->id);
                    $roles = get_user_roles($context1, $USER->id);
                    $profe = 0;

                    if (!empty($roles) && $conf_hijo->edition == 0) {
                        foreach ($roles as $key => &$valor) {
                            if ($valor->shortname == 'editingteacher') {
                                $PAGE->requires->js_init_call('inn.no_import', array('id_course' => $course->id, 'dir' => $CFG->wwwroot)); //curso hijo importado no permitir copia de seguridad
                            }
                        }
                    }
                } else if ($borrado != 0) { //el curso fue importado y la instancia está activa
                    $context1 = context_course::instance($course->id);
                    $roles = get_user_roles($context1, $USER->id);
                    $profe = 0;
                    if (!empty($roles)) {
                        foreach ($roles as $key => &$valor) {
                            if ($valor->shortname == 'editingteacher') { // si es profesor con permisos de edición
                                $profe = 1;
                                break;
                            }
                        }
                    }
                    $sql = 'SELECT p.id, r.courseid_sp, r.courseid_sh
                            ,p.reemplazar, p.permiso, p.cant_secciones, p.cant_actividades,p.cant_recursos
                          FROM {bc_permisos_aplicados} p
                          LEFT JOIN {bc_rel_padre_hijo} r ON r.courseid_sp = p.idcourse
                          WHERE r.courseid_sh = :idcourse1
                             OR (r.courseid_sh IS NULL AND p.idcourse = 0)
                          ORDER BY p.idcourse DESC, p.fecha DESC LIMIT 1';
                    $permisos = $DB->get_record_sql($sql, array('idcourse1' => $course->id));
                    //restringido pacial (1) y completamente (2)
                    //echo '<pre>'; print_r($permisos);echo '</pre>';
                    if (!empty($permisos) && ($permisos->permiso == 2 || $permisos->permiso == 1)) {
                        if (strpos($urlself, 'grade/edit/tree/index.php') && $profe) $PAGE->requires->js_init_call('inn.no_edit_calificaciones'); //el profesor quiere añadir una actividad
                        if (strpos($urlself, 'course/edit.php') && $profe) $PAGE->requires->js_init_call('inn.no_edit_config'); //el profesor quiere añadir una actividad
                        if (property_exists($USER, 'editing') && $USER->editing && $profe) {

                            $actAll = $DB->get_records('modules', array(), '', 'name');


                            $PAGE->requires->js_init_call('inn.list_actAll', array(array_keys($actAll))); // lista de TODAS las actividad
                            $PAGE->requires->js_init_call('inn.getTags', array($conf_hijo->edition, $course->id, $permisos->reemplazar, $permisos->permiso)); // crear tags de editar y reemplazar actividad
                            $PAGE->requires->js_init_call('inn.no_edit', array(0)); // llamar al metodo que oculta el menú de editar
                            $PAGE->requires->js_init_call('inn.delete_menu');
                            $PAGE->requires->js_init_call('inn.ocultarBotonesLeccion');
                            if (strpos($urlself, 'course/view.php')) $PAGE->requires->js_init_call('inn.ocultarTodaEdit', array($permisos->permiso));
                            $url = $_SERVER['REQUEST_URI'];
                            if (strpos($url, 'course/modedit.php') !== false) {
                            } else  $PAGE->requires->js_init_call('inn.ocultar_elementos_atendance', array($course->id));

                            if (strpos($urlself, 'feedback/edit.php')) $PAGE->requires->js_init_call('inn.feedback_edit');

                            if (strpos($urlself, 'quiz/attempt.php')) {
                                $PAGE->requires->js_init_call('inn.quiz_edit');
                                $PAGE->requires->js_init_call('inn.delete_menu');
                            }
                        } else if (!property_exists($USER, 'editing') || (!$USER->editing && $profe)) {

                            $PAGE->requires->js_init_call('inn.no_edit', array(0)); // llamar al metodo que oculta el menú de editar
                            $PAGE->requires->js_init_call('inn.ocultarBotonesLeccion');
                            $PAGE->requires->js_init_call('inn.delete_menu');
                            $PAGE->requires->js_init_call('inn.cerrarLoader');
                        }

                        if ($profe) {


                            $add_category = $DB->get_record_sql(
                                'SELECT * FROM {grade_items} i 
                                                                    INNER JOIN {grade_categories} c ON c.id = i.iteminstance
                                                                    WHERE i.courseid = :courseid AND i.itemtype = "category" 
                                                                    AND i.idnumber LIKE "ACT_EXTRA"',
                                array('courseid' => $course->id)
                            ); //buscar que exista la categoria ACT_EXTRA

                            if (!empty($add_category)) {
                                if (
                                    strpos($_SERVER['REQUEST_URI'], 'course/modedit.php?add=')
                                    || strpos($_SERVER['REQUEST_URI'], 'bank/modedit.php?add=')
                                ) {
                                    $PAGE->requires->js_init_call('inn.category_act_extra', array($add_category)); //el profesor quiere añadir una actividad
                                }
                            }

                            if (strpos($urlself, 'course/view.php')) { //el profesor está en el curso
                                $add_sect_acti = $DB->get_record('bc_add_sections_activities', array('courseid' => $course->id));
                                //si la categoria extra NO existe no dejar añadir nuevas secciones
                                if (empty($add_category)) $PAGE->requires->js_init_call('inn.NO_add_sect_actividad', array('No puede añadir secciones porque el curso no tiene categoría extra en las calificaciones'));
                                else { //nuevas seeciones
                                    $sect = $DB->get_record_sql('SELECT * FROM {course_sections} WHERE course = :course ORDER BY section DESC LIMIT 1;', array('course' => $course->id));
                                    $PAGE->requires->js_init_call('inn.add_activity_section', array($add_sect_acti)); //permitir añadir una sección de penultimo
                                    //si existe la extra permitir añadir secciones hasta la config de cantidad de secciones
                                    if ($permisos->cant_secciones > 0 && (empty($add_sect_acti)  || (property_exists($add_sect_acti, 'section') && (empty($add_sect_acti->section) || $add_sect_acti->section < $permisos->cant_secciones))))
                                        $PAGE->requires->js_init_call('inn.add_section_penultimo', array($sect->id, $sect->course)); //permitir añadir una sección de penultimo
                                    else if (is_object($add_sect_acti) && property_exists($add_sect_acti, 'section') && $add_sect_acti->section) {
                                        //echo '<pre>section:'; print_r($add_sect_acti->section); echo '</pre>'; 
                                        //echo '<pre>cant_secciones:'; print_r($permisos->cant_secciones); echo '</pre>'; 
                                        $PAGE->requires->js_init_call('inn.NO_add_sections', array('No puede añadir secciones porque ya creó ' . $permisos->cant_secciones . ' secciones'));
                                    } else if ($permisos->cant_secciones < 1) $PAGE->requires->js_init_call('inn.NO_add_sections', array('No puede añadir secciones porque están configuradas en ' . $permisos->cant_secciones . ' secciones'));
                                }
                            }
                        }
                    }
                    //si está desbloqueado pero igual se puede reemplazar activiades del banco
                    elseif (!empty($permisos)) $PAGE->requires->js_init_call('inn.getTags', array($conf_hijo->edition, $course->id, $permisos->reemplazar)); // crear tags de editar y reemplazar actividad



                    if (!$profe) {
                        if ($USER->editing) {
                            $PAGE->requires->js_init_call('inn.getTags', array($conf_hijo->edition, $course->id, 0));
                            $PAGE->requires->js_init_call('inn.cerrarLoader');
                        }
                    }
                } else if ($borrado == 0 && !empty($conf_hijo) && property_exists($conf_hijo, 'id') && !empty($conf_hijo->id)) { // el curso fue borrado pero sigue el registro de que se importó    
                    $tok = sha1('2017.UVD_TokeN_noDos');
                    $url_actual = explode('/local/', $_SERVER['HTTP_REFERER']);
                    $url = $conf_hijo->url_padre . '/webservice/rest/server.php?wstoken=' . $tok . '&wsfunction=local_remoter_register_node&moodlewsrestformat=json';
                    $params = array(
                        'function' => 'D02',
                        'url' => $conf_hijo->url_padre,
                        'nombre' => '',
                        'token' => $conf_hijo->token,
                        /* 'ip'=>$_SERVER['SERVER_ADDR'], */
                        'url_hijo' => $CFG->wwwroot,
                        'estado' => $conf_hijo->estado,
                        'edition_acti' => $conf_hijo->edition,
                        'startdate' => 0,
                        'enddate8' => $course_import_ya->courseid_sp,
                        'enddate16' => $course_import_ya->courseid_sh,

                    );
                    $curl = new curl;
                    $results = json_decode($curl->post($url, $params));
                    if (!empty($results)) {
                        if (array_key_exists('0', $results) && $results[0]->ack == 1) {
                            if (!$DB->delete_records('bc_rel_padre_hijo', array('id' => $course_import_ya->id))) {
                                /* echo 'No se eliminó la relación del curso padre con el hijo en el Hijo'; */
                            }
                        }
                    }
                }
            }
        } else {
            if ($borrado == 0 && !empty($conf_hijo) && !empty($course_import_ya) && property_exists($course_import_ya, 'courseid_sp')) { // el curso fue borrado pero sigue el registro de que se importó    
                $tok = sha1('2017.UVD_TokeN_noDos');
                $url_actual = explode('/local/', $_SERVER['HTTP_REFERER']);
                $url = $conf_hijo->url_padre . '/webservice/rest/server.php?wstoken=' . $tok . '&wsfunction=local_remoter_register_node&moodlewsrestformat=json';
                $params = array(
                    'function' => 'D02',
                    'url' => $conf_hijo->url_padre,
                    'nombre' => '',
                    'token' => $conf_hijo->token,
                    /* 'ip'=>$_SERVER['SERVER_ADDR'], */
                    'url_hijo' => $CFG->wwwroot,
                    'estado' => $conf_hijo->estado,
                    'edition_acti' => $conf_hijo->edition,
                    'startdate' => 0,
                    'enddate8' => 0,
                    'enddate16' => 0,
                );
                $curl = new curl;
                $results = json_decode($curl->post($url, $params));
                if (!empty($results)) {
                    if (array_key_exists('0', $results) && $results[0]->ack == 1) {
                        if (!$DB->delete_records('bc_rel_padre_hijo', array('id' => $course_import_ya->id))) {
                            /* echo 'No se eliminó la relación del curso padre con el hijo en el Hijo'; */
                        }
                    }
                }
            }
        }

        if (
            has_capability('mod/assign:addinstance', $context) && is_object($conf_hijo)
            && property_exists($conf_hijo, 'estado') && $conf_hijo->estado == 1
        ) { //si el usuario tiene permisos para añadir assigen en el curso y si está activo el nodo, si se ha creado el HVP

            $url = new moodle_url('/local/backup_course/forms/search_courses/view_search_course.php', array('id_nodo' => $course->id));
            $navigation->add(
                get_string('import', 'local_backup_course'),
                $url,
                navigation_node::TYPE_SETTING,
                null,
                null,
                new pix_icon('i/import', '')
            );
        }
    }
    // del lado del padre
    if (get_config('local_backup_course', 'instancia') == '0') {



        if (
            has_capability('local/backup_course:access', $context) && get_config('local_backup_course', 'instancia') == '0' &&
            strpos($urlself, '/course/')                      ||
            (strpos($urlself, '/mod/')    || strpos($urlself, '/user/') || strpos($urlself, 'grade/edit/tree/item.php') ||
                strpos($urlself, '/grade/')  || strpos($urlself, 'local/backup_course/update'))
        ) { // del lado del padre

            $tb_reg = $DB->get_records_sql('SELECT rel.registroid, COUNT(rel.registroid) AS tot_courses, reg.url_hijo, reg.token
                                        FROM {bc_rel_padre_hijo} rel
                                        LEFT JOIN {bc_registro_pc} reg ON reg.id = rel.registroid 
                                        WHERE rel.courseid_sp = :courseid_sp AND reg.url_hijo is not null
                                        GROUP BY reg.url_hijo, rel.registroid, reg.token ', array('courseid_sp' => $course->id));
            if (!empty($tb_reg)) {

                if (strpos($urlself, '/mod/feedback/edit_item') || strpos($urlself, '/mod/feedback/edit.php')) {
                    $update = optional_param('update', 0, PARAM_INT);
                    $id = optional_param('id', 0, PARAM_INT);
                    $id_act = ($id != 0) ? $id : $update;
                    $cmid = optional_param('cmid', 0, PARAM_INT);
                    $id_act = ($cmid != 0) ? $cmid : $id_act;

                    if (strpos($urlself, '/mod/feedback/edit_item') && !empty($id)) {
                        $jsGetDataC = array(
                            'id_course'  => $course->id,
                            'id_act'     => $id_act,
                            'type_act'   => 'feedback',
                            'section'    => null,
                            'user'       => $USER->id,
                        );
                    } else if (strpos($urlself, '/mod/feedback/edit.php')) {
                        $cm     = get_coursemodule_from_id('', $id, 0, true, MUST_EXIST);
                        $jsGetDataC = array(
                            'id_course'  => $course->id,
                            'id_act'     => $cm->id,
                            'type_act'   => $cm->modname,
                            'section'    => $cm->sectionnum,
                            'user'       => $USER->id,
                        );
                    }

                    $PAGE->requires->js(new moodle_url($CFG->wwwroot . '/local/backup_course/update/js/objetsUpdates/updateObjet.js'));
                    $PAGE->requires->js(new moodle_url($CFG->wwwroot . '/local/backup_course/update/js/objetsUpdates/CRE.js'));
                    $PAGE->requires->js(new moodle_url($CFG->wwwroot . '/local/backup_course/update/js/objetsUpdates/UPD.js'));
                    $PAGE->requires->js(new moodle_url($CFG->wwwroot . '/local/backup_course/update/js/buttons.js'));
                    $PAGE->requires->js_init_call('AFB.clickInButton', $jsGetDataC);
                } else if ((strpos($urlself, '/course/') || strpos($urlself, '/mod/') || strpos($urlself, 'local/backup_course/update') || strpos($urlself, '/grade/') || strpos($urlself, '/user/'))) {
                    $PAGE->requires->js(new moodle_url($CFG->wwwroot . '/local/backup_course/update/js/buttons.js'));
                    $PAGE->requires->js_init_call('AFB.btnCancel');
                    $PAGE->requires->js_init_call('AFB.tagAlink');
                    if (strpos($urlself, '/course/view.php') && property_exists($USER, 'editing') && $USER->editing) { //redirect form de add activity in hijo desde el padre

                        $PAGE->requires->js(new moodle_url($CFG->wwwroot . '/local/backup_course/js/ajax/moveSecAndActi.js'));
                        $PAGE->requires->js_init_call('mov.move_dis');

                        $PAGE->requires->js(new moodle_url($CFG->wwwroot . '/local/backup_course/create/js/up_create.js'));
                        $PAGE->requires->js_init_call('r_url.form_add'); // llamar función para redireccionar el form de creación
                    }
                } else {
                    $update = optional_param('update', 0, PARAM_INT);
                    $id = optional_param('id', 0, PARAM_INT);
                    $id_act = ($id != 0) ? $id : $update;
                    $cmid = optional_param('cmid', 0, PARAM_INT);
                    $id_act = ($cmid != 0) ? $cmid : $id_act;
                    if (!empty($id_act)) {
                        if (!strpos($urlself, 'grade/edit/tree/')) {
                            $cm     = get_coursemodule_from_id('', $id_act, 0, true, MUST_EXIST);
                            $jsGetDataC = array(
                                'id_course'  => $course,
                                'id_act'     => $cm,
                                'type_act'   => $cm->modname,
                                'section'    => $cm->sectionnum,
                                'user'       => $USER->id,
                            );
                        }
                    }
                    /*echo '<br><br>';
                    print_r($jsGetDataC);*/
                    if (strpos($urlself, 'question/question.php')) {
                        $PAGE->requires->js(new moodle_url($CFG->wwwroot . '/local/backup_course/update/js/buttons.js'));
                        $PAGE->requires->js_init_call('AFB.saveAndReturn', $jsGetDataC);
                    } else if (strpos($urlself, 'grade/edit/tree/index.php')) {
                        $jsGetDataC = array(
                            'id_course'  => $course->id,
                            'id_act'     => optional_param('id', 0, PARAM_INT),
                            'type_act'   => 'course',
                            'section'    => null,
                            'user'       => $USER->id,
                        );
                        //echo '<br><br>aqui11111';
                        //print_r($jsGetDataC);

                        $PAGE->requires->js(new moodle_url($CFG->wwwroot . '/local/backup_course/update/js/objetsUpdates/updateObjet.js'));
                        $PAGE->requires->js(new moodle_url($CFG->wwwroot . '/local/backup_course/update/js/buttons.js'));
                        $PAGE->requires->js(new moodle_url($CFG->wwwroot . '/local/backup_course/update/js/objetsUpdates/CRE.js'));
                        $PAGE->requires->js(new moodle_url($CFG->wwwroot . '/local/backup_course/update/js/objetsUpdates/UPD.js'));
                        $PAGE->requires->js(new moodle_url($CFG->wwwroot . '/local/backup_course/js/objetos/QRY.js'));
                        $PAGE->requires->js_init_call('AFB.clickInButton', $jsGetDataC);
                        $PAGE->requires->js_init_call('AFB.btnCancel');
                    } else if (strpos($urlself, 'grade/edit/tree/item.php')) {

                        $jsGetDataC = array(
                            'id_course'  => $course->id,
                            'id_act'     => optional_param('id', 0, PARAM_INT),
                            'type_act'   => 'course',
                            'section'    => null,
                            'user'       => $USER->id,
                        );

                        $PAGE->requires->js(new moodle_url($CFG->wwwroot . '/local/backup_course/update/js/buttons.js'));

                        $PAGE->requires->js_init_call('AFB.clickInButton', $jsGetDataC);
                        $PAGE->requires->js_init_call('AFB.btnCancel');
                    }
                }
            }
            echo '<script type="text/javascript" src="https://code.jquery.com/jquery-3.6.3.min.js"></script>';
            echo '<link href="' . $CFG->wwwroot . '/local/backup_course/css/style.css" rel="stylesheet" type="text/css" />';
            echo '<link href="' . $CFG->wwwroot . '/local/backup_course/css/jquery-confirm.css" rel="stylesheet" type="text/css" />';
        }
    }
}
