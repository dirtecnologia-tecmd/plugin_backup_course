<?php
//require_once('/../../../config.php');
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of query_bl_wl
 *
 * @author daniela.sierra
 */
class query_bl_wl {
    /*
     * Run the class -> run
     * Funcion principal, verifica los permisos que tienen las IP y los tokens
     * @params -> array(url_padre,token,ip,estado)
     * return {int};
     */
    public static function run($params){
        $objQRY = new self();
        $resp = new stdClass();
        $QRY_BL = $objQRY->QRY_BL($params['url']);
        if($QRY_BL == 1){
           $QRY_WL = $objQRY->QRY_WL($params['url']);
           if($QRY_WL == 1){
               $objQRY->CRE_WL($params);
               $resp->ack = 1;
               $resp->response = 'Puede crear';
           }else{
               $resp->ack = 0;
               $resp->response = 'Ya existe en la blanca';
           }
 
        }else{
            $resp->ack = 0;
            $resp->response = 'estas en la negra';
        }
        return $resp;
    } 
    /*
     * Search in balck_list -> QRY_BL
     * Busca en la lista negra 
     * @params -> string
     * Retorna el permiso
     * return {int};
     */
    private function QRY_BL($url){
        global $DB;
        $bl = $DB->get_records('bc_balck_list',array('url' =>$url));
        $res = (!empty($bl)) ? 0 : 1;
        return $res;
    }
    /*
     * Search in white_list -> QRY_WL
     * Busca en la lista blanca
     * @params -> string
     * Retorna el permiso
     * return {int};
     */
    private function QRY_WL($url){
        global $DB;
        $wl = $DB->get_records('bc_white_list',array('url' =>$url));
        $res = (!empty($wl)) ? 0: 1;
        return $res;      
    }
    /*
     * Search in white_list -> QRY_URLWL
     * Busca en la lista blanca
     * @params -> string
     * Retorna el id de la búsqueda
     * return {int};
     */
    public function QRY_URLWL($url){
        global $DB;
        $wl = $DB->get_record('bc_white_list',array('url' =>$url));
        //$id = each($wl);
        $id = $wl->id;
        return $id;      
    }
    /*
     * Search in bc_registro_pc -> QRY_RBC
     * Busca en la tabla de registro
     * @params -> array(url_padre,token,estado)
     * Retorna el id de la búsqueda
     * return {int};
     */
    public function QRY_RBC($url,$tok){
        global $DB;
        $rbc = $DB->get_record('bc_registro_pc',array('url_padre' =>$url,'token'=>$tok));
        //$id = each($rbc);
        if(!empty($rbc)){
            $id = $rbc->id;
        }else{
            $id = 0;
        }
        
        return $id;    
    }
    /*
     * Search in bc_registro_pc -> QRY_RBC_tok
     * Verifia que la ip se encurntre registrada
     * @params -> array(url_padre,token,estado)
     * Retorna el id si la encuentra
     * return {int};
     */
    public function QRY_RBC_tok($url, $tok/* , $ip */){
        global $DB;
        $rbc = $DB->get_record('bc_registro_pc',array('url_hijo' =>$url,'token'=>$tok/* , 'ip'=>$ip */));
        if(!empty($rbc)){
            //$id = each($rbc);
            $id = $rbc->id;
            return $id;
        }else{
            return null;
        }
            
    }
    /*
     * Create in balck_list-> CRE_BL
     * Inserta en la lista Negra
     * @params -> array(url_padre,token,ip,estado)
     * return {};
     */
    public function CRE_BL($params){
        global $DB;
        $registro = new stdClass();
        $registro->token = $params['token'];
        $registro->url = $params['url']; 
        /* $registro->ip = $params['ip']; */
        $registro->estado = $params['estado'];
        return $DB->insert_record('bc_balck_list', $registro);
    }
    /*
     * Create in white_list -> CRE_WL
     * Inserta en la lista blanca
     * @params -> array(url_padre,token,estado)
     * return {};
     */
    private function CRE_WL($params){
        global $DB;
        $registro = new stdClass();
        $registro->token = sha1($params['token']);
        $registro->url = $params['url']; 
        $registro->estado = $params['estado'];
        $DB->insert_record('bc_white_list', $registro);     
    }
    /*
     * Update in white_list -> UPT_WL
     * Actualiza la lista blanca
     * @params -> array(url_padre,token,estado)
     * return {};
     */
    public function UPD_WL($params){
        global $DB;
        $DB->update_record('bc_white_list',$params);     
    }
    /*
     * Delete in white_list -> DEL_WL
     * Elimina el item en la lista blanca
     * @params -> int(id)
     * return {};
     */
    public function DEL_WL($id){
        global $DB;
        $DB->delete_records('bc_white_list',array('id'=>$id));
    }
    
    
}
