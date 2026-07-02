<?php
require_once('../../../../config.php');
require_once("$CFG->libdir/filelib.php");

class crud_permisos {
    public function __construct() {
        $this->ejecutar();
    }

    public function ejecutar() { 
        $datosJSON = file_get_contents("php://input");
        $_POST = json_decode($datosJSON);
        $idfunc = $_POST->key; 
        switch ($idfunc) {
            case 'Q01':
                $resp = $this->search_category($_POST->parent);
                break;
            case 'Q02':
                $resp = $this->search_instancias();
                break;
            case 'Q03':
                $resp = $this->search_course($_POST->search);
                break;
            case 'C01':
                $resp = $this->save_permisos($_POST);
                break;
            case 'Q04':
                $resp = $this->listar_guardados();
                break;
            case 'D01':
                $resp = $this->delete_permiso($_POST->id);
                break;
            case 'U01':
                $resp = $this->update_permiso($_POST);
                break;
            default:
                $resp = array('error' => 'Función no válida');
                break;
        }
        echo json_encode($resp);
    }

    private function search_category($parent) {
        global $DB;
        $res = new stdClass();
        $categ =  $DB->get_records('course_categories', array('parent' => $parent));
        foreach ($categ as $key => $value) {
            $res->$key = $value;
            if($value->coursecount > 0){
                $res->$key->cursos = $DB->get_records('course', array('category' => $value->id));
            }
        }
        return $res;
        
    }
    
    private function search_instancias() {
        global $DB;
        return $DB->get_records_sql("SELECT * FROM {bc_registro_pc} WHERE nombre NOT LIKE '%Soy%'");
    }
    
    private function search_course($sea) {
        global $DB;
        return $DB->get_records_sql("SELECT * FROM {course} WHERE fullname LIKE '%".$sea."%' OR shortname LIKE '%".$sea."%'");        
    }
    
    private function save_permisos($datos) {
        global $DB, $USER;
        $insertar = array();
        $instances = $datos->instances;

        foreach ($instances as $key => $value) {
            if($value){
                $courses = explode(',', $datos->courses);
                if(!empty($courses)){
                    foreach ($courses as $cur) {
                        $inse = new stdClass();
                        $inse->permiso = $datos->permiso;
                        $inse->idinstance = $value;
                        $inse->fecha = time();
                        $inse->user = $USER->id;
                        $inse->cant_actividades = $datos->actividades;
                        $inse->cant_secciones = $datos->secciones;
                        $inse->cant_recursos = $datos->recursos;
                        $inse->idcourse = $cur;
                        $inse->reemplazar = $datos->reemplazar;
                        $inse->estado = 1; //estado activo del permiso, 0 eliminado
                        $insertar[] = $inse;
                    }
                }else{
                    $inse = new stdClass();
                    $inse->permiso = $datos->permiso;
                    $inse->idinstance = $value;
                    $inse->fecha = time();
                    $inse->user = $USER->id;
                    $inse->cant_actividades = $datos->actividades;
                    $inse->cant_secciones = $datos->secciones;
                    $inse->cant_recursos = $datos->recursos;
                    $inse->idcourse = 0; //aplicar a todos los cursos de la instancia
                    $inse->reemplazar = $datos->reemplazar;
                    $inse->estado = 1;
                    $insertar[] = $inse;
                }
                    
            }
                
        }

        $DB->insert_records('bc_permisos_aplicados', $insertar); // insertar los registros
        return $this->enviar_permisos();
    }
    
    
    private function listar_guardados(){
        global $DB;
        return $DB->get_records_sql('SELECT pa.*, c.fullname, c.shortname, r.nombre, r.url_hijo
                                    FROM {bc_permisos_aplicados} pa
                                    LEFT JOIN {course} c ON c.id = pa.idcourse
                                    INNER JOIN {bc_registro_pc} r ON r.id = pa.idinstance
                                    WHERE pa.estado = ?',array('estado'=>1)); //Todos los permisos activos osea en estado 1
    }
    
    private function delete_permiso($idperm){
        global $DB; //no borrar sino dejar apagado
        $DB->update_record('bc_permisos_aplicados',array('id'=>$idperm, 'estado'=>0));
        return $this->enviar_permisos();
    }
    private function update_permiso($perms){
        global $DB;
        $perms->fecha = time();
        $DB->update_record('bc_permisos_aplicados',$perms);
        return $this->enviar_permisos();
    }
    
    private function enviar_permisos(){
        global $DB;
        $instancias = $DB->get_records('bc_registro_pc', array('estado' => 1)); // todas las intancias activas
        $tok = sha1('2017.UVD_TokeN_noDos');
        
        if(!empty($instancias)){
            foreach ($instancias as $key => $instncia) {
                $permisos = $DB->get_records('bc_permisos_aplicados',array('idinstance'=>$instncia->id));
                if(!empty($permisos)){
                    $url = $instncia->url_hijo . '/webservice/rest/server.php?wstoken='.$tok.'&wsfunction=local_permisos_set&moodlewsrestformat=json';
                    $params = array('obj_perm' => json_encode($permisos));
                    $curl = new curl;
                    $results = json_decode($curl->post($url, $params));
                    if (!empty($results)) {
                        //echo '<pre>$results:'; var_dump($results);echo '</pre>';
                        //if ($results && property_exists($results, 'response')) { }
                    }else{ echo 'respuesta vacía: '.$instncia->url_hijo;}
                }
                    
            }
        }
    }


}

new crud_permisos();
