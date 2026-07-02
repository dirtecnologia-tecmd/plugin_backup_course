<?php


include_once '../../../config.php';
class configuration_sftp{
    public static function run() {
        $opt = $_POST['func'];
        $obj = new self();
        switch ($opt) {
            case 'C':
                $res = $obj->saveSftp();
                break;
            case 'Q':
                $res = $obj->querySftp();
                break;
            case 'U':
                $res = $obj->updateSftp();
                break;
            case 'D':
                $res = $obj->deleteSftp();
                break;

            default:
                $res = null;
                break;
        }
        echo json_encode($res);
        
    }
    /*
     * Método para crear la configuración del Sftp
     */
    private function saveSftp() {
        global $DB;
        $obj = new self();
        $registro = (object)$_POST;
        if(!empty($_POST['id_sftp'])){
            if($obj->webservices_sftp($registro)){
                return $obj->updateSftp($registro);
            }
        }else{
            if($obj->querySftp() == 'NO ha configurado el SFTP'){
                if($id = $DB->insert_record('bc_config_sftp', $registro)){
                    //if($obj->webservices_sftp($registro)){
                        return $obj->querySftp();
                    //}
                }else{
                    return null;
                }
            }else{
                return 'No es posible insertar mas datos';
            }
        }
    }
    
    /*
     * Método para Buscar la configuración del Sftp
     */
    private function querySftp() {
        global $DB;
        $result = $DB->get_records_sql('SELECT * FROM {bc_config_sftp}');
        if(!empty($result)){
            return $result;
        }else{
            return 'NO ha configurado el SFTP';
        }
        
    }
    /*
     * Método para actualizar la configuración del Sftp
     */
    private function updateSftp($datos) {
        global $DB;
        $obj = new self();
        $datos->id = $datos->id_sftp;
        if($DB->update_record('bc_config_sftp',$datos)){
            return $obj->querySftp();
        }else{
            return null;
        }
    }
    /*
     * Método para eliminar la configuración del Sftp
     */
    private function deleteSftp() {
        global $DB;
        $obj = new self();
        $obj->querySftp();
    }
    
    /*
     * 
     */
    private function webservices_sftp($registro) {
        global $DB,$CFG;
        $tok = sha1('2017.UVD_TokeN_noDos');
        $params = array(           
            'server'    => $registro->server,
            'port'      => $registro->port,
            'username'  => $registro->username,
            'password'  => $registro->password
        );
        $result = $DB->get_records_sql('SELECT * FROM {bc_registro_pc}');
        $i = 0;
        $res = false;
        if(!empty($result)){
            require_once("$CFG->libdir/filelib.php");
            $cant = count((array)$result);
            
            foreach ($result as $key => $value) {
                $url = $value->url_hijo.'/webservice/rest/server.php?wstoken='.$tok.'&wsfunction=local_remoter_sftp_data&moodlewsrestformat=json';
                $curl = new curl;
                $results = json_decode($curl->post($url,$params));
                if(!empty($results) && property_exists($results,'ack')){
                    if($results->ack == 1){
                        $i += $results->ack;
                    }
                }else{
                    print_r($results);
                }
            }
            if($i == $cant){
               $res = true;
            }
            
        }else{
            $res = true;
        }
        return $res;
            
        

    }
    
    
}
configuration_sftp::run();

// Juanes_2014_30