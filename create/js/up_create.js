/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


class up_create {
    /*
     * Reemplazar url del formulario de creación de actividades por el nuestro
     * @returns {Generator}
     */
    form_add() {
        /*<div id="overlay-loader" style="display: block;"><div class="sk-cube-grid"><div class="sk-cube sk-cube1"></div><div class="sk-cube sk-cube2"></div><div class="sk-cube sk-cube3"></div><div class="sk-cube sk-cube4"></div><div class="sk-cube sk-cube5"></div><div class="sk-cube sk-cube6"></div><div class="sk-cube sk-cube7"></div><div class="sk-cube sk-cube8"></div><div class="sk-cube sk-cube9"></div></div></div>*/
        if (!document.getElementById('overlay-loader_block')) {
            $("#page-content").append('<div id="overlay-loader_block" style="display:none">' +
                '<div class="sk-cube-grid">' +
                '<div class="sk-cube sk-cube1"></div>' +
                '<div class="sk-cube sk-cube2"></div>' +
                '<div class="sk-cube sk-cube3"></div>' +
                '<div class="sk-cube sk-cube4"></div>' +
                '<div class="sk-cube sk-cube5"></div>' +
                '<div class="sk-cube sk-cube6"></div>' +
                '<div class="sk-cube sk-cube7"></div>' +
                '<div class="sk-cube sk-cube8"></div>' +
                '<div class="sk-cube sk-cube9"></div>' +
                '</div>' +
                '</div>');
        }
        var bt = document.getElementsByClassName('section-modchooser-link btn btn-link');
        for (var k = 0; k < bt.length; k++) bt[k].setAttribute('onclick', 'r_url.ventana_create();');
    }

    enviar_create(data, course, url) {

        var fjson = JSON.parse(data);
        $.ajax({
            url: '../methods/class.create.php',
            data: {
                key: 'C01',
                data: data,
                course: course
            },
            type: 'POST',
            success: function (json) {

            },
            error: function (result, textStatus, errorThrown) {
                console.log('result', result);
            },
            complete: function (json, status) {
                console.log('json', json);
                if (status != 'error') {

                    document.getElementsByTagName('body')[0].innerHTML = json.responseText + '<a id="boton_volver" href="' + url + '">Continuar</a>' +
                        '<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">';
                } else {
                    console.log('json', json);
                    console.log('status', status);
                }
            }
        });
    }

    ventana_create() {
        document.getElementById('overlay-loader_block').style.display = 'block';
        setTimeout(function () {
            var itemss = document.getElementsByClassName('optioninfo');
            for (var k = 0; k < itemss.length; k++) {
                var it_a = itemss[k].getElementsByTagName('a');
                if (it_a && it_a[0]) {
                    var ur = it_a[0].href;
                    var newHref = ur.replace("course/mod.php", "local/backup_course/create/layouts/mod.php");
                    it_a[0].href = newHref;
                }
            }
            document.getElementById('overlay-loader_block').style.display = 'none';
        }, 2500);
    }


    cambiar_btn_enviar() {

        setTimeout(function () {
            if (window.location.href.indexOf('/local/backup_course/create/layouts/modedit.php') != -1) { //crear actividades
                var btns = document.getElementById('id_submitbutton2');
            } else if (window.location.href.indexOf('local/backup_course/update/layouts/question/question.php') != -1 //crear preguntas
                ||
                window.location.href.indexOf('local/backup_course/update/layouts/question/category.php') != -1) { //crear categorias de preguntas
                var btns = document.getElementById('id_submitbutton');
            }
            btns.value = "Enviar a Creación";
        }, 500);

    }

    alert_no_save() {
        $.confirm({
            title: 'Error?',
            content: 'No se puede enviar creaciones con calificación',
            icon: 'fa fa-warning',
            theme: 'modern',
            closeIcon: true,
            animation: 'scale',
            type: 'red',
            buttons: {
                cancel: {
                    btnClass: 'btn-red',
                    text: 'Cancelar',
                    action: function () {
                        window.history.back();
                    }

                }
            }
        });
    }

    list_actualizar_create_save(lugar, id_course, redi, fromform, mform) {
        console.log('fromform', fromform);
        $.ajax({
            async: false,
            url: lugar + '/local/backup_course/update/methods/QRY/class.query.php',
            data: {
                key: 'Q01',
                courseid_h: id_course
            },
            type: 'POST',
            success: function (json) {},
            error: function (json, textStatus, errorThrown) {},
            complete: function (json, status) {
                if (status != 'error') {
                    if (json.responseText.length > 2) {
                        if (typeof JSON.parse(json.responseText) == 'object') {
                            var array_check = []; //nodos a actualizar
                            var in_html = '';
                            $.each(JSON.parse(json.responseText), function (k, v) {
                                in_html += '<input type="checkbox" name="foo" value="' + v['id'] + '" id="checkbox' + v['id'] + '"/>' + v['url_hijo'] + '<br>';
                                array_check.push(v['id']);
                            });

                            $.confirm({
                                title: 'Crear?',
                                content: 'Seleccione los hijos a actualizar' +
                                    '<div id="list_tokens" style="text-align: left;">' +
                                    '<input type="checkbox" name="select-all" id="select-all" onClick="r_url.confir_nodos_actu_select(this)"/>Seleccionar todos<br>' +
                                    '<div id="list_tokens_inputs">' + in_html + '</div>' +
                                    '</div>',
                                icon: 'fa fa-question',
                                theme: 'modern',
                                closeIcon: true,
                                animation: 'scale',
                                type: 'red',
                                buttons: {
                                    enviar: {
                                        text: 'Crear',
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
                                                r_url.saveAndReturnActivities(lugar, id_course, fromform, mform, array_check, redi);
                                            } else {
                                                msjBC.error('Seleccionar', 'Debe seleccionar un hijo');
                                                r_url.list_actualizar_create_save(lugar, id_course, fromform, mform);
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

                        }
                    }
                }
            }
        });
    }


    confir_nodos_actu_select(source) {
        var checkboxes = document.getElementsByName('foo');
        for (var i = 0, n = checkboxes.length; i < n; i++) {
            checkboxes[i].checked = source.checked;
        }
    }

    saveAndReturnActivities(lugar, id_course, fromform, mform, array_check, redi) {
        $.ajax({
            async: false,
            url: lugar + '/local/backup_course/create/methods/class.create.php',
            data: {
                'key': 'C01',
                'course': id_course,
                'fromform': fromform,
                'mform': mform,
                'array_check': array_check,
                'redi': redi
            },
            type: 'POST',
            success: function (json) {
                /*  console.log("Hola soy la respuesta => "+json); */
            },
            error: function (json, textStatus, errorThrown) {},
            complete: function (json, status) {
                if (status != 'error') {
                    if (json.responseText.length > 2) {
                        console.log('json.responseText', json.responseText);
                        document.getElementsByTagName('body')[0].innerHTML = json.responseText + '<a id="boton_volver" href="' + redi + '">Continuar</a>' +
                            '<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">';
                    }
                }
            }
        });
    }

}
var r_url = new up_create();