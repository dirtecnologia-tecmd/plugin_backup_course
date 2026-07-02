<?php
require_once('../../../../config.php');
require_once("$CFG->libdir/filelib.php");

class update
{

    public static function run()
    {
        $obj = new self();
        $idfunc = $_POST['key'];
        switch ($idfunc) {
            case 'U01':
                $resp = $obj->updateToken($_POST['data_child']['node_id']);
                break;
            case 'U02':
                $resp = $obj->updateCourse($_POST['data_padre'], $_POST['id_nodo'], $_POST['fechas']);
                break;
            case 'U03':
                $resp = $obj->updatenotificate();
                break;
            case 'U04':
                $resp = $obj->saveSequence();
                break;
            case 'U05':
                $resp = $obj->updateAddActivity($_POST['courseid']);
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

        if( $url_https == 'https'){

            $url_http = str_replace("s", "", $url_https);
        
            return $url_http.":".$url_expl[1];

        }else{

            return $url;

        }
 
    }

    /*
     * Actualizar Tokens -> updateToken
     * Permite actualizar un token en el padre y el hijo
     * @params {int} $id
     * Retorna la verificacion de la actualización
     * return {objet};
     */
    private function updateToken($id)
    {
        global $DB;
        $registro = (object)$_POST;
        $registro_token = new stdClass();
        $tok = sha1('2017.UVD_TokeN_noDos');
        $url_actual = explode('/local/', $_SERVER['HTTP_REFERER']);
        $url = $this->formatHttp($registro->data_child['node_domain']) . '/webservice/rest/server.php?wstoken=' . $tok . '&wsfunction=local_remoter_register_node&moodlewsrestformat=json';

        if (isset($registro->token)) {
            $registro_token->token = sha1($registro->token);
            $new_tok = $registro->token;
        } else {
            $new_tok = '';
        }
        //$config_sftp = $DB->get_record_sql('SELECT * FROM {bc_config_sftp} LIMIT 1');
        //$arr_sftp = each($config_sftp);
        //$arr_sftp = ($config_sftp);
        $params = array(
            'function' => $registro->key,
            'url' => $this->formatHttp($url_actual[0]),
            'nombre' => $registro->data_child['node_token'],
            'token' => $new_tok,
            /* 'ip' => $_SERVER['SERVER_ADDR'], */
            'url_hijo' => $this->formatHttp($registro->data_child['node_domain']),
            'startdate' => $registro->data_child['startdate'],
            'enddate8' => $registro->data_child['enddate8'],
            'enddate16' => $registro->data_child['enddate16'],
            'estado' => $registro->data_child['node_status'],
            'edition_acti' => $registro->data_child['edition_acti'],
            /*'server'    => $arr_sftp->server,
                        'port'      => $arr_sftp->port,
                        'username'  => $arr_sftp->username,
                        'password'  => $arr_sftp->password*/
        );

        /* var_dump($params);
        var_dump($url); */

        $curl = new curl;
        $results = json_decode($curl->post($url, $params));
        if (!empty($results) && array_key_exists('0', $results) && property_exists($results[0], 'ack')) {
            foreach ($results as $arr) {
                if ($arr->ack == 1) {
                    $registro_token->id = $id;
                    $registro_token->nombre = $registro->data_child['node_name'];
                    /* $registro_token->ip = $registro->data_child['node_ip']; */
                    $registro_token->url_hijo = $this->formatHttp($registro->data_child['node_domain']);
                    $registro_token->startdate = $registro->data_child['startdate'];
                    $registro_token->enddate8 = $registro->data_child['enddate8'];
                    $registro_token->enddate16 = $registro->data_child['enddate16'];
                    $registro_token->estado = $registro->data_child['node_status'];
                    $registro_token->edition = $registro->data_child['edition_acti'];
                    $registro_token->url_padre = $this->formatHttp($url_actual[0]);

                    $DB->update_record('bc_registro_pc', $registro_token);
                }
            }
        } else {
            print_r($results);
        }
        return $results;
    }
    /*
     * UpdateCourse en el nodo -> updateCourse
     * Actualiza la informacion general del curso en el hijo, con la información del padre
     * @params {array} $padre
     * @params {int} $id_nodo
     * @params {int} $semana
     */
    private function updateCourse($padre, $id_nodo, $semana)
    {
        global $DB;
        $padre['id'] = $id_nodo;
        unset($padre['fullname'], $padre['category'], $padre['idnumber'], $padre['shortname'], $padre['timecreated'], $padre['timemodified']);
        $padre['timemodified'] = time();
        $tb_fechas = $DB->get_record_sql('SELECT * FROM {bc_registro_pc} WHERE nombre = "Padre" limit 1');
        if (!empty($tb_fechas) && property_exists($tb_fechas, 'startdate')) {
            $padre['startdate'] = $tb_fechas->startdate;
            $padre['enddate'] = ($semana == 8) ? $tb_fechas->enddate8 : $tb_fechas->enddate16;
        }
        if ($DB->update_record('course', $padre)) {
            return (int)$semana;
        } else {
            return 0;
        }
    }
    /*
     * Updatenotificate->notificar a los nodos una actualización
     */
    private function updatenotificate()
    {
        global $DB;
        $registro = (object)$_POST;
        $registro_token = new stdClass();
        $tok = sha1('2017.UVD_TokeN_noDos');
        $url_actual = explode('/local/', $_SERVER['HTTP_REFERER']);
        $url = $registro->url_padre . '/webservice/rest/server.php?wstoken=' . $tok . '&wsfunction=local_remoter_register_node&moodlewsrestformat=json';
        if (isset($registro->token)) {
            $registro_token->token = sha1($registro->token);
            $new_tok = $registro->token;
        } else {
            $new_tok = '';
        }
        $params = array(
            'function' => $registro->key,
            'url' => $url_actual[0],
            'nombre' => $registro->data_child['node_token'],
            'token' => $new_tok,
            /* 'ip' => $_SERVER['SERVER_ADDR'], */
            'url_hijo' => $registro->data_child['node_domain'],
            'estado' => $registro->data_child['node_status'],
        );

        $curl = new curl;
        $results = json_decode($curl->post($url, $params));
    }

    /* 
     * Create Relation in course_modules and sections sequence -> saveSequence
     * Actualiza la secuencia de las actividades creadas en el hijo, para las secciones
     */
    private function saveSequence()
    {
        global $DB;
        $registro = (object)$_POST;
        $up = new stdClass();
        $up->id = $registro->id;
        $up->sequence = $registro->sequence;
        $DB->update_record('course_sections', $up);
        return $registro->id;
    }

    private function updateAddActivity($courseid){

        global $DB;

        $response = false;

        $sections = $DB->get_record('bc_add_sections_activities', array('courseid' => $courseid));
        if($sections){
            $data = new stdClass();
            $data->id = $sections->id;
            $data->section = $sections->section > 0? $sections->section -1 : 0;
            $response = $DB->update_record('bc_add_sections_activities', $data);
        }
        return $response;
    }
}
update::run();
