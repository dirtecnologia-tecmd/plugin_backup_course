/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class view_updates {
    /**
     * Listar nodos actualizados
     * @returns {undefined}
     */
    get_updates() {
        $('.loader').css("display", "block");
        $('#list_updates_black').empty();
        $.ajax({
            url: '../../methods/class_admin_updates.php',
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
                            $('#list_updates_black').append('<span class="lista_no_updates" >' + json.responseText + '</span>');
                            if (typeof JSON.parse(json.responseText) == 'object') {
                                $('#list_updates_black').empty();
                                $.each(JSON.parse(json.responseText), function (k, v) {
                                    $('#list_updates_black').append('<tr id="datos_nodos-' + v['id'] + '" onclick="v_updates.get_list_courses_updates(' + v['id'] + ')">' +
                                            '<td style="color: #000;"> ' + v['nombre'] + '</td>' +
                                            '<td style="color: #000;"> ' + v['url_hijo'] + '</td>' +
                                            //'<span style="width: 30%; float: left; border-left: #dbdbdb solid 1px; border-right: #dbdbdb solid 1px;"> id: ' + v['id_act_sp'] + '</span><br>' +
                                            //'<span class="nombre_curso_list" data-toggle="collapse" data-target="#contenDataPlantilla-'+v['id']+'"><i class="fa fa-fw fa-eye"></i> Ver objeto</span><br>' +
                                            //'<textarea class="collapse" id="contenDataPlantilla-'+v['id']+'" style="width: 100%;">' + v['obj_act'] + '</textarea>' +
                                        '</tr>');
                                           
                                });
                            }else {
                                $('#list_updates_black').append('<span class="lista_no_updates" >No hay updates en lista negra</span>');
                            }

                        } else {
                            $('#list_updates_black').append('<span class="lista_no_updates" >No hay updates bloqueados</span>');
                        }
                    } catch (e) {
                    } finally {
                        $('.loader').css("display", "none");
                    }
                }
            }

        });
    }
       
    
    /**
     * Listar cursos updates en hijos
     * @returns {undefined}
     */
    get_list_courses_updates(id) {
        $('.loader').css("display", "block");
        $('#list_updates_activos').empty();
        $.ajax({
            url: '../../methods/class_admin_updates.php',
            data: {key: 'Q02', id_nodo:id},
            type: 'POST',
            success: function (json) {},
            error: function (result, textStatus, errorThrown) {},
            complete: function (json, status) {
                if (status != 'error') {
                    try {
                        if (json.responseText.length > 2) {
                            $('#list_updates_activos').append('<span class="lista_no_updates" >' + json.responseText + '</span>');
                            if (typeof JSON.parse(json.responseText) == 'object') {
                                $('#list_updates_activos').empty();
                                $.each(JSON.parse(json.responseText), function (k, v) {
                                    $('#list_updates_activos').append('<tr id="datos_list_course-' + v['id_course_sp'] + '" onclick="v_updates.get_list_items_updates(' + v['id_course_sp'] + ', '+id+')">' +
                                            '<td class="lista_no_updates" style="color: #000;">' + v['fullname'] + '</td>' +
                                            '<td class="lista_no_updates" style="color: #000;">' + v['shortname'] + '</td>' +
                                            //'<td class="lista_no_updates" >' + v['email'] + '</td>' +
                                        '</tr>');
                                           
                                });
                            }else{
                                $('#list_updates_activos').append('<span class="lista_no_updates" >No hay updates activos</span>');
                            }

                        } else {
                            $('#list_updates_activos').append('<span class="lista_no_updates" >No hay updates activos</span>');

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
     * Borrar updates de la lista negra
     * @param {int} id
     * @param {string} url
     * @param {string} tok
     * @returns {Generator}
     */
    get_list_items_updates(id_course, id_nodo){
        $('.loader').css("display", "block");
        $.ajax({
            url: '../../methods/class_admin_updates.php',
            data: {key: 'Q03', id_nodo: id_nodo, id_course: id_course},
            type: 'POST',
            success: function (json) {},
            error: function (result, textStatus, errorThrown) {},
            complete: function (json, status) {
                if (status != 'error') {
                    try {
                        if (json.responseText.length > 2) {
                            $('#list_updates_items').append('<span class="lista_no_updates" >' + json.responseText + '</span>');
                            if (typeof JSON.parse(json.responseText) == 'object') {
                                $('#list_updates_items').empty();
                                $.each(JSON.parse(json.responseText), function (k, v) {
                                    $('#list_updates_items').append('<tr id="datos_list_items_course-' + v['id'] + '" onclick="">' +
                                            '<td style="width: 20%; float: left; color: #000;">' + v['type_act'] + '</td>' +
                                            '<td style="width: 20%; float: left; color: #000;">' + v['time_update_date'] + '</td>' +
                                            '<td style="width: 40%; float: left; color: #000;">' + v['email'] + '</td>' +
                                            '<td style="width: 20%; float: left; color: #000;" data-toggle="collapse" data-target="#contenDataPlantilla-'+v['id']+'"><i class="fa fa-fw fa-eye"></i> Ver objeto</td>' +
                                            
                                        '</tr>'+
                                        '<tr class="collapse" id="contenDataPlantilla-'+v['id']+'"><td style="width: 100%;">'+
                                            '<textarea style="width: 100%;">' + v['obj_act'] + '</textarea>' +
                                        '</td></tr>');
                                           
                                });
                            }else{
                                $('#list_updates_items').append('<span class="lista_no_updates" >No hay updates activos</span>');
                            }

                        } else {
                            $('#list_updates_items').append('<span class="lista_no_updates" >No hay updates activos</span>');

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
}

var v_updates = new view_updates();
v_updates.get_updates();
