/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


function objetosUpdate(){} 

/*
 * Creacion de obeto para la actualización de un Nodo
 * @param {type} data
 * @returns {objetosUpdate.prototype.obU01.UPD_U01|Object}
 */
objetosUpdate.prototype.obU01 = function(data){
    /*
     * 
     * @type Object
     * Objeto para Actualizar nodo
     * Todos los datos son parametros 
     * Envío de datos para actualización
     */
    console.log('obU01', data);
    var UPD_U01 = new Object();
        var contenido = document.getElementById('item_list_'+data[0].value).innerHTML;
        var partes = contenido.split(' <br> ');
        var tok = partes[1].split('Token: ');
        tok = tok[1];
        UPD_U01.type = 'UPD';
        UPD_U01.key = 'U01';
        UPD_U01.ws_url = '/class.update.php';
        UPD_U01.data_child = {
            node_id: data[0].value,
            node_domain : data[5].value,
            node_name : data[4].value,
            /* node_ip : data[5].value, */
            node_status: data[15].value,
            edition_acti: data[16].value,
            node_token: tok,
            startdate: new Date(data[8].value,  data[7].value-1,  data[6].value).getTime() / 1000, 
            enddate8:  new Date(data[11].value, data[10].value-1, data[9].value, 23, 59).getTime() / 1000,
            enddate16: new Date(data[14].value, data[13].value-1, data[12].value, 23, 59).getTime() / 1000
        };
        if(new_token === 'yes'){
            UPD_U01.token = inerface.cr_pass(); 
        }
    return UPD_U01;
};
/*
 * Creacion de objeto para la actualización del curso
 * @param {obj} data
 * @returns {objetosUpdate.prototype.obU02.UPD_U02|Object}
 */
objetosUpdate.prototype.obU02 = function(data,id_nodo, fechas){
    /*
     * @type Object
     * Objeto para actualizar el curso
     * Todos los datos son parámetros 
     */
    var UPD_U02 = new Object();
        UPD_U02.type = 'UPD';
        UPD_U02.key = 'U02';
        UPD_U02.ws_url = '/class.update.php';
        UPD_U02.id_nodo = id_nodo;
        UPD_U02.data_padre = data;
        UPD_U02.fechas = fechas;
    return UPD_U02;
};

/*
 * Notificar a los nodos una actualización
 * @param {type} data
 * @param {type} id_nodo
 * @returns {Object|objetosUpdate.prototype.obU03.UPD_U03}
 */
objetosUpdate.prototype.obU03 = function(id_course,url_padre){
    /*
     * @type Object
     * Objeto para actualizar el curso
     * Todos los datos son parámetros 
     */
    var UPD_U03 = new Object();
        UPD_U03.type = 'UPD';
        UPD_U03.key = 'U03';
        UPD_U03.ws_url = '/class.update.php';
        UPD_U03.id_padre = id_course;
        UPD_U03.url_padre = url_padre;
    return UPD_U03;
};
/*
 * @param {int} id_sect
 * @param {int} id_course_module
 * @returns {Object|objetosCrear.prototype.obC04.CRE_C04}
 */
objetosUpdate.prototype.obU04 = function(id_sect,arrSc){
    /*
     * @type Object
     * Objeto para crear la secuencia de las actividades en las secciones en el hijo
     * Todos los datos son parametros 
     * Envío de datos para creación
     */
    var secu = arrSc.toString();

    var CRE_U04 = new Object();
        CRE_U04.type = 'UPD';
        CRE_U04.key = 'U04';
        CRE_U04.ws_url = '/class.update.php';
        CRE_U04.id = id_sect;
        CRE_U04.sequence = secu;
    return CRE_U04;
};

var OUPD = new objetosUpdate();
