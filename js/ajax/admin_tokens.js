/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class admin_tokens {
    /**
     * Listar tokens de la lista negra
     * @returns {undefined}
     */
    get_tokens_black() {
        $('.loader').css("display", "block");
        $('#list_tokens_black').empty();
        $.ajax({
            url: '../../methods/class_admin_tokens.php',
            data: {key: 'Q01'},
            type: 'POST',
            success: function (json) {

            },
            error: function (result, textStatus, errorThrown) {
                //GSC.error('2','getBanckPreguntas',result.status);
            },
            complete: function (json, status) {
                if (status != 'error') {
                    try {
                        if (json.responseText.length > 2) {
                            $('#list_tokens_black').append('<span class="lista_no_tokens" >' + json.responseText + '</span>');
                            if (typeof JSON.parse(json.responseText) == 'object') {
                                $('#list_tokens_black').empty();
                                $.each(JSON.parse(json.responseText), function (k, v) {
                                    $('#list_tokens_black').append('<div id="datos_creados_sftp">' +
                                            '<span class="lista_no_tokens" > URL: ' + v['url'] + '</span><br>' +
                                            '<span class="lista_no_tokens" > Token: ' + v['token'] + '</span><br>' +
                                            /* '<span class="lista_no_tokens" > IP: ' + v['ip'] + '</span><br>' + */
                                            //'<span class="lista_no_tokens" > Password:'+v['password']+'</span>'+
                                            '<div title="Eliminar" class="delete-item-hijo" id="delete_' + v['id'] + '" onclick="admin_tok.delete_tokens_black(\'' + v['id'] + '\',\'' + v['url'] + '\',\'' + v['token'] + '\')"></div>' +
                                        '</div>');
                                           
                                });
                            }else {
                                $('#list_tokens_black').append('<span class="lista_no_tokens" >No hay tokens en lista negra</span>');
                            }

                        } else {
                            $('#list_tokens_black').append('<span class="lista_no_tokens" >No hay tokens bloqueados</span>');

                        }
                    } catch (e) {
                        //GSC.error('1','getBanckPreguntas',e);
                    } finally {
                        admin_tok.get_tokens_activos();
                        
                    }
                }
            }

        });
    }
    /*
     * Borrar tokens de la lista negra
     * @param {int} id
     * @param {string} url
     * @param {string} tok
     * @returns {Generator}
     */
    delete_tokens_black(id, url, tok){
        $('.loader').css("display", "block");
        $.ajax({
            url: '../../methods/class_admin_tokens.php',
            data: {key: 'D01', id: id, url: url, token:tok},
            type: 'POST',
            success: function (json) {},
            error: function (result, textStatus, errorThrown) {},
            complete: function (json, status) {
                if (status != 'error') {
                    try {
                        if (typeof JSON.parse(json.responseText) == 'object') {
                            var respu = JSON.parse(json.responseText);
                            respu.bc_balck_list_p ? msjBC.ok('Eliminado','El token se elimininó de la black_list'): console.log('no se elimintó de la black_list del padre');
                            respu.bc_white_list_p ? msjBC.ok('Eliminado','El token se elimininó de la white_list'): console.log('no se elimintó de la white_list del padre');
                            respu.bc_registro_pc_p ? msjBC.ok('Eliminado','El token se elimininó de la bc_registro_pc_p'): console.log('no se elimintó de la bc_registro_pc del padre');
                            console.log('Respuesta del hijo bc_registro_pc', respu.bc_registro_pc_h);
                        }else{
                            console('revisa el network');
                        }
                        
                    } catch (e) {
                        msjBC.error('Error','Error al eliminar');
                    } finally {
                        admin_tok.get_tokens_black();
                    }
                }
            }

        });
    }
    
    
    
    /**
     * Listar tokens activos en el padre
     * @returns {undefined}
     */
    get_tokens_activos() {
        $('.loader').css("display", "block");
        $('#list_tokens_activos').empty();
        $.ajax({
            url: '../../methods/class_admin_tokens.php',
            data: {key: 'Q02'},
            type: 'POST',
            success: function (json) {},
            error: function (result, textStatus, errorThrown) {},
            complete: function (json, status) {
                if (status != 'error') {
                    try {
                        if (json.responseText.length > 2) {
                            $('#list_tokens_activos').append('<span class="lista_no_tokens" >' + json.responseText + '</span>');
                            if (typeof JSON.parse(json.responseText) == 'object') {
                                $('#list_tokens_activos').empty();
                                $.each(JSON.parse(json.responseText), function (k, v) {
                                    var edit = v['edition'] == 0? 'NO': 'SI';
                                    $('#list_tokens_activos').append('<div id="datos_creados_sftp">' +
                                            '<span class="lista_no_tokens" > Nombre: ' + v['nombre'] + '</span><br>' +
                                            '<span class="lista_no_tokens" > URL hijo: ' + v['url_hijo'] + '</span><br>' +
                                            /* '<span class="lista_no_tokens" > IP: ' + v['ip'] + '</span><br>' + */
                                            //'<span class="lista_no_tokens" > Token: '+v['token']+'</span><br>'+
                                            '<span class="lista_no_tokens" > Estado: '+v['estado']+'</span><br>'+
                                            '<span class="lista_no_tokens" > Edición: '+edit+'</span>'+
                                            '<div title="Desactivar" class="block-item-hijo" id="edit_' + v['id'] + '" onclick="admin_tok.update_tokens_activos(\'' + v['id'] + '\')"></div>' +
                                        '</div>');
                                           
                                });
                            }else{
                                $('#list_tokens_activos').append('<span class="lista_no_tokens" >No hay tokens activos</span>');
                            }

                        } else {
                            $('#list_tokens_activos').append('<span class="lista_no_tokens" >No hay tokens activos</span>');

                        }
                    } catch (e) {
                        //GSC.error('1','getBanckPreguntas',e);
                    } finally {
                        $('.loader').css("display", "none");
                    }
                }
            }

        });
    }
    /*
     * Borrar tokens de la lista negra
     * @param {int} id
     * @param {string} url
     * @param {string} tok
     * @returns {Generator}
     */
    update_tokens_activos(id){
        $('.loader').css("display", "block");
        $.ajax({
            url: '../../methods/class_admin_tokens.php',
            data: {key: 'U01', id: id},
            type: 'POST',
            success: function (json) {},
            error: function (result, textStatus, errorThrown) {},
            complete: function (json, status) {
                if (status != 'error') {
                    try {
                        if (typeof JSON.parse(json.responseText) == 'object') {
                            var respu = JSON.parse(json.responseText);
                            respu.bc_registro_pc_p ? msjBC.ok('Actualizado','El token se actualizó en bc_registro_pc del padre'): console.log('no se actualizó de la bc_registro_pc del padre');
                            console.log('Respuesta del hijo bc_registro_pc', respu.bc_registro_pc_h);
                        }else{
                            console('revisa el network');
                        }
                        
                    } catch (e) {
                        msjBC.error('Error','Error al actualizar');
                    } finally {
                        admin_tok.get_tokens_activos();
                    }
                }
            }

        });
    }
}

var admin_tok = new admin_tokens();
admin_tok.get_tokens_black();
