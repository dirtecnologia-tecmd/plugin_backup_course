/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


function saveLog() { }

saveLog.prototype.saveAndReturnActivities = function (id_course, id_act, type_act, id_user, datos, section, lugar, redi, array_check = []) {
    var script_confirm = document.createElement('script');

    script_confirm.onload = function () {

        let obC01 = type_act != 'course' ? AFB.objSave(type_act, JSON.parse(datos), id_course) : ObAc.switchActivitiesUpdate('course', id_course);


        let dataCon = JSON.parse(datos);

        var info = OCREUp.obC01(id_course, id_act, type_act, obC01, id_user);
        if (dataCon.delete) {
            datos = JSON.stringify(dataCon);
        }

        console.log('info', info);

        $.ajax({
            async: false,
            url: lugar + 'update/methods/' + info.type + info.ws_url,
            data: info,
            type: 'POST',
            success: function (json) { },
            error: function (json, textStatus, errorThrown) { },
            complete: function (json, status) {
                if (status != 'error') {
                    try {
                        var objUp = JSON.parse(json.responseText);
                        type_act == 'course' ? SLog.objCourse(id_course, id_user, lugar, objUp, datos, redi, array_check) : SLog.notificNodos(id_course, objUp, obC01, id_act, section, type_act, lugar, redi, array_check);
                    } catch (e) {

                    } finally {

                    }
                }
            }
        });
    };
    script_confirm.src = lugar + "../../lib/jquery/jquery-3.6.1.js";
    document.getElementsByTagName('head')[0].appendChild(script_confirm);

};

/*
 * Metodo para guardar en la tabla update_nodos y notificar la actualización a los nodos
 * @param {int} id_course
 * @param {obj} obj
 * @param {obj} obC01
 * @returns {undefined}
 */
saveLog.prototype.notificNodos = function (id_course, obj, obC01, id_act, section, type_act, lugar, redi, array_check = []) {
    var info = OCREUp.obC02(id_course, obj.id_updates_log, obC01, id_act, type_act, obj.id_updates_courses, array_check);
    console.log("Soy la info de notificNodos =>", info);
    $.ajax({
        //async: true,
        url: lugar + 'update/methods/' + info.type + info.ws_url,
        data: info,
        type: 'POST',
        success: function (json) { },
        error: function (json, textStatus, errorThrown) { },
        complete: function (json, status) {
            if (status != 'error') {
                document.getElementById('overlay-loader_block_modedit').style.display = 'none';
                document.getElementsByTagName('body')[0].innerHTML = json.responseText + '<a id="boton_volver" href="' + redi + '">Continuar</a>' +
                    '<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">';
                //$('.collapse').collapse();
            }
        }
    });
};

/*
 * Actualizaciones del curso como libro, grupos, agrupaciones
 */
saveLog.prototype.objCourse = function (id_course, id_user, lugar, objUp, datos, redi, array_check = []) {
    if (document.getElementById('overlay-loader_block_modedit')) {
        document.getElementById('overlay-loader_block_modedit').style.display = 'block';
    }

    var obAct = datos != '' ? AFB.objSave('course', JSON.parse(datos), id_course) : ObAc.switchActivitiesUpdate('course', id_course);
    if (window.location.href.indexOf('local/backup_course/update/layouts/category') != -1) {
        obAct = ObAc.switchActivitiesUpdate('course', id_course);
    }

    var info = OUPDUpd.obU01(id_course, obAct, id_user, objUp.id_updates_log, objUp.id_updates_courses, array_check);
    console.log('objCourse->info', info, 'objCourse->obAct', obAct);
    let dataCon = JSON.parse(datos);
    if (dataCon.delete) {
        info.deleteItem = dataCon.delete;
    }

    $.ajax({
        //async: true,
        url: lugar + 'update/methods/' + info.type + info.ws_url,
        data: info,
        type: 'POST',
        success: function (json) { },
        error: function (json, textStatus, errorThrown) { },
        complete: function (json, status) {
            if (status != 'error') {
                document.getElementsByTagName('body')[0].innerHTML = json.responseText + '<a id="boton_volver" href="' + redi + '">Continuar</a>' +
                    '<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">';

            }
        }
    });
};

/*
 * Consultar los nodos activos relacionados con el curso
 * Ventana de confirmación de elección de nodos a actualizar si es solo actualizar
 * Si es creación seleccionar todos los nodos
 * @param {int} id_course
 * @param {int} id_act
 * @param {string} type_act
 * @param {int} id_user
 * @param {string} datos
 * @param {int} section
 * @param {string} lugar
 * @param {string} redi
 * @param {int} new_create 0 es actualización  1=nuevo de creación
 * @returns {undefined}
 */
saveLog.prototype.confir_nodos_actu = function (id_course, id_act, type_act, id_user, datos, section, lugar, redi, new_create = 0) {

    $.ajax({
        url: lugar + 'update/methods/QRY/class.query.php',
        data: {
            key: 'Q01',
            courseid_h: id_course
        },
        type: 'POST',
        success: function (json) { },
        error: function (result, textStatus, errorThrown) { },
        complete: function (json, status) {
            if (status != 'error') {
                if (json.responseText.length > 2) {
                    if (typeof JSON.parse(json.responseText) == 'object') {
                        var script_confirm = document.createElement('script');
                        script_confirm.onload = function () {
                            var array_check = []; //nodos a actualizar
                            var in_html = '';
                            $.each(JSON.parse(json.responseText), function (k, v) {
                                in_html += '<input type="checkbox" name="foo" value="' + v['id'] + '" id="checkbox' + v['id'] + '"/>' + v['url_hijo'] + '<br>';
                                if (new_create == 1) { //empujar a todos los nodos la creación
                                    array_check.push(v['id']);
                                }
                            });
                            if (new_create == 0) { // si es solo actualización
                                $.confirm({
                                    title: 'ACTUALIZAR?',
                                    content: 'Seleccione los hijos a actualizar' +
                                        '<div id="list_tokens" style="text-align: left;">' +
                                        '<input type="checkbox" name="select-all" id="select-all" onClick="SLog.confir_nodos_actu_select(this)"/>Seleccionar todos<br>' +
                                        '<div id="list_tokens_inputs">' + in_html + '</div>' +
                                        '</div>',
                                    icon: 'fa fa-question',
                                    theme: 'modern',
                                    closeIcon: true,
                                    animation: 'scale',
                                    type: 'red',
                                    buttons: {
                                        ocho_semanas: {
                                            text: 'Actualizar',
                                            btnClass: 'btn-blue',
                                            action: function () {
                                                var uno = false;
                                                var array_check = [];
                                                var checkboxes = document.getElementsByName('foo');
                                                for (var i = 0, n = checkboxes.length; i < n; i++) {
                                                    if (checkboxes[i].checked) {
                                                        uno = checkboxes[i].checked;
                                                        array_check.push(checkboxes[i].value);
                                                    }
                                                }
                                                if (uno) {
                                                    console.log("Hola soy los datos", datos);
                                                    SLog.saveAndReturnActivities(id_course, id_act, type_act, id_user, datos, section, lugar, redi, array_check);
                                                } else {
                                                    msjBC.error('Seleccionar', 'Debe seleccionar un hijo');
                                                    SLog.confir_nodos_actu(id_course, id_act, type_act, id_user, datos, section, lugar, redi, new_create);
                                                }
                                            }
                                        },
                                        cancel: {
                                            btnClass: 'btn-red',
                                            text: 'Cancelar',
                                            action: function () {
                                                location.href = redi;
                                            }
                                        }
                                    }
                                });
                            } else { //si es un creación
                                SLog.saveAndReturnActivities(id_course, id_act, type_act, id_user, datos, section, lugar, redi, array_check);
                            }
                        }
                        script_confirm.src = lugar + "js/jquery-confirm.js";
                        document.getElementsByTagName('head')[0].appendChild(script_confirm);

                    } else {
                        console.log('La respuesta del archivo no es un object  /update/methods/QRY/: ' + json.responseText);
                    }
                } else {
                    console.log('No hay datos');
                }

            } else {
                console.log('Error en el archivo /update/methods/QRY/class.query.php: ' + json.responseText);
            }


        }
    });

    saveLog.prototype.confir_nodos_actu_select = function (source) {
        var checkboxes = document.getElementsByName('foo');
        for (var i = 0, n = checkboxes.length; i < n; i++) {
            checkboxes[i].checked = source.checked;
        }
    }



}



var SLog = new saveLog();