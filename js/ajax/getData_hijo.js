var CDH = null; //definicion de new CallData_hijos(); 
var new_token = null; // creación de un nuevo token para al actualizacion de estos
var arrReg = [];// Array de resultados de la consulta de la tabla de registros

$(document).ready(function(){
    /*
     * CallData_hijos -> Method para CRUD de tokens
     * @returns {getData_hijoL#7.CallData_hijos}
     * msjBC instancia en el archivo mensajes.js
     */
    function CallData_hijos(){} 
    
    /*
     * List de Tokens creados -> getlistaTokens
     * Lista los tokens creados
     * @returns {undefined}
     */
    CallData_hijos.prototype.getlistaTokens = function(){
        $('.loader').css("display", "block");
        $('#list_tokens_creados').empty();
        $('#id_reg').val('');
        arrReg = [];
        var info = OQRY.obQ01();
        $.ajax({
            url : '../../methods/'+info.type+info.ws_url,
            data : info,
            type : 'POST',
            success : function(json) {
                var fjson = JSON.parse(json);
                if(json.length > 2){
                    arrReg = fjson;
                    $.each(fjson, function(k,v){
                        var Act = v['estado'] === '1' ? 'Activo' : 'Inactivo' ;
                        var edit = v['edition'] === '1' ? 'Si' : 'No' ;
                    $('#list_tokens_creados').append('<div class="tok_no_selec" id="item_list_'+v["id"]+'"> '+
                            '<div class="edit-item-hijo" id="token_'+v["id"]+'" onclick="funcList.selec_token('+"'"+v["id"]+"'"+')"></div>'+
                            '<div class="delete-item-hijo" id="token_'+v["id"]+'" onclick="funcList.eliminar_token('+v["id"]+')"></div>'+
                            'Nombre: '+v['nombre']+' <br> '+
                            'Token: '+v['token']+' <br> '+
                            'URL: '+v['url_hijo'] +' <br> '+
                            'Fecha inicio del curso: '+v['startdate'] +' <br> '+
                            'Fecha fin de 8 semanas: '+v['enddate8'] +' <br> '+
                            'Fecha fin de 16 semanas: '+v['enddate16'] +' <br> '+
                            'Estado: '+Act +' <br> '+
                            'Edición de actividades: '+edit +' <br> '+
                        '</div>');
                    });                   
                }else{
                    $('#list_tokens_creados').append('<span class="lista_no_tokens" >NO hay registros de Moodle hijos creados</span>');
                    
                }
                $('.loader').hide();
            }
        });
    };
   /*
    * Guardar Tokens desde el Padre -> getGuardarNodo
    * Crear y/o unir nodos al padre
    * @returns {undefined}
    */
    CallData_hijos.prototype.getGuardarNodo = function(){
        var form = $('#form_create_hijo');
        var data = form.serializeArray();
        var info = OCRE.obC01(data);
        $.ajax({
            url : '../../methods/'+info.type+info.ws_url,
            data : info ,
            type : 'POST',
            success : function(json) { 
                if(json !== 'null'){
                    var fjson = JSON.parse(json);
                    $.each(fjson, function(k,v){
                        if(v['ack'] === 1){
                            /* $( "#id_ip_hijo" ).val(''); */
                            $( "#id_url_hijo" ).val('');
                            $( "#id_nombre_hijo" ).val('');
                            msjBC.ok('Datos guardados','Acaba de crear un Nodo');
                            CDH.getlistaTokens();
                        }else{
                            msjBC.error('ERROR',v['response']);
                        }
                    });
                }else{
                    msjBC.error('ERROR','No es posible unirse a este nodo');
                }
            }
        }); 
    };
    /*
     * Actualizar Token -> updateNewToken
     * Actualizar un nodo en el padre y el hijo
     * @returns {undefined}
     */
    CallData_hijos.prototype.updateToken = function () {
        var form = $('#form_create_hijo');
        var data = form.serializeArray();
        var info = OUPD.obU01(data);
        $.ajax({
            url : '../../methods/'+info.type+info.ws_url,
            data : info ,
            type : 'POST',
            success : function(json) {   
                 if(json !== 'null'){
                    var fjson = JSON.parse(json);
                    $.each(fjson, function(k,v){
                        if(v['ack'] === 1){
                            /* $( "#id_ip_hijo" ).val(''); */
                            $( "#id_url_hijo" ).val('');
                            $( "#id_nombre_hijo" ).val('');
                            msjBC.ok('Datos guardados',v['response']);
                            CDH.getlistaTokens();
                        }else{
                            msjBC.error('ERROR',v['response']);
                        }
                    });
                }else{
                    msjBC.error('ERROR','No es posible actualizar este nodo');
                }
            }
        }); 
    };
    
    /*
     * Eliminar Token seleccionado -> deletToken
     * Eliminar un nodo
     * @returns {undefined}
     */
    
    CallData_hijos.prototype.deletToken = function(id){
        var info = ODEL.obD01(id);
        $.ajax({
            url : '../../methods/'+info.type+info.ws_url,
            data : info,
            type : 'POST',
            success : function(json) {
                if(json !== 'null'){
                    var fjson = JSON.parse(json);
                    $.each(fjson, function(k,v){
                        if(v['ack'] === 1){
                            $('#list_tokens_creados').empty();
                            msjBC.ok('Datos Eliminados',v['response']);
                            CDH.getlistaTokens();
                        } else{
                            msjBC.error('ERROR',v['response']);
                        }
                    });
                }
            }
        }); 
        /* $( "#id_ip_hijo" ).val(''); */
        $( "#id_url_hijo" ).val('');
        $( "#id_nombre_hijo" ).val('');
    };
    /*
     * Reconocer si el parametro recibido en una URL
     * @param {type} str
     * @returns {Boolean}
     */
    CallData_hijos.prototype.is_url = function (str) {
        var regexp = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;
        return regexp.test(str);
    };
    /*
     * Reconocer si el parametro recibido en una IP
     * @returns {Number}
     */
/*     CallData_hijos.prototype.is_ip = function () {
        var ipRE = new RegExp(/^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/ );
        var ip = $('#id_ip_hijo').val();
        var valid = ipRE.test(ip) ? 1 : 0 ;
        return valid;
    }; */
    /*
     * Reconocer si el parametro recibido está repetido
     * @returns {Object|Number|vr}
     */
    CallData_hijos.prototype.url_repetida = function () {
        var uf = $('#id_url_hijo').val();
        vr = 0;
        $.each(arrReg, function(k,v){
            if(uf === v['url_hijo']){
                vr = vr+1;
            }   
        });
        return vr; 
        
    }; 
    
    CDH = new CallData_hijos();   
    CDH.getlistaTokens();

    $( "#create_token" ).click(function() {
        var startdate = new Date($('#id_startdate_year').val(), $('#id_startdate_month').val()-1, $('#id_startdate_day').val()).getTime() / 1000; 
        var enddate8 =  new Date($('#id_enddate8_year').val() , $('#id_enddate8_month').val()-1 , $('#id_enddate8_day').val(), 23, 59).getTime() / 1000;
        var enddate16 = new Date($('#id_enddate16_year').val(), $('#id_enddate16_month').val()-1, $('#id_enddate16_day').val(), 23, 59).getTime() / 1000;
        var ifu = parseInt($('#id_reg').val());
        var a = CDH.url_repetida();
        if(!CDH.is_url($( "#id_url_hijo" ).val())){
            msjBC.error('ERROR','URL no válida'); 
        }else if( $('#id_nombre_hijo').val().length === 0){
           msjBC.error('ERROR','Debe llenar todos los Campos');  
        }/* else if(CDH.is_ip() === 0){
            msjBC.error('ERROR','IP no válida');
        } */else if(startdate >= enddate8){
            msjBC.error('ERROR','La fecha de inicio debe se menor a la fecha fin de semana 8');
        }else if(enddate8 >= enddate16){
            msjBC.error('ERROR','La fecha fin de 8 semanas debe se menor a la de semana 16');
        }else if($('#id_reg').val() === ''){
            if(a > 0){
                msjBC.error('ERROR','URL Ya existe');
            }else{
                CDH.getGuardarNodo();
            }
        }else if(ifu !== ''){
            funcList.alert_Confirmar(); 
         }
    });
    
    $("#id_cancelar_elimitacion_hijo").click(function() {funcList.no_selec_token(); });
    
    $("#cancelar_token").click(function() { funcList.no_selec_token();});
   
});

/*
 * Funciones para los botones
 * @type type
 */


var funcList = {
    
    no_selec_token: function(){
        document.getElementById('id_reg').value = '';
        document.getElementById('id_nombre_hijo').value = '';
        /* document.getElementById('id_ip_hijo').value = ''; */
        document.getElementById('id_url_hijo').value = '';
    },
    selec_token:function(id){
        var contenido = document.getElementById('item_list_'+id).innerHTML;
        var partes = contenido.split(' <br> ');
        var nombre = partes[0].split('Nombre: ');
        nombre = nombre[1];
        /* var ip = partes[2].split('IP: ');
        ip = ip[1]; */
        var url = partes[2].split('URL: ');
        url = url[1]; 
        
        document.getElementById('id_reg').value = id;
        document.getElementById('id_nombre_hijo').value = nombre;
        /* document.getElementById('id_ip_hijo').value = ip; */
        document.getElementById('id_url_hijo').value = url;
        
        var startdate = partes[3].split('Fecha inicio del curso: ');
        startdate = startdate[1].split('-');
        document.getElementById('id_startdate_year').value = parseInt(startdate[0]);
        document.getElementById('id_startdate_month').value = parseInt(startdate[1]);
        document.getElementById('id_startdate_day').value = parseInt(startdate[2]);
        var enddate8 = partes[4].split('Fecha fin de 8 semanas: ');
        enddate8 = enddate8[1].split('-');
        document.getElementById('id_enddate8_year').value = parseInt(enddate8[0]);
        document.getElementById('id_enddate8_month').value = parseInt(enddate8[1]);
        document.getElementById('id_enddate8_day').value = parseInt(enddate8[2]);
        var enddate16 = partes[5].split('Fecha fin de 16 semanas: ');
        enddate16 = enddate16[1].split('-');
        document.getElementById('id_enddate16_year').value = parseInt(enddate16[0]);
        document.getElementById('id_enddate16_month').value = parseInt(enddate16[1]);
        document.getElementById('id_enddate16_day').value = parseInt(enddate16[2]);
        
        var estado = partes[6].split('Estado: ');
        document.getElementById('id_estado_token').value = estado[1] == "Activo"? 1:0;
        var edition = partes[7].split('Edición de actividades: ');
        document.getElementById('id_edition_acti').value = edition[1] == "Si"? 1:0;
        
    },
    eliminar_token: function (id){
        $.confirm({
                title: 'ELIMINAR',
                content: '¿Desea Eliminar el registro?',
                icon: 'fa fa-question',
                theme: 'modern',
                closeIcon: true,
                animation: 'scale',
                type: 'red',
                buttons: {
                    deleteToken: {
                        text: 'Eliminar',
                        btnClass: 'btn-dark',
                        action: function(){
                            CDH.deletToken(id);   
                        }
                    },

                    cancel: {
                        btnClass: 'btn-red',
                        text: 'Cancelar'
                    }
                }
            }); 

    },
    alert_Confirmar: function (){
        $.confirm({
            title: 'ACTUALIZAR NODO',
            content: '¿Desea generar un nuevo Token para este hijo?',
            icon: 'fa fa-question',
            theme: 'modern',
            closeIcon: true,
            animation: 'scale',
            type: 'blue',
            buttons: {
                upTokenyes: {
                    text: 'SI',
                    btnClass: 'btn-dark',
                    action: function(){
                        new_token = 'yes'; 
                        CDH.updateToken();  
                    }
                },
                upTokenno:{
                    text: 'NO',
                    btnClass: 'btn-dark',
                    action: function(){
                        new_token = null; CDH.updateToken();
                    }                           
                },
                cancel: {
                    btnClass: 'btn-red',
                    text: 'Cancelar'
                }
            }
            }); 

    },
};



/*
* Variable para crear un token
* @type type
*/
var inerface = {
   cr_pass: function(){
       var long = parseInt(40);
       var caracteres = "abcdefghijkmnpqrtuvwxyzABCDEFGHIJKLMNPQRTUVWXYZ2346789";
       var contrasena = "";
       for (i=0; i<long; i++) contrasena += caracteres.charAt(Math.floor(Math.random()*caracteres.length));
       return contrasena;
   },   
};



    /*
     * 
     * @type Object
     * Nomenclaturas de SERVICIOS y API
     */
    
    /*1. CRE -> Creación
    2. UPD -> Actualización
    3. QRY -> Consulta
    4. DEL -> Eliminar
    
    1. CRE
        1.1. CRE_C01 -> Creación de los nodos o hijos

    
    3. QRY
        3.1. QRY_Q01 -> Consulta de los Cursos disponibles en el Nodo Padre
    
    */
    

     /*
     * 
     * @type Object
     * Objeto respuesta creación de Nodo
     */   
    
    ACK = new Object();
    
    // El ack se llena dependiendo de la validación 
    
    //ACK.response = 1; // True
    ACK.response = null; // False
    
   



