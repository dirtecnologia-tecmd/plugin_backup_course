
function objetosDelate(){} 

/*
 * @param {type} id
 * @returns {objetosdelate.prototype.obD01.DEL_D01|Object}
 */
objetosDelate.prototype.obD01 = function(id){
    /*
     * @type Object
     * Objeto para eliminar nodo
     * Todos los datos son parametros 
     * Envío de datos para Eliminación
     */
    var DEL_D01 = new Object();
        var contenido = document.getElementById('item_list_'+id).innerHTML;
        var partes = contenido.split(' <br> ');
        var url = partes[2].split('URL: ');
        url = url[1];   
        /* console.log('obD01', url);   */  
        var tok = partes[1].split('Token: ');
        tok = tok[1];
        DEL_D01.type = 'DEL';
        DEL_D01.key = 'D01';
        DEL_D01.ws_url = '/class.delete.php';
        DEL_D01.data_child = {
            node_id: id,
            node_domain: url,
            node_token: tok,
        };
    return DEL_D01;
};

/*
 * @param {type} data
 * @returns {objetosdelate.prototype.obD01.DEL_D01|Object}
 */

objetosDelate.prototype.obD02 = function(id_nodo){
    /*
     * @type Object
     * Objeto para eliminar informacion del curso
     * Todos los datos son parametros 
     * Envío de datos para Eliminación
     */
    var DEL_D02 = new Object();
        DEL_D02.type = 'DEL';
        DEL_D02.key = 'D02';
        DEL_D02.ws_url = '/class.delete.php';
        DEL_D02.node_id = id_nodo;
        
    return DEL_D02;
};



var ODEL = new objetosDelate();

