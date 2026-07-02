<?php

/* 
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/EmptyPHP.php to edit this template
@Creado: 21/10/2024 11:13:06 p. m.
@Autora: Daniela Sierra Vergel 
 */

require_once('../../../config.php');

class ActivityCRUD {


    /**
     * Actualizar actividad para retirarla
     */
    public function upRetirar($idAct) {
        global $DB;
        $act = array('id'=>$idAct, 'retirar'=>1);
        $DB->update_record('bc_own_activities', $act); //actualizar
        return true;
        
    }
    
    /**
     * Actualizar actividad para desPublicar
     */
    public function desPublicar($idAct, $private) {
        global $DB;
        $act = array('id'=>$idAct, 'private'=>$private);
        $DB->update_record('bc_own_activities', $act); //actualizar
        return true;
        
    }
    
    /**
     * Actualizar actividad para conteo
     */
    public function contarActividad($s3url) {
        global $DB, $USER;
        $upAct = new stdClass();
        $s3urlLike = '%' . $s3url . '%';// Concatenar '%' al inicio y al final del URL para la búsqueda
        // Realizar la consulta
        $act = $DB->get_record_sql('SELECT * FROM {bc_own_activities} WHERE url LIKE ?', array($s3urlLike));
        if($act && !empty($act)){
            $upAct->id = $act->id; 
            if($USER->email == $act->email_teacher)$upAct->cant_owner =  (int)$act->cant_owner +1; //sumar si es el mismo autor
            else $upAct->cant_others =  (int)$act->cant_others +1; //sumar cuando es diferente profe
            $DB->update_record('bc_own_activities', $upAct); //actualizar
            return true;
        }else return false;
        
    }
}