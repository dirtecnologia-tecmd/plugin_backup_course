<?php
require_once('../../../../config.php');
require_once("$CFG->libdir/filelib.php");
require_once './excepcions_errors.php';

class create extends excepcions_errors
{

    public static function run()
    {
        $objCRE = new self();
        $idfunc = $_POST['key'];
        switch ($idfunc) {
            case 'C01':
                $resp = $objCRE->saveToken();
                break;
            case 'C02':
                $resp = $objCRE->saveSection();
                break;
            case 'C03':
                $resp = $objCRE->saveActivity();
                break;
            case 'C04':
                $resp = $objCRE->saveRelationPH();
                break;
            case 'C05':
                $resp = $objCRE->saveGrade_items();
                break;
            case 'C06':
                $resp = $objCRE->emailError();
                break;
            case 'C07':
                $resp = $objCRE->saveSectionPenultimo();
                break;
        }
        echo json_encode($resp);
        //redirect($FULLME);
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
     * Create token -> saveToken
     * Se une al hijo y crea un token correspondiente
     * Retorna la respuesta exitosa o inválida
     * return {objet};
     */
    private function saveToken()
    {
        global $DB, $CFG;
        $registro = (object)$_POST;
        $tok = sha1('2017.UVD_TokeN_noDos');
        $url_actual = explode('/local/', $_SERVER['HTTP_REFERER']);
        $url = $this->formatHttp($registro->data_child['node_domain']) . '/webservice/rest/server.php?wstoken=' . $tok . '&wsfunction=local_remoter_register_node&moodlewsrestformat=json';
        /*  var_dump($url); */
        $params = array(
            'function' => $registro->key,
            'url' => $this->formatHttp($url_actual[0]),
            'nombre' => '',
            'token' => $registro->token,
            /* 'ip'=>$_SERVER['SERVER_ADDR'], */
            'url_hijo' => $this->formatHttp($registro->data_child['node_domain']),
            'startdate' => $registro->data_child['startdate'],
            'enddate8' => $registro->data_child['enddate8'],
            'enddate16' => $registro->data_child['enddate16'],
            'estado' => $registro->data_child['node_status'],
            'edition_acti' => $registro->data_child['edition_acti'],
        );

        $curl = new curl;
        $results = json_decode($curl->post($url, $params));
        if (!empty($results)) {
            if (array_key_exists('0', $results) && $results[0]->ack == 1) {
                $registro_token = new stdClass();
                $registro_token->nombre = $registro->data_child['node_name'];
                /* $registro_token->ip = $registro->data_child['node_ip']; */
                $registro_token->url_hijo = $this->formatHttp($registro->data_child['node_domain']);
                $registro_token->startdate = $registro->data_child['startdate'];
                $registro_token->enddate8 = $registro->data_child['enddate8'];
                $registro_token->enddate16 = $registro->data_child['enddate16'];
                $registro_token->token = sha1($registro->token);
                $registro_token->estado = $registro->data_child['node_status'];
                $registro_token->edition = $registro->data_child['edition_acti'];
                $registro_token->url_padre = $this->formatHttp($url_actual[0]);
                $id_reg = $DB->insert_record('bc_registro_pc', $registro_token);
                $moodle_data = str_replace("\\", '/', $CFG->dirroot);
                $moodle_data = $moodle_data . '/local/backup_course/tmp/' . $id_reg . '/';
            }/*  else {
                echo '$results: ';
                print_r($results);
            } */
        }
        //}
        return $results;
    }

    /*
     * Create section -> saveSection
     * Crea las secciones del curso en el hijo
     * Retorna el id creado 
     * return {id};
     */
    private function saveSection()
    {
        global $DB;
        $registro = (object)$_POST;
        $registro->section['course'] = $registro->id_nodo;
        $registro->section['sequence'] = '';
        $registro->section['activities'] = null;
        if (empty($registro->section['availability'])) {
            $registro->section['availability'] = NULL;
        }
        $section = (object)$registro->section;
        if ($id = $DB->insert_record('course_sections', $section)) {
            return (int)$id;
        } else {
            return null;
        }
    }

    /*
     * Guardar que se creo una nueva sección en el curso
     * Retorna el id creado 
     * return {id};
     */
    private function saveSectionPenultimo()
    {
        global $DB, $USER;
        $insert = new stdClass();
        $insert->section = 1;
        $insert->courseid = $_POST['courseid'];
        $insert->userid = $USER->id;

        $sections = $DB->get_record('bc_add_sections_activities', array('courseid' => $_POST['courseid']));
        if ($sections) {
            $insert->id = $sections->id;
            $insert->section = $sections->section + 1;
            return $DB->update_record('bc_add_sections_activities', $insert);
        } else {
            if ($id = $DB->insert_record('bc_add_sections_activities', $insert)) return (int)$id;
            else return null;
        }
    }



    /*
     * Create Activity -> saveActivity
     * Guarda las actividades del curso en el hijo
     * Retorna objeto con la información creada
     * return {id};
     */
    private function saveActivity()
    {
        global $DB, $CFG, $USER;
        $objCRE = new self();
        $registro = (object)$_POST;

        $errors = new stdClass();
        $errors->courseid = $registro->id_nodo;
        $errors->userid = $USER->id;
        $return = new stdClass();
        $return->table = $registro->table;
        $return->id_como = 0;

        $section = (object)($registro->activity);
        $section->course = $registro->id_nodo;
        if ($registro->table == 'collaborate') {
            if (empty($section->sessionid)) $section->sessionid = null;
        }
        if ($registro->table == 'customcert') {
            if (empty($section->deliveryoption)) $section->deliveryoption = null;
        }
        if ($registro->table == 'hvp') {
            $section->filtered = json_encode($section->filtered);
            $main_library_id = $registro->info['main_library_id'];
            $library = $DB->get_record('hvp_libraries', array('machine_name' => $main_library_id['machine_name'], 'patch_version' => $main_library_id['patch_version'], 'major_version' => $main_library_id['major_version'], 'minor_version' => $main_library_id['minor_version']));
            if (!empty($library)) {
                $section->main_library_id = $library->id;
            } else {
                echo 'no existe la libreria: ' . $main_library_id['machine_name'] . ' version: ' . $main_library_id['major_version'] . '.' . $main_library_id['minor_version'] . ' instale la libreria e inténtelo de nuevo <pre>';
                print_r($main_library_id);
                echo '</pre>';
                die();
            }
        }
        $return->id_acti_p = $section->id;
        if ($objCRE->date_open_close($registro->table, $section, $registro->semana)) { //establecer fecha de inicio y fin de la actividad
            $id_acti = $DB->insert_record($registro->table, $section); // crear la actividad

            $return->id_acti = $id_acti;
        } else {
            echo 'No se pudo actualizar fechas';
        }

        // conocer el id de modules con el nombre del tipo de actividad
        $result = $DB->get_record_sql("SELECT MAX(tab.id) AS id, modu.id AS id_modu
                                        FROM {$CFG->prefix}{$registro->table} tab 
                                        LEFT JOIN {modules} modu ON modu.name = :name 
                                        WHERE tab.course = :course GROUP BY modu.id LIMIT 1", array('course' => $registro->id_nodo, 'name' => $registro->table));
        /*Guardar relacion*/
        if (!empty($result)) {
            $id = $result;
            $id_module = (int)$id->id_modu;
            $relation = array_values($registro->modules);
            $relation = (object)$relation[0];
            $return->id_como_p = $relation->id;
            $id_como = $objCRE->save_course_modules($id_module, $registro, $id_acti, $relation);
            $context = context_module::instance($id_como);
            $return->id_como = $id_como;
        }

        if (property_exists($registro, 'info')) { // guardar la informacion adicional de cada actividad
            $bank = null;
            if (property_exists($registro, 'banckPreguntas')) {
                $bank = (array)$registro->banckPreguntas;
            }

            //Realizar ordenamiento para correcta secuencia 
            if (isset($registro->info['lesson_pages'])) {
                $next = 0;
                $array_orden = [];

                foreach ($registro->info['lesson_pages'] as $key => $value) {

                    if ($value['prevpageid'] == 0) {
                        $next = $value['nextpageid'];
                    } else if ($value['prevpageid'] != 0) {
                        if ($next ==  $value['id'] && $next < $value['nextpageid']) {
                            $next = $value['nextpageid'];
                        } else {
                            array_push($array_orden, $value);
                            unset($registro->info['lesson_pages'][$key]);
                        }
                    }
                }

                // Llamada a la función recursiva si hay elementos desordenados
                if (count($array_orden) > 0) {
                    $resultado = $this->procesarArrayOrden($array_orden, $next);

                    foreach ($resultado as $indice => $valor) {
                        $registro->info['lesson_pages'][$valor['id']] = $valor;
                    }
                }
            }

            $return->info_actividad = $objCRE->getInfoAc($registro->table, $registro->info, $section, $registro->id_nodo, $relation, $id_module, $id_como, $id_acti, $bank);
        }
        if (property_exists($registro, 'bankH5P')) {
            $bankH5P = json_decode($registro->bankH5P); // Decodificar el JSON contenido en la propiedad 'bankH5P'
            if (!empty($bankH5P)) { // Verificar que el objeto decodificado no esté vacío
                // Llamar al método saveH5P_Bank y asignar el resultado a la propiedad 'bankH5P' del objeto $return
                $return->bankH5P = $objCRE->saveH5P_Bank($bankH5P, $return->id_como);
            }
        }

        return $return;
    }


    public function procesarArrayOrden(&$array_orden, $next)
    {
        $resultados = [];
        while (count($array_orden) > 0) {
            foreach ($array_orden as $k => $v) {
                if ($next == $v['id']) {
                    $next = $v['nextpageid'];
                    unset($array_orden[$k]);
                    $resultados[] = $v;
                    break; // Salir del bucle para procesar el siguiente
                }
            }
        }
        return $resultados; // Retornar todos los resultados procesados
    }

    /* 
     * Método para configurar las fechas de las actividades por defecto
     * de acuerdo con las fechas de inicio y cierre del curso establecidas en el padre
     * @param obj $table 
     * @param obj $section 
     * @param int $semana
     */
    private function date_open_close($table, $section, $semana)
    {
        global $DB;
        $tb_fechas = $DB->get_record_sql('SELECT * FROM {bc_registro_pc} WHERE nombre = "Padre" limit 1');
        if (!empty($tb_fechas) && property_exists($tb_fechas, 'startdate')) {
            $open = '';
            $close = '';
            switch ($table) {
                case 'assign':
                    $section->cutoffdate = ($semana == 8) ? $tb_fechas->enddate8 : $tb_fechas->enddate16;
                    $section->gradingduedate = ($semana == 8) ? $tb_fechas->enddate8 : $tb_fechas->enddate16;
                    $open = "allowsubmissionsfromdate";
                    $close = "duedate";
                    break;
                case 'choice':
                    $open = "timeopen";
                    $close = "timeclose";
                    break;
                case 'choicegroup':
                    $open = "timeopen";
                    $close = "timeclose";
                    break;
                case 'collaborate':
                    $open = "timestart";
                    $close = "timeend";
                    break;
                case 'data':
                    $section->timeviewfrom = $tb_fechas->startdate;
                    $section->timeviewto = ($semana == 8) ? $tb_fechas->enddate8 : $tb_fechas->enddate16;
                    $open = "timeavailablefrom";
                    $close = "timeavailableto";
                    break;
                case 'feedback':
                    $open = "timeopen";
                    $close = "timeclose";
                    break;
                case 'forum':
                    $open = "assesstimestart";
                    $close = "assesstimefinish";
                    break;
                case 'game':
                    $open = "timeopen";
                    $close = "timeclose";
                    break;
                case 'glossary':
                    $open = "assesstimestart";
                    $close = "assesstimefinish";
                    break;
                case 'groupselect':
                    //$open = "timeavailable";
                    //$close = "timedue";
                    break;
                case 'lesson':
                    $open = "available";
                    $close = "deadline";
                    break;
                case 'quiz':
                    $open = "timeopen";
                    $close = "timeclose";
                    break;
                case 'scorm':
                    $open = "timeopen";
                    $close = "timeclose";
                    break;
                case 'workshop':
                    $section->assessmentstart = $tb_fechas->startdate;
                    $section->assessmentend = ($semana == 8) ? $tb_fechas->enddate8 : $tb_fechas->enddate16;
                    $open = "submissionstart";
                    $close = "assessmentend";
                    break;
            }

            $section->$open = $tb_fechas->startdate;
            $section->$close = ($semana == 8) ? $tb_fechas->enddate8 : $tb_fechas->enddate16;
        }
        return true;
    }

    /* 
     * Método para crear el course_modules
     * @param int $id_module 
     * @param obj $registro 
     * @param int $id_acti
     * @param obj $relation
     */
    private function save_course_modules($id_module, $registro, $id_acti, $relation)
    {
        global $DB;

        $relation->course = (int)$registro->id_nodo;
        $relation->section = (int)$registro->id_section;
        $relation->instance = $id_acti;
        $relation->module = $id_module;
        if (empty($relation->completiongradeitemnumber)) {
            $relation->completiongradeitemnumber = ($relation->completiongradeitemnumber == "") ? NULL : 0;
        }

        unset($relation->name_table, $relation->id_table);
        return $DB->insert_record('course_modules', $relation); // guardar la informacion de la actividad en course_modules
    }


    /**
     * Given an object containing all the necessary data,
     * (defined by the form in mod_form.php) this function
     * will create a new instance and return the id number
     * of the new instance.

     * @param object $files 
     * @param int $id
     * @param string $dir_sftp
     * return bool
     */
    private function resource_add_instance_nueva($files, $to, $name_archive, $id_nodo, $ob_data)
    {
        global $CFG, $DB, $USER;
        $objCRE = new self;
        $errors = new stdClass();
        $errors->userid = $USER->id;
        $errors->courseid = $id_nodo;
        $component = 'mod_resource';
        $ob_data = (object) $ob_data;



        require_once("$CFG->libdir/moodlelib.php");
        $fs = get_file_storage();
        $file_record = array(
            'contextid' => $ob_data->contextid,
            'component' => $component,
            'filearea' => $ob_data->filearea,
            'itemid' => 0,
            'filepath' => $ob_data->filepath,
            'filename' => $ob_data->filename,
            'source' => $ob_data->filename,
            'timecreated' => time(),
            'timemodified' => time()
        );
        $ruta = $to . $name_archive;
        if ($packagefile = $fs->create_file_from_pathname($file_record, $ruta)) {
            //unlink($ruta);
            return true;
        }


        return false;
    }
    /**
     * Given an object containing all the necessary data,
     * (defined by the form in mod_form.php) this function
     * will create a new instance and return the id number
     * of the new instance.

     * @param object $scorm 
     * @param int $id
     * @param string $dir_sftp
     * return bool
     */
    private function scorm_add_instance_nueva($scorm, $id, $dir_sftp)
    {
        global $CFG, $DB;
        $objCRE = new self;
        require_once($CFG->dirroot . '/mod/scorm/lib.php');
        require_once($CFG->dirroot . '/mod/scorm/locallib.php');
        require_once("$CFG->libdir/moodlelib.php");
        $cmid       = $scorm->coursemodule;
        $cmidnumber = $scorm->cmidnumber;
        $courseid   = $scorm->course;

        $context = context_module::instance($cmid);

        $scorm = scorm_option2text($scorm);
        $DB->set_field('course_modules', 'instance', $id, array('id' => $cmid));

        // Reload scorm instance.
        $record = $DB->get_record('scorm', array('id' => $id));

        // Extra fields required in grade related functions.
        $record->course     = $courseid;
        $record->cmidnumber = $cmidnumber;
        $record->cmid       = $cmid;

        $fs = get_file_storage();
        $name_arc = str_replace('.zip', '_' . $courseid . '.zip', $record->reference);
        $from_zip_file = $dir_sftp;

        $file_record = array(
            'contextid' => $context->id,
            'component' => 'mod_scorm',
            'filearea' => 'package',
            'itemid' => 0,
            'filepath' => '/',
            'filename' => $name_arc,
            'timecreated' => time(),
            'timemodified' => time()
        );

        $packagefile = $fs->create_file_from_pathname($file_record, $from_zip_file);

        $newhash = $packagefile->get_contenthash();
        $fs->delete_area_files($context->id, 'mod_scorm', 'content');

        $packer = get_file_packer('application/zip');
        $packagefile->extract_to_storage($packer, $context->id, 'mod_scorm', 'content', 0, '/');
        $record->revision++;
        $record->sha1hash = $newhash;

        return true;
    }

    /**
     * Given an object containing all the necessary data,
     * (defined by the form in mod_form.php) this function
     * will create a new instance and return the id number
     * of the new instance.

     * @param object $scorm 
     * @param int $id
     * @param string $dir_sftp
     * return bool
     */
    private function customcert_add_instance_nueva($customcert_elements, $to, $name_archive, $id_nodo, $ob_data)
    {
        global $CFG, $DB, $USER;
        $objCRE = new self;
        $errors = new stdClass();
        $errors->userid = $USER->id;
        $errors->courseid = $id_nodo;
        $component = 'mod_customcert';

        try {
            require_once $CFG->dirroot . '/local/backup_course/folder_S3/controlador2_m.php';
            $s3 = new Controlador2_m(); //hacer transfer
            $s3->run('transfer', $name_archive, $to, $id_nodo);

            require_once("$CFG->libdir/moodlelib.php");
            $fs = get_file_storage();
            $file_record = array(
                'contextid' => $ob_data->contextid,
                'component' => $component,
                'filearea' => $ob_data->filearea,
                'itemid' => $ob_data->itemid,
                'filepath' => $ob_data->filepath,
                'filename' => $ob_data->filename,
                'source' => $ob_data->filename,
                'timecreated' => time(),
                'timemodified' => time()
            );

            if ($packagefile = $fs->create_file_from_pathname($file_record, $to . $name_archive)) {
                $s3->run('delete', $customcert_elements['url_customcert'], $name_archive, $id_nodo);
                unlink($to . $name_archive);
                return true;
            }
        } catch (Exception $exc) {
            $errors->error = json_encode($customcert_elements);
            $errors->description = 'No se pudo crear el archivo de customcert -- ' . $exc->getTraceAsString();
            $objCRE->save_error($errors);
        }

        return false;
    }


    /*
     * Guarda la relacion entre las actividades del padre y el hijo
     * return {id};
     */
    private function saveRelationPH()
    {
        global $DB, $USER;
        $registro = (object)$_POST;
        $results = new stdClass();
        $tb_wl = $DB->get_record_sql('SELECT * FROM {bc_white_list} LIMIT 1');
        if (!empty($tb_wl)) {
            //$tb_wl = each($tb_wl);
            $tok_p = $tb_wl->token;
            $tb_reg = $DB->get_record('bc_registro_pc', array('token' => $tok_p));
            $tok = sha1('2017.UVD_TokeN_noDos');
            $url_p = $tb_wl->url;
            $estado = $tb_wl->estado;
            $url_actual = explode('/local/', $_SERVER['HTTP_REFERER']);

            if (!empty($tb_reg)) {
                //$tb_reg = each($tb_reg); 
                $registroid = $tb_reg->id;

                $url = $this->formatHttp($url_p) . '/webservice/rest/server.php?wstoken=' . $tok . '&wsfunction=local_remoter_relation_node&moodlewsrestformat=json';
                $params = array(
                    'function' => $registro->key,
                    'url' => $this->formatHttp($url_actual[0]),
                    'token' => $tok_p,
                    /* 'ip' => $_SERVER['SERVER_ADDR'], */
                    'url_padre' => $this->formatHttp($url_p),
                    'id_reg' => $registroid,
                    'id_nodo' => $registro->id_nodo,
                    'id_padre' => $registro->id_padre,
                    'estado' => $estado,
                    'obj' => json_encode($registro->obj),
                    'id_user' => $USER->id
                );

                $curl = new curl;
                $results = json_decode($curl->post($url, $params));
                //$tb_rel = $DB->get_records('bc_rel_padre_hijo',array('courseid_sh'=>$registro->id_nodo, 'registroid' =>$registroid));
                $tb_rel = $DB->get_record_sql('SELECT * FROM {bc_rel_padre_hijo} b WHERE b.courseid_sh = :courseid_sh AND b.registroid = :registroid ORDER BY b.id DESC LIMIT 1', array('courseid_sh' => $registro->id_nodo, 'registroid' => $registroid));
                $relation = new stdClass();
                $relation->registroid = $registroid;
                $relation->registroid_nodo = $registroid;
                $relation->courseid_sh = $registro->id_nodo;
                $relation->courseid_sp = $registro->id_padre;
                $relation->estado_update = 0;
                $relation->objet_ph = json_encode($registro->obj);
                $relation->userid_nodo = $USER->id;

                if (property_exists($results, 'response')) {

                    if ($results->response == 'Importación realizada') {

                        $datas = $DB->insert_record('bc_rel_padre_hijo', $relation);

                        $bc_rel = $DB->get_record('bc_rel_padre_hijo', array('id' => $datas));

                        $phObj = json_decode($bc_rel->objet_ph);

                        $arrayActv = array();

                        foreach ($phObj->sectionAndActi->sections as $key => $val) {
                            if (property_exists($val, 'activities')) {
                                foreach ($val as $k2 => $v2) {
                                    if (is_array($v2)) {

                                        foreach ($v2 as $k3 => $v3) {
                                            if (!empty($phObj->rubrica)) {
                                                $v3 = (object) $v3;
                                                foreach ($v3 as $k4) {
                                                    array_push($arrayActv, $k4);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        if (!empty($phObj->rubrica)) {

                            $phObj->grading_definitions = new stdClass();
                            $phObj->gradingform_rubric_criteria = new stdClass();
                            $phObj->gradingform_rubric_levels = new stdClass();

                            $arrActi = array('forum', 'assign');

                            $arrayActv = (object) $arrayActv;

                            foreach ($arrActi as $valAct) {

                                if (property_exists($phObj->rubrica, $valAct)) {

                                    foreach ($phObj->rubrica->$valAct as $kf) {

                                        foreach ($arrayActv  as $kac) {

                                            if ($kac->table == "$valAct" && $kac->id_acti_p == $kf) {

                                                $modules = $DB->get_record('modules', ['name' => $valAct]);

                                                $cm = $DB->get_record('course_modules', array('module' => $modules->id, 'instance' => $kac->id_acti));

                                                $context = $DB->get_record('context', array('instanceid' => $cm->id, 'contextlevel' => 70));

                                                foreach ($phObj->rubrica->$valAct->gradingAreas as $keyn => $value) {

                                                    $value->contextid = $context->id;

                                                    $id_hijoGr = $DB->insert_record('grading_areas', $value);

                                                    foreach ($phObj->rubrica->$valAct->grading_definitions as $kgd => $gd) {
                                                        if ($gd != 'false') {
                                                            if ($value->id == $gd->areaid) {
                                                                $gd->areaid = $id_hijoGr;
                                                                $id_hijoDef = $DB->insert_record('grading_definitions', $gd);
                                                                $phObj->grading_definitions->$kgd['p'] = $gd->id;
                                                                $phObj->grading_definitions->$kgd['h'] = $id_hijoDef;
                                                                foreach ($phObj->rubrica->$valAct->gradingform_rubric_criteria as $krc => $vcr) {
                                                                    if ($vcr->definitionid == $gd->id) {
                                                                        $vcr->definitionid = $id_hijoDef;
                                                                        $id_hijoCrit = $DB->insert_record('gradingform_rubric_criteria', $vcr);

                                                                        //Relación padre - hijo
                                                                        $phObj->gradingform_rubric_criteria->$krc['p'] = $vcr->id;
                                                                        $phObj->gradingform_rubric_criteria->$krc['h'] = $id_hijoCrit;
                                                                        /* gradingform_rubric_levels */
                                                                        $phObj->rubrica->$valAct->gradingform_rubric_levels = (array) $phObj->rubrica->$valAct->gradingform_rubric_levels;
                                                                        ksort($phObj->rubrica->$valAct->gradingform_rubric_levels);
                                                                        foreach ($phObj->rubrica->$valAct->gradingform_rubric_levels as $klv => $vlv) {
                                                                            if ($vlv->criterionid == $vcr->id) {
                                                                                $newLevel = new stdClass();
                                                                                $newLevel = clone $vlv;
                                                                                $newLevel->criterionid = $id_hijoCrit;

                                                                                $id_hijoLvl = $DB->insert_record('gradingform_rubric_levels', $newLevel);

                                                                                $phObj->gradingform_rubric_levels->$klv['p'] = $newLevel->id;
                                                                                $phObj->gradingform_rubric_levels->$klv['h'] = $id_hijoLvl;
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                                /* break; */
                                                            }
                                                        }
                                                    }
                                                    unset($phObj->rubrica->$valAct->gradingAreas->$keyn);
                                                    break;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        $bc_rel->objet_ph = json_encode($phObj);

                        $DB->update_record('bc_rel_padre_hijo', $bc_rel);
                    } else if ($results->response == 'Se realizó de nuevo la importación' && !empty($tb_rel)) {

                        $id = $tb_rel->id;
                        $relation->id = $id;
                        $DB->update_record('bc_rel_padre_hijo', $relation);

                        $bc_rel = $DB->get_record('bc_rel_padre_hijo', array('id' => $relation->id));

                        $phObj = json_decode($bc_rel->objet_ph);

                        $arrayContextId = array();

                        $arrayActv = array();

                        foreach ($phObj->sectionAndActi->sections as $key => $val) {
                            if (property_exists($val, 'activities')) {
                                foreach ($val as $k2 => $v2) {
                                    if (is_array($v2)) {

                                        foreach ($v2 as $k3 => $v3) {
                                            if (!empty($phObj->rubrica)) {
                                                $v3 = (object) $v3;
                                                foreach ($v3 as $k4) {
                                                    array_push($arrayActv, $k4);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        if (!empty($phObj->rubrica)) {

                            $phObj->grading_definitions = new stdClass();
                            $phObj->gradingform_rubric_criteria = new stdClass();
                            $phObj->gradingform_rubric_levels = new stdClass();

                            $arrActi = array('forum', 'assign');

                            $arrayActv = (object) $arrayActv;

                            foreach ($arrActi as $valAct) {

                                if (property_exists($phObj->rubrica, $valAct)) {

                                    foreach ($phObj->rubrica->$valAct as $kf) {

                                        foreach ($arrayActv  as $kac) {

                                            if ($kac->table == "$valAct" && $kac->id_acti_p == $kf) {

                                                $modules = $DB->get_record('modules', ['name' => $valAct]);

                                                $cm = $DB->get_record('course_modules', array('module' => $modules->id, 'instance' => $kac->id_acti));

                                                $context = $DB->get_record('context', array('instanceid' => $cm->id, 'contextlevel' => 70));

                                                foreach ($phObj->rubrica->$valAct->gradingAreas as $keyn => $value) {

                                                    $value->contextid = $context->id;

                                                    $id_hijoGr = $DB->insert_record('grading_areas', $value);

                                                    foreach ($phObj->rubrica->$valAct->grading_definitions as $kgd => $gd) {
                                                        if ($gd != 'false') {
                                                            if ($value->id == $gd->areaid) {

                                                                $gd->areaid = $id_hijoGr;
                                                                $id_hijoDef = $DB->insert_record('grading_definitions', $gd);
                                                                //Relación padre - hijo
                                                                $phObj->grading_definitions->$kgd['p'] = $gd->id;
                                                                $phObj->grading_definitions->$kgd['h'] = $id_hijoDef;

                                                                foreach ($phObj->rubrica->$valAct->gradingform_rubric_criteria as $krc => $vcr) {
                                                                    if ($vcr->definitionid == $gd->id) {
                                                                        $vcr->definitionid = $id_hijoDef;
                                                                        $id_hijoCrit = $DB->insert_record('gradingform_rubric_criteria', $vcr);

                                                                        //Relación padre - hijo
                                                                        $phObj->gradingform_rubric_criteria->$krc['p'] = $vcr->id;
                                                                        $phObj->gradingform_rubric_criteria->$krc['h'] = $id_hijoCrit;
                                                                        /* gradingform_rubric_levels */
                                                                        $phObj->rubrica->$valAct->gradingform_rubric_levels = (array) $phObj->rubrica->$valAct->gradingform_rubric_levels;
                                                                        ksort($phObj->rubrica->$valAct->gradingform_rubric_levels);
                                                                        foreach ($phObj->rubrica->$valAct->gradingform_rubric_levels as $klv => $vlv) {
                                                                            if ($vlv->criterionid == $vcr->id) {
                                                                                $newLevel = new stdClass();
                                                                                $newLevel = clone $vlv;
                                                                                $newLevel->criterionid = $id_hijoCrit;

                                                                                $id_hijoLvl = $DB->insert_record('gradingform_rubric_levels', $newLevel);

                                                                                $phObj->gradingform_rubric_levels->$klv['p'] = $newLevel->id;
                                                                                $phObj->gradingform_rubric_levels->$klv['h'] = $id_hijoLvl;
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                    unset($phObj->rubrica->$valAct->gradingAreas->$keyn);
                                                    break;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        $bc_rel->objet_ph = json_encode($phObj);

                        $DB->update_record('bc_rel_padre_hijo', $bc_rel);
                    }
                } else {
                    echo '<pre>$results: ';
                    print_r($results);
                    echo '</pre>';
                }

                $block = $DB->get_record('block', ['name' => 'bloque_recursos']);
                if ($block) {
                    $this->configBlockRecursos($relation->courseid_sh, $relation->objet_ph);
                }

                rebuild_course_cache($registro->id_nodo);
            }
        } else {
            $results->ack = 0;
            $results->response = 'No eres hijo en el padre';
        }
        return $results;
    }

    private function configBlockRecursos($course_id, $objet_ph)
    {
        global $DB;
        $objet_ph = json_decode($objet_ph);
        $context = context_course::instance($course_id);
        $block_instance = $DB->get_record('block_instances', ['parentcontextid' => $context->id, 'blockname' => 'bloque_recursos']);
        if (!$block_instance) {
            return false; // No se encontró el bloque
        }

        $config = unserialize(base64_decode($block_instance->configdata));
        $nueva_config = new stdClass();

        foreach ($config as $key => $value) {
            if (strpos($key, 'activity_') === 0) {
                $id = (int) str_replace('activity_', '', $key);
                $matchFound = false;
                foreach ($objet_ph->sectionAndActi->sections as $section) {
                    if (!empty($section->activities)) {
                        foreach ($section->activities as $grupo) {
                            foreach ($grupo as $actividad) {
                                $actividad = (object) $actividad;
                                if (isset($actividad->id_como_p) && $actividad->id_como_p == $id) {
                                    $newKey = 'activity_' . $actividad->id_como;
                                    $nueva_config->$newKey = $value;
                                    $matchFound = true;
                                    break 3; // Rompe section, grupo y actividad
                                }
                            }
                        }
                    }
                }

                if (!$matchFound) {
                    // Si no hubo match, se conserva la clave original
                    $nueva_config->$key = $value;
                }
            } else {
                // Claves que no son de actividad, se copian tal cual
                $nueva_config->$key = $value;
            }
        }

        if (isset($config->order_data)) {
            $ordenes = json_decode($config->order_data, true);
            $nuevo_ordenes = [];

            foreach ($ordenes as $oldid => $info) {
                $matchFound = false;

                foreach ($objet_ph->sectionAndActi->sections as $section) {
                    if (!empty($section->activities)) {
                        foreach ($section->activities as $grupo) {
                            foreach ($grupo as $actividad) {
                                $actividad = (object) $actividad;
                                if (isset($actividad->id_como_p) && $actividad->id_como_p == (int)$oldid) {
                                    $nuevo_ordenes[$actividad->id_como] = $info;
                                    $matchFound = true;
                                    break 3;
                                }
                            }
                        }
                    }
                }

                if (!$matchFound) {
                    // Si no hay match, conserva el ID original
                    $nuevo_ordenes[$oldid] = $info;
                }
            }

            $nueva_config->order_data = json_encode($nuevo_ordenes);
        }

        // Serializar y guardar de nuevo en configdata
        $block_instance->configdata = base64_encode(serialize($nueva_config));
        $DB->update_record('block_instances', $block_instance);
    }



    /*
    * Método para guardar la informacion adicional de las actividades(Tablas de cada actividad)
    * 
    * @param int $typeAc 
    * @param object $info
    * @param object $section
    * @param int $id_nodo
    * @param int $id_module
    * @param int $id_como
    * @param int $id_acti
    * @param object $bancoPreg
    * return object
    */
    private function getInfoAc($typeAc, $info, $section, $id_nodo, $relation, $id_module, $id_como, $id_acti, $bancoPreg)
    {
        global $DB, $CFG, $USER;
        $objCRE = new self;
        $info_actividad = array();
        $errors = new stdClass();
        $errors->userid = $USER->id;
        $errors->courseid = $id_nodo;
        $como_context = context_module::instance($id_como);
        $course_context = context_course::instance($id_nodo);

        if (
            $typeAc != 'customcert' && $typeAc != 'resource' && $typeAc != 'scorm'
            && $typeAc != 'assign' && $typeAc != 'lti' && $typeAc != 'hvp' && $typeAc != 'h5pactivity'
            && $typeAc != 'quiz' && $typeAc != 'workshop' && $typeAc != 'folder'
        ) {
            $arr_forum_discussions_p = array();
            $id_forum_discussions_h = array();
            $arr_lesson_pages_p = array();
            $arr_lesson_pages_h = array();
            foreach ($info as $k => $v) {
                if ($v && (is_array($v) || is_object($v))) {

                    foreach ($v as $key => $value) {
                        $value['course'] = $id_nodo;
                        if (array_key_exists('feedback', $value)) { ///para feedback
                            $value['feedback'] = $id_acti;
                        }
                        if (array_key_exists('userid', $value)) {
                            $value['userid'] = $USER->id;
                        }

                        if ($typeAc == 'forum') { ////////////inicio Foros
                            if ($k == 'forum_discussions') {
                                $arr_forum_discussions_p[] = $value['id'];
                                $value['forum'] = $id_acti;
                                $id_forum_disc = $DB->insert_record($k, $value);

                                $id_forum_discussions_h[] = $id_forum_disc;
                                $info_actividad[$k][$key]['p'] = $value['id'];
                                $info_actividad[$k][$key]['h'] = $id_forum_disc;
                            } else if ($k == 'forum_posts') {
                                foreach ($value as $keye => $valuer) {
                                    if (is_array($valuer) && array_key_exists('discussion', $valuer)) {
                                        $pos = array_search($valuer['discussion'], $arr_forum_discussions_p);
                                        if (is_int($pos)) {
                                            $valuer['userid'] = $USER->id;
                                            $valuer['discussion'] = $id_forum_discussions_h[$pos];
                                            $valuer['firstpost'] = $DB->insert_record($k, $valuer);
                                            $info_actividad[$k][$keye]['p'] = $arr_forum_discussions_p[$pos];
                                            $info_actividad[$k][$keye]['h'] = $valuer['firstpost'];
                                            $valuer['id'] = $id_forum_discussions_h[$pos];
                                            $DB->update_record('forum_discussions', $valuer);
                                        }
                                    }
                                }
                            } ////////////fin Foros
                        } else if ($typeAc == 'lesson') { //////// inicio de las lessions

                            $oldLessonid =  $value['lessonid'];

                            $value['lessonid'] = $id_acti;

                            if ($k == 'lesson_pages') {

                                $arr_lesson_pages_p[] = $value['id'];
                                $info_actividad[$k][$key]['p'] = $value['id'];
                                unset($value['id']);
                                $id_les = $DB->insert_record($k, $value);
                                $info_actividad[$k][$key]['h'] = $id_les;

                                $value['prevpageid'] = ($value['prevpageid'] == 0) ? 0 : $id_les - 1;
                                $value['nextpageid'] = ($value['nextpageid'] == 0) ? 0 : $id_les + 1;

                                $value['id'] = $id_les;
                                $arr_lesson_pages_h[] = $id_les;
                                $DB->update_record('lesson_pages', $value);
                            } else {

                                $pos_jumpto = array_search($value['jumpto'], $arr_lesson_pages_p);

                                if (is_int($pos_jumpto) && $pos_jumpto > 0) {
                                    $value['jumpto'] = $arr_lesson_pages_h[$pos_jumpto];
                                }

                                $pos = array_search($value['pageid'], $arr_lesson_pages_p);

                                if (is_int($pos)) {
                                    $value['pageid'] = $arr_lesson_pages_h[$pos];
                                    unset($value['id']);
                                    $id_lesson_answers = $DB->insert_record($k, $value);

                                    $info_actividad[$k][$key]['p'] = $arr_lesson_pages_p[$pos];
                                    $info_actividad[$k][$key]['h'] = $id_lesson_answers;
                                }
                            } ////////////fin lesson



                        } else  if ($typeAc == 'book') { //////// inicio de los libros
                            $value[$typeAc . 'id'] = $id_acti;
                            $info_actividad[$k][$key]['p'] = $value['id'];

                            if ($k == 'book_chapters') {
                                $regisPH = $DB->get_record('bc_registro_pc', array('nombre' => 'Padre'));
                                //reemplazar las uls del padre por las del hijo
                                if (!empty($regisPH))   $value['content'] = str_replace($regisPH->url_padre, $regisPH->url_hijo, $value['content']);
                                $id_typeAc = $DB->insert_record($k, $value);
                                $info_actividad[$k][$key]['h'] = $id_typeAc;
                            } else if ($k == 'book_h5p_mod') {
                                $id_book_chapters = array_values($info_actividad['book_chapters']);
                                $value['userid'] = $USER->id;
                                if ($k == 'book_h5p_mod') {
                                    $value['contextid'] = $como_context->id;
                                    $position = array_search($value['itemid'], array_column($id_book_chapters, 'p'));

                                    if (is_int($position)) {
                                        $value['itemid'] = $id_book_chapters[$position]['h'];
                                        $aliasrecord = new stdClass();
                                        $aliasrecord->contextid = $como_context->id;
                                        $aliasrecord->component = $value['component'];
                                        $aliasrecord->filearea = $value['filearea'];
                                        $aliasrecord->filepath = $value['filepath'];
                                        $aliasrecord->filename = $value['filename'];
                                        $aliasrecord->itemid = $value['itemid'];
                                        //echo '<pre>->>>>$value:'; var_dump($value); echo '</pre>';
                                        $regis_ya = $DB->get_record_sql('SELECT * FROM {files} WHERE contextid = :contextid '
                                            . 'AND component = :component AND filearea = :filearea '
                                            . 'AND itemid    = :itemid    AND filepath = :filepath  AND '
                                            . $DB->sql_compare_text('filename') . ' = ' . $DB->sql_compare_text(':filename') . ' AND '
                                            . $DB->sql_compare_text('source') . ' = ' . $DB->sql_compare_text(':source'), array(
                                            'contextid' => $como_context->id,
                                            'component' => $value['component'],
                                            'filearea' => $value['filearea'],
                                            'itemid' => $value['itemid'],
                                            'filepath' => $value['filepath'],
                                            'filename' => $value['filename'],
                                            'source' => $value['filename']
                                        ));
                                        if (empty($regis_ya)) {
                                            $fs = get_file_storage();
                                            $archivo = $value['contenthash'];
                                            $cr1 = substr($archivo, 0, 2);
                                            $cr2 = substr($archivo, 2, 2);
                                            $moodle_data = $CFG->dataroot . '/filedir/' . $cr1 . '/' . $cr2 . '/' . $archivo;
                                            $file_record = array(
                                                'contextid' => $como_context->id,
                                                'component' => $value['component'],
                                                'filearea' => $value['filearea'],
                                                'itemid' => $value['itemid'],
                                                'filepath' => $value['filepath'],
                                                'filename' => $value['filename'],
                                                'source' => $value['filename'],
                                                'timecreated' => time(),
                                                'timemodified' => time()
                                            );
                                            try {
                                                if ($packagefile = $fs->create_file_from_pathname($file_record, $moodle_data)) {
                                                }
                                            } catch (Exception $exc) {
                                            }
                                        }
                                    }
                                }
                            } else {
                                $id_typeAc = $DB->insert_record($k, $value);
                                $info_actividad[$k][$key]['h'] = $id_typeAc;
                            }
                            unset($value['id']);
                        } else {
                            $value[$typeAc . 'id'] = $id_acti;
                            $info_actividad[$k][$key]['p'] = $value['id'];
                            unset($value['id']);
                            $id_typeAc = $DB->insert_record($k, $value);

                            $info_actividad[$k][$key]['h'] = $id_typeAc;
                        }
                    }
                }
            }
        } else if ($typeAc == 'lti') {

            $typeId = 0;

            foreach ($info as $k => $v) {
                $v = (array)$v;
                if ($k == 'lti_types' && !empty($v) && array_key_exists('id', $v)) {
                    $info_actividad[$k]['p'] = $v['id'];
                    $lti = $DB->get_record('lti', array('id' => $id_acti));
                    $lti_types = $DB->get_record('lti_types', array('id' => $lti->typeid));

                    if (!empty($lti_types) && $lti_types->name == $v['name']) {

                        $lti->typeid = $lti_types->id;
                    } else {

                        $lti_types = $DB->get_record_sql('SELECT * FROM {lti_types} WHERE name = :name LIMIT 1', array('name' => $v['name']));
                        if (!empty($lti_types)) {

                            $lti->typeid = $lti_types->id;
                        }
                    }
                    $typeId = $lti->typeid;
                    $info_actividad[$k]['h'] = $lti->typeid;
                    $DB->update_record('lti', $lti);
                }
            }
        } else if ($typeAc == 'workshop') {
            $workshopform_rubric = array();
            foreach ($info as $k => $v) {
                foreach ($v as $key => $value) {
                    if ($k == 'workshopform_rubric' || $k == 'workshopform_rubric_levels') {
                        if ($k == 'workshopform_rubric') {
                            $value['workshopid'] = $id_acti;
                            $id_rub = $DB->insert_record($k, $value);

                            $workshopform_rubric[$key]['h'] = $id_rub;
                            $workshopform_rubric[$key]['p'] = $key;
                            $info_actividad[$k]['p'] = $value['id'];
                            $info_actividad[$k]['h'] = $id_rub;
                        } else {
                            $id_workshopform_rubric = array_values($workshopform_rubric);
                            foreach ($value as $ke => $val) {
                                $position = array_search($val['dimensionid'], array_column($id_workshopform_rubric, 'p'));
                                if (is_int($position)) {
                                    $val['dimensionid'] = $id_workshopform_rubric[$position]['h'];
                                    $info_actividad[$k]['p'] = $val['id'];
                                    $info_actividad[$k]['h'] = $DB->insert_record($k, $val);
                                }
                            }
                        }
                    } else {
                        $value['workshopid'] = $id_acti;
                        $id_typeAc = $DB->insert_record($k, $value);

                        $info_actividad[$k][$value['id']]['p'] = $value['id'];
                        $info_actividad[$k][$value['id']]['h'] = $id_typeAc;
                    }
                }
            }
        } else if ($typeAc == 'h5pactivity') {
            foreach ($info as $k => $v) {

                if ($k == 'h5p_libraries') {
                    foreach ($v as $ke => $val) {
                        if (is_array($val) && array_key_exists('machine_name', $val)) {
                            $lib = $DB->get_record('h5p_libraries', array('machine_name' => $val['machine_name'], 'patch_version' => $val['patch_version'], 'major_version' => $val['major_version'], 'minor_version' => $val['minor_version']));
                            if (empty($lib)) {
                                $array_libro[$val['id']] = $DB->insert_record($k, $val);
                            } else {
                                $array_libro[$val['id']] = $lib->id;
                            }
                        }
                    }
                } else if ($k == 'h5p_contents_libraries') {
                    foreach ($v as $ke => $val) {
                        if (array_key_exists($val['library_id'], $array_libro)) {
                            $val['library_id'] = $array_libro[$val['library_id']];
                            $val['h5pid'] = $id_acti;
                            $info_actividad[$k][$ke]['p'] = $val['id'];
                            $info_actividad[$k][$ke]['h'] = $DB->insert_record($k, $val);
                        }
                    }
                } else if ($k == 'admin_english' && $DB->get_manager()->table_exists('admin_english')) {
                    foreach ($v as $ke => $val) {
                        $info_actividad[$k][$ke]['p'] = $val['id'];
                        $existe = $DB->get_record('admin_english', array('courseid' => $id_nodo, 'hvpid' => $id_acti, 'diapid' => $val['diapid']));
                        if (empty($existe)) {
                            $info_actividad[$k][$ke]['h'] = $DB->insert_record('admin_english', array('courseid' => $id_nodo, 'hvpid' => $id_acti, 'diapid' => $val['diapid'], 'habili' => $val['habili'], 'subcontent' => $val['subcontent']));
                        } else {
                            $info_actividad[$k][$ke]['h'] = $existe->id;
                        }
                    }
                }
            }
            if (!empty($bancoPreg['h5p_files'])) {
                $id_rel = $info['id_rel'];

                require_once("$CFG->libdir/moodlelib.php");
                foreach ($bancoPreg['h5p_files'] as $keey => $valuee) {
                    if ($valuee['id_como_p'] == $relation->id) {
                        //$valuee['itemid'] = $id_acti;
                        $valuee['contextid'] = $como_context->id;
                        $moodle_data = $CFG->dataroot . '/temp/courseH5P_' . $id_nodo . '/' . $valuee['filename'];
                        if (file_exists($moodle_data)) {
                            $fs = get_file_storage();
                            if ($fs->create_file_from_pathname($valuee, $moodle_data)) {
                                unlink($moodle_data);
                            } else {
                                throw new Exception('No se pudo crear el file ' . $valuee['filename']);
                                die();
                            }
                        }
                        break;
                    }
                }
                if (is_dir($CFG->dataroot . '/temp/courseH5P_' . $id_nodo)) {
                    $carpeta = @scandir($CFG->dataroot . '/temp/courseH5P_' . $id_nodo);
                    if (count($carpeta) > 2) {
                    } else {
                        rmdir($CFG->dataroot . '/temp/courseH5P_' . $id_nodo);
                    }
                }
            }
        } else if ($typeAc == 'hvp') {
            $array_libro = array();
            foreach ($info as $k => $v) {

                if ($k == 'hvp_libraries') {
                    foreach ($v as $ke => $val) {
                        if (is_array($val) && array_key_exists('machine_name', $val)) {
                            $lib = $DB->get_record('hvp_libraries', array('machine_name' => $val['machine_name'], 'patch_version' => $val['patch_version'], 'major_version' => $val['major_version'], 'minor_version' => $val['minor_version']));
                            if (empty($lib)) {
                                $array_libro[$val['id']] = $DB->insert_record($k, $val);
                            } else {
                                $array_libro[$val['id']] = $lib->id;
                            }
                        }
                    }
                } else if ($k == 'hvp_contents_libraries') {
                    foreach ($v as $ke => $val) {
                        if (array_key_exists($val['library_id'], $array_libro)) {
                            $val['library_id'] = $array_libro[$val['library_id']];
                            $val['hvp_id'] = $id_acti;
                            $info_actividad[$k][$ke]['p'] = $val['id'];
                            $info_actividad[$k][$ke]['h'] = $DB->insert_record($k, $val);
                        }
                    }
                } else if ($k == 'admin_english' && $DB->get_manager()->table_exists('admin_english')) {
                    foreach ($v as $ke => $val) {
                        $info_actividad[$k][$ke]['p'] = $val['id'];
                        $existe = $DB->get_record('admin_english', array('courseid' => $id_nodo, 'hvpid' => $id_acti, 'diapid' => $val['diapid']));
                        if (empty($existe)) {
                            $info_actividad[$k][$ke]['h'] = $DB->insert_record('admin_english', array('courseid' => $id_nodo, 'hvpid' => $id_acti, 'diapid' => $val['diapid'], 'habili' => $val['habili'], 'subcontent' => $val['subcontent']));
                        } else {
                            $info_actividad[$k][$ke]['h'] = $existe->id;
                        }
                    }
                }
            }
            if (!empty($bancoPreg['hvp_files'])) {
                $id_rel = $info['id_rel'];

                require_once("$CFG->libdir/moodlelib.php");
                foreach ($bancoPreg['hvp_files'] as $keey => $valuee) {
                    if ($valuee['itemid'] == $info['id_hvp']) {
                        $valuee['itemid'] = $id_acti;
                        $valuee['contextid'] = $como_context->id;
                        $moodle_data = $CFG->dataroot . '/temp/courseH5P_' . $id_nodo . '/' . $valuee['filename'];
                        if (file_exists($moodle_data)) {
                            $fs = get_file_storage();
                            if ($fs->create_file_from_pathname($valuee, $moodle_data)) {
                                unlink($moodle_data);
                            } else {
                                throw new Exception('No se pudo crear el file ' . $valuee['filename']);
                                die();
                            }
                        }
                    }
                }
                if (is_dir($CFG->dataroot . '/temp/courseH5P_' . $id_nodo)) {
                    $carpeta = @scandir($CFG->dataroot . '/temp/courseH5P_' . $id_nodo);
                    if (count($carpeta) > 2) {
                    } else {
                        rmdir($CFG->dataroot . '/temp/courseH5P_' . $id_nodo);
                    }
                }
            }
        } else if ($typeAc == 'resource') {

            foreach ($info as $k => $v33) {
                $info_actividad[$k] = array();
                if ($k == 'id_rel') {
                    $id_rel = $v33;
                } else if ($k == 'url_resource') {
                    foreach ($v33 as $key3 => $value33) {
                        $value33['contextid'] = $como_context->id;
                        $archivo = $value33['contenthash']; //  url_files
                        $name_archive = $archivo . '_' . $id_nodo . '_' . $id_rel;
                        $to = $CFG->dataroot . '/temp/courseResource_' . $id_nodo . '/';
                        $objCRE->resource_add_instance_nueva($v33, $to, $archivo, $id_nodo, $value33);
                    }
                }
            }
        } else if ($typeAc == 'assign') {

            $gradingform_rubric_criteria = array();
            $gradingform_rubric_levels = array();
            foreach ($info as $k => $v) {
                foreach ($v as $key => $value) {
                    if ($k == 'assign_plugin_config') {
                        $value['course'] = $id_nodo;
                        $value['assignment'] = $id_acti;
                        $id_assign_plugin_config = $DB->insert_record($k, $value);

                        $info_actividad[$k][$key]['p'] = $value['id'];
                        $info_actividad[$k][$key]['h'] = $id_assign_plugin_config;
                    }
                    if ($k == 'context') {
                        $context_como = $como_context;

                        $context = $course_context;

                        if (empty($context_como)) {
                            //$path_cont = each($context);
                            $path_cont = ($context);
                            $value['instanceid'] = $id_como;
                            $id_contexto = $DB->insert_record($k, $value);

                            $info_actividad[$k][$key]['p'] = $value['id'];
                            $info_actividad[$k][$key]['h'] = $id_contexto;
                            $value['id'] = $id_contexto;
                            $value['path'] = $path_cont->path . '/' . $id_contexto;
                            $DB->update_record('context', $value);
                        } else {
                            //$path_cont_cmo = each($context_como);
                            $path_cont_cmo = ($context_como);
                            $id_contexto = $path_cont_cmo->id;
                        }
                    }
                }
            }
        } else if ($typeAc == 'quiz') {

            $context = array();
            $id_question_categories = array();
            $id_question_bank_entries = array();

            $id_question_slots = array();
            $id_question_usages = array();
            $id_question = array();
            $id_question_answers = array();

            foreach ($info as $k => $v) {
                //echo '-->>'.$k.'<<--';
                krsort($v);
                foreach ($v as $key => $val) {
                    if ($k == 'quiz_sections' || $k == 'quiz_feedback') {
                        $kId = $val['id'];
                        unset($val['id']);
                        $val['quizid'] = $id_acti;
                        $info_actividad[$k][$kId]['p'] = $kId;
                        $info_actividad[$k][$kId]['h'] = $DB->insert_record($k, $val);
                    }

                    if ($k == 'question_categories') {
                        foreach ($val as $keye => $value) {
                            //echo '<pre>$value; '; print_r($value); die();
                            $kId = $value['id'];
                            if (is_array($bancoPreg) && array_key_exists('question_categories', $bancoPreg)) {
                                $cate_bank = array_values($bancoPreg['question_categories']);
                                $position = array_search($kId, array_column($bancoPreg['question_categories'], 'p'));
                            } else $position = null;

                            if (!is_int($position)) {
                                $par_init = $value['parent'];
                                $value['info'] = empty($value['info']) ? '' : $value['info'];
                                $value['contextid'] = $course_context->id;



                                if ($value['parent'] != 0) {
                                    $id_question_c = array_values($id_question_categories);
                                    $position = array_search($value['parent'], array_column($id_question_c, 'p')); //verificar si existe en la recien creadas
                                    if (is_int($position))
                                        $value['parent'] = $id_question_c[$position]['h'];
                                    else {
                                        if (is_array($bancoPreg) && array_key_exists('question_categories', $bancoPreg)) {
                                            $cate_bank = array_values($bancoPreg['question_categories']); //verificar si existen en las creadas el banco
                                            $position = array_search($value['parent'], array_column($cate_bank, 'p'));
                                            if (is_int($position))
                                                $value['parent'] = $cate_bank[$position]['h'];
                                        }
                                    }
                                }

                                $id_question_categories[$key]['p'] = $key;
                                $info_actividad[$k][$kId]['p'] = $kId;

                                $id_cat = $DB->insert_record($k, $value);
                                $info_actividad[$k][$kId]['h'] = $id_cat;
                                $id_question_categories[$key]['h'] = $id_cat;
                            }
                        }
                    }

                    if ($k == 'question_bank_entries') {
                        foreach ($val as $keye => $value) {
                            $kId = $value['id'];
                            //echo '<pre>$bancoPreg: ';  print_r($bancoPreg['question_categories']);  echo '</pre>'; die();
                            if (is_array($bancoPreg) && array_key_exists('question_bank_entries', $bancoPreg)) {
                                $bank_bank = array_values($bancoPreg['question_categories']);
                                $position = array_search($kId, array_column($bank_bank, 'p'));
                            } else $position = null;

                            if (!is_int($position)) {
                                $id_question_c = array_values($id_question_categories);
                                $position = array_search($kId, array_column($id_question_c, 'p')); //verificar si existe en la recien creadas
                                if (is_int($position))
                                    $value['questioncategoryid'] = $id_question_c[$position]['h'];
                                else {
                                    if (is_array($bancoPreg) && array_key_exists('question_categories', $bancoPreg)) {
                                        $cate_bank = array_values($bancoPreg['question_categories']); //verificar si existen en las creadas el banco
                                        $position = array_search($value['questioncategoryid'], array_column($cate_bank, 'p'));
                                        if (is_int($position))
                                            $value['questioncategoryid'] = $cate_bank[$position]['h'];
                                    }
                                }
                                $existe = $DB->get_records($k, array('questioncategoryid' => $value['questioncategoryid']));
                                if (empty($existe)) {
                                    $id_question_bank_entries[$key] = array();
                                    $id_question_bank_entries[$key]['p'] = $key;
                                    $id_ban = $DB->insert_record($k, $value);
                                    $info_actividad[$k][$kId]['p'] = $kId;
                                    $info_actividad[$k][$kId]['h'] = $id_ban;
                                    $id_question_bank_entries[$key]['h'] = $id_ban;
                                }
                            }
                        }
                    }

                    if ($k == 'quiz_slots') {
                        $val['quizid'] = $id_acti;
                        $id_question_slots[$key] = array();
                        $id_slots = $DB->insert_record($k, $val);
                        $id_question_slots[$key]['p'] = $val['id'];
                        $id_question_slots[$key]['h'] = $id_slots;
                        $info_actividad[$k][$key]['p'] = $val['id'];
                        $info_actividad[$k][$key]['h'] = $id_slots;
                    }

                    foreach ($val as $keye => $value) {
                        /*if($k == 'question_usages'){
                            $position = array_search($value['contextid'], array_column($context, 'p'));
                            if(is_int($position)){
                                $value['contextid'] = $context[$position]['h'];
                                $id_question_usages[$keye]['p'] = $keye;
                                $info_actividad[$k][$keye]['p'] = $value['id'];
                                unset($value['id']);
                                $id_question_usages[$keye]['h'] = $DB->insert_record($k, $value);
                                
                                $info_actividad[$k][$keye]['h'] = $id_question_usages[$keye]['h'];
                            }
                        }*/


                        if ($k == 'question' && !empty($info['question']) && is_array($bancoPreg) && array_key_exists('question', $bancoPreg)) {
                            if (array_key_exists('idnumber', $value) && empty($value['idnumber'])) {
                                $value['idnumber'] = null;
                            }


                            $bancoPreg['question'][count($bancoPreg['question'])]['p'] = $value['id'];
                            $info_actividad[$k][$keye]['p'] = $value['id'];
                            //unset($value['id']);

                            if ($id_que = $DB->insert_record($k, $value)) {
                                $bancoPreg['question'][count($bancoPreg['question'])]['h'] = $id_que;
                                $info_actividad[$k][$keye]['h'] = $id_que;
                                $id_question[$keye]['p'] = $value['id'];
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
                        }
                        if ($k == 'question_answers' && !empty($info['question_answers'])) {
                            $id_question = array_values($id_question);
                            $position = array_search($value['question'], array_column($id_question, 'p'));
                            if (is_int($position)) {
                                $value['question'] = $id_question[$position]['h'];
                                $info_actividad[$k][$keye]['p'] = $value['id'];
                                if (!array_key_exists('question_answers', $bancoPreg)) $bancoPreg['question_answers'] = [];
                                $bancoPreg['question_answers'][count($bancoPreg['question_answers'])]['p'] = $value['id'];
                                $idQes = $DB->insert_record($k, $value);

                                $info_actividad[$k][$keye]['h'] = $idQes;
                                $bancoPreg['question_answers'][count($bancoPreg['question_answers'])]['h'] = $idQes;
                                $id_question_answers[$keye]['p'] = $value['id'];
                                $id_question_answers[$keye]['h'] = $idQes;
                            }
                        }
                        if ($k == 'question_truefalse' && !empty($info['question_truefalse'])) {
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

                        if ($k == 'question_attempts' && !empty($info['question_attempts'])) {
                            $id_question_usages = array_values($id_question_usages);
                            $position = array_search($value['questionusageid'], array_column($id_question_usages, 'p'));
                            if (is_int($position)) {
                                $value['questionusageid'] = $id_question_usages[$position]['h'];
                                $position2 = array_search($value['questionid'], array_column($id_question, 'p'));
                                if (is_int($position2)) {
                                    $value['questionid'] = $id_question[$position2]['h'];
                                    $info_actividad[$k][$keye]['p'] = $value['id'];
                                    if (array_key_exists('question_attempts', $bancoPreg)) {
                                        $bancoPreg['question_attempts'][count($bancoPreg['question_attempts'])]['p'] = $value['id'];
                                        unset($value['id']);
                                        $id_question_attempts = $DB->insert_record($k, $value);

                                        $info_actividad[$k][$keye]['h'] = $id_question_attempts;
                                        $bancoPreg['question_attempts'][count($bancoPreg['question_attempts'])]['h'] = $id_question_attempts;
                                    }
                                }
                                if (!empty($bancoPreg)) {
                                    $position3 = array_search($value['questionid'], array_column($bancoPreg['question'], 'p'));
                                    if (is_int($position3)) {
                                        $value['questionid'] = $bancoPreg['question'][$position3]['h'];
                                        $info_actividad[$k][$keye]['p'] = $value['id'];
                                        $bancoPreg['question_attempts'][count($bancoPreg['question_attempts'])]['p'] = $value['id'];
                                        unset($value['id']);
                                        $id_question_attempts = $DB->insert_record($k, $value);

                                        $info_actividad[$k][$keye]['h'] = $id_question_attempts;
                                        $bancoPreg['question_attempts'][count($bancoPreg['question_attempts'])]['h'] = $id_question_attempts;
                                    }
                                }
                            }
                        }
                        if ($k == 'question_multianswer' && !empty($info['question_multianswer'])) {
                            $parti = explode(',', $value['sequence']);
                            $sequence = array();
                            for ($g = 0; $g < count($parti); $g++) {
                                $position1 = array_search($parti[$g], array_column($id_question, 'p'));
                                if (is_int($position1)) {
                                    $sequence[$g] = $id_question[$position1]['h'];
                                }
                                if (!empty($bancoPreg)) {
                                    $position0 = array_search($parti[$g], array_column($bancoPreg['question'], 'p'));
                                    if (is_int($position0) && array_key_exists('h', $bancoPreg['question'][$position0])) {
                                        $sequence[$g] = $bancoPreg['question'][$position0]['h'];
                                    }
                                }
                            }
                            $position = array_search($value['question'], array_column($id_question, 'p'));
                            if (is_int($position)) {
                                $value['question'] = $id_question[$position]['h'];
                            }
                            if (!empty($bancoPreg)) {
                                $position2 = array_search($value['question'], array_column($bancoPreg['question'], 'p'));
                                if (is_int($position2)) {

                                    $value['question'] = $bancoPreg['question'][$position2]['h'];
                                }
                            }
                            $value['sequence'] = implode(",", $sequence);
                            $info_actividad[$k][$keye]['p'] = $value['id'];
                            $bancoPreg['question_multianswer'][count($bancoPreg['question_multianswer'])]['p'] = $value['id'];
                            unset($value['id']);
                            $question_multianswer = $DB->insert_record($k, $value);

                            $info_actividad[$k][$keye]['h'] = $question_multianswer;
                            $bancoPreg['question_multianswer'][count($bancoPreg['question_multianswer'])]['h'] = $question_multianswer;
                        }

                        if ($k == 'qtype_ddimageortext' || $k == 'qtype_ddimageortext_drags' || $k == 'qtype_ddimageortext_drops' || $k == 'qtype_ddmarker' || $k == 'qtype_ddmarker_drags' || $k == 'qtype_ddmarker_drops' || $k == 'qtype_essay_options' || $k == 'qtype_match_options' || $k == 'qtype_match_subquestions' || $k == 'qtype_multichoice_options' || $k == 'qtype_randomsamatch_options' || $k == 'qtype_shortanswer_options') {
                            $position = array_search($value['questionid'], array_column($id_question, 'p'));

                            if (is_int($position)) {
                                $value['questionid'] = $id_question[$position]['h'];
                                $info_actividad[$k][$value['id']]['p'] = $value['id'];
                                if (!array_key_exists($k, $bancoPreg)) $bancoPreg[$k] = [];
                                $bancoPreg[$k][count($bancoPreg[$k])]['p'] = $value['id'];
                                $inK = $DB->insert_record($k, $value);

                                $info_actividad[$k][$value['id']]['h'] = $inK;
                                $bancoPreg[$k][count($bancoPreg[$k])]['h'] = $inK;
                            }
                        }
                    }


                    ///versiones
                    if ($k == 'question_versions') {
                        foreach ($val as $keye => $value) {
                            $kId = $value['id'];
                            $init_questionbankentryid = $value['questionbankentryid'];
                            $init_questionid = $value['questionid'];
                            //echo '<pre>$bancoPreg: ';  print_r($bancoPreg['question_categories']);  echo '</pre>'; die();
                            if (is_array($bancoPreg) && array_key_exists('question_versions', $bancoPreg)) {
                                $vers_banck = array_values($bancoPreg['question_versions']);
                                $position = array_search($kId, array_column($vers_banck, 'p'));
                            } else $position = null;

                            if (!is_int($position)) {
                                $id_question_ban = array_values($id_question_bank_entries);
                                $position = array_search($kId, array_column($id_question_ban, 'p')); //verificar si existen en las recien creadas
                                if (is_int($position))
                                    $value['questionbankentryid'] = $id_question_ban[$position]['h'];
                                else {
                                    if (is_array($bancoPreg) && array_key_exists('question_bank_entries', $bancoPreg)) {
                                        $bank_bank = array_values($bancoPreg['question_bank_entries']);
                                        $position = array_search($value['questionbankentryid'], array_column($bank_bank, 'p')); //verificar si existen en las creadas el banco
                                        if (is_int($position))
                                            $value['questionbankentryid'] = $bank_bank[$position]['h'];
                                    }
                                }

                                $id_question = array_values($id_question);
                                $position = array_search($kId, array_column($id_question, 'p')); //verificar si existen en las recien creadas
                                if (is_int($position))
                                    $value['questionid'] = $id_question[$position]['h'];
                                else {
                                    if (is_array($bancoPreg) && array_key_exists('question', $bancoPreg)) {
                                        $id_question_ban = array_values($bancoPreg['question']);
                                        $position = array_search($value['questionid'], array_column($id_question_ban, 'p')); //verificar si existen en las creadas el banco
                                        if (is_int($position))
                                            $value['questionid'] = $id_question_ban[$position]['h'];
                                    }
                                }
                                if ($init_questionbankentryid != $value['questionbankentryid'] && $init_questionid != $value['questionid']) {
                                    $id_ban = $DB->insert_record($k, $value);
                                    $info_actividad[$k][$kId]['p'] = $kId;
                                    $info_actividad[$k][$kId]['h'] = $id_ban;
                                }
                            }
                        }
                    }
                }
            }
            foreach ($info as $k => $v) {
                if ($k == 'question_set_references' || $k == 'question_references') {
                    //echo '-->>'.$k.'<<--';
                    //echo '<pre>$v: '; print_r($v);    echo '</pre>';
                    foreach ($v as $key => $val) {
                        foreach ($val as $keye => $value) {
                            if (array_key_exists('version', $value) && empty($value['version'])) $value['version'] = null;


                            $id_question_slots = array_values($id_question_slots);
                            $position = array_search($value['itemid'], array_column($id_question_slots, 'p')); //verificar si existen
                            if (is_int($position)) {
                                $value['itemid'] = $id_question_slots[$position]['h'];
                                if (array_key_exists('questionbankentryid', $value)) { //question_references
                                    $id_question_ban = array_values($id_question_bank_entries);
                                    $position = array_search($value['questionbankentryid'], array_column($id_question_ban, 'p')); //verificar si existen en las recien creadas
                                    if (is_int($position))
                                        $value['questionbankentryid'] = $id_question_ban[$position]['h'];
                                    else {
                                        if (is_array($bancoPreg) && array_key_exists('question_bank_entries', $bancoPreg)) {
                                            $bank_bank = array_values($bancoPreg['question_bank_entries']);
                                            $position = array_search($value['questionbankentryid'], array_column($bank_bank, 'p')); //verificar si existen en las creadas el banco
                                            if (is_int($position))
                                                $value['questionbankentryid'] = $bank_bank[$position]['h'];
                                        }
                                    }
                                }
                                //question_set_references
                                if (array_key_exists('questionscontextid', $value)) $value['questionscontextid'] = $course_context->id;
                                if (array_key_exists('usingcontextid', $value)) $value['usingcontextid'] = $como_context->id;
                                if (array_key_exists('filtercondition', $value)) {
                                    $filtercondition = json_decode($value['filtercondition']);
                                    if (property_exists($filtercondition, 'questioncategoryid')) {
                                        $id_question_c = array_values($id_question_categories);
                                        $position = array_search($filtercondition->questioncategoryid, array_column($id_question_c, 'p')); //verificar si existe en la recien creadas
                                        if (is_int($position))
                                            $filtercondition->questioncategoryid = $id_question_c[$position]['h'];
                                        else {
                                            if (is_array($bancoPreg) && array_key_exists('question_categories', $bancoPreg)) {
                                                $cate_bank = array_values($bancoPreg['question_categories']); //verificar si existen en las creadas el banco
                                                $position = array_search($filtercondition->questioncategoryid, array_column($cate_bank, 'p'));
                                                if (is_int($position))
                                                    $filtercondition->questioncategoryid = $cate_bank[$position]['h'];
                                            }
                                        }
                                        $filtercondition = json_encode($filtercondition);
                                        $value['filtercondition'] = $filtercondition;
                                    }
                                }
                                $id_ref = $DB->insert_record($k, $value);
                                $info_actividad[$k][$kId]['p'] = $value['id'];
                                $info_actividad[$k][$kId]['h'] = $id_ref;
                            }
                        }
                    }
                }
            }
        } else if ($typeAc == 'customcert') {
            $objCRE = new self();
            $id_customcert_templates = array();
            $id_temp_p = 0;
            $id_temp_h = 0;

            /*             echo '<pre>$info: ';
            print_r($info, true);
            echo '</pre>';
            die(); */

            foreach ($info as $k => $v) {
                $info_actividad[$k] = array();
                if ($k == 'customcert_templates') {
                    $v['contextid'] = $como_context->id;
                    $id_temp_h = $DB->insert_record('customcert_templates', $v);
                    $id_temp_p = $v['id'];
                    $info_actividad[$k]['p'] = $v['id'];
                    $info_actividad[$k]['h'] = $id_temp_h;
                } else if ($k == 'id_rel') {
                    $id_rel = $v;
                } else if ($k == 'customcert_pages') {
                    $id_customcert_pages = array();
                    $info_actividad[$k] = array();

                    foreach ($v as $ke => $valu) {
                        $valu['templateid'] = $id_temp_h;

                        $id_pag = $DB->insert_record('customcert_pages', $valu);
                        $id_customcert_pages[$ke] = array();
                        $id_customcert_pages[$ke]['p'] = $valu['id'];
                        $id_customcert_pages[$ke]['h'] = $id_pag;

                        $info_actividad[$k][$ke] = array();
                        $info_actividad[$k][$ke]['p'] = $valu['id'];
                        $info_actividad[$k][$ke]['h'] = $id_pag;
                    }
                } else if ($k == 'customcert_elements') {
                    $info_actividad[$k] = array();
                    foreach ($v as $ke => $valu) {
                        $id_customcert_pages = array_values($id_customcert_pages);
                        $position = array_search($valu['pageid'], array_column($id_customcert_pages, 'p'));
                        if (is_int($position)) {
                            $valu['pageid'] = $id_customcert_pages[$position]['h'];
                            if ($valu['data'] && !empty($valu['data'])) {
                                $ob_data = json_decode($valu['data']);

                                if (is_object($ob_data) && property_exists($ob_data, 'contextid') && property_exists($ob_data, 'filename')  && property_exists($ob_data, 'filearea') && isset($valu['customcert_archivo']) && $valu['customcert_archivo']) {
                                    $ob_data->contextid = $course_context->id;
                                    $valu['data'] = json_encode($ob_data);
                                    $archivo = $valu['customcert_archivo'];
                                    $name_archive = $archivo . '_' . $id_nodo . '_' . $id_rel;
                                    $to = $CFG->dataroot . '/temp/';
                                    $objCRE->customcert_add_instance_nueva($valu, $to, $name_archive, $id_nodo, $ob_data);
                                }
                            }
                            $id_ele = $DB->insert_record('customcert_elements', $valu);
                            $info_actividad[$k][$ke] = array();
                            $info_actividad[$k][$ke]['p'] = $valu['id'];
                            $info_actividad[$k][$ke]['h'] = $id_ele;
                        }
                    }
                }

                if ($id_temp_h) $DB->update_record('customcert', array('id' => $id_acti, 'templateid' => $id_temp_h)); // }


            }
        } else if ($typeAc == 'folder') {

            require_once $CFG->dirroot . '/local/backup_course/folder_S3/controlador2_m.php';
            $s3 = new Controlador2_m(); //hacer transfer

            $DB->delete_records('files', array('contextid' => $como_context->id));

            $info = (object)$info;

            $fs = get_file_storage();

            $files_fold = (object)json_decode($info->files_fold);
            $moodle_data_folder = (object)json_decode($info->moodle_data_folder);
            $name_archive_folder = (object)json_decode($info->name_archive_folder);
            $files_dir = (object)json_decode($info->files_dir);
            $hashed = (object)json_decode($info->hashed);
            $urls_file = (object)json_decode($info->urls_file);
            $rel = json_decode($info->rel);

            foreach ($files_fold as $kffold => $vffold) {

                foreach ($vffold as $kvff => $vvff) {

                    foreach ($name_archive_folder as $knmf => $vnmf) {

                        $parts = explode('_', $vnmf);

                        $firstPart = $parts[0];

                        foreach ($hashed as $khs => $vhs) {

                            foreach ($urls_file as $kurls => $vurls) {

                                $fileName = basename($vurls);
                                $par = explode("_", $fileName);
                                // Obtener la primera parte
                                $name = $par[0];

                                if ($firstPart == $name && $firstPart == $vvff->contenthash) {

                                    $mainString = $vnmf;
                                    $substring = $vhs;

                                    $position = strpos($mainString, $substring);

                                    $associatedSubstring = substr($mainString, $position, strlen($substring));

                                    if ($position !== false) {

                                        $archivo = $vvff->contenthash;

                                        $name_archive = $vnmf;
                                        $to = $CFG->dataroot . '/temp/';
                                        $s3->run('transfer', $name_archive, $to, $id_nodo);

                                        //Cambiamos el nombre del archivo
                                        $rutaArchivo = $to . $name_archive;
                                        $nuevoNombre = $vvff->contenthash;
                                        rename($rutaArchivo, dirname($rutaArchivo) . '/' . $nuevoNombre);


                                        $from_zip_file = $to . $nuevoNombre;

                                        $file_record = array(
                                            'contextid' => $como_context->id,
                                            'component' => 'mod_folder',
                                            'filearea' => $vvff->filearea,
                                            'itemid' => 0,
                                            'filepath' => $vvff->filepath,
                                            'filename' => $vvff->filename,
                                            'timecreated' => time(),
                                            'timemodified' => time()
                                        );

                                        if (filesize($from_zip_file) > 0) {


                                            $file = $fs->get_file(
                                                $file_record['contextid'],
                                                $file_record['component'],
                                                $file_record['filearea'],
                                                $file_record['itemid'],
                                                $file_record['filepath'],
                                                $file_record['filename']
                                            );

                                            if (!$file) {
                                                $packagefile = $fs->create_file_from_pathname($file_record, $from_zip_file);

                                                $packagefile = (array) $packagefile;

                                                foreach ($packagefile as  $pck => $vpck) {
                                                    $vpck = (object)$vpck;
                                                    if (property_exists($vpck, 'id')) {
                                                        $info_actividad['files_folder']["$vvff->id"]['p'] = $vvff->id;
                                                        $info_actividad['files_folder']["$vvff->id"]['h'] = $vpck->id;
                                                    }
                                                }

                                                if (file_exists($from_zip_file)) {
                                                    unlink($from_zip_file);
                                                }

                                                if (file_exists($rutaArchivo)) {
                                                    unlink($rutaArchivo);
                                                }
                                            }

                                            $moodle_data = $vurls;

                                            $s3->run('delete', $moodle_data, $name_archive, $id_nodo);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } else if ($typeAc == 'scorm') {
            $id_scorm = array();
            foreach ($info as $k => $v) {
                if ($k == 'scoes') {
                    foreach ($v as $ke => $valu) {
                        unset($v['scorm']);
                        $valu['scorm'] = $id_acti;
                        $id_scorm[$ke] = $DB->insert_record('scorm_scoes', $valu);

                        $info_actividad[$k][$ke]['p'] = $valu['id'];
                        $info_actividad[$k][$ke]['h'] = $id_scorm[$ke];
                    }
                } else if ($k == 'scoes_data') {
                    foreach ($v as $ke => $valu) {
                        foreach ($valu as $k_e => $v_alu) {
                            if (array_key_exists($v_alu['scoid'], $id_scorm)) {
                                $v_alu['scoid'] = $id_scorm[$v_alu['scoid']];
                                $id_scoes_data = $DB->insert_record('scorm_scoes_data', $v_alu);
                                $info_actividad[$k][$k_e]['p'] = $v_alu['id'];
                                $info_actividad[$k][$k_e]['h'] = $id_scoes_data;
                            }
                        }
                    }
                } else if ($k == 'id_rel') {
                    $id_rel = $v;
                }
            }


            $archivo = $section->sha1hash;

            $moodle_data = $info['url_scorm'];
            $name_archive = $archivo . '_' . $id_nodo . '_' . $id_rel . '.zip';
            $to = $CFG->dataroot . '/temp/';

            $cr1 = substr($archivo, 0, 2);
            $cr2 = substr($archivo, 2, 2);

            $file_dir = '/filedir/' . $cr1 . '/' . $cr2 . '/' . $archivo;

            $info_actividad["file_dir"] = $file_dir;

            try {
                require_once $CFG->dirroot . '/local/backup_course/folder_S3/controlador2_m.php';
                $s3 = new Controlador2_m(); //hacer transfer
                $s3->run('transfer', $name_archive, $to, $id_nodo);

                $section->scormtype = 'local';
                $section->visible = $relation->visible;
                $section->cmidnumber = $relation->idnumber;
                $section->groupmode = $relation->groupmode;
                $section->groupingid = $relation->groupingid;
                $section->availabilityconditionsjson = $relation->availability;
                $section->tags = '';
                $section->section = 0;
                $section->coursemodule = $id_como;
                $section->module = $id_module;
                $section->modulename = 'scorm';
                $section->add = 'scorm';
                $section->update = 0;
                $section->return = 0;
                $section->sr = 0;
                $section->instance = $id_acti;

                $objCRE = new self();
                $values = $objCRE->scorm_add_instance_nueva($section, $id_acti, $to . $name_archive);

                if ($values) {
                    $s3->run('delete', $moodle_data, $name_archive, $id_nodo);
                    unlink($to . $name_archive);
                }
            } catch (Exception $exc) {
                //$errors->error = json_encode($infoActividad);
                //$errors->description = 'No se pudo crear el scrom -- ' . $exc->getTraceAsString();
                //$objCRE->save_error($errors);
            }
        }

        return $info_actividad;
    }


    /*
     * Guardar los archivos h5p relacionados a la actividad que estén en el banco de contenido
     * retunr {arrray}
     */
    private function saveH5P_Bank($bankH5P, $id_como)
    {
        global $DB, $CFG;
        $files_save = array();
        $como_context = context_module::instance($id_como);
        foreach ($bankH5P as $key => $value) { //recorrer todos los archivos
            $value = (array) $value;
            $regis_ya = $DB->get_record_sql('SELECT * FROM {files} WHERE contextid = :contextid '
                . 'AND component = :component AND filearea = :filearea '
                . 'AND itemid    = :itemid    AND filepath = :filepath  AND '
                . $DB->sql_compare_text('filename') . ' = ' . $DB->sql_compare_text(':filename') . ' AND '
                . $DB->sql_compare_text('source') . ' = ' . $DB->sql_compare_text(':source'), array(
                'contextid' => $como_context->id,
                'component' => $value['component'],
                'filearea' => $value['filearea'],
                'itemid' => $value['itemid'],
                'filepath' => $value['filepath'],
                'filename' => $value['filename'],
                'source' => $value['filename']
            ));
            if (empty($regis_ya)) {
                $fs = get_file_storage();
                $archivo = $value['contenthash'];
                $cr1 = substr($archivo, 0, 2);
                $cr2 = substr($archivo, 2, 2);
                $moodle_data = $CFG->dataroot . '/filedir/' . $cr1 . '/' . $cr2 . '/' . $archivo;
                if (file_exists($moodle_data)) {
                    $file_record = array(
                        'contextid' => $como_context->id,
                        'component' => $value['component'],
                        'filearea' => $value['filearea'],
                        'itemid' => $value['itemid'],
                        'filepath' => $value['filepath'],
                        'filename' => $value['filename'],
                        'source' => $value['filename'],
                        'timecreated' => time(),
                        'timemodified' => time()
                    );
                    try {
                        if ($packagefile = $fs->create_file_from_pathname($file_record, $moodle_data)) {
                            array_push($files_save, array('p' => $value['id'], 'h' => $packagefile));
                        }
                    } catch (Exception $exc) {
                    }
                } else array_push($files_save, array('p' => $value['id'], 'h' => 'el H5P NO viajó con el banco'));
            } else array_push($files_save, array('p' => $value['id'], 'h' => $regis_ya->id));
        }
        return $files_save;
    }


    private static function compararPorSortorder($a, $b)
    {
        return $b['sortorder'] - $a['sortorder'];
    }

    /*
     * Create agupaciones, items de las categorias de las calificaciones, 
     * actualizar las opciones para los choicegroup-> saveGrade_items
     * @params {objet} 
     */
    private function saveGrade_items()
    {
        global $DB;
        $objCRE = new self();
        $registro = (object)$_POST;
        $arry_items = array();
        $arr = array();
        $relation_obj = json_decode($registro->rela);
        usort($registro->itm, array('create', 'compararPorSortorder'));

        for ($i = 0; $i < count($relation_obj); $i++) {
            if (!empty($relation_obj[$i])) {
                for ($j = 0; $j < count($relation_obj[$i]); $j++) {
                    if (!empty($relation_obj[$i][$j])) {
                        $arr[] = (array)$relation_obj[$i][$j];
                        if ($relation_obj[$i][$j]->table == 'choicegroup' && !empty($relation_obj[$i][$j])) {
                            $choicegroup_options = $DB->get_records('choicegroup_options', array('choicegroupid' => $relation_obj[$i][$j]->id_acti)); // buscar todos los choicegroup_options que hay
                            if (!empty($choicegroup_options) && property_exists($registro, 'groups_p')) {
                                foreach ($choicegroup_options as $valuel) {
                                    $valores = (array)$valuel;
                                    $position = array_search($valores['groupid'], $registro->groups_p);
                                    if (is_int($position)) {
                                        $valores['groupid'] = $registro->groups_h[$position];
                                    }
                                    $ac = $DB->update_record('choicegroup_options', $valores); // asignar los grupos a los choicegroup
                                }
                            }
                        }
                    }
                }
            }
        }

        for ($i = 0; $i < count($relation_obj); $i++) {
            if (!empty($relation_obj[$i])) {
                for ($j = 0; $j < count($relation_obj[$i]); $j++) {
                    if (!empty($relation_obj[$i][$j]) && $relation_obj[$i][$j]->table == 'game') {
                        //$game = $DB->get_records('game',array('id' =>$relation_obj[$i][$j]->id_acti));
                        $game = $DB->get_record('game', array('id' => $relation_obj[$i][$j]->id_acti));
                        //$game = each($game);
                        $insert = new stdClass();
                        $insert->id = $relation_obj[$i][$j]->id_acti;
                        for ($h = 0; $h < count($arr); $h++) {
                            if ($arr[$h]['id_acti_p'] == $game->quizid && $arr[$h]['table'] == 'quiz') {
                                $insert->quizid = $arr[$h]['id_acti'];
                                $DB->update_record('game', $insert);
                            } else if ($arr[$h]['id_acti_p'] == $game->glossaryid && $arr[$h]['table'] == 'glossary') {
                                $insert->glossaryid = $arr[$h]['id_acti'];
                                $DB->update_record('game', $insert);
                            } else if ($arr[$h]['id_acti_p'] == $game->bookid && $arr[$h]['table'] == 'book') {
                                $insert->bookid = $arr[$h]['id_acti'];
                                $DB->update_record('game', $insert);
                            }
                        }

                        if (!empty($registro->banck['question_categories'])) {
                            for ($k = 0; $k < count($registro->banck['question_categories']); $k++) {
                                if ($game->questioncategoryid == $registro->banck['question_categories'][$k]['p']) {
                                    $insert->questioncategoryid = $registro->banck['question_categories'][$k]['h'];
                                    $DB->update_record('game', $insert);
                                }
                            }
                        }
                    }
                }
            }
        }
        $h = 0;
        if (!empty($registro->catH) && !empty($registro->catP) && !empty($registro->id_nodo)) {
            foreach ($registro->itm as $key => $val) {

                $val['courseid'] = $registro->id_nodo;
                if (!empty($registro->catH) && !empty($registro->catP) && $val['itemtype'] == 'mod') {
                    for ($a = 0; $a < count($registro->catP); $a++) {
                        if ($registro->catP[$a] == $val['categoryid']) {
                            $val['categoryid'] = $registro->catH[$a];
                        }
                    }
                }
                for ($j = 0; $j < count($arr); $j++) {
                    if (!empty($arr[$j])) {

                        if ($val['itemtype'] == 'course') {
                            $val['iteminstance'] = $registro->catH[0];
                            if (empty($val['itemnumber'])) {
                                $val['itemnumber'] = null;
                            }
                            if (empty($val['itemmodule'])) {
                                $val['itemmodule'] = null;
                            }
                        } else if (($val['itemtype'] == 'category' || $val['itemtype'] == 'manual') && $val['itemtype'] != 'mod') {
                            $position = array_search($val['iteminstance'], $registro->catP);
                            if (is_int($position)) {
                                $val['iteminstance'] = $registro->catH[$position];
                            }
                            if (empty($val['itemnumber'])) {
                                $val['itemnumber'] = null;
                            }
                            if (empty($val['itemmodule'])) {
                                $val['itemmodule'] = null;
                            }
                        }
                        if ($val['itemtype'] == 'manual') {
                            $position = array_search($val['categoryid'], $registro->catP);
                            if (is_int($position)) {
                                $val['categoryid'] = $registro->catH[$position];
                            }
                        }
                        if ($val['iteminstance'] == $arr[$j]['id_acti_p'] && $val['itemmodule'] == $arr[$j]['table']) {
                            $val['iteminstance'] = $arr[$j]['id_acti'];
                        }

                        if (empty($val['decimals'])) {
                            $val['decimals'] = null;
                        }
                        if (empty($val['categoryid'])) {
                            $val['categoryid'] = null;
                        }
                        if (empty($val['scaleid'])) {
                            $val['scaleid'] = null;
                        }
                        if (empty($val['outcomeid'])) {
                            $val['outcomeid'] = null;
                        }
                        if (empty($val['calculation'])) {
                            $val['calculation'] = null;
                        } else {

                            // Construye un mapa p -> h una sola vez
                            $map = [];
                            if (!empty($arry_items['grade_items'])) {
                                foreach ($arry_items['grade_items'] as $gi) {
                                    if (isset($gi['p'], $gi['h'])) {
                                        $map[(int)$gi['p']] = $gi['h'];
                                    }
                                }
                            }

                            // Reemplaza dinámicamente cada ##giN##, preservando lo que haya alrededor (comas, +, ), espacios, etc.)
                            $val['calculation'] = preg_replace_callback('/##gi(\d+)##/', function ($m) use ($map) {
                                $p = (int)$m[1];
                                return isset($map[$p]) ? "##gi{$map[$p]}##" : $m[0]; // si no hay mapeo, deja el token igual
                            }, $val['calculation']);
                        }
                        if (empty($val['iteminstance'])) {
                            $val['iteminstance'] = null;
                        }
                    }
                }
                $arry_items['grade_items'][$h]['p'] = $val['id'];
                $arry_items['grade_items'][$h]['h'] = $DB->insert_record('grade_items', $val); // asignar items a Configuración Calificaciones
                $id_como = $objCRE->availability_grade($arry_items['grade_items'][$h], $registro->id_nodo, $relation_obj, property_exists($registro, 'groups_p') ? $registro->groups_p : null, property_exists($registro, 'groups_h') ? $registro->groups_h : null);
                $h += 1;
            }
        }
        if (!empty($registro->groupings_groups)) {
            $h = 0;
            foreach ($registro->groupings_groups as $key => $value) {
                if (!empty($value['id'])) {
                    $position = array_search($value['groupingid'], $registro->groupings_p);
                    if (is_int($position)) {
                        $value['groupingid'] = $registro->groupings_h[$position];
                        $pos = array_search($value['groupid'], $registro->groups_p);
                        if (is_int($pos)) {
                            $value['groupid'] = $registro->groups_h[$pos];
                            $arry_items['groupings_groups'][$h]['p'] = $value['id'];
                            $arry_items['groupings_groups'][$h]['h'] = $DB->insert_record('groupings_groups', $value); //guardar los grupos dentro de las agrupaciones
                            $h += 1;
                        }
                    }
                }
            }
        }

        if ($DB->get_manager()->table_exists('groupselect')) {
            $groupselect = $DB->get_records('groupselect', array('course' => $registro->id_nodo));

            if (!empty($groupselect) && is_array($groupselect)) {
                foreach ($groupselect as $key => $value) {
                    if (!empty($value->targetgrouping)) {
                        $position = array_search($value->targetgrouping, $registro->groupings_p);
                        if (is_int($position)) {
                            $value->targetgrouping = $registro->groupings_h[$position];
                            $DB->update_record('groupselect', $value);
                        }
                    }
                }
            }
        }

        return $arry_items;
    }
    /*
     * Enviar email de error -> emailError
     * return {mensaje};
     */
    private function emailError()
    {
        global $DB, $USER;
        $objCRE = new self();
        $registro = (object)$_POST;
        //$para = 'luis.caceres@uniminuto.edu';
        $para = 'campus@uniminuto.edu';

        $titulo = 'Error en la importación';

        $mensaje = '<html>' .
            '<head>' .
            '<title>Error en la importación</title>' .
            '</head>' .
            '<body>' .
            '<p>En ' . $_SERVER['HTTP_REFERER'] . ' el Curso: ' . $registro->id_nodo .
            ' importando el curso en el padre: ' .
            $registro->id_padre . ' tiene el error: </p>' .
            '<img src="' . $registro->img . '" />' .
            '</body>' .
            '</html>';
        try {
            if (!@mail($para, $titulo, $mensaje)) {
                throw new Exception('No se envió el email');
            }
        } catch (Exception $exc) {
            $errors = new stdClass();
            $errors->courseid = $registro->id_nodo;
            $errors->userid = $USER->id;
            $errors->error = json_encode($mensaje);
            $errors->description = 'No se puede enviar mensaje -- ' . $exc->getTraceAsString();
            $objCRE->save_error($errors);
        }

        return $mensaje;
    }

    /*
     * Relacionar las restricciones de calificación de las actividades con el grade_items
     */
    private function availability_grade($val, $id_nodo, $relation_obj, $g_p, $g_h)
    {
        global $DB;
        $objCRE = new self();
        $course_modules = $DB->get_records('course_modules', array('course' => $id_nodo));
        foreach ($course_modules as $key => $value) {
            if (!empty($value->availability)) {
                $availability = json_decode($value->availability);
                if (property_exists($availability, 'c')) {

                    for ($i = 0; $i < count($availability->c); $i++) {
                        if (property_exists($availability->c[$i], 'type')) {
                            if ($availability->c[$i]->type == 'grade') {
                                if ($availability->c[$i]->id == $val['p']) {
                                    $availability->c[$i]->id = (int)$val['h'];
                                }
                            }
                            if ($availability->c[$i]->type == 'completion') {
                                $id_cm = (int)$objCRE->availability_complete($relation_obj, $availability->c[$i]->cm);
                                if (!empty($id_cm)) {
                                    $availability->c[$i]->cm = $id_cm;
                                }
                            }
                            if ($availability->c[$i]->type == 'group') {
                                $position = array_search($availability->c[$i]->id, $g_p);
                                if (is_int($position)) {
                                    $availability->c[$i]->id = (int)$g_h[$position];
                                }
                            }
                        }

                        if (property_exists($availability->c[$i], 'c')) {
                            for ($j = 0; $j < count($availability->c[$i]->c); $j++) {
                                if (property_exists($availability->c[$i]->c[$j], 'type')) {
                                    if ($availability->c[$i]->c[$j]->type == 'grade') {
                                        if ($availability->c[$i]->c[$j]->id == $val['p']) {
                                            $availability->c[$i]->c[$j]->id = (int)$val['h'];
                                        }
                                    }
                                    if ($availability->c[$i]->c[$j]->type == 'completion') {
                                        if ($availability->c[$i]->type == 'completion') {
                                            $id_cm = (int)$objCRE->availability_complete($relation_obj, $availability->c[$i]->c[$j]->cm);
                                            if (!empty($id_cm)) {
                                                $availability->c[$i]->c[$j]->cm = $id_cm;
                                            }
                                        }
                                    }
                                    if ($availability->c[$i]->c[$j]->type == 'group') {
                                        $position = array_search($availability->c[$i]->c[$j]->id, $g_p);
                                        if (is_int($position)) {
                                            $availability->c[$i]->c[$j]->id = (int)$g_h[$position];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                $value->availability = json_encode($availability);
                $DB->update_record('course_modules', $value);
            }
        }
    }

    private function availability_complete($relation_obj, $id)
    {
        $res = 0;
        foreach ($relation_obj as $key => $value) {
            if (is_object($value) || is_array($value)) {
                foreach ($value as $k => $v) {
                    if ($v->id_como_p == $id) {
                        $res = $v->id_como;
                    }
                }
            }
        }
        return $res;
    }
}
create::run();
