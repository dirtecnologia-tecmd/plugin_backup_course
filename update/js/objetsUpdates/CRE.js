/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function objetosCrearUpdate(){}
OCREUp = new objetosCrearUpdate();

/* Objeto para introducior info en las tablas updates_courses y updates_log
 * @param {int} idCurso_p
 * @param {int} idAct
 * @param {string} tpAct
 * @param {obj} obAct
 * @param {int} id_user
 * @returns {objetosCrearUpdate.prototype.OCREUp.CRE_C01|Object}
 */
objetosCrearUpdate.prototype.obC01 = function(idCurso_p,idAct,tpAct,obAct,idUser){
    
    /*
     * @type Object
     * Objeto para crear updates_courses y updates_log
     * Todos los datos son parametros 
     * Envío de datos para creación
     */
    var CRE_C01 = new Object();
        CRE_C01.type = 'CRE';
        CRE_C01.key = 'C01';
        CRE_C01.ws_url = '/class.create.php';
        CRE_C01.id_course_sp = idCurso_p;
        CRE_C01.id_act_sp = idAct;
        CRE_C01.type_act = tpAct;
        CRE_C01.obj_act = obAct;
        CRE_C01.id_user = idUser;
    return CRE_C01;
};

/* Metodo para crear el objeto con los parámetros 
 * @param {int} idCurso_p
 * @param {int} idUp
 * @param {obj} obC01
 * @returns {objetosCrearUpdate.prototype.OCREUp.CRE_C02|Object}
 */
objetosCrearUpdate.prototype.obC02 = function(idCurso_p, idUp, obC01, id_act, type_act, id_obj, array_check){
    /*
     * @type Object
     * Objeto para crear update_nodos
     * Todos los datos son parametros 
     * Envío de datos para creación
     */
    var CRE_C02 = new Object();
        CRE_C02.type = 'CRE';
        CRE_C02.key = 'C02';
        CRE_C02.ws_url = '/class.create.php';
        CRE_C02.id_course_sp = idCurso_p;
        CRE_C02.obj_act = obC01;
        CRE_C02.id_updates_log = idUp;
        CRE_C02.id_updates_courses = id_obj;
        CRE_C02.id_act_sp = id_act;
        CRE_C02.type_act = type_act;
        CRE_C02.array_check = array_check;
        
    return CRE_C02;
};