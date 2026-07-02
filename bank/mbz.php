<?php

/* 
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/EmptyPHP.php to edit this template
@Creado: 15/10/2024 10:36:01 p. m.
@Autora: Daniela Sierra Vergel 
*/

require_once('../../../config.php');
require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
require_once($CFG->dirroot . '/backup/controller/backup_controller.class.php');
require_once '../folder_S3/controlador2_m.php';

class ActivityBackup {

    private $modid;
    private $courseid;

    /**
     * Genera un archivo de respaldo (.mbz) para la actividad.
     */
    public function generateBackup() {
        global $CFG, $USER;
        // Crear un controlador de respaldo para una actividad específica.
        $bc = new backup_controller(
            backup::TYPE_1ACTIVITY,   // Tipo de respaldo: Actividad
            $this->modid,             // ID de course_modules (Módulo del curso)
            backup::FORMAT_MOODLE,    // Formato Moodle (.mbz)
            backup::INTERACTIVE_NO,   // No interactivo
            backup::MODE_GENERAL,     // Modo general de respaldo
            $USER->id                 // ID del usuario que realiza el respaldo
        );

        // Configurar el nombre del archivo de respaldo.
        $backup_filename = 'actividad_' . $this->modid . '_' . date('Ymd_His') . '.mbz';
        $bc->get_plan()->get_setting('filename')->set_value($backup_filename);

        // Ejecutar el plan de respaldo.
        $bc->execute_plan();

        // Obtener el archivo generado.
        $results = $bc->get_results();
        $backup_file = $results['backup_destination']; // El archivo en el área del curso.

        if ($backup_file) {
            // Definir una ruta para guardar el archivo.
            $backup_path = $CFG->dataroot . '/temp/backup/' . $backup_filename;
            $backup_file->copy_content_to($backup_path);

            $bc->destroy();// Limpiar el controlador.
            return $backup_path;// Retornar la ruta del archivo generado.
        } else {
            throw new Exception('Error al generar el respaldo de la actividad.');
        }

    }
    
    public function generarS3($modid, $courseid) {
        global $CFG, $USER;
        $this->modid = $modid;  // ID de course_modules
        $this->courseid = $courseid; // ID del curso
        $s3 = new Controlador2_m();
        $nameArchivo = $this->courseid.'_' .$this->modid.'_' . date('Ymd_His'). '.mbz';
        $backup_path = $this->generateBackup();
        $archivoS3 =  $s3->run('create', $backup_path, $nameArchivo, $this->courseid);
        if (file_exists($backup_path)) unlink($backup_path); //eliminar el temporal del moodleData
        return $archivoS3;
    }
    
    public function traerMBZdeS3($backup_filename, $s3url) {
        global $CFG;
        $path = parse_url($s3url, PHP_URL_PATH);// Usamos parse_url para obtener la parte del path
        $parts = explode('/', $path);// Dividimos el path por '/' y obtenemos la parte correspondiente

        // Verificamos que haya al menos tres partes y tomamos la segunda (índice 2)
        if (isset($parts[2])) {
            $idCou = $parts[2]; // Esto debería ser '15'
            $s3 = new Controlador2_m();  
            $path = $CFG->dataroot . '/temp/backup/';  // Directorio temporal en moodledata para el archivo de respaldo  
            return $s3->run('transfer', $backup_filename, $path, $idCou);  // Transferir el .mbz a moodledata

        }else return false;
        
            
    }
    
    /**
     * Restaura un archivo de respaldo (.mbz) descargado desde S3 para una actividad en una sección específica del curso.
     * @param string $s3url URL del archivo .mbz en S3.
     * @param int $courseid ID del curso donde se restaurará la actividad.
     * @param int $sectionid ID de la sección donde se restaurará la actividad.
     * @return string Mensaje de éxito o error.
     */
    public function restoreActivityFromS3($s3url, $courseid, $sectionid) {
        global $CFG, $USER, $DB;

        // Obtener la información de la sección del curso
        $seccion = $DB->get_record('course_sections', ['id' => $sectionid], '*', MUST_EXIST);
        if (!isset($seccion->id)) {
            throw new Exception('La sección especificada no es válida.');
        }

        $backup_filename = basename($s3url);
        $path = $CFG->dataroot . '/temp/backup/';
        $full_backup_path = $path . $backup_filename;

        if ($this->traerMBZdeS3($backup_filename, $s3url)) {
            if (!file_exists($full_backup_path)) {
                throw new Exception('El archivo no existe en la ruta temporal: ' . $full_backup_path);
            }

            $tempdir = restore_controller::get_tempdir_name($courseid, $USER->id);
            $temp_restore_dir = $CFG->tempdir . '/backup/' . $tempdir;

            if (!file_exists($temp_restore_dir)) {
                mkdir($temp_restore_dir, 0777, true);
            }

            copy($full_backup_path, $temp_restore_dir . '/backup.mbz');

            $packer = get_file_packer('application/vnd.moodle.backup');
            if (!$packer->extract_to_pathname($full_backup_path, $temp_restore_dir)) {
                throw new Exception('Error al descomprimir el archivo .mbz.');
            }

            $backup_xml = $temp_restore_dir . '/moodle_backup.xml';
            if (!file_exists($backup_xml)) {
                throw new Exception('El archivo moodle_backup.xml no se encuentra en la estructura descomprimida.');
            }

            // Modificar todas las referencias a <sectionid> en el archivo moodle_backup.xml
            $xml_content = file_get_contents($backup_xml);

            // Cambiar todos los <sectionid> existentes al ID de la sección deseada
            $xml_content = preg_replace(
                '/<sectionid>\d+<\/sectionid>/',
                '<sectionid>' . $seccion->id . '</sectionid>',
                $xml_content
            );

            // Guardar los cambios en el archivo XML
            file_put_contents($backup_xml, $xml_content);

            // Verificar que el cambio se haya realizado correctamente
            $xml_content_verification = file_get_contents($backup_xml);

            // Inicializar el controlador de restauración
            try {
                $rc = new restore_controller(
                    $tempdir,
                    $courseid,
                    backup::INTERACTIVE_NO,
                    backup::MODE_GENERAL,
                    $USER->id,
                    backup::TARGET_EXISTING_ADDING
                );
            } catch (Exception $e) {
                throw new Exception('Error al crear el restore_controller: ' . $e->getMessage());
            }

            // Ejecutar la restauración
            if (!$rc->execute_precheck()) {
                throw new Exception('Error en la preverificación de la restauración.');
            }

            $rc->execute_plan();

            // Obtener el último módulo creado en el curso
            $sql = "SELECT * FROM {course_modules} WHERE course = ? ORDER BY id DESC LIMIT 1";
            $module = $DB->get_record_sql($sql, [$courseid]);

            if (!$module) {
                throw new Exception('No se encontró el último módulo restaurado.');
            }

            // Eliminar el módulo de la secuencia de la sección anterior
            $old_section = $DB->get_record('course_sections', ['id' => $module->section], '*', MUST_EXIST);
            if ($old_section) {
                $sequence = explode(',', $old_section->sequence);
                $sequence = array_diff($sequence, [$module->id]);
                $old_section->sequence = implode(',', $sequence);
                $DB->update_record('course_sections', $old_section);
            }

            // Asignar el módulo a la nueva sección
            $DB->set_field('course_modules', 'section', $seccion->id, ['id' => $module->id]);

            // Actualizar la secuencia de la nueva sección
            $section = $DB->get_record('course_sections', ['id' => $seccion->id], '*', MUST_EXIST);
            $section->sequence = $section->sequence ? $section->sequence . ',' . $module->id : $module->id;
            $DB->update_record('course_sections', $section);

            // Purgar la caché del curso
            rebuild_course_cache($courseid, true);


            // Limpiar y eliminar archivos temporales
            $rc->destroy();
            if (file_exists($full_backup_path)) {
                unlink($full_backup_path);
            }
            if (file_exists($temp_restore_dir)) {
                array_map('unlink', glob("$temp_restore_dir/*.*"));
                rmdir($temp_restore_dir);
            }

            return $courseid;
        } else {
            throw new Exception('Error al descargar el archivo .mbz desde S3.');
        }
    }


}



