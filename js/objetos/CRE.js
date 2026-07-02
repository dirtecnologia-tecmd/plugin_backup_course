/*
 * 
 * @returns {objetosCrear}
 */

function objetosCrear() { }

/*
 * @param {obj} data
 * @returns {objetosCrear.prototype.obC01.CRE_C01|Object}
 */
objetosCrear.prototype.obC01 = function (data) {

    /*
     * 
     * @type Object
     * Objeto para crear el hijo
     * Todos los datos son parametros 
     * Envío de datos para creación
     */
    var CRE_C01 = new Object();
    CRE_C01.token = inerface.cr_pass();
    CRE_C01.type = 'CRE';
    CRE_C01.key = 'C01';
    CRE_C01.ws_url = '/class.create.php';
    CRE_C01.data_child = {
        node_domain: data[5].value,
        node_name: data[4].value,
        /* node_ip : data[5].value, */
        node_status: data[15].value,
        edition_acti: data[16].value,
        /*startdate: new Date(data[9].value+'.'+data[8].value+'.'+data[7].value).getTime() / 1000, // data[12].value+ data[13].value+ data[14].value+ data[15].value,
        enddate8:  new Date(data[14].value+'.'+data[13].value+'.'+data[12].value).getTime() / 1000,
        enddate16: new Date(data[19].value+'.'+data[18].value+'.'+data[17].value).getTime() / 1000*/
        startdate: new Date(data[8].value, data[7].value - 1, data[6].value).getTime() / 1000,
        enddate8: new Date(data[11].value, data[10].value - 1, data[9].value, 23, 59).getTime() / 1000,
        enddate16: new Date(data[14].value, data[13].value - 1, data[12].value, 23, 59).getTime() / 1000

    };
    return CRE_C01;
};
/*
 * @param {obj} data
 * @param {int} id_nodo
 * @returns {objetosCrear.prototype.obC02.CRE_C02|Object}
 */

objetosCrear.prototype.obC02 = function (data, id_nodo) {
    /*
     * 
     * @type Object
     * Objeto para crear secciones en el hijo
     * Todos los datos son parametros 
     * Envío de datos para creación
     */
    var CRE_C02 = new Object();
    CRE_C02.type = 'CRE';
    CRE_C02.key = 'C02';
    CRE_C02.ws_url = '/class.create.php';
    CRE_C02.id_nodo = id_nodo;
    CRE_C02.section = data;
    return CRE_C02;
};
/*
 * @param {objet} data
 * @param {string} table
 * @param {int} id_nodo
 * @param {objet} course_module
 * @returns {Object|objetosCrear.prototype.obC03.CRE_C03}
 */
objetosCrear.prototype.obC03 = function (data, table, id_nodo, course_module, id_sect, info, bankPre, semana, bankH5P) {
    /*
     * 
     * @type Object
     * Objeto para crear actividades en el hijo
     * Todos los datos son parametros 
     * Envío de datos para creación
     */
    var CRE_C03 = new Object();
    CRE_C03.type = 'CRE';
    CRE_C03.key = 'C03';
    CRE_C03.ws_url = '/class.create.php';
    CRE_C03.table = table;
    CRE_C03.id_nodo = id_nodo;
    CRE_C03.semana = semana;
    CRE_C03.activity = data;
    CRE_C03.modules = course_module;
    CRE_C03.id_section = id_sect;
    CRE_C03.info = info;
    CRE_C03.bankH5P = bankH5P;
    CRE_C03.banckPreguntas = bankPre;
    return CRE_C03;
};


/*
 * @param {int} id_nodo
 * @param {int} id_padre
 * @returns {Object|objetosCrear.prototype.obC05.CRE_C04}
 */
objetosCrear.prototype.obC04 = function (id_nodo, id_padre, obj) {
    /*
     * @type Object
     * Objeto para crear la relacion entre el curso padre y el hijo
     * Todos los datos son parametros 
     * Envío de datos para creación de la relacion entre el curso padre y el hijo
     */

    var CRE_C04 = new Object();
    CRE_C04.type = 'CRE';
    CRE_C04.key = 'C04';
    CRE_C04.ws_url = '/class.create.php';
    CRE_C04.id_nodo = id_nodo;
    CRE_C04.id_padre = id_padre;
    CRE_C04.obj = obj;
    return CRE_C04;
};

/*
 * @param {obj} obj
 * @returns {Object|objetosCrear.prototype.obC05.CRE_C05}
 */
objetosCrear.prototype.obC05 = function (id_padre, id_nodo,
    cat_p, cat_h, obj, rela,
    groups_p, groups_h, groupings_p, groupings_h, groupings_groups, banck) {
    /*
     * @type Object
     * Objeto para crear agupaciones, items de las categorias de las calificaciones
     * Todos los datos son parametros 
     * Envío de datos para creación de la relacion entre el curso padre y el hijo
     */

    var CRE_C05 = new Object();
    CRE_C05.type = 'CRE';
    CRE_C05.key = 'C05';
    CRE_C05.ws_url = '/class.create.php';
    CRE_C05.id_nodo = id_nodo;
    CRE_C05.id_padre = id_padre;

    CRE_C05.catP = cat_p;
    CRE_C05.catH = cat_h;
    CRE_C05.itm = obj;
    CRE_C05.rela = JSON.stringify(rela);

    CRE_C05.groups_p = groups_p;
    CRE_C05.groups_h = groups_h;
    CRE_C05.groupings_p = groupings_p;
    CRE_C05.groupings_h = groupings_h;
    CRE_C05.groupings_groups = groupings_groups;
    CRE_C05.banck = banck;

    return CRE_C05;
};
/*
 * @param {int} id_nodo
 * @param {int} id_padre
 * @param {img} img
 * @returns {Object|objetosCrear.prototype.obC06.CRE_C06}
 */
objetosCrear.prototype.obC06 = function (id_nodo, id_padre, img) {
    /*
     * @type Object
     * Objeto enviar mensaje de error
     * Todos los datos son parametros 
     * Envío de datos para creación del mensaje de error y envia a email
     */

    var CRE_C06 = new Object();
    CRE_C06.type = 'CRE';
    CRE_C06.key = 'C06';
    CRE_C06.ws_url = '/class.create.php';
    CRE_C06.id_nodo = id_nodo;
    CRE_C06.id_padre = id_padre;
    CRE_C06.img = img.toDataURL("image/jpeg");
    return CRE_C06;
};


/*
 * Documentacion del objeto para 
 */



var OCRE = new objetosCrear();