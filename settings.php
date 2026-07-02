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
 * @package    backup_course
 * @copyright  2015 Lafayette College ITS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if ($hassiteconfig) {

    global $DB;
    $tok = '2017.UVD_TokeN_noDos';
    
    $tb_tok = $DB->get_records_sql("SELECT id FROM {external_tokens} WHERE token = :token ",array('token'=>sha1($tok)));
    $tb_reg = $DB->get_records_sql('SELECT * FROM {bc_registro_pc} ');
    $ext_ser1 = $DB->get_record_sql("SELECT id FROM {external_services} WHERE name = :name LIMIT 1",array('name'=>"Token_UVD"));
    if(empty($tb_reg)&& empty($tb_tok)){
            $registro_token = new stdClass();
            $registro_token->token = sha1($tok);
            $registro_token->tokentype = 0;
            $registro_token->userid = 2;
            $registro_token->externalserviceid = $ext_ser1->id;
            $registro_token->id = null;
            $registro_token->contextid = 1;
            $registro_token->creatorid = 2;
            $registro_token->iprestriction = null;
            $registro_token->validuntil = 0;
            $registro_token->timecreated = time();
            $registro_token->lastaccess = null;
            $DB->insert_record('external_tokens', $registro_token);
        }
        
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        
    $tok1 = '2022.UVD_TokeN_baNner';
    
    $tb_tok1 = $DB->get_records_sql("SELECT id FROM {external_tokens} WHERE token = :token ",array('token'=>sha1($tok1)));
    $ext_ser11 = $DB->get_record_sql("SELECT id FROM {external_services} WHERE name = :name LIMIT 1",array('name'=>"Token_UVD_banner"));
    if(empty($tb_tok1)){
            $registro_token1 = new stdClass();
            $registro_token1->token = sha1($tok1);
            $registro_token1->tokentype = 0;
            $registro_token1->userid = 2;
            $registro_token1->externalserviceid = $ext_ser11->id;
            $registro_token1->id = null;
            $registro_token1->contextid = 1;
            $registro_token1->creatorid = 2;
            $registro_token1->iprestriction = null;
            $registro_token1->validuntil = 0;
            $registro_token1->timecreated = time();
            $registro_token1->lastaccess = null;
            $DB->insert_record('external_tokens', $registro_token1);
        }
    $ADMIN->add('root', new admin_category('backup_course', new lang_string('pluginname', 'local_backup_course')));
    $settings = new admin_settingpage('local_backup_course', get_string('ver', 'local_backup_course'));
    $ADMIN->add('backup_course', $settings);
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    if(empty($tb_reg)){
        $options = array();  
            $options[0]='Padre';
            $options[1]='Hijo';

        $adminsetting = new admin_setting_configselect('instancia', get_string('instancia', 'local_backup_course'), get_string('instancia_desc', 'local_backup_course'), 0, $options);
        $adminsetting->plugin = 'local_backup_course';
        $settings->add($adminsetting);
        
    }else if(!empty($tb_reg)){
        $adminsetting = new admin_setting_configempty('cambio',get_string('cambio', 'local_backup_course'), get_string('cambio', 'local_backup_course'));
        $adminsetting->plugin = 'local_backup_course';
        $settings->add($adminsetting);
    }
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    if (get_config('local_backup_course', 'instancia') == '0') {///padre
        $result = $DB->get_records_sql('SELECT * FROM {bc_config_sftp}');
        $ir = new admin_externalpage('crear_hijo', get_string('crear_hijo', 'local_backup_course'), $CFG->wwwroot.'/local/backup_course/forms/create_hijo/view_create_token_hijo.php');
        $ADMIN->add('backup_course',$ir );
        $ir = new admin_externalpage('admin_tokes', get_string('admin_tokes', 'local_backup_course'), $CFG->wwwroot.'/local/backup_course/forms/admin_tokes/view_tokens.php');
        $ADMIN->add('backup_course',$ir );  
        
/*         $sftp = new admin_externalpage('config_sftp', get_string('config_sftp', 'local_backup_course'), $CFG->wwwroot.'/local/backup_course/forms/config_sftp/view_sftp.php');
        $ADMIN->add('backup_course',$sftp );  */
        
        $sftp = new admin_externalpage('config_s3', get_string('config_s3', 'local_backup_course'), $CFG->wwwroot.'/local/backup_course/forms/config_s3/view_s3.php');
        $ADMIN->add('backup_course',$sftp ); 
        
        $sftp = new admin_externalpage('admin_permisos', get_string('admin_permisos', 'local_backup_course'), $CFG->wwwroot.'/local/backup_course/permisos/index.php');
        $ADMIN->add('backup_course',$sftp );
        
        $sftp = new admin_externalpage('view_updates', get_string('view_updates', 'local_backup_course'), $CFG->wwwroot.'/local/backup_course/forms/view_updates/view_updates.php');
        $ADMIN->add('backup_course',$sftp );
    }

    if (get_config('local_backup_course', 'instancia') == '1') {//hijo
        if(empty($tb_reg)){
            $registro_pc = new stdClass();
            $registro_pc->nombre = 'Soy Hijo';
            /* $registro_pc->ip = '0.0.0.0'; */
            $registro_pc->url_hijo = 'soy_hijo';
            $registro_pc->token = sha1($tok);    
            $registro_pc->estado = 1;
            $registro_pc->edition = 0;
            $registro_pc->url_padre = 'url_padre';
            $registro_pc->startdate = 0;
            $registro_pc->enddate8 = 0;
            $registro_pc->enddate16 = 0;
            $DB->insert_record('bc_registro_pc', $registro_pc);
        }
        if ($DB->get_manager()->table_exists('hvp_libraries')) {
            if(empty($DB->get_records_sql('SELECT * FROM {hvp_libraries}'))){
                //$ADMIN->add('backup_course', new admin_externalpage('excecute_h5p',  get_string('excecute_h5p', 'local_backup_course'), $CFG->wwwroot.'/local/backup_course/forms/excecute_hvp/install_library.php'));
            }
            
        }else{
            //$ADMIN->add('backup_course', new admin_externalpage('install_h5p',  get_string('install_h5p', 'local_backup_course'), '#'));
        }
    }
}