<?php

/* 
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/EmptyPHP.php to edit this template
@Creado: 16/10/2024 9:03:18 a. m.
@Autora: Daniela Sierra Vergel 
 */

require_once('../../../config.php');
// Obtener los datos de la solicitud POST
$data = json_decode(file_get_contents("php://input"), true);
if(isset($data['method']) && $data['method'] == 'Restaurar'){
    
    if (isset($data['s3url']) && isset($data['courseid']) && isset($data['sectionid'])) {
        require_once('mbz.php');
        $s3url = $data['s3url'];
        $courseid = $data['courseid'];
        $sectionid = $data['sectionid'];

            // Crear una instancia de la clase y restaurar la actividad desde S3
            $backup2 = new ActivityBackup();
            $couRes = $backup2->restoreActivityFromS3($s3url, $courseid,$sectionid);
            if($couRes == $courseid){
                ///////////////aumentar el contador de la actividad
                require_once('crud.php');
                $ret = new ActivityCRUD();
                $upC = $ret->contarActividad($s3url);
                if($upC) echo json_encode(['success' => true, 'courseid'=>$couRes, 'section'=>$sectionid]);// Enviar una respuesta JSON exitosa
                else echo json_encode(['success' => false, 'error'=>'Contar actividad']);
            }else echo json_encode(['success' => false, 'error'=>'Error en la extracción del archivo', 'courseid'=>$couRes]);

    } else  echo json_encode(['success' => false, 'error' => 'Datos insuficientes']);
}elseif(isset($data['method']) && $data['method'] == 'Retirar'){
    if (isset($data['idAct'])){
        require_once('crud.php');
        $ret = new ActivityCRUD();
        $ret->upRetirar($data['idAct']);
        echo json_encode(['success' => true, 'mensaje' => 'Retirada']);
    }else echo json_encode(['success' => false, 'error' => 'Datos insuficientes']);
        
}elseif(isset($data['method']) && $data['method'] == 'Publicar'){
    if (isset($data['idAct'])&& isset($data['selecc'])){
        require_once('crud.php');
        $ret = new ActivityCRUD();
        $ret->desPublicar($data['idAct'],$data['selecc']);
        echo json_encode(['success' => true, 'mensaje' => 'Public']);
    }else echo json_encode(['success' => false, 'error' => 'Datos insuficientes']);
        
}else {
    echo json_encode(['success' => false, 'error' => 'Método incorrecto']);
}

