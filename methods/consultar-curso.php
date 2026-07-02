<?php

include_once '../../../config.php';
require_once("$CFG->libdir/filelib.php");
require_login(0, false);
if (isguestuser()) {  // Login as real user!
    $SESSION->wantsurl = (string)new moodle_url('index.php');
    redirect(get_login_url());
}

class ConsultarCurso
{
    private $function;
    private $datos;

    public function __construct($function, $datos)
    {
        $this->function = $function;
        $this->datos =  json_encode($datos);

        if ($this->function == 0) {
            $this->traerDatosCurso($this->datos);
        }
    }

    private function traerDatosCurso($datos)
    {
        global $CFG;

        $datos = json_decode($datos);

        $tok = '2017.UVD_TokeN_noDos';

        $url = $this->formatHttp($datos->url) . '/webservice/rest/server.php?wstoken=' . sha1($tok) . '&wsfunction=local_remoter_info_curso&moodlewsrestformat=json';

        $params = array(
            'id_course' => $datos->id_course,
        );

        $curl = new curl;

        $results = json_decode($curl->post($url, $params));

        $results->url_actual = $CFG->wwwroot;

        echo json_encode($results);
    }

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
}

//Recibimos los datos 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $function = $_POST['function'];
    $curso = new ConsultarCurso($function, $_POST);
} else {
    echo 'No se recibieron datos';
}
