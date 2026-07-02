/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


function objetosUpdateUpdate(){} 

/*
 * Creacion de obeto para la actualización de un Nodo
 * @param {type} data
 * @returns {objetosUpdate.prototype.obU01.UPD_U01|Object}
 */
objetosUpdateUpdate.prototype.obU01 = function(id_course,obAct,id_user,id_updates_log,id_updates_courses, array_check){
    /*
     * 
     * @type Object
     * Objeto para Actualizar nodo
     * Todos los datos son parametros 
     * Envío de datos para actualización
     */
    var UPD_U01 = new Object();
        UPD_U01.type = 'UPD';
        UPD_U01.key = 'U01';
        UPD_U01.ws_url = '/class.update.php';
        UPD_U01.user = id_user;
        UPD_U01.obCourse = obAct;
        UPD_U01.idCourse_p = id_course;
        UPD_U01.id_updates_log = id_updates_log;
        UPD_U01.id_updates_courses = id_updates_courses;
        UPD_U01.array_check = array_check;
    
    return UPD_U01;
};

var OUPDUpd = new objetosUpdateUpdate();
    