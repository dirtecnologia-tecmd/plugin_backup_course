/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
function config_sftp() {
    
};

config_sftp.prototype.getDataSftp = function () {
    $('.loader').css("display", "block");
    $('#list_sftCreados').empty();
    var info = {
        func: 'Q',
        //value: 'Q'
    };
    Csftp.ajaxSftp(info);
        
};
config_sftp.prototype.saveDataSftp = function () {
    $('.loader').css("display", "block");
    $('#list_sftCreados').empty();
    var info = $('#form_config_sftp').serializeArray();
    info.push({
        name: 'func',
        value: 'C'
    });
    Csftp.ajaxSftp(info);
};
config_sftp.prototype.updateDataSftp = function () {
    $('.loader').css("display", "block");
    $('#list_sftCreados').empty();
    info.push({
        name: 'func',
        value: 'U'
    });
};
config_sftp.prototype.deleteDataSftp = function () {
    $('.loader').css("display", "block");
    $('#list_sftCreados').empty();
    info.push({
        name: 'func',
        value: 'D'
    });
};
config_sftp.prototype.ajaxSftp = function (info) {
    $.ajax({
        url : '../../methods/sftp_crud.php',
        data : info,
        type : 'POST',
        success : function(json) {
                
        },
        error : function(result, textStatus, errorThrown) {
            //GSC.error('2','getBanckPreguntas',result.status);
        },
        complete : function(json, status) {
            if(status != 'error'){                  
                try {
                    if(json.responseText.length > 2){
                        $('#list_sftCreados').append('<span class="lista_no_tokens" >'+json.responseText+'</span>');
                        if(typeof JSON.parse(json.responseText) == 'object'){
                            $('#list_sftCreados').empty();
                            $.each(JSON.parse(json.responseText), function(k,v){
                                $('#create_sftp').css("display", "none");
                                $('#list_sftCreados').append('<div id="datos_creados_sftp">'+
                                                                '<span class="lista_no_tokens" > Servidor:'+v['server']+'</span><br>'+
                                                                '<span class="lista_no_tokens" > Port:'+v['port']+'</span><br>'+
                                                                '<span class="lista_no_tokens" > Username:'+v['username']+'</span><br>'+
                                                                //'<span class="lista_no_tokens" > Password:'+v['password']+'</span>'+
                                                                '<div class="edit-item-hijo" id="edit_'+v['id']+'" onclick="Csftp.editButton(\''+v['id']+'\',\''+v['server']+'\',\''+v['port']+'\',\''+v['username']+'\',\''+v['password']+'\')"></div>'+
                                                             '</div>');
                            });
                        }
                                           
                    }else{
                        $('#list_sftCreados').append('<span class="lista_no_tokens" >NO ha configurado el SFTP</span>');

                    }
                } catch (e) {
                    //GSC.error('1','getBanckPreguntas',e);
                } finally {
                    $('.loader').css("display", "none");
                    Csftp.buttonCancel();
                }  
            }
        }

    });
};
config_sftp.prototype.editButton = function (id,server,port,username,password) {
    $('#id_sftp').val(id);
    $('#id_server').val(server);
    $('#id_port').val(port);
    $('#id_username').val(username);
    $('#id_password').val(password);
    $('#create_sftp').val('Actualizar');
    $('#create_sftp').css("display", "block");
    $('#create_sftp').css('float', 'left');
    var info = $('#form_config_sftp').serializeArray();
};

config_sftp.prototype.buttonCancel = function () {
    $('#id_sftp').val('');
    $('#id_server').val('');
    $('#id_port').val('');
    $('#id_username').val('');
    $('#id_password').val('');
    var btnA = $('#create_sftp').val();
    if(btnA == 'Actualizar'){
        $('#create_sftp').css("display", "none");
    }
};

Csftp = new config_sftp();   
Csftp.getDataSftp();

$( "#create_sftp" ).click(function() {
    if( $('#id_server').val().length === 0){
       msjBC.error('ERROR','Debe escribir el nombre del servidor');  
    }else if( $('#id_port').val().length === 0){
       msjBC.error('ERROR','Debe escribir el puerto');  
    }else if( $('#id_username').val().length === 0){
       msjBC.error('ERROR','Debe escribir el nombre del usuario');  
    }else if( $('#id_password').val().length === 0){
       msjBC.error('ERROR','Debe escribir la contraseña');  
    }else{
        Csftp.saveDataSftp();
    }
});

$("#cancelar_sftp").click(function() {
    Csftp.buttonCancel();
});