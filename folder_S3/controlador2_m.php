<?php
//include_once '../../../config.php';
require_once 'class.UsoBucketS3.php';


class Controlador2_m
{

    public function run($action, $path, $file = null, $id_course = 0)
    {
        $_obj = new self();
        
        switch ($action) {
            case 'create': // save
                $res = $_obj->save_file($path, $file, $id_course);
                break;
            case 'transfer': // transferir archivo del s3 al moodledata del hijo
                $res = $_obj->transfer_file($path, $file, $id_course);
                break;
            case 'delete': // delete
                $res = $_obj->delete_file_path($path, $file, $id_course);
                break;
        }
        return $res;
    }


    private function save_file($path, $file, $id_course)
    {
        global $CFG;
        $path_all = '';
        $obj = new UsoBucketS3_backup($CFG->dirroot);
        $obj->set_path($path_all);
        $url = $obj->subirArchivo($path, $file, $id_course);
        $obj->get_jsonHeader();
        return $url;
    }
    private function transfer_file($name, $to, $id_course)
    {
        global $CFG;
        $obj = new UsoBucketS3_backup($CFG->dirroot);
        return $obj->transfer($name, $to, $id_course);
    }


    private function delete_file_path($path, $file, $id_course)
    {
        global $CFG;
        $obj = new UsoBucketS3_backup($CFG->dirroot);
        return $obj->delete_element($path, $file, $id_course);
    }
}

//Controlador::run();
