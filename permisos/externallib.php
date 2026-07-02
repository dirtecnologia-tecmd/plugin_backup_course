<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 * @package    local_backup_token_external
 * @copyright  2015 Lafayette College ITS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once '../../config.php';
require_once($CFG->dirroot . '/lib/externallib.php');
require_once($CFG->dirroot . '/lib/completionlib.php');

class local_permisos extends external_api
{
    /* 
     * Find_token_parameters
     * Estructura de los parametros que recibe el WS 
     * Peticion del padre     
     */

    public static function find_permisos_instancia_parameters() {
        return new external_function_parameters(
            array(
                'obj_perm' => new external_value(PARAM_RAW, 'obj_perm'),
            )
        );
    }
    /*
     * Funcion para crear permisos
     * @params {objet} $obj_perm
     * Retorna la verificacion de la creación
     * return {objet}
     */
    public static function find_permisos_instancia($obj_perm) {
        global $DB, $USER, $CFG;
        $DB->delete_records('bc_permisos_aplicados');
        $ob_insert = array();
        $obj_perm= json_decode($obj_perm);
        //$archivoBoo = fopen('pruebaBook.txt', 'w');
        //fwrite($archivoBoo, print_r($obj_perm, true));
        foreach ($obj_perm as $key => $ins) {
            if ($ins->estado==1) array_push($ob_insert, $ins);
        }
        return array('response' => $DB->insert_records('bc_permisos_aplicados', $ob_insert));
    }
    /*
     * Estructura de la respuesta de WS
     */
    public static function find_permisos_instancia_returns() {
        return  new external_single_structure(
                array(
                    'response'  => new external_value(PARAM_RAW, 'respuesta del server'),
                )
        );
    }

}
