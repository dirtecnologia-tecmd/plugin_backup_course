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
 * Upgrade scripts for course format "uvd"
 *
 * @package    format_uvd
 * @copyright  2017 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade script for format_uvd
 *
 * @param int $oldversion the version we are upgrading from
 * @return bool result
 */
function xmldb_local_backup_course_upgrade($oldversion) {
    global $DB,$CFG;
    $dbman = $DB->get_manager();
    
    if ($oldversion < 2024052004) {
        // Add a bc_registro_pc
        $table = new xmldb_table('bc_registro_pc'); 
        if($dbman->table_exists($table)){
            // Adding fields to table bc_registro_pc.
            $field = new xmldb_field('startdate', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0); 
            // Conditionally launch add field.
            if (!$dbman->field_exists($table, $field)) $dbman->add_field($table, $field);//añadir el campo startdate
            $field = new xmldb_field('enddate8', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0); 
            // Conditionally launch add field.
            if (!$dbman->field_exists($table, $field)) $dbman->add_field($table, $field);//añadir el campo enddate8
            $field = new xmldb_field('enddate16', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0); 
            // Conditionally launch add field.
            if (!$dbman->field_exists($table, $field)) $dbman->add_field($table, $field);//añadir el campo enddate16
        }
        
        ///////////////////////////////////////////////////////////////////////////////////////////////      
        $table = new xmldb_table('bc_add_sections_activities');// Define table bc_add_sections_activities to be created.
        if (!$dbman->table_exists($table)){// Conditionally launch create table for bc_add_sections_activities.

            // Adding fields to table auth_lti_linked_login.
            $table->add_field('id',        XMLDB_TYPE_INTEGER, '10',  null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('courseid',  XMLDB_TYPE_INTEGER, '10',  null, XMLDB_NOTNULL, null, null);
            $table->add_field('userid',    XMLDB_TYPE_INTEGER, '10',  null, XMLDB_NOTNULL, null, null);
            $table->add_field('section',   XMLDB_TYPE_INTEGER, '10',  null, null, null, null);
            $table->add_field('actividad', XMLDB_TYPE_CHAR,    '500', null, null, null, null);

            // Adding keys to table auth_lti_linked_login.
            $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
            $dbman->create_table($table);//create table
        }        
        
        $table = new xmldb_table('bc_configuracion_s3');// Define table bc_configuracion_s3 to be created.
        if (!$dbman->table_exists($table)){// Conditionally launch create table for bc_configuracion_s3.

            // Adding fields to table auth_lti_linked_login.
            $table->add_field('id',        XMLDB_TYPE_INTEGER, '10',  null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('bucket',    XMLDB_TYPE_CHAR, '255',  null, XMLDB_NOTNULL, null, null);
            $table->add_field('public_key',    XMLDB_TYPE_TEXT, '999',  null, XMLDB_NOTNULL, null, null);
            $table->add_field('private_key',   XMLDB_TYPE_TEXT, '999',  null, null, null, null);
            $table->add_field('userid_registro',   XMLDB_TYPE_INTEGER, '10',  null, null, null, null);
            $table->add_field('fecha_registro', XMLDB_TYPE_CHAR,    '50', null, null, null, null);

            // Adding keys to table auth_lti_linked_login.
            $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
            $dbman->create_table($table);//create table
        }
        
    }
    if ($oldversion < 2024090100) {
        // Añadir campos a la tabla bc_permisos
        $table = new xmldb_table('bc_permisos');
        if (!$dbman->table_exists($table)) {
            $table->add_field('id',        XMLDB_TYPE_INTEGER, '10',  null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('nombre', XMLDB_TYPE_CHAR, '255', null, null, null, null);
            $table->add_field('descripcion', XMLDB_TYPE_CHAR, '255', null, null, null, null);
            
            $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
            $dbman->create_table($table);//create table
        }

        // Añadir campos a la tabla bc_permisos_aplicados
        $table = new xmldb_table('bc_permisos_aplicados');
        if (!$dbman->table_exists($table)) {
            $table->add_field('id',        XMLDB_TYPE_INTEGER, '10',  null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('permiso', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('idinstance', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('idcourse', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('fecha', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('user', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('estado', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('cant_secciones', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('cant_actividades', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('cant_recursos', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

            $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
            $dbman->create_table($table);//create table
        }
        $field = new xmldb_field('estado', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0); 
        if (!$dbman->field_exists($table, $field)) $dbman->add_field($table, $field);//añadir el campo estado
        
        $field = new xmldb_field('reemplazar', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0); 
        if (!$dbman->field_exists($table, $field)) $dbman->add_field($table, $field);//añadir el campo reemplazar
        


    }
    
    
    if ($oldversion < 2024100300) {

        // Define table bc_own_activities to be created.
        $table = new xmldb_table('bc_own_activities');

        // Adding fields to table bc_own_activities.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('idnodo', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('idnumber_teacher', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('email_teacher', XMLDB_TYPE_CHAR, '500', null, XMLDB_NOTNULL, null, null);
        $table->add_field('fullname_teacher', XMLDB_TYPE_CHAR, '800', null, XMLDB_NOTNULL, null, null);
        $table->add_field('idcourse_p', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('idcourse_h', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('idactivity_h', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('idsection_h', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreate', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('url', XMLDB_TYPE_CHAR, '800', null, null, null, null);
        $table->add_field('private', XMLDB_TYPE_CHAR, '45', null, XMLDB_NOTNULL, null, null);
        $table->add_field('type_activity', XMLDB_TYPE_CHAR, '225', null, XMLDB_NOTNULL, null, null);
        $table->add_field('cant_owner', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('cant_others', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table bc_own_activities.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for bc_own_activities.
        if (!$dbman->table_exists($table))  $dbman->create_table($table);

    }
    
    if ($oldversion < 2024101000) {
        // Add a bc_own_activities
        $table = new xmldb_table('bc_own_activities'); 
        if($dbman->table_exists($table)){
            // Adding fields to table bc_own_activities.
            $field = new xmldb_field('name_activity', XMLDB_TYPE_CHAR, '500', null, null, null, null); 
            if (!$dbman->field_exists($table, $field)) $dbman->add_field($table, $field);//añadir el campo name_activity
            // 
            // Adding fields to table bc_own_activities.
            $field = new xmldb_field('intro_activity', XMLDB_TYPE_TEXT, null, null, null, null, null); 
            if (!$dbman->field_exists($table, $field)) $dbman->add_field($table, $field);//añadir el campo intro_activity            
            
        }
    }
    
    if ($oldversion < 2024101100) {
        // Add a bc_own_activities
        $table = new xmldb_table('bc_own_activities'); 
        if($dbman->table_exists($table)){
            //eliminar primero el campo idnumber_teacher
            $field = new xmldb_field('idnumber_teacher', XMLDB_TYPE_CHAR, '500', null, null, null, null);
            if (!$dbman->field_exists($table, $field)){
                $dbman->add_field($table, $field);//añadir el campo idnumber_teacher
            }else{
                $dbman->drop_field($table, $field);//eliminarlo
                $dbman->add_field($table, $field);//añadir el campo idnumber_teacher
            }

        }
    }
    
    if ($oldversion < 2024101900) {
        // Add a bc_own_activities
        $table = new xmldb_table('bc_own_activities'); 
        if($dbman->table_exists($table)){
            //eliminar primero el campo retirar
            $field = new xmldb_field('retirar', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            if (!$dbman->field_exists($table, $field)){
                $dbman->add_field($table, $field);//añadir el campo retirar, 0 en banco, 1:retirar
            }
        }
    }


    return true;
}
