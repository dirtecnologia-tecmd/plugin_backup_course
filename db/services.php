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

 */

$services = array(
     'Token_UVD' => array(
          'functions' => array(
               'local_remoter_register_node',
               'local_backup_list_coursesAct',
               'local_backup_list_courses',
               'local_remoter_relation_node',
               'local_remoter_bloques_node',
               'local_remoter_deleTem_node',
               'local_remoter_banco_preguntas',
               'local_remoter_rubrica',
               //SFTP
               'local_remoter_sftp_data',
              //BANCO DE ACTIVIDADES PROPIAS NUEVAS
               'local_own_activities',
               //Actualización
               'local_update_notificar_nodos',
               'local_update_empezar_nodos',
               'local_update_recibir_notifi_nodos',
               'local_update_empezar_cursos',
               'local_delete_question_quiz',
               'local_mov_act',
               'local_remoter_info_curso',
               'local_remoter_get_credentials_s3',
               //Permisos
               'local_permisos_set'
          ),
          'restrictedusers' => 0,
          'enabled' => 1,
          'timecreated' => time(),
          'shortname' => 'token',
     ),
     'Token_UVD_banner' => array(
          'functions' => array('local_banner_json_student'),
          'restrictedusers' => 0,
          'enabled' => 1,
          'timecreated' => time(),
          'shortname' => 'token_banner',
     )
);

$functions = array(
     'local_backup_list_courses' => array(
          'classname' => 'local_backup_token_external',
          'methodname' => 'find_courses',
          'classpath' => 'local/backup_course/externallib.php',
          'description' => 'Lista de Cursos.',
          'type' => 'read',
          'services' => array('token'),
     ),

     'local_backup_list_coursesAct' => array(
          'classname' => 'local_backup_token_external',
          'methodname' => 'find_coursesAct',
          'classpath' => 'local/backup_course/externallib.php',
          'description' => 'Lista de Actividades.',
          'type' => 'read',
          'services' => array('token'),
     ),

     'local_remoter_register_node' => array(
          'classname' => 'local_backup_token_external',
          'methodname' => 'find_token',
          'classpath' => 'local/backup_course/externallib.php',
          'description' => 'Nodos creados en la tabla de registro.',
          'type' => 'read',
          'services' => array('token'),
     ),

     'local_remoter_relation_node' => array(
          'classname' => 'local_backup_token_external',
          'methodname' => 'find_relation',
          'classpath' => 'local/backup_course/externallib.php',
          'description' => 'Relacion de la importación.',
          'type' => 'read',
          'services' => array('token'),
     ),

     'local_remoter_bloques_node' => array(
          'classname' => 'local_backup_token_external',
          'methodname' => 'find_bloques',
          'classpath' => 'local/backup_course/externallib.php',
          'description' => 'Bloques del curso.',
          'type' => 'read',
          'services' => array('token'),
     ),
     'local_remoter_deleTem_node' => array(
          'classname' => 'local_backup_token_external',
          'methodname' => 'find_deleTem',
          'classpath' => 'local/backup_course/externallib.php',
          'description' => 'Eliminar archivos temporales',
          'type' => 'read',
          'services' => array('token'),
     ),

     'local_remoter_banco_preguntas' => array(
          'classname' => 'local_backup_token_external',
          'methodname' => 'find_BanckPreguntas',
          'classpath' => 'local/backup_course/externallib.php',
          'description' => 'Banco de preguntas del curso',
          'type' => 'read',
          'services' => array('token'),
     ),

     'local_remoter_rubrica' => array(
          'classname' => 'local_backup_token_external',
          'methodname' => 'find_Rubrica',
          'classpath' => 'local/backup_course/externallib.php',
          'description' => 'Rubrica',
          'type' => 'read',
          'services' => array('token'),
     ),

     'local_remoter_info_curso' => array(
          'classname' => 'local_backup_token_external',
          'methodname' => 'info_curso',
          'classpath' => 'local/backup_course/externallib.php',
          'description' => 'Obtener la información del curso para la previsualización',
          'type' => 'read',
          'services' => array('token'),
     ),

     'local_remoter_get_credentials_s3' => array(
          'classname' => 'local_backup_token_external',
          'methodname' => 'get_credentials_s3',
          'classpath' => 'local/backup_course/externallib.php',
          'description' => 'Obtiene las credenciales de s3 del padre',
          'type' => 'read',
          'services' => array('token'),
     ),

     /*
     * Datos del sftp
     */
     'local_remoter_sftp_data' => array(
          'classname' => 'local_backup_token_external',
          'methodname' => 'find_sftp',
          'classpath' => 'local/backup_course/externallib.php',
          'description' => 'Datos para la conección del sftp',
          'type' => 'read',
          'services' => array('token'),
     ),

     /*
     * Services para la actualización
     */

     'local_update_notificar_nodos' => array(
          'classname' => 'local_update_external',
          'methodname' => 'find_notificateUpdate',
          'classpath' => 'local/backup_course/update/externallib.php',
          'description' => 'Notificación a los nodos de un actualización',
          'type' => 'read',
          'services' => array('token'),
     ),

     'local_update_empezar_nodos' => array(
          'classname' => 'local_update_external',
          'methodname' => 'find_empezarUpdate',
          'classpath' => 'local/backup_course/update/externallib.php',
          'description' => 'Iniciar la actualización en los cursos del nodo',
          'type' => 'read',
          'services' => array('token'),
     ),

     'local_update_recibir_notifi_nodos' => array(
          'classname' => 'local_update_external',
          'methodname' => 'find_recibir_notifi',
          'classpath' => 'local/backup_course/update/externallib.php',
          'description' => 'Recibir los cursos actualizados en el nodo ',
          'type' => 'read',
          'services' => array('token'),
     ),

     'local_update_empezar_cursos' => array(
          'classname' => 'local_update_external',
          'methodname' => 'find_empezarUpdateCourse',
          'classpath' => 'local/backup_course/update/externallib.php',
          'description' => 'Actualizar info del curso',
          'type' => 'read',
          'services' => array('token'),
     ),

     'local_delete_question_quiz' => array(
          'classname' => 'local_update_external',
          'methodname' => 'deleteQuestionQuiz',
          'classpath' => 'local/backup_course/update/externallib.php',
          'description' => 'Eliminar pregunta de un quiz',
          'type' => 'read',
          'services' => array('token'),
     ),

     'local_mov_act' => array(
          'classname' => 'local_update_external',
          'methodname' => 'mover_actividades',
          'classpath' => 'local/backup_course/update/externallib.php',
          'description' => 'Actualizar orden de actividades',
          'type' => 'read',
          'services' => array('token'),
     ),

     //////////////////////////////////////BANNER///////////////////////////////////
     'local_banner_json_student' => array(
          'classname' => 'local_banner_token_external',
          'methodname' => 'find_periodo_academico',
          'classpath' => 'local/backup_course/externallib.php',
          'description' => 'Json notas estudiante.',
          'type' => 'read',
          'services' => array('token_banner'),
     ),
    
    //////////////////////////////////////PERMISOS///////////////////////////////////
     'local_permisos_set' => array(
          'classname' => 'local_permisos',
          'methodname' => 'find_permisos_instancia',
          'classpath' => 'local/backup_course/permisos/externallib.php',
          'description' => 'Enviar permisos correspondientes a intancia',
          'type' => 'read',
          'services' => array('token'),
     ),
    
    //////////////////BANCO DE ACTIVIDADES PROPIAS NUEVAS/////////////////////////////////
     'local_own_activities' => array(
          'classname' => 'local_bankActividades',
          'methodname' => 'find_get_actividad',
          'classpath' => 'local/backup_course/bank/externallib.php',
          'description' => 'Enviar actividad creada por el profesor',
          'type' => 'read',
          'services' => array('token'),
     ),

);
