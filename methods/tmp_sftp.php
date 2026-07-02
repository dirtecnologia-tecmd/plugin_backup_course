<?php

class sftp_UVD_connection{
    
    private $_server;
    private $_port;
    private $_username;
    private $_password;
    private $_path_data;
    private $_name_data;
    private $_conexiones = array();
    
    public function __construct($function, $moodle_data, $name_archive, $rel = null, $id_course = null, $dataroot = null, $id_como = null, $id_acti = null){
        $this->_path_data = $moodle_data;
        $this->_name_data = $name_archive;
        $this->data_conexiones();
        
        return $this->init($function, $rel, $id_course, $dataroot, $id_como, $id_acti);    
    }
    private function init($function, $rel = null, $id_course = null, $dataroot = null, $id_como = null, $id_acti = null) {
        $params = $this->_getConexion();
        $conn = $this->_conectar($params,$dataroot);
        switch ($function){
            case 'create':
                $resp = $this->putFiles($conn, $rel, $id_course, $dataroot);
                break;
            case 'read':
                return  $this->getFiles($conn, $rel, $id_course, $dataroot, $id_como, $id_acti);
                //break;
        }
    } 
    
    /**
     * genera la conexion con la base de datos
     */
    private function _conectar($params,$dataroot){
        try {
            if(is_string($params->_server)){
                if($connection = ssh2_connect($params->_server, $params->_port)){
                    $pub_key = $dataroot.'/local/backup_course/id_rsa.pub';
                    $pri_key = $dataroot.'/local/backup_course/id_rsa';
                    if (ssh2_auth_pubkey_file($connection, $params->_username,$pub_key,$pri_key,$params->_password)) {
                        $sftp = ssh2_sftp($connection);
                        return $connection;      
                    } else {
                       //die('Public Key Authentication Failed');
                    }
                }else {
                    //throw new Exception("No se pudo conectar ".$params->_server);
                }
            }
                
        } catch (Exception $e) {
            //echo 'No se pudo conectar',  $e->getMessage();
        }
        
            
    }
    /*
     * extra
     */
    private function data_conexiones() {
        global $DB;
        $arra = array();
        $sftp = $DB->get_records_sql('SELECT * FROM {bc_config_sftp}');
        if(!empty($sftp)){
            
            foreach ($sftp as $key => $value) {
                $arra['server'] = $value->server;
                $arra['port'] = $value->port;
                $arra['username'] = $value->username;
                $arra['password'] = $value->password;
            }
        }
        $this->_conexiones = $arra;
        //return $arra;
            
    }  
    /**
     * 
     * @param type $nomConexion
     * @return \self
     */
    private function _getConexion(){
        global $DB;
        $sftp = $DB->get_record_sql('SELECT * FROM {bc_config_sftp} LIMIT 1');
        $this->_server = $sftp->server;
        $this->_port = $sftp->port;
        $this->_username = $sftp->username;
        $this->_password = $sftp->password;
        return $this;
    }   
    /*
     * 
     */
    private function putFiles($conn, $rel = null, $id_course = null, $dataroot = null){
        if(is_string($this->_path_data)){
            try {
                if(ssh2_scp_send($conn, $this->_path_data, $this->_name_data, 0777)){
                    ssh2_exec($conn, 'exit');
                    unset($conn);
                    $resp = 'Archivo transferido';
                }else{
                    
                }
            } catch (Exception $e) {
                $resp = 'Error transfiriendo el archivo';
            }
                
            return $resp;
        }else if(is_object($this->_path_data) || is_array($this->_path_data)){
            ssh2_exec($conn, 'exit');
            unset($conn);
        }
            
    }
    /*
     * 
     */  
    private function getFiles($conn, $rel = null, $id_course = null, $dataroot = null, $id_como = null, $id_acti = null) {
        try {
            $sftp = ssh2_sftp($conn);
            if(is_string($this->_path_data)){
                if(ssh2_scp_recv($conn, $this->_name_data, $this->_path_data)){
                    ssh2_sftp_unlink($sftp, $this->_name_data);
                    ssh2_exec($conn, 'exit');
                    unset($conn);
                    return true;
                }

            }else if(is_object($this->_path_data) || is_array($this->_path_data)){
                ssh2_exec($conn, 'exit');
                unset($conn);
                return true;
            }
        }catch (Exception $e) {
            //$resp = 'Error transfiriendo el archivo';
        }
            

    }

}
