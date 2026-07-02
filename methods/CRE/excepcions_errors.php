<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class excepcions_errors{
    protected function save_error($errors) {
        global $DB;
        $DB->insert_record('bc_excepcions_errors', $errors);
    }
    
    /* 
     * Método para eliminar los datos creados recientemente debido a un error
     * @param string $tabla 
     * @param int $id
     */ 
    private function delete_for_errors($tabla,$id) {  
        global $DB;
        $DB->delete_records($tabla,array('id'=>$id));
    } 
}