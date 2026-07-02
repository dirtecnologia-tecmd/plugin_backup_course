var GSC = null; // definicion de new search_course();
var arrCourse = []; // Objeto de las secciones y actividades del curso
var arrRelation = []; // array con id de course_modules creados en el hijo
var cantRel = null; // cantidad de actividades por seccion
var actis = null; // informacion de las actividades por seccion
var statePSC = null; // cantidad de secciones
var jObjCourse = []; // informacion de los cursos de todas las coinicidencias encontradas
var jObjCoursePadre = []; //informacion general del curso seleccionado
var idCoursePadre = null; // id del curso padre
var idCourseNodo = null; // id del curso hijo
var objRelationPH = {}; // objeto con la relacion de las secciones y actividades entre el padre y el hijo
var winPro; // ventana de progreso de la información
var secciones = []; // info secciones
var actividades = []; // info de las actividades por seccion
var items = []; // relación de las actividades del padre y las de actividades creadas en el hijo
var items_id_act = []; // actividades creadas
var banckPreguntas = []; // relacion del banco de preguntas con el del padre
let contentBank = []; // relación de todos los archivos del banco de contenido
var rubrica = [];
var objetAllCourse = new Object(); // toda la infocacion del curso(banco de preguntas, bsecciones, actividades, categorias, bloques, scalas)
var fechas_8o16 = 0; // Fechas de inicio y fin del curso si es de 8 o 16 semanas

$(document).ready(function () {
    function search_course() { }
    GSC = new search_course();
    /*
     * List de Cursos disponibles -> getlistaCourses
     * @returns {undefined}
     */
    search_course.prototype.getlistaCourses = function () {
        $('.list_courses_vacia').hide();
        $('.loader_list_courses').css("display", "block");
        $('#list_courses_padre').empty();
        var form = $('#form_search_course');
        var data = form.serializeArray();
        var info = OQRY.obQ02(data);

        $.ajax({
            url: '../../methods/' + info.type + info.ws_url,
            data: info,
            type: 'POST',
            success: function (json) {

            },
            error: function (json, textStatus, errorThrown) {
                $('#list_courses_padre').append('<h2>Por favor vuelva a intentarlo</h2>' +
                    '<span class="lista_no_tokens" >2 getlistaCourses: ' + json.status + '</span>');
                $('.loader_list_courses').hide();
            },
            complete: function (json, status) {
                if (status != 'error') {
                    try {
                        var fjson = JSON.parse(json.responseText);
                        if (json && fjson.ack == 1) {
                            if (fjson.response != '[]') {
                                jObjCourse = JSON.parse(fjson.response);
                                console.log('jObjCourse', jObjCourse);
                                $.each(jObjCourse, function (k, v) {
                                    $('#list_courses_padre').append('<div class="tok_no_selec" id="item_list_' + v["id"] + '" > ' +
                                        '<div class="nombre_curso_list" title="Nombre del curso">' + v['fullname'] + '</div> ' +
                                        '<div class="short_curso_list" title="Nombre corto del curso">' + v['shortname'] + ' </div> ' +
                                        '<div class="view_course" data-toggle="modal" data-target="#modal-default" title="Vista previa del curso" onclick="GSC.view_course_iframe(' + v['id'] + ', \'' + v['url_padre'] + '\', \'' + v['fullname'] + '\', \'' + v['shortname'] + '\')"><i class="fa fa-fw fa-eye"></i></div>' +
                                        '<div class="import-item-padre" title="importar curso" onclick="conFir.confirmar(' + info.id_nodo + ',' + k + ')"></div>' +
                                        '</div>');
                                });
                            } else {
                                $('#list_courses_padre').append('<span class="lista_no_tokens" >No hay cursos encontrados</span>');
                            }
                        } else {
                            $('#list_courses_padre').append('<span class="lista_no_tokens" >' + fjson.response + '</span>');

                        }

                    } catch (e) {
                        $('#list_courses_padre').append('<h2>Por favor vuelva a intentarlo</h2>' +
                            '<span class="lista_no_tokens" >1 Error: ' + e + '</span>');
                        //location.reload(true);

                    } finally {
                        $('.loader_list_courses').hide();
                    }
                }
            }
        });
    };

    /*
     * Crear iframe de vista del curso
     * @returns {undefined}
     */

    search_course.prototype.view_course_iframe = function (id, url, name, short) {

        const formData = new FormData();

        formData.append('function', 0);
        formData.append('id_course', id);
        formData.append('url', url);

        fetch('../../methods/consultar-curso.php', {
            method: 'POST',
            body: formData
        }).then(response => {

            if (!response.ok) {
                throw new Error('Error en la solicitud');
            }
            return response.text();

        }).then(datos => {

            const data = JSON.parse(datos);

            document.getElementById('contenido_curso').innerHTML = GSC.templatePreview(data, name, short);

        });

    }

    /*
     * Comprobar login en el padre o mostrar el curso
     * @returns {undefined}
     */

    /*     search_course.prototype.onload_view_course_iframe1 = function (url) {
            var x = document.getElementById("frame_padre");
            if (x.contentWindow.location.href != 'about:blank') {
                var frame = x.contentWindow.document;
                if (x.contentWindow.location.href.indexOf('/login') != -1) {
                    setTimeout(function () {
                        $.ajax({
                            type: 'POST',
                            url: x.contentWindow.location.href,
                            data: {
                                username: GSC.convert_a_texto('61646d696e'),
                                password: GSC.convert_a_texto('436d616f696538352e'),
                                rememberusername: 0,
                                logintoken: frame.getElementsByName('logintoken')[0].value
                            },
                            success: function (json) { },
                            error: function (result, textStatus, errorThrown) {
                                console.log('->>>', 'No hay login');
                            },
                            complete: function (json, status) {
                                if (status != 'error') {
                                    x.contentWindow.location = url;
    
                                }
                            }
                        });
                    }, 1000);
    
                } else if (x.contentWindow.location.href.indexOf('/course') != -1) {
                    var he = frame.getElementById('page-header');
                    var me = frame.getElementById('nav-drawer');
                    var fo = frame.getElementById('page-footer');
                    frame.getElementById('page') ? frame.getElementById('page').style.width = "100%" : '';
                    var na = frame.getElementsByClassName('fixed-top navbar navbar-light bg-white navbar-expand');
                    he ? he.style.display = "none" : '';
                    me ? me.style.display = "none" : '';
                    fo ? fo.style.display = "none" : '';
                    na && na[0] ? na[0].style.display = "none" : '';
                    frame.getElementById('nav-drawer') ? frame.getElementById('nav-drawer').style.display = "none" : '';
                    x.style.display = "block";
                    document.getElementById('carg_loader_list_courses') ? document.getElementById('carg_loader_list_courses').style.display = "none" : '';
                } else {
                    var he = frame.getElementById('page-header');
                    var me = frame.getElementById('nav-drawer');
                    var fo = frame.getElementById('page-footer');
                    frame.getElementById('page') ? frame.getElementById('page').style.width = "100%" : '';
                    var na = frame.getElementsByClassName('fixed-top navbar navbar-light bg-white navbar-expand');
                    he ? he.style.display = "none" : '';
                    me ? me.style.display = "none" : '';
                    fo ? fo.style.display = "none" : '';
                    na && na[0] ? na[0].style.display = "none" : '';
                    frame.getElementById('nav-drawer') ? frame.getElementById('nav-drawer').style.display = "none" : '';
                    x.contentWindow.location = url;
                }
            }
        }; */


    /*
     * List de preguntas en el banco del curso en el padre-> getBanckPreguntas
     * @returns {undefined}
     */
    search_course.prototype.getBanckPreguntas = function () {
        var info = OQRY.obQ07(idCoursePadre, idCourseNodo);
        $.ajax({
            url: '../../methods/' + info.type + info.ws_url,
            data: info,
            type: 'POST',
            success: function (json) {

            },
            error: function (result, textStatus, errorThrown) {
                GSC.error('2', 'getBanckPreguntas', result.status);
            },
            complete: function (json, status) {
                if (status != 'error') {
                    try {
                        banckPreguntas = JSON.parse(json.responseText);
                        document.getElementById('msj_cre').getElementsByTagName('d')[0].style.display = 'block';
                        document.getElementById('porcentaje_mensaje').getElementsByTagName('i')[1].style.display = 'none';
                        document.getElementById('porcentaje_mensaje').getElementsByTagName('i')[2].style.display = 'block';
                        GSC.getBanckContenido();
                    } catch (e) {
                        GSC.error('1', 'getBanckPreguntas', e);
                    } finally {

                    }
                }
            }
        });
    };
    
    /*
     * List de preguntas en el banco del curso en el padre-> getBanckPreguntas
     * @returns {undefined}
     */
    search_course.prototype.getBanckContenido = function () {
        var info = OQRY.obQ100(idCoursePadre, idCourseNodo);
        $.ajax({
            url: '../../methods/' + info.type + info.ws_url,
            data: info,
            type: 'POST',
            success: function (json) {

            },
            error: function (result, textStatus, errorThrown) {
                GSC.error('2', 'getBanckContenido', result.status);
            },
            complete: function (json, status) {
                if (status != 'error') {
                    try {
                        var newContenido = JSON.parse(json.responseText);
                        if (banckPreguntas) Object.assign(banckPreguntas, newContenido);
                        else banckPreguntas = newContenido;
                        document.getElementById('msj_cre').getElementsByTagName('d')[0].style.display = 'block';
                        document.getElementById('porcentaje_mensaje').getElementsByTagName('i')[1].style.display = 'none';
                        document.getElementById('porcentaje_mensaje').getElementsByTagName('i')[2].style.display = 'block';
                        GSC.getFilesH5P();
                    } catch (e) {
                        GSC.error('1', 'getBanckContenido', e);
                        console.log("Hola soy el json => ".json);
                    } finally {

                    }
                }
            }
        });
    };
    
    search_course.prototype.getFilesH5P = function () {
        var info = OQRY.obQ101(idCoursePadre, idCourseNodo);
        $.ajax({
            url: '../../methods/' + info.type + info.ws_url,
            data: info,
            type: 'POST',
            success: function (json) {

            },
            error: function (result, textStatus, errorThrown) {
                GSC.error('2', 'getFilesH5P', result.status);
            },
            complete: function (json, status) {
                if (status != 'error') {
                    try {
                        var newContenido = JSON.parse(json.responseText);
                        if (banckPreguntas) Object.assign(banckPreguntas, newContenido);
                        else banckPreguntas = newContenido;
                        document.getElementById('msj_cre').getElementsByTagName('d')[0].style.display = 'block';
                        document.getElementById('porcentaje_mensaje').getElementsByTagName('i')[1].style.display = 'none';
                        document.getElementById('porcentaje_mensaje').getElementsByTagName('i')[2].style.display = 'block';
                        GSC.getFilesResource();
                    } catch (e) {
                        GSC.error('1', 'getFilesH5P', e);
                        console.log("Hola soy el json => ".json);
                    } finally {

                    }
                }
            }
        });
    };
    
    
    search_course.prototype.getFilesResource = function () {
        var info = OQRY.obQ102(idCoursePadre, idCourseNodo);
        $.ajax({
            url: '../../methods/' + info.type + info.ws_url,
            data: info,
            type: 'POST',
            success: function (json) {

            },
            error: function (result, textStatus, errorThrown) {
                GSC.error('2', 'getFilesResource', result.status);
            },
            complete: function (json, status) {
                if (status != 'error') {
                    try {
                        var newContenido = JSON.parse(json.responseText);
                        if (banckPreguntas) Object.assign(banckPreguntas, newContenido);
                        else banckPreguntas = newContenido;
                        console.log('banckPreguntas',banckPreguntas)
                        document.getElementById('msj_cre').getElementsByTagName('d')[0].style.display = 'block';
                        document.getElementById('porcentaje_mensaje').getElementsByTagName('i')[1].style.display = 'none';
                        document.getElementById('porcentaje_mensaje').getElementsByTagName('i')[2].style.display = 'block';
                        GSC.save_course();
                    } catch (e) {
                        GSC.error('1', 'getFilesResource', e);
                        console.log("Hola soy el json => ".json);
                    } finally {

                    }
                }
            }
        });
    };

    search_course.prototype.getRubrica = function () {
        var info = OQRY.obQ09(idCoursePadre, idCourseNodo);
        $.ajax({
            url: '../../methods/' + info.type + info.ws_url,
            data: info,
            type: 'POST',
            success: function (json) { },
            error: function (result, textStatus, errorThrown) {
                GSC.error('2', 'getRubrica', result.status);
            },
            complete: function (json, status) {
                if (status != 'error') {
                    try {
                        rubrica = JSON.parse(json.responseText);
                    } catch (e) {
                        GSC.error('1', 'getRubrica', e);
                    } finally {

                    }
                }
            }
        });
    };

    /*
     * List de Sections del curso Padre-> getlistaSections
     * @returns {undefined}
     */
    search_course.prototype.getlistaSections = function () {
        $('#region-main').empty();
        $('#region-main').append('<h3>Procesos de la Importación:</h3>' +
            '<ul class="msj_lista" id="porcentaje_mensaje">' +
            '<li class="msj_lista_li" id="msj_pre"> Preparar la configuración <d></d>' +
            '<i class="fa fa-fw fa-circle-o-notch"></i>' +
            '</li>' +
            '<li class="msj_lista_li" id="msj_cre"> Crear banco de preguntas y archivos<d></d>' +
            '<i class="fa fa-fw fa-circle-o-notch"></i>' +
            '</li>' +
            '<li class="msj_lista_li" id="msj_gua"> Guardar secciones y actividades <d></d>' +
            '<i class="fa fa-fw fa-circle-o-notch"></i>' +
            '</li>' +
            '<li class="msj_lista_li" id="msj_ter"> Terminar la configuración <d></d>' +
            '<i class="fa fa-fw fa-circle-o-notch"></i>' +
            '</li>' +
            '</ul>');
        document.getElementById('porcentaje_mensaje').getElementsByTagName('i')[0].style.display = 'block';

        var info = OQRY.obQ03(idCoursePadre, idCourseNodo);

        $('.loader_list_courses').css("display", "block");
        $('body').css("overflow", "auto");
        console.log('../../methods/' + info.type + info.ws_url);
        console.log('Soy info => ', info);
        $.ajax({
            url: '../../methods/' + info.type + info.ws_url,
            data: info,
            type: 'POST',
            success: function (json) {

            },
            error: function (result, textStatus, errorThrown) {
                $('#region-main').empty();
                $('#region-main').append('2 getlistaSections: <br><h3>2 Procesos de la Importación: fallido, vuelva a intentarlo</h3>Error: ' + result.status);
                $('#region-main').append('<br><a href="javascript:location.reload(true);" id="boton_volver">Volver a intentarlo</a>');
                setTimeout(function () {
                    winPro.close();
                }, 800);
            },
            complete: function (json, status) {

                if (status != 'error') {
                    try {
                        var fjson = JSON.parse(json.responseText);
                        var fjson_res = JSON.parse(fjson.response);
                        //Agregamos la información del banco de contenido
                        contentBank = JSON.parse(fjson.files_bank);

                        for (var i = 0; i < fjson_res.length; i++) {
                            arrCourse[i] = JSON.parse(fjson_res[i]);
                        }
                        for (var k = 0; k < arrCourse.length; k++) {
                            if (arrCourse[k].activities !== null) {
                                arrCourse[k].activities = JSON.parse(arrCourse[k].activities);
                                for (var h = 0; h < arrCourse[k].activities.length; h++) {
                                    arrCourse[k].activities[h].info = JSON.parse(arrCourse[k].activities[h].info);
                                }
                            }
                        }
                        console.log('arrCourse', arrCourse);
                        $('#list_courses_padre').empty();
                        statePSC = 0;
                        GSC.deleteSections();
                    } catch (e) {
                        GSC.error('1', 'getlistaSections', e);

                    } finally {

                    }
                }
            }
        });
    };

    /*
     * Informacion de los datos del curso(bloques, calificaciones, grupos) -> getDataInfoCourse
     * Busca en el padre la información y la crea en el hijo, devuelve un objeto con la relacion entre estos OCRE.obC06
     * @returns {undefined}
     */
    search_course.prototype.getDataInfoCourse = function () {

        var data = OQRY.obQ06(idCoursePadre, idCourseNodo, objetAllCourse);

        console.log("HOLA SOY  getDataInfoCourse => ", data);

        $.ajax({
            url: '../../methods/' + data.type + data.ws_url,
            data: data,
            type: 'POST',
            success: function (json) {

            },
            error: function (result, textStatus, errorThrown) {
                GSC.error('2', 'getDataInfoCourse', result.status);
            },
            complete: function (json, status) {

                if (status != 'error') {
                    try {
                        GSC.getDataInfoCourse_grade_items(JSON.parse(json.responseText));
                    } catch (e) {
                        GSC.error('1', 'getDataInfoCourse', e);
                    } finally {

                    }
                }
            }
        });
    };
    /*
     * Guardar las agupaciones
     * @param {type} obj
     * @returns {undefined}
     */
    //Verificar
    search_course.prototype.getDataInfoCourse_grade_items = function (obj) {
        var data = OCRE.obC05(idCoursePadre, idCourseNodo,
            obj.cat_p, obj.cat_h, obj.grade_items, items,
            obj.groups_p, obj.groups_h, obj.groupings_p, obj.groupings_h, obj.groupings_groups, banckPreguntas);
        $.ajax({
            url: '../../methods/' + data.type + data.ws_url,
            data: data,
            type: 'POST',
            success: function (json) {

            },
            error: function (result, textStatus, errorThrown) {
                GSC.error('2', 'getDataInfoCourse_grade_items', result.status);
            },
            complete: function (json, status) {
                if (status != 'error') {
                    try {
                        objetAllCourse.sectionAndActi = objRelationPH;
                        objetAllCourse.demas_info = obj;
                        objetAllCourse.demas_info.groupings_groups = JSON.parse(json.responseText);
                        objetAllCourse.rubrica = rubrica;
                        GSC.creRelationPH();
                    } catch (e) {
                        GSC.error('1', 'getDataInfoCourse_grade_items', e);
                    } finally {

                    }
                }
            }
        });
    };

    /*
     * Actualizar la informacion del curso en el hijo -> updateInfoCourse
     * @returns {undefined}
     */
    search_course.prototype.updateInfoCourse = function () {

        var data = OUPD.obU02(jObjCoursePadre, idCourseNodo, fechas_8o16);

        console.log("Soy la data de updateInfoCourse => ", data);

        $.ajax({
            url: '../../methods/' + data.type + data.ws_url,
            data: data,
            type: 'POST',
            success: function (json) {

            },
            error: function (result, textStatus, errorThrown) {
                GSC.error('2', 'updateInfoCourse', result.status);
            },
            complete: function (json, status) {
                if (status != 'error') {
                    try {
                        if (parseInt(json.responseText, 10) == fechas_8o16) {
                            document.getElementById('msj_pre').getElementsByTagName('d')[0].style.display = 'block';
                            document.getElementById('porcentaje_mensaje').getElementsByTagName('i')[0].style.display = 'none';
                            document.getElementById('porcentaje_mensaje').getElementsByTagName('i')[1].style.display = 'block';
                            document.getElementsByClassName('jconfirm-title')[0].innerHTML = 'IMPORTANDO!';
                            GSC.getBanckPreguntas();
                        } else {
                            GSC.error('3', 'updateInfoCourse', 'el curso no se actualizó correctamente');
                        }

                    } catch (e) {
                        GSC.error('1', 'updateInfoCourse', e);
                    } finally {

                    }
                }
            }
        });
    };
    /*
     * validate recorrido de las secciones del curso
     * @returns {undefined}
     */
    search_course.prototype.save_course = function () {
        if (statePSC !== null || statePSC <= arrCourse.length) {
            GSC.save_course_section_false(arrCourse.length);
        }
    };
    /*
     * Barra de progreso de la importación
     * @param {int} tA
     * @returns {undefined}
     */
    search_course.prototype.save_course_section_false = function (tA) {
        var porcentaje = parseInt((statePSC * 100) / tA);
        $('#porcentaje_barra').css("width", porcentaje + "%");
        document.getElementById('porcentaje').innerHTML = ' ' + porcentaje + '%';
        if (statePSC < tA) {
            GSC.creListSections(arrCourse[statePSC]);
        }

        if (porcentaje === 100) {
            document.getElementById('msj_gua').getElementsByTagName('d')[0].style.display = 'block';
            document.getElementById('porcentaje_mensaje').getElementsByTagName('i')[2].style.display = 'none';
            document.getElementById('porcentaje_mensaje').getElementsByTagName('i')[3].style.display = 'block';
            objRelationPH.sections = secciones;
            objetAllCourse.cursos = {
                shortname_padre: jObjCoursePadre.shortname,
                id_padre: idCoursePadre,
                id_hijo: idCourseNodo
            }
            objetAllCourse.bancoPregu = banckPreguntas;
            objetAllCourse.contentBank = contentBank;
            /* objetAllCourse.rubrica = rubrica; */
            //Aqui agrego las secciones al objeto
            /* objetAllCourse.newSec = ; */
            //objetAllCourse.SecAndActivities = arrCourse;
            GSC.getRubrica();
            GSC.getDataInfoCourse();
        }

    };
    /*
     * Validate actividites in section
     * @param {int} id_sect
     * @returns {undefined}
     */
    search_course.prototype.save_acti = function (id_sect) {
        if (cantRel !== null || cantRel <= actis.length) {
            GSC.save_course_acti_false(id_sect, actis[cantRel], actis.length);
        }
    };

    /*
     * Convertir Hexadecimal a texto
     * @returns {undefined}
     */
    search_course.prototype.convert_a_texto = function (str1) {
        var hex = str1.toString();
        var str = '';
        for (var n = 0; n < hex.length; n += 2) {
            str += String.fromCharCode(parseInt(hex.substr(n, 2), 16));
        }
        return str;
    };

    /*
     * Progreso de las actividades en las secciones
     * @param {int} id_sect
     * @param {obj} actis
     * @param {int} tA
     * @returns {undefined}
     */
    search_course.prototype.save_course_acti_false = function (id_sect, actis, tA) {
        if (cantRel < tA) {
            //console.log('actis.bankH5P',actis.bankH5P)
            GSC.creListActivities(actis.act, actis.table, id_sect, actis.como, tA, actis.info, actis.bankH5P);
        }
    };

    /*
     * Create Sections = creListSections
     * @param {obj} sect
     */
    search_course.prototype.creListSections = function (sect) {

        var info = OCRE.obC02(sect, idCourseNodo);

        $.ajax({
            url: '../../methods/' + info.type + info.ws_url,
            data: info,
            type: 'POST',
            success: function (id_sect) {

            },
            error: function (result, textStatus, errorThrown) {
                var errores = result.status + ' -- ' + result.responseText;
                GSC.error('2', 'creListSections', errores);
            },
            complete: function (json, status) {
                if (status != 'error') {
                    try {
                        if (!isNaN(json.responseText)) {
                            secciones[statePSC] = {
                                idsec_padre: sect.id,
                                idsec_hijo: json.responseText
                            };
                            cantRel = 0;
                            if (sect.activities) {
                                items[statePSC] = items_id_act;
                                secciones[statePSC].activities = actividades;
                                actis = sect.activities;
                                GSC.save_acti(json.responseText);
                                /* GSC.getRubrica(); */

                            } else {
                                statePSC += 1;
                                GSC.save_course();
                            }

                        } else {
                            GSC.error('1', 'creListSections', 'Sección ' + sect.id + ' no crada');
                        }
                    } catch (e) {
                        GSC.error('1', 'creListSections', e);
                    } finally {

                    }
                }
            }
        });
    };
    /*
     * Create Activities = creListActivities
     * @param {obj} acti
     * @param {string} table
     * @param {int} id_sect
     * @param {obj} como
     * @param {int} cA
     */
    search_course.prototype.creListActivities = function (acti, table, id_sect, como, cA, infoma, bankH5P) {
        var info = OCRE.obC03(acti, table, idCourseNodo, como, id_sect, infoma, banckPreguntas, fechas_8o16, bankH5P);

        console.log("Soy la info en creListActivities => " + JSON.stringify(info));

        $.ajax({
            url: '../../methods/' + info.type + info.ws_url,
            data: info,
            type: 'POST',
            success: function (json) {
                console.log("Soy dep 2 => " + JSON.stringify(json));
            },
            error: function (result, textStatus, errorThrown) {
                var errores = result.status + ' -- ' + result.responseText;
                GSC.error('2', 'creListActivities', errores);
            },
            complete: function (result, status) {
                if (status != 'error') {
                    try {
                        var fjson = JSON.parse(result.responseText);
                        fjson.table = table;
                        items_id_act[cantRel] = fjson;
                        actividades[cantRel] = items[statePSC];
                        arrRelation[cantRel] = parseInt(fjson.id_como);
                        cantRel += 1;
                        GSC.save_acti(id_sect);
                        actividades = [];
                        if (cantRel === cA) {
                            statePSC += 1;
                            GSC.creSequence(id_sect, arrRelation);
                        }

                    } catch (e) {
                        GSC.error('1', 'creListActivities' + result.responseText, e);
                    } finally {

                    }
                }
            }

        });
    };


    /*
     * Create Sequence = creSequence
     * @param {int} id_sect
     * @param {array} id_como
     * @returns {undefined}
     */
    search_course.prototype.creSequence = function (id_sect, arrSeq) {
        var info = OUPD.obU04(id_sect, arrSeq);
        $.ajax({
            url: '../../methods/' + info.type + info.ws_url,
            data: info,
            type: 'POST',
            success: function (json) {

            },
            error: function (result, textStatus, errorThrown) {
                GSC.error('2', 'creSequence', result.status);
            },
            complete: function (json, status) {
                if (status != 'error') {
                    try {
                        GSC.save_course();
                        arrRelation = [];
                        cantRel = 0;
                        actividades = [];
                        items_id_act = [];
                    } catch (e) {
                        GSC.error('1', 'creSequence', e);
                    } finally {

                    }
                }
            }
        });
    };
    /*
     * Method para crear la relacion entre las actividades del padre y el hijo, en la taba bc_rel_padre_hijo ->creRelationPH
     */
    search_course.prototype.creRelationPH = function () {

        delete objetAllCourse.demas_info.grade_items;
        var info = OCRE.obC04(idCourseNodo, idCoursePadre, objetAllCourse);
        $.ajax({
            url: '../../methods/' + info.type + info.ws_url,
            data: info,
            type: 'POST',
            success: function (json) {
                console.log("Hola soy el json retorno " + json);
            },
            error: function (result, textStatus, errorThrown) {
                GSC.error('2', 'creRelationPH', result.status);
            },
            complete: function (json, status) {
                if (status != 'error') {
                    //try {
                    document.getElementById('msj_ter').getElementsByTagName('d')[0].style.display = 'block';
                    document.getElementById('porcentaje_mensaje').getElementsByTagName('i')[3].style.display = 'none';
                    $('#region-main').append('<a href="../../../../course/view.php?id=' + idCourseNodo + '" id="boton_volver">Regresar al curso</a>');
                    if (json !== 'null') {
                        /* console.log("Hola soy el JSON 1 =>>>>>" + json); */
                        console.log("Hola soy el JSON 2 =>>>>>" + JSON.stringify(json));
                        /* console.log("Hola soy el JSON 3 =>>>>>" + JSON.parse(json)); */

                        var fjson = JSON.parse(json.responseText);
                        if (fjson.response) {
                            msjBC.informacion('IMPORTACIÓN', fjson.response);
                        } else {
                            msjBC.error('ERROR', 'La relación no se creó');
                        }
                    } else {
                        msjBC.error('ERROR', 'No se Importó');
                    }
                    winPro.close();
                    /*} catch (e) {
                        GSC.error('1','creRelationPH',e);
                    } finally {

                    } */
                }
            }
        });
    };

    /*
     * Eliminar secciones del nodo -> deleteSections
     * @returns {undefined}
     */
    search_course.prototype.deleteSections = function () {

        var dele = ODEL.obD02(idCourseNodo);

        console.log("Estoy en delete sections");

        $.ajax({
            url: '../../methods/' + dele.type + dele.ws_url,
            data: dele,
            type: 'POST',
            success: function (json) {
                //GSC.updateInfoCourse();
            },
            error: function (result, textStatus, errorThrown) {
                GSC.error('2', 'deleteSections', result.status);
            },
            complete: function (json, status) {
                if (status != 'error') {

                    try {
                        var res = parseInt(json.responseText);
                        console.log("Soy el JSON de deleteSections => ", res);
                        if (res == idCourseNodo) {
                            GSC.updateInfoCourse();
                        } else {
                            GSC.error('3', 'deleteSections', 'El curso no se eliminó correctamente. Error' + json.response);
                        }

                    } catch (e) {
                        GSC.error('1', 'deleteSections', e);

                    } finally {

                    }
                }
            }

        });
    };
    /*
     * Limpiar y mostrar los errores en caso de catch o success
     * @returns {undefined}
     */
    search_course.prototype.error = function (num, functi, e) {
        $('#region-main').empty();
        $('#region-main').append(num + ' ' + functi + ': <br><h3>' + num + ' Procesos de la Importación en el curso ' + idCourseNodo + ': falló, vuelva a intentarlo</h3>Error: ' + e);
        GSC.emailError();
    };

    /*
     * Enviar los errorres a soporte
     * @returns {undefined}
     */
    search_course.prototype.emailError = function () {
        $('#region-main').append('<br><a href="javascript:location.reload(true);" id="boton_volver">Volver a intentarlo</a>');
        winPro.close();
        html2canvas($('#region-main'), {
            onrendered: function (canvas) {

                var data = OCRE.obC06(idCourseNodo, idCoursePadre, canvas);
                $.ajax({
                    url: '../../methods/' + data.type + data.ws_url,
                    data: data,
                    type: 'POST',
                    success: function (json) { },
                    error: function (result, textStatus, errorThrown) {
                        //GSC.error('2','deleteSections',result.status);
                    },
                });
            },
        });
    };

    /*
     * Limpiar lista-> borrar
     * @returns {undefined}
     */
    search_course.prototype.borrarList = function () {
        $('#id_search').val('');
        $('#header_list_courses').empty();
        $('#header_list_courses').append('Lista de cursos en el Padre<hr>' +
            '<div class="loader_list_courses"></div>' +
            '<div class="list_courses_vacia">Busque un curso</div>' +
            '<div id="list_courses_padre"> </div>');
        document.getElementById("frame_padre") ? document.getElementById("frame_padre").style.display = "none" : '';
    };
    $("#id_search").keypress(function (e) {
        var keycode = (e.keyCode ? e.keyCode : e.which);
        if (keycode == '13') {
            document.getElementById('search_course_in_p').click();
            e.preventDefault();
            return false;
        }
    });

    /*Eventos de los botones*/
    $("#search_course_in_p").click(function () {
        if ($('#id_search').val().length > 2) {
            GSC.getlistaCourses();
        } else {
            msjBC.informacion('INFORMACIÓN', 'Debe digitar 3 o más caractéres');
        }
    });
    /*
     * Ventana de díalogo
     * @returns {undefined}
     */
    search_course.prototype.window_porcent = function () {
        winPro = $.dialog({
            icon: 'fa fa-fw fa-circle-o-notch',
            title: 'Preparando!',
            closeIcon: false,
            content: '<span class="avance_curso"> Avance del curso ' + jObjCoursePadre.shortname + ' para ' + fechas_8o16 + ' semanas</span><br>' +
                '<span class="porcentaje" id="porcentaje">0%</span>' +
                '<div class="progress-striped">' +
                '<div class="bar" id="porcentaje_barra"></div>' +
                '<div class="" id="">El proceso puede tardar varios minutos....</div>' +
                '</div>',
            action: GSC.getlistaSections()
        });

    };
    $("#cancelar_busqueda").click(function () {
        GSC.borrarList();
    });

    search_course.prototype.templatePreview = function (data, name, short) {

        let datos = data.response;

        let url = data.url_actual;

        const titulo = document.getElementById('head_view_course');

        titulo.innerHTML = 'Vista previa - ' + name + ' - ' + short;

        let html = '<div class="accordion py-5 px-5" id="contenido_curso_acc" style="max-width: 100%; overflow-x: auto;">';

        datos = JSON.parse(datos);

        if (Array.isArray(datos)) {

            datos.forEach((item, index) => {
                html += `
                <div class="card">
                        <div class="card-header" id="heading${index}">
                            <h2 class="mb-0">
                                <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapse${index}" aria-expanded="true" aria-controls="collapse${index}">
                                `;

                let section_name = '';
                if (Object.keys(item).length !== 0) {
                    for (const key in item) {

                        if (item.hasOwnProperty(key)) {

                            section_name = item[key].section_name;

                        }
                    }
                } else {
                    section_name = 'Tema ' + index;
                }

                html += `
                ${section_name}
                </button>
                            </h2>
                        </div>
                        <div id="collapse${index}" class="collapse show" aria-labelledby="heading${index}" data-parent="#contenido_curso_acc">
                            <div class="card-body">`;
                if (Object.keys(item).length !== 0) {  // Verificar si el objeto no está vacío

                    for (const key in item) {
                        if (item.hasOwnProperty(key)) {
                            html +=
                                `<div class="col-md-12 d-flex p-3">
                                    <div class="optionicon mt-2 mb-1 icon-no-margin modicon_${item[key].module_name} activityiconcontainer ${item[key].purposeclass}  ">
                                        <img class="icon icon activityicon" style="width: 30px; height: 30px" src="${url + '/mod/' + item[key].module_name + '/pix/monologo.svg'}" alt="Imagen">
                                    </div>
                                    <div class="ml-2 p-2">
                                        <div>${item[key].nombre_actividad}</div>
                                        <div>${item[key].activity_name}</div>
                                    </div>
                                </div>`;
                        }
                    }

                } else {
                    html += `<h3>Sin actividades</h3>`;
                }

                html += `
                    </div>
                </div>
            </div>`;

            });

        } else {
            console.error('The response data is not an array.');
        }

        html += `</div>`;

        return html;

    }

});
/*Alertas para confirmar*/
var conFir = {
    /*
     * Confirmar importación
     * @param {int} id_nodo
     * @param {int} posi
     */
    confirmar: function (id_nodo, posi) {
        idCoursePadre = parseInt(jObjCourse[posi].id);
        jObjCoursePadre = jObjCourse[posi];
        idCourseNodo = id_nodo;
        var script = document.createElement('script');
        script.onload = function () {
            var script_confirm = document.createElement('script');
            script_confirm.onload = function () {
                $.confirm({
                    title: 'IMPORTAR',
                    content: 'Tenga en cuenta, que se <b>BORRARÁ</b> la información del curso actual y será remplazada por la información de la importación <br><br>' +
                        '<b style="font-size: 17px">¿DESEA IMPORTAR ESTE CURSO PARA?</b><br>' +
                        '<input type="radio" name="semanas" value="8" id="semanas_8"> 8 semanas<br>' +
                        '<input type="radio" name="semanas" value="16" id="semanas_16" checked> 16 semanas<br>',
                    icon: 'fa fa-question',
                    theme: 'modern',
                    closeIcon: true,
                    animation: 'scale',
                    type: 'red',
                    buttons: {
                        ocho_semanas: {
                            text: 'Importar',
                            btnClass: 'btn-blue',
                            action: function () {
                                fechas_8o16 = (document.getElementById('semanas_8').checked == true) ? 8 : 16;
                                GSC.window_porcent();
                            }
                        },
                        cancel: {
                            btnClass: 'btn-red',
                            text: 'Cancelar'
                        }
                    }
                });
            };
            script_confirm.src = "../../js/jquery-confirm.js";
            document.getElementsByTagName('head')[0].appendChild(script_confirm);
        };
        script.src = "../../../../lib/jquery/jquery-3.6.1.js";
        document.getElementsByTagName('head')[0].appendChild(script);
    }

};