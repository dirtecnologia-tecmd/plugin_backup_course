<?php

include_once '../../../config.php';
require_once("$CFG->libdir/filelib.php");
include_once '../folder_S3/class.UsoBucketS3.php';
require_login(0, false);
if (isguestuser()) {  // Login as real user!
    $SESSION->wantsurl = (string)new moodle_url('index.php');
    redirect(get_login_url());
}
class Configurar_S3
{
    private $function;
    private $datos;

    public function __construct($function, $datos)
    {
        $this->function = $function;
        $this->datos =  json_encode($datos);

        if ($this->function == 0) {

            $this->registrarDatos_S3($this->datos);
        } else if ($this->function == 1) {

            $this->obtenerRegistros_S3();
        }
    }
    
    private function registrarDatos_S3($datos)
    {
        global $DB, $USER, $CFG;

        $datos = json_decode($datos);

        $public_key = $datos->public_key;

        $private_key = $datos->private_key;

        $bucket = $datos->bucket;

        $obj = new UsoBucketS3_backup($CFG->dirroot);

        $cliente_s3 = $obj->_prueba_conexionS3($public_key, $private_key);

        $buckets_list = [];

        try {
            $result = $cliente_s3->listObjects([
                'Bucket' => $bucket
            ]);

            // Imprime los nombres de los objetos en el bucket
            foreach ($result['Contents'] as $object) {
                $buckets_list[] = $object['Key'];
            }

            $clave = "e303574e418a25bd47c79c2d35bbe7034a8a5c3d";

            $public_key = $this->encrypt(trim($public_key), $clave);

            $private_key = $this->encrypt(trim($private_key), $clave);

            $params =  [
                'bucket' => $bucket,
                'public_key' => $public_key,
                'private_key' => $private_key,
                'userid_registro' => $USER->id,
                'fecha_registro' => time()
            ];

            // Ejecutar la sentencia TRUNCATE
            $DB->execute("TRUNCATE TABLE {bc_configuracion_s3}");

            if ($DB->insert_record('bc_configuracion_s3', $params)) {

                echo json_encode(array("state" => "OK", "msj" => "La conexión se ha realizado exitosamente"));

            } else {

                echo json_encode(array("state" => "ERROR", "msj" => "Ocurrió un error en el registro de datos"));

            }

        } catch (Exception $e) {

            echo json_encode(array("state" => "ERROR", "msj" => "No es posible realizar la conexión, verifique las credenciales e intente de nuevo."));

        }
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

    private function obtenerRegistros_S3()
    {

        global $DB;

        $clave = "e303574e418a25bd47c79c2d35bbe7034a8a5c3d";

        $registro = $DB->get_record_sql('SELECT * FROM {bc_configuracion_s3} LIMIT 1');

        if ($registro) {

            $public_key =  $this->decrypt($registro->public_key, $clave);

            $private_key =  $this->decrypt($registro->private_key, $clave);

            $bucket = $registro->bucket;

            echo json_encode(["state" => "true", "public_key" => $public_key, "private_key" => $private_key, "bucket" => $bucket]);
        } else {

            echo json_encode(["state" => "false"]);
        }
    }

    public function decrypt($texto_cifrado, $clave)
    {
        // Decodificar la cadena codificada en base64
        $texto_cifrado = base64_decode($texto_cifrado);

        // Obtener el IV y el texto cifrado
        $iv = substr($texto_cifrado, 0, openssl_cipher_iv_length('aes-256-cbc'));
        $texto_cifrado = substr($texto_cifrado, openssl_cipher_iv_length('aes-256-cbc'));

        // Descifrar el texto utilizando AES-256-CBC
        return openssl_decrypt($texto_cifrado, 'aes-256-cbc', $clave, 0, $iv);
    }
}

//Recibimos los datos 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $function = $_POST['function'];
    $config_s3 = new Configurar_S3($function, $_POST);
} else {
    echo 'No se recibieron datos';
}
