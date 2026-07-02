<?php
require_once('../../../config.php');
require_once("$CFG->libdir/filelib.php");
require_once("$CFG->libdir/pagelib.php");
require_once("$CFG->libdir/blocklib.php");
require_once 'CRE/excepcions_errors.php';

class admin_tokens extends excepcions_errors{
    
    public static function run(){
        $obj = new self();
        $idfunc = $_POST['key']; 
        switch($idfunc){
            case 'Q01': 
                $resp = $obj->lista_tokens();
                break;
            case 'D01': 
                $resp = $obj->delete_token();
                break;
            case 'Q02': 
                $resp = $obj->lista_tokens_activos();
                break;
            case 'U01': 
                $resp = $obj->desactivar_token();
                break;
        }
        echo json_encode($resp);
    }
    /**
     * Listar tokens de la lista negra
     * @returns {array}
     */
    private function lista_tokens() {
        global $DB;
        $tokens = $DB->get_records('bc_balck_list',array(), $sort='', $fields='*');
        return $tokens;
    }
    /*
     * Borrar tokens de la lista negra
     * @param {int} id
     * @param {string} url
     * @param {string} tok
     * @returns {Generator}
     */
    private function delete_token() {
        global $DB;
        $registro = (object)$_POST;
        $res = array();
        $res['bc_balck_list_p'] = $DB->delete_records('bc_balck_list',array('id'=>$registro->id));
        $res['bc_white_list_p'] = $DB->delete_records('bc_white_list',array('url'=>$registro->url));
        $res['bc_registro_pc_p'] = $DB->delete_records('bc_registro_pc',array('url_hijo'=>$registro->url));
        $tok = sha1('2017.UVD_TokeN_noDos');
        $url_actual = explode('/local/', $_SERVER['HTTP_REFERER']);
        $url = $registro->url.'/webservice/rest/server.php?wstoken='.$tok.'&wsfunction=local_remoter_register_node&moodlewsrestformat=json';
        $params = array('function'=>$registro->key,
                        'url' =>$url_actual[0],
                        'nombre' => '',
                        'token' => $registro->token,
                        /* 'ip' => $_SERVER['SERVER_ADDR'], */
                        'url_hijo' => $registro->url,
                        'edition_acti'=>'',
                        'estado' => '',
                        'server' => '',
                        'port' => '',
                        'username' => '',
                        'password' => '',
                        'startdate' => '',
                        'enddate8' => '',
                        'enddate16' => ''
                );

        $curl = new curl;
        $res['bc_registro_pc_h'] = json_decode($curl->post($url,$params));
        return $res;
    }
    
    /**
     * Listar tokens activos
     * @returns {array}
     */
    private function lista_tokens_activos() {
        global $DB;
        $tokens = $DB->get_records('bc_registro_pc',array('estado'=>1), $sort='', $fields='*');
        return $tokens;
    }
    
    
    /*
     * Desactivar tokens
     * @param {int} id
     * @param {string} url
     * @param {string} tok
     * @returns {Generator}
     */
    private function desactivar_token() {
        global $DB;
        $registro = (object)$_POST;
        $res = array();
        $tb_reg = $DB->get_record('bc_registro_pc',array('id'=>$registro->id));
        //$arr_sftp = $DB->get_record('bc_config_sftp',array());
        $tb_reg->estado = 0; // desactivar estado
        $res['bc_registro_pc_p'] = $DB->update_record('bc_registro_pc',$tb_reg);
        $tok = sha1('2017.UVD_TokeN_noDos');
        $url_actual = explode('/local/', $_SERVER['HTTP_REFERER']);
        $url = $tb_reg->url_hijo.'/webservice/rest/server.php?wstoken='.$tok.'&wsfunction=local_remoter_register_node&moodlewsrestformat=json';
        $params = array('function'=>$registro->key,
                        'url' =>$url_actual[0],
                        'nombre' => $tb_reg->nombre,
                        'token' => $tb_reg->token,
                        /* 'ip' => $_SERVER['SERVER_ADDR'], */
                        'url_hijo' =>$tb_reg->url_hijo,
                        'startdate'=>$tb_reg->startdate,
                        'enddate8'=>$tb_reg->enddate8,
                        'enddate16'=>$tb_reg->enddate16,
                        'estado' => $tb_reg->estado,
                        'edition_acti' => $tb_reg->edition,
                        /*'server'    => $arr_sftp->server,
                        'port'      => $arr_sftp->port,
                        'username'  => $arr_sftp->username,
                        'password'  => $arr_sftp->password*/
                );

        $curl = new curl;
        $res['bc_registro_pc_h'] = json_decode($curl->post($url,$params));
        return $res;
    }
}
admin_tokens::run();
