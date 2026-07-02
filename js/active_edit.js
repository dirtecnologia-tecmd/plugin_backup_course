/*
 * @PARAMS
 * @innovame 
 * setting elements for editing dates
 * @autor Daniela Sierra Vergel
 */

/*
 * getElementsByClassName(activityinstance)
 */

var actAll = [];
var act = ['attendance', 'assign', 'choice', 'choicegroup', 'data', 'feedback', 'forum', 'glossary', 'groupselect', 'lesson', 'quiz', 'workshop', 'scorm', 'h5pactivity', 'lti', 'customcert', 'folder', 'wiki'];
var elem = null;
var foru = null;
var edit_activo;
var permitir_category = null; //saber si mostrar añadir actividaddes
var course_id = null;
var section_new = null;
var tipo_permiso = 0; //0 desbloqueado, 1=parcial, 2=sin permisos
var pemReemplazar = 0;

function Innovame() {
    this.cantidad = act.length;
}
/*
 * Llamar jquery si no está
 */
Innovame.prototype.llamar_jqry = function () {
    var head_jq = document.getElementsByTagName('head')[0];
    var esta = 0;
    for (var q = 0; q < head_jq.getElementsByTagName('script').length; q++) {
        if (head_jq.getElementsByTagName('script')[q].src.indexOf('jquery-3.6.0.js') != -1) {
            esta = 1
        };
    }
    if (esta == 0) {
        var script_jqu = document.createElement('script');
        script_jqu.onload = function () {

        };
        script_jqu.src = "../local/backup_course/js/jquery.js";
        head_jq.appendChild(script_jqu);
    }
}

/*
 * No permitir la edición para el botón de editar
 * @param {type} edit
 * @returns {undefined}
 */
Innovame.prototype.no_edit = function (Y, edit = 0, add_category = 0) {

    if ((typeof $) != 'undefined') {
        permitir_category = add_category;
        if (!document.getElementById("overlay-loader_block") && document.getElementById('page-content')) {
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
        document.getElementById("overlay-loader_block").style.display = "block";
        //console.log("overlay-loader_block",'71')

        var a_tag = document.getElementById('page-content').getElementsByTagName('a');
        for (var a = 0; a < a_tag.length; a++) {
            if (a_tag[a] && a_tag[a].href && a_tag[a].href.indexOf('/course/modedit.php') != -1) {
                a_tag[a].parentElement.style.display = 'block';
            } //ver la edición de la actividad
            else if (a_tag[a] && a_tag[a].href && a_tag[a].href.indexOf('/question/edit.php') != -1) {
                /* a_tag[a].parentElement.style.display = 'none'; */
            } //ver edición de quices
            else if (a_tag[a] && a_tag[a].href && a_tag[a].href.indexOf('/question/category.php') != -1) {
                a_tag[a].parentElement.style.display = 'none';
            } //ver categorías del banco
            else if (a_tag[a] && a_tag[a].href && a_tag[a].href.indexOf('/question/import.php') != -1) {
                a_tag[a].parentElement.style.display = 'none';
            } //ver importar del banco
            else if (a_tag[a] && a_tag[a].href && a_tag[a].href.indexOf('/question/export.php') != -1) {
                a_tag[a].parentElement.style.display = 'none';
            } //ver exportar del banco
            else if (a_tag[a] && a_tag[a].href && a_tag[a].href.indexOf('/mod/quiz/edit.php') != -1) {
                a_tag[a].parentElement.style.display = 'none';
            } //ver banco de preguntas
            else if (a_tag[a] && a_tag[a].href && a_tag[a].href.indexOf('grade/edit/tree/index.php') != -1) {
                var hre = a_tag[a].href;
                var newHref = hre.replace("grade/edit/tree/index.php", "local/backup_course/layout/index.php");
                a_tag[a].href = newHref;
            }
        }

        setTimeout(function () { //formato de curso uvd
            document.getElementById("overlay-loader_block").style.display = "block";
            //console.log("overlay-loader_block",'101')
            var format_uvd = document.getElementById('tabs-format-course');
            if (format_uvd && format_uvd.getElementsByTagName('li')) {
                if (edit == 0) {
                    var tg_li = format_uvd.getElementsByTagName('li');
                    for (var li = 0; li < tg_li.length; li++) {
                        document.getElementById("overlay-loader_block").style.display = "block";
                        var on = format_uvd.getElementsByTagName('li')[li].getAttribute('onclick');
                        if (on.indexOf('inn.no_edit') == -1) {
                            format_uvd.getElementsByTagName('li')[li].setAttribute('onclick', on + '; setTimeout(function(){inn.no_edit(0,1);}, 800);');
                        }
                    }
                    inn.getTags(0, edit_activo, pemReemplazar, tipo_permiso);
                }
            }
            inn.hideEdit(); //tags edit


        }, 1500);
    }
};
/*
 * Permitir que el profesor añada actividades solo a la categoría act_extra
 * @param {type} Y
 * @returns {undefined}
 */
Innovame.prototype.category_act_extra = function (Y, add_category) {
    permitir_category = add_category
    let sel_category = document.getElementById('id_gradecat');
    let sel_category_taller = document.getElementById('id_gradecategory');
    let sel_category_taller2 = document.getElementById('id_gradinggradecategory');

    if (sel_category_taller2 && add_category) {
        let idcat = add_category.id;
        sel_category_taller2.innerHTML = '<option value="' + idcat + '">' + add_category.fullname + '</option>';
        sel_category_taller2.value = idcat;
    }
    if (sel_category_taller && add_category) {
        let idcat = add_category.id;
        sel_category_taller.innerHTML = '<option value="' + idcat + '">' + add_category.fullname + '</option>';
        sel_category_taller.value = idcat;
    }
    if (sel_category && add_category) {
        let idcat = add_category.id;
        sel_category.innerHTML = '<option value="' + idcat + '">' + add_category.fullname + '</option>';
        sel_category.value = idcat;
    }

};

Innovame.prototype.list_actAll = function (Y, ac) { actAll = ac; };

Innovame.prototype.cerrarLoader = function () {
    $(document).ready(function () {
        if (document.querySelector("#overlay-loader_block")) {
            setTimeout(function () {
                document.querySelector("#overlay-loader_block").style.display = "none";
            }, 1500);

        }
    });
};



/*
 * Mostrar boton Editor para editingteacher en cursos
 */
Innovame.prototype.showBtnEditor = function (Y, url) {
    $(document).ready(function () {
        let pathbtn = url + "/local/editor_uniminuto/templates/actividades-proponente/list.php";
        let iconbtn = url + "/theme/uniminuto/pix/editor.svg";
        let htmlbtn = '<div class="d-none d-md-block align-self-center mr-3"><a href="' + pathbtn + '" class="btn link-editor" title="Editor" aria-expanded="false"><img src="' + iconbtn + '" alt="Icono Editor"></a></div>';
        const containerMain = document.querySelector(".main-header__bottom .navbar-nav.ml-auto.flex-md-row.flex-column");
        containerMain.insertAdjacentHTML('afterbegin', htmlbtn);
    });
};

/*
 * No Permitir que el profesor modifique el libro de calificaciones
 * @param {type} Y
 * @returns {undefined}
 */
Innovame.prototype.no_edit_calificaciones = function (Y) {
    this.no_edit(Y);
    let table = document.getElementById('grade_edit_tree_table');
    if (table) {// Verificar si la tabla existe
        let cells = table.querySelectorAll('.column-actions'); //Todos los elementos td con la clase .column-actions     
        cells.forEach(function (cell) { cell.parentNode.removeChild(cell); });// Iterar sobre los elementos td y eliminarlos
        let mov = table.querySelectorAll('.action-icon'); //Todos los elementos de mover las actividades     
        mov.forEach(function (mov) { mov.parentNode.removeChild(mov); });// Iterar sobre los elementos y eliminarlos
    }
    let btns = document.getElementById('gradetreesubmit');
    if (btns) btns.parentNode.removeChild(btns);

};

/*
 * No Permitir que el profesor modifique las configuraciones del curso
 * @param {type} Y
 * @returns {undefined}
 */
Innovame.prototype.no_edit_config = function (Y) {
    this.no_edit(Y);

    let btns = document.getElementById('fgroup_id_buttonar');
    if (btns) btns.parentNode.removeChild(btns);

};



/*
 * Permitir que el profesor agrege una sección de penúltimo
 * @param {type} Y
 * @returns {undefined}
 */
Innovame.prototype.add_section_penultimo = function (Y, id_sect_penul, courseid) {
    setTimeout(function () {
        let tg_add_sec = document.getElementsByClassName('add-sections'); //botones de añadir seccion
        let cant_sec = tg_add_sec.length;
        if (tg_add_sec[0]) {
            if (cant_sec == 1) {
                tg_add_sec[0].setAttribute("data-id", id_sect_penul);
                tg_add_sec[0].setAttribute("onclick", 'inn.save_section_penultimo(' + courseid + ')');
            } else {
                for (let lo = 0; lo < tg_add_sec.length; lo++) {
                    document.getElementById("overlay-loader_block").style.display = "block";
                    let penultimo = tg_add_sec.length - 2;
                    if (lo != penultimo) { //dejar añadir sección en el penúltimo
                        tg_add_sec[lo] ? tg_add_sec[lo].innerHTML = '' : '';
                        tg_add_sec[lo] ? tg_add_sec[lo].style.display = "none" : '';
                    } else {
                        tg_add_sec[penultimo].setAttribute("data-id", id_sect_penul);
                        tg_add_sec[penultimo].setAttribute("onclick", 'inn.save_section_penultimo(' + courseid + ')');
                    }
                }
            }
        }

    }, 1200);
}

Innovame.prototype.save_section_penultimo = function (courseid) {
    $.ajax({
        url: '../local/backup_course/methods/CRE/class.create.php',
        data: {
            key: 'C07',
            courseid: courseid
        },
        type: 'POST',
        success: function (json) {
            location.reload();
        }
    });
}


/*
 * Permitir que el profesor agrege una actividad por sección
 * @param {type} Y
 * @returns {undefined}
 */
Innovame.prototype.add_activity_section = function (Y, json_activ) {
    let tg_add_act = document.getElementsByClassName('section-modchooser-link'); //botones de añadir actividad

    for (let lo = 0; lo < tg_add_act.length; lo++) {
        if (tg_add_act && tg_add_act[lo]) {

            // Crear un nuevo botón para el banco
            let newBtn = document.createElement('a');
            newBtn.innerHTML = '<i class="icon fa fa-folder-open"></i> Traer una actividad o recurso del banco'; // Ícono + Texto del botón
            newBtn.className = 'col-md-6 btn btn-link text-decoration-none  activity-add d-flex align-items-center p-3 mb-3';
            newBtn.href = 'javascript:CallWindow("../local/backup_course/bank/importar.php?section=' + lo + '&courseid=' + course_id + '","Añadir una actividad o un recurso")'; // Corrección de la ruta
            newBtn.style.cssText = 'max-width: 48%; margin-left: 1%; margin-top: 4px; float:left; padding: 1.4rem !important;';

            tg_add_act[lo].style.cssText = 'max-width: 48%; margin-left: 1%; float:left;'; //hacer espacio al nuevo btn
            tg_add_act[lo].parentNode.insertBefore(newBtn, tg_add_act[lo].nextSibling);// insertar nuebo btn despues del tg_add_act

            // Añadir el evento click al botón original
            $(tg_add_act[lo]).click(function () { inn.onclick_ventana_add(lo); });
        }

    }


}

/*
 * NO Permitir que el profesor agrege una sección ni actividades
 * @param {type} Y
 * @returns {undefined}
 */
Innovame.prototype.NO_add_sect_actividad = function (Y, state) {
    let tg_add_act = document.getElementsByClassName('section-modchooser-link'); //botones de añadir actividad
    for (let lo = 0; lo < tg_add_act.length; lo++) {
        $(tg_add_act[lo]).click(function () { inn.onclick_ventana_add(lo); });
    }
    inn.NO_add_sections(Y, state);
}

Innovame.prototype.NO_add_sections = function (Y, state) {
    setTimeout(function () {
        let tg_add_sec = document.getElementsByClassName('add-sections'); //botones de añadir seccion
        for (let i = 0; i < tg_add_sec.length; i++) {
            tg_add_sec[i] ? tg_add_sec[i].innerHTML = '' : '';
            if (i == tg_add_sec.length - 1 && tg_add_sec[i]) {
                var nuevoDiv = document.createElement('div');
                nuevoDiv.innerHTML = state;
                nuevoDiv.classList.add('badge', 'badge-secondary');
                tg_add_sec[i].parentNode.replaceChild(nuevoDiv, tg_add_sec[i]);
            } else tg_add_sec[i] ? tg_add_sec[i].style.display = "none" : '';
        }
    }, 1200);
}

/*
 * Mostrar overlay
 * @returns {undefined}
 */
Innovame.prototype.getTags = function (Y, edit_act, course_id, perReem, permiso) {
    pemReemplazar = perReem;
    tipo_permiso = permiso;
    edit_activo = edit_act;
    if ((typeof $) != 'undefined') {
        $(document).ready(function () {
            if (!document.getElementById("overlay-loader_block") && document.getElementById('page-content')) {
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
            document.getElementById("overlay-loader_block").style.display = "block";
            //console.log("overlay-loader_block",'322')
            inn.getCorrer();
        });
    }
    inn.get_section_new(course_id);
    inn.btnDelete();
};
var elem;
var foru;

/*
 * Contar elementos para reemplazar boton de edición
 * @returns {undefined}
 */
Innovame.prototype.getCorrer = function (edit = 0) {
    document.getElementById("overlay-loader_block").style.display = "block";
    //console.log("overlay-loader_block",'338')
    setTimeout(function () {
        var script_confirm = document.createElement('script');
        script_confirm.onload = function () {
            var script_bootstrap = document.createElement('script');
            script_bootstrap.onload = function () {
                this.pathCourse = window.location.href;
                this.idCourse = this.pathCourse.split('=');
                /* elem = (document.getElementById('tabs-format-course') ? document.getElementsByClassName('list_format') : document.getElementsByClassName('activityinstance')); */
                elem = document.querySelectorAll('.activity-item a.aalink.stretched-link');
                for (var i = 0; i < elem.length; i++) {
                    inn.getLinks(i, parseInt(this.idCourse[1]));
                }
                var format_uvd = document.getElementById('tabs-format-course');
                if (format_uvd && format_uvd.getElementsByTagName('li')) {
                    if (edit == 0) {
                        var tg_li = format_uvd.getElementsByTagName('li');
                        for (var li = 0; li < tg_li.length; li++) {
                            document.getElementById("overlay-loader_block").style.display = "block";
                            var on = format_uvd.getElementsByTagName('li')[li].getAttribute('onclick');
                            if (on.indexOf('inn.getCorrer') == -1) {
                                format_uvd.getElementsByTagName('li')[li].setAttribute('onclick', on + '; setTimeout(function(){inn.getCorrer(1);}, 800);');
                            }
                        }
                    }
                }
            };
            /* script_bootstrap.src = "https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js"; */
            script_bootstrap.src = "https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js";
            document.getElementsByTagName('head')[0].appendChild(script_bootstrap);
        };

        /* script_confirm.src = "https://code.jquery.com/jquery-3.7.0.min.js"; */
        script_confirm.src = "https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js";
        document.getElementsByTagName('head')[0].appendChild(script_confirm);

        document.getElementById("overlay-loader_block").style.display = "none";
    }, 3000);

};
Innovame.prototype.getLinks = function (pos, idC) {
    document.getElementById("overlay-loader_block").style.display = "block";
    this.posicion = pos;
    var proponA = document.createElement('a');
    proponA.className = (document.getElementById('tabs-format-course') ? 'tagA_uvd reempla_act' : 'tagA ta');
    var linkA = document.createElement('a');

    linkA.style.backgroundImage = "url('../local/backup_course/pix/edit.png')";
    linkA.setAttribute("data-toggle", "tooltip");
    linkA.setAttribute("data-placement", "left");
    linkA.setAttribute("title", "Editar Ajustes de la actividad");
    linkA.className = (document.getElementById('tabs-format-course') ? 'tagA_uvd' : 'tagA');

    var tagAOk = elem[this.posicion];
    var tagsA = tagAOk;
    const activity_items = document.querySelectorAll('.activity-item a.aalink.stretched-link');
    console.log("Hola soy los tagsA ===> " + tagsA);
    this.getRes = inn.getPath(tagsA.href);
    if (this.getRes[0] && this.getRes[0] == true) { //otro formato de curso
        var partLink = tagsA.href.split('?');
        var partId = partLink[1] ? partLink[1].split('=') : [0, 0];
        var pathFrame = "../local/backup_course/layout/" + this.getRes[1] + ".php?id_act=" + partId[1] + "&id_curso=" + idC;
        linkA.href = 'javascript:CallWindow("' + pathFrame + '")';
        linkA.id = "id_edit_activ_" + partId[1];
        var tagA_uvd = tagAOk.getElementsByClassName('tagA_uvd');
        if (typeof tagA_uvd[0] == 'undefined' && edit_activo == 0) {
            const links = document.querySelectorAll('.activity-item a.aalink.stretched-link');
            const parent = links[this.posicion].closest('.activity-item'); // Encuentra el contenedor padre más cercano
            if (parent) {
                parent.appendChild(linkA); // Agregar el nuevo elemento al contenedor padre
            }
        }
        var reempla_act = tagAOk.getElementsByClassName('reempla_act');
        console.log("getRes => ", this.getRes);
        if (typeof reempla_act[0] == 'undefined') {
            if (this.getRes[1] == 'assign' || this.getRes[1] == 'forum' || this.getRes[1] == 'wiki' || this.getRes[1] == 'workshop' || this.getRes[1] == 'glossary' || this.getRes[1] == 'quiz' || this.getRes[1] == 'lesson' || this.getRes[1] == 'h5pactivity') {
                proponA.id = 'id_assign_reem_' + partId[1];
                inn.gettype_activity(partId[1], idC, this.getRes[1]);
                const links = document.querySelectorAll('.activity-item a.aalink.stretched-link');
                const parent = links[this.posicion].closest('.activity-item'); // Encuentra el contenedor padre más cercano
                if (parent) {
                    parent.appendChild(proponA); // Agregar el nuevo elemento al contenedor padre
                }
            }
        }

    } else if (tagsA.href.indexOf('javascript:ef.open_modal') !== -1) { // formato de curso uvd
        var partLink = tagsA.href.split("','");
        var partId = partLink[1];
        this.getResUVD = inn.getPathUVD(partLink[0]);
        if (this.getResUVD[0] == true) {
            var pathFrame = "../local/backup_course/layout/" + this.getResUVD[1] + ".php?id_act=" + partId + "&id_curso=" + idC;
            linkA.href = 'javascript:CallWindow("' + pathFrame + '")';
            linkA.id = "id_edit_activ_" + partId;
            var tagA_uvd = tagAOk.getElementsByClassName('tagA_uvd');
            if (typeof tagA_uvd[0] == 'undefined' && edit_activo == 0) {
                const links = document.querySelectorAll('.activity-item a.aalink.stretched-link');
                const parent = links[this.posicion].closest('.activity-item'); // Encuentra el contenedor padre más cercano
                if (parent) {
                    parent.appendChild(linkA); // Agregar el nuevo elemento al contenedor padre
                }
            }

            var reempla_act = tagAOk.getElementsByClassName('reempla_act');
            if (typeof reempla_act[0] == 'undefined') {
                if (this.getResUVD[1] == 'assign' || this.getResUVD[1] == 'forum' || this.getRes[1] == 'wiki' || this.getRes[1] == 'workshop' || this.getRes[1] == 'glossary' || this.getRes[1] == 'quiz' || this.getRes[1] == 'lesson' || this.getRes[1] == 'h5pactivity') {
                    proponA.id = 'id_assign_reem_' + partId;
                    inn.gettype_activity(partId, idC, this.getResUVD[1]);
                    const links = document.querySelectorAll('.activity-item a.aalink.stretched-link');
                    const parent = links[this.posicion].closest('.activity-item'); // Encuentra el contenedor padre más cercano
                    if (parent) {
                        parent.appendChild(proponA); // Agregar el nuevo elemento al contenedor padre
                    }
                }
            }

        }
    }
    
    document.getElementById("overlay-loader_block").style.display = "none";
};

Innovame.prototype.ocultar_elementos_atendance = function (indice, id_curso) {
    course_id = id_curso;
    // Obtenemos la URL actual
    let urlActual = window.location.href;

    let cadenaComparar1 = "/mod/attendance/manage.php?";
    let cadenaComparar2 = "/mod/attendance/sessions.php";
    let cadenaComparar3 = "/course/modedit.php";

    // Comparamos la URL actual con la cadena de texto
    if (urlActual.indexOf(cadenaComparar1) !== -1 || urlActual.indexOf(cadenaComparar2) !== -1 || urlActual.indexOf(cadenaComparar3) !== -1) {
        const estilo = document.createElement('style');
        estilo.textContent = `
        .navbar.navbar-fixed-top.moodle-has-zindex, .fixed-top.navbar.navbar-light, .action-menu.moodle-actionmenu.d-inline , .desktop-first-column.block-region, #course-header, #id_submissiontypes,  #id_submissionsettings, 
        #id_groupsubmissionsettings, #block-region-side-pre ,#page-footer, #id_notifications, #id_modstandardgrade, #id_tagshdr, #id_competenciessection,#id_availabilityconditionsheader, 
        #id_activitycompletionheader, #fitem_id_restrictgroupbutton, #id_flowcontrol,
        #nav-drawer, .mt-5.mb-1.activity-navigation, #soporte-bar, #fitem_id_groupmode, #page-navbar,
        #id_entrieshdr, #id_appearancehdr, #id_entrieshdr, #fitem_id_rolewarning, #fitem_id_assessed, #fgroup_id_scale,
        #fitem_id_cmidnumber, #fitem_id_groupingid, #fitem_id_timelimit, #id_security{
            display: none !important;
        }
        #region-main-course-format{
            width: 100%!important}
            body.drawer-open-left {
                margin-left: 0 !important;
            }

        .secondary-navigation{
            display:block !important;
        }    

        .nav-item.dropdown.dropdownmoremenu{
            display: none;
        }
        `;

        document.head.appendChild(estilo);

        // Si coinciden, generamos un alert
        document.getElementById("overlay-loader_block").style.display = "block";
        //console.log("overlay-loader_block",'487')
        document.getElementsByTagName("header") && document.getElementsByTagName("header")[0] ? document.getElementsByTagName("header")[0].style.display = "none" : "";
        document.getElementsByTagName("footer") && document.getElementsByTagName("footer")[0] ? document.getElementsByTagName("footer")[0].style.display = "none" : "";
        document.getElementById("overlay-loader_block").style.display = "none";
        const navItem = document.querySelectorAll("li.nav-item>a.nav-link");
        if (navItem != null) {
            navItem.forEach(element => {
                const tag = element.href;
                if (tag.indexOf("/course/modedit.php?update") != -1) {
                    // Obtener la URL actual
                    const urlParams = new URLSearchParams(window.location.search);

                    // Obtener el valor del parámetro 
                    const id = urlParams.get("id");

                    // Modificar la URL
                    element.href = tag.replace("/course/modedit.php?update=" + id + "&return=1", "/local/backup_course/layout/attendance.php?id_act=" + id + "&id_curso=" + id_curso);

                    element.style.display = "flex";

                }
                if (tag.indexOf("/attendance/report.php?") != -1) {
                    element.remove();
                }
                if (tag.indexOf("/attendance/import.php?") != -1) {
                    element.remove();
                }
                if (tag.indexOf("/attendance/export.php?") != -1) {
                    element.remove();
                }
                if (tag.indexOf("/attendance/view.php?") != -1) {

                    const opcionesAssitencia = document.querySelectorAll('.cell.c5.lastcol>a');

                    opcionesAssitencia.forEach((element, index) => {

                        if (index % 3 === 0) { // Comprobar si es el primer elemento de cada grupo de tres
                            const tomarAsistencia = element;
                            // Modificar la URL
                            tomarAsistencia.href = tomarAsistencia.href.replace("/mod/attendance/take.php", "/local/backup_course/layout/take.php");
                        } else if (index % 3 === 1) {
                            const tomarAsistencia = element;
                            // Modificar la URL
                            tomarAsistencia.href = tomarAsistencia.href.replace("/mod/attendance/sessions.php", "/local/backup_course/layout/sessions.php");
                        } else if (index % 3 === 2) {
                            const tomarAsistencia = element;
                            // Modificar la URL
                            tomarAsistencia.href = tomarAsistencia.href.replace("/mod/attendance/sessions.php", "/local/backup_course/layout/sessions.php");
                        }

                    });

                    /*const tomarAsistencia = opcionesAssitencia[0];
                      // Modificar la URL
                      tomarAsistencia.href = tomarAsistencia.href.replace("/mod/attendance/take.php", "/local/backup_course/layout/take.php");
                     */

                }
            });
        }

        /*         const boton2 = document.getElementById('id_submitbutton2');
        
                if (boton2 != undefined) {
                    boton2.addEventListener('click', function () {
                        window.parent.location.reload();
                    });
                } */

    }

}




/*
 * 
 * @param {type} tagA
 * @returns {Array}
 */
Innovame.prototype.onclick_ventana_add = function (section) {
    document.getElementById('overlay-loader_block').style.display = 'block';
    //console.log("overlay-loader_block",'570')
    const urlActual = window.location;

    let path = urlActual.pathname.split('/');

    let newUrl = "";

    if (path[1] !== 'course' && path[1] !== 'local' && path[1] !== 'mod') {
        newUrl = urlActual.origin + '/' + path[1] + '/local/backup_course/methods/QRY/class.query.php';

    } else {
        newUrl = urlActual.origin + '/local/backup_course/methods/QRY/class.query.php';
    }
    newUrl = '../local/backup_course/methods/QRY/class.query.php'

    $.ajax({

        url: newUrl,

        data: {
            key: 'Q11',
            courseid: course_id,
            section: section
        },
        type: 'POST',
        success: function (json) {
            let datas = JSON.parse(json);
            let data = datas[0]
            let cantResourse = datas[1];
            let cantActivities = datas[2];
            let url = 0, page = 0;
            let contadorAct = {};
            for (const i of actAll) contadorAct[i] = 0; //iniciar contador por cada tipo de actividad
            for (const i in data) {
                let other = JSON.parse(data[i].other);
                if (other != null && other.modulename === 'url') url++;
                if (other != null && other.modulename === 'page') page++;
                if (actAll.indexOf(other.modulename) > -1 && (other.modulename != 'url' && other.modulename && 'page')) contadorAct[other.modulename]++; //sumarle al tipo de actividad

            }
            setTimeout(function () {
                document.getElementById('overlay-loader_block').style.display = 'block';
                var optionscontainer = document.getElementsByClassName('modchoosercontainer d-flex flex-column flex-fill');
                if (optionscontainer && optionscontainer[0]) {
                    //menuitem
                    var op_par = optionscontainer[0];
                    var option = op_par.getElementsByClassName('option');
                    var ln = parseInt(option.length);
                    var arr = [];

                    let sections = document.getElementsByClassName('section course-section');

                    for (var i = 0; i < ln; i++) {
                        document.getElementById('overlay-loader_block').style.display = 'block';
                        var nomActivi = option[i].getAttribute("data-internal");
                        if ((nomActivi.indexOf('url') === -1 || url >= cantResourse)   //permitir url hasta la cantidad permitida
                            && (nomActivi.indexOf('page') === -1 || page >= cantResourse)) //permitir page
                        { arr.push(option[i]); } //actividades a quitar

                    }
                    var aEliminar = []
                    for (var k in arr) {//recorrer las actividades a quitar para determinar si se pueden dejar
                        document.getElementById('overlay-loader_block').style.display = 'block';
                        //sección nueva
                        if (!permitir_category) {// existe la categoria 
                            var canTviejas = sections.length - section_new
                            //es una sección nueva
                            if (section_new > 0 && section >= canTviejas) {
                                var nomActivi = arr[k].getAttribute("data-internal");
                                if (nomActivi == 'url' || nomActivi == 'page') { } //no quitar si es un recurso en sección nueva
                                else if (contadorAct[nomActivi] < cantActivities) { } // no quitar si aun NO se llegó a la cantidad permitida de actividades
                                else { aEliminar.push(arr[k]); } //remover del listado a quitar
                            } else { aEliminar.push(arr[k]) } //remover del listado a quitar
                        } else { aEliminar.push(arr[k]) } //remover del listado a quitar
                    }
                    inn.add_newAct_bank(aEliminar);
                } else
                    document.getElementById('overlay-loader_block').style.display = 'none';
            }, 2500);
        }
    });

    Innovame.prototype.add_newAct_bank = function (aEliminar) {
        document.getElementById('overlay-loader_block').style.display = 'block';
        setTimeout(function () {
            var optionscontainer = document.getElementsByClassName('modchoosercontainer d-flex flex-column flex-fill');
            if (optionscontainer && optionscontainer[0]) {
                var op_par = optionscontainer[0];
                var option = op_par.getElementsByClassName('option');
                for (var k = 0; k < option.length; k++) {
                    document.getElementById('overlay-loader_block').style.display = 'block';
                    if (option && option[k]) {
                        var enlace = option[k].getElementsByTagName('a');
                        if (enlace && enlace[0]) enlace[0].href = enlace[0].href.replace('/course/mod.php', '/local/backup_course/bank/mod.php');

                    }
                }
                for (var k in aEliminar) aEliminar[k].remove();
                $('.searchbar.input-group').remove();
                document.getElementById('overlay-loader_block').style.display = 'none';
            } else
                document.getElementById('overlay-loader_block').style.display = 'none';
        }, 1500);
    }



};

function getMisActividades() {
    const urlActual = window.location;
    let path = urlActual.pathname.split('/');
    let newUrl = "";

    if (path[1] !== 'course' && path[1] !== 'local' && path[1] !== 'mod') {

        newUrl = urlActual.origin + '/' + path[1] + '/local/backup_course/methods/QRY/class.query.php';

    } else {

        newUrl = urlActual.origin + '/local/backup_course/methods/QRY/class.query.php';
    }
    newUrl = '../local/backup_course/methods/QRY/class.query.php'

    $.ajax({

        url: newUrl,
        data: {
            key: 'Q12',
            courseid: course_id,
        },
        type: 'POST',
        success: function (json) {
            let datas = JSON.parse(json);
            let data = datas[0];

            inn.editBotones(data);
        }
    });
}

/**
 * @param String name
 * @return String
 */
function getParameterByName(name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
        results = regex.exec(location.search);
    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}


Innovame.prototype.getPath = function (tagA) {
    this.tagA = tagA;
    this.arr_resp = new Array();
    this.getParts = this.tagA.split('/');
    for (var k = 0; k < this.getParts.length; k++) {
        for (var l = 0; l < this.cantidad; l++) {
            if (act[l] == this.getParts[k]) {
                this.arr_resp[0] = true;
                this.arr_resp[1] = act[l];
            }
        }
    }
    return this.arr_resp;
};

Innovame.prototype.getPathUVD = function (tagA) {
    this.tagA = tagA;
    this.arr_respUVD = new Array();
    var activity = this.tagA.split("('");
    for (var l = 0; l < act.length; l++) {
        if (act[l] === activity[1]) {
            this.arr_respUVD[0] = true;
            this.arr_respUVD[1] = act[l];
        }
    }
    return this.arr_respUVD;
};

/*
 * consultar el tipo de actividad (evaluativa o comprensión) y saber la sección
 * @param {int} id_act
 * @param {int} idC
 * @returns {undefined}
 */
Innovame.prototype.gettype_activity = function (id_act, idC, nombre_modulo) {
    idC = parseInt(idC);
    id_act = parseInt(id_act);
    $.ajax({
        url: '../local/backup_course/methods/QRY/class.query.php',
        data: {
            key: 'Q08',
            id_cm: id_act,
            courseid: idC,
            nombre_modulo: nombre_modulo
        },
        type: 'POST',
        success: function (json) {
            let as = document.getElementById('id_assign_reem_' + id_act);
            //console.log('as',as)
            if (json !== 'null' && as) {
                let fjson = JSON.parse(json);

                as.setAttribute("data-toggle", "tooltip");
                as.setAttribute("data-placement", "left");
                if (fjson.estado == 'No') { // no existe el plugin de activities

                } else {
                    if (fjson.sect > 0 && pemReemplazar != 0) {
                        if (/* fjson.estado == 0 ||  */!fjson.estado) {


                            as.setAttribute("style", "background-image: url('../local/backup_course/pix/replace.png')");
                            if (fjson.activemethod == "rubric") {
                                as.setAttribute("title", "Reemplazar actividad evaluativa");
                                //as.innerHTML = 'Reemplazar actividad evaluativa';
                                as.href = '../local/editor_uniminuto/templates/proponer-actividad/index.php?courseid=' + idC + '&id_mod=' + encodeURIComponent(fjson.id_module);
                            } else {
                                as.setAttribute("title", "Reemplazar actividad de comprensión");
                                //as.innerHTML = 'Reemplazar actividad de comprensión';
                                as.href = '../local/editor_uniminuto/templates/proponer-actividad/index.php?courseid=' + idC + '&id_mod=' + encodeURIComponent(fjson.id_module);
                            }

                        } else {

                            if (fjson.estado == 0) {
                                as.setAttribute('style', `color:#00b208 !important; 
                                background-image: url("../local/backup_course/pix/i_editando.png"); 
                                filter: brightness(0) saturate(100%) invert(77%) sepia(78%) saturate(602%) hue-rotate(339deg) brightness(105%) contrast(104%);
                                background-position: left; 
                                text-align: center; 
                                padding-left: 30px;`);
                                as.innerHTML = 'Borrador';
                                as.setAttribute("title", "Estado de reemplazo: Borrador");
                                as.href = '../local/editor_uniminuto/templates/proponer-actividad/index.php?courseid=' + idC + '&id_mod=' + encodeURIComponent(fjson.id_module);
                            } else if (fjson.estado == 1) {
                                as.setAttribute('style', `color:#7c47eb !important; 
                                background-image: url("../local/backup_course/pix/i_corrigiendo.png"); 
                                filter: brightness(0) saturate(100%) invert(27%) sepia(83%) saturate(3056%) hue-rotate(249deg) brightness(96%) contrast(91%); 
                                background-position: left; 
                                text-align: center; 
                                padding-left: 30px;`);
                                as.innerHTML = 'Revisión';
                                as.setAttribute("title", "Estado de reemplazo: Revisión");
                                as.href = '../local/editor_uniminuto/templates/proponer-actividad/index.php?courseid=' + idC + '&id_mod=' + encodeURIComponent(fjson.id_module);
                            } else if (fjson.estado == 2) {
                                as.setAttribute('style', 'color:#00b208 !important; background-image: url("../local/backup_course/pix/i_aprobada.png"); background-position: left; text-align: center; padding-left: 30px;');
                                as.innerHTML = 'Aprobada';
                                as.setAttribute("title", "Estado de reemplazo: Aprobada");
                                as.href = '../local/editor_uniminuto/templates/proponer-actividad/index.php?courseid=' + idC + '&id_mod=' + encodeURIComponent(fjson.id_module);
                            } else if (fjson.estado == 3) {
                                as.setAttribute('style', 'color:#bc0c0c !important; background-image: url("../local/backup_course/pix/i_importada.png"); background-position: left; text-align: center; padding-left: 30px;');
                                as.innerHTML = 'Importada';
                                as.setAttribute("title", "Estado de reemplazo: Importada");
                                as.href = '../local/editor_uniminuto/templates/proponer-actividad/index.php?courseid=' + idC + '&id_mod=' + encodeURIComponent(fjson.id_module);
                            }

                        }
                    }
                }
                /*  $('[data-toggle="tooltip"]').tooltip(); */
            } else {
                //msjBC.error('ERROR', 'No es posible unirse a este nodo');
            }
        }
    });
};



//Mantiene la opacidad al momento de que un elemento se intente arrastrar
function changeElement() {

    var cant = document.getElementsByClassName("dragging").length;

    for (var i = 0; i < cant; i++) {

        /* border: 1px solid #dee2e6; */


        document.getElementsByClassName("dragging")[i].style.opacity = '1';

    }

}

function changeElement2(extension) {

    var cant = document.getElementsByClassName("dragging").length;

    for (var i = 0; i < cant; i++) {

        document.getElementsByClassName("dragging")[i].style.opacity = '1';

    }

    var elemento = extension;

    for (var i = 0; i < elemento.length; i++) {

        if (elemento[i].classList.contains("courseindex-item") && elemento[i].classList.contains("dragging") && !(elemento[i].classList.contains("pageitem"))) {

            elemento[i].style.border = "0";
            elemento[i].style.backgroundColor = "transparent";
        }

    }

}

function changeElement3() {

    var cant = document.getElementsByClassName("dragging").length;

    for (var i = 0; i < cant; i++) {

        document.getElementsByClassName("dragging")[i].style.opacity = '1';

    }

    var elemento = document.querySelectorAll('div.courseindex-section');

    for (var i = 0; i < elemento.length; i++) {
        if (elemento[i].classList.contains("dragging")) {
            elemento[i].style.backgroundColor = 'white';
            elemento[i].style.borderLeft = 'solid 3px transparent';
            elemento[i].style.borderTop = '0';
            elemento[i].style.borderRight = '0';
            elemento[i].style.borderBottom = '0';
        }
    }

}

function borders() {

    let element = document.querySelectorAll("div.activity-item:hover");

    for (var i = 0; i < element.length; i++) {

        element[i].style.backgroundColor = '#ffffff';
        element[i].style.border = '1px solid #dee2e6';

    }
}

/*
 * Ocultar la posibilidad de editar div edit
 * @returns {undefined}
 */

Innovame.prototype.hideEdit = function () {

    document.getElementById('overlay-loader_block').style.display = 'block';
    //console.log("overlay-loader_block",'922')
    setTimeout(function () {
        document.getElementById('overlay-loader_block').style.display = 'block';
        //console.log("overlay-loader_block",'926')
        $(document).ready(function () {

            document.getElementById('overlay-loader_block').style.display = 'block';
            //console.log("overlay-loader_block",'930')
            var edBlo = document.getElementsByClassName('block_innovame_uvd  block block_with_controls')[0];
            if (edBlo) {
                edBlo = edBlo.getElementsByClassName('moodle-actionmenu');
            }
            if (edBlo) {
                edBlo[0].innerHTML = '';
            }
            if (document.getElementsByClassName('editar_acti').length > 0) {
                var len = document.getElementsByClassName('editar_acti').length;
                for (var i = 0; i < len; i++) {
                    document.getElementById('overlay-loader_block').style.display = 'block';
                    document.getElementsByClassName('editar_acti')[i].innerHTML = '';
                }
                if (document.getElementsByClassName('w3-container btn_cre').length > 0) { // formato de curso uvd
                    var cant = document.getElementsByClassName('w3-container btn_cre').length;
                    for (var r = 0; r < cant; r++) {
                        $(document.getElementsByClassName('w3-container btn_cre')[r].getElementsByClassName('w3-btn w3-white w3-border w3-border-blue w3-round')[0]).remove();
                    }
                }

                if (document.getElementsByClassName('section_action_menu').length > 0) {
                    var cant1 = document.getElementsByClassName('section_action_menu').length;
                    for (var t = 0; t < cant1; t++) {
                        document.getElementById('overlay-loader_block').style.display = 'block';
                        document.getElementsByClassName('section_action_menu')[t].innerHTML = '';
                    }
                }

            }

            let menuIzq = document.querySelectorAll("nav#courseindex.courseindex");
            if (menuIzq !== null || menuIzq !== undefined) {
                if (menuIzq[0]) {
                    let extension = menuIzq[0].getElementsByTagName("li");
                    if (extension !== null || extension !== undefined) {
                        for (var i = 0; i < extension.length; i++) {
                            if (extension[i]) {
                                if (extension[i].querySelectorAll("span.dragicon.ml-auto") && extension[i].querySelectorAll("span.dragicon.ml-auto")[0]) extension[i].querySelectorAll("span.dragicon.ml-auto")[0].style.visibility = "hidden";
                                extension[i].addEventListener("dragstart", (event) => {
                                    changeElement2(extension);
                                    event.preventDefault();
                                });
                            }
                        }
                    }
                }

            }

            let seccionTemas = document.querySelectorAll("div.courseindex-section-title");
            for (var i = 0; i < seccionTemas.length; i++) {
                seccionTemas[i].style.border = 'inherit';
                seccionTemas[i].querySelectorAll("span.dragicon.ml-auto")[0].style.visibility = "hidden";
                seccionTemas[i].style.backgroundColor = "white";
                seccionTemas[i].addEventListener("dragstart", (event) => {
                    changeElement3();
                    event.preventDefault();
                });
            }

            document.getElementById('overlay-loader_block').style.display = 'block';
            //console.log("overlay-loader_block",'990')
            //ocular edición secciones 
            if (document.getElementsByClassName('moodle-actionmenu section-cm-edit-actions commands').length > 0) {

                getMisActividades(course_id);

                if (document.getElementsByClassName('visibleifjs addresourcemodchooser').length > 0) {

                    var len5 = document.getElementsByClassName('visibleifjs addresourcemodchooser').length;

                    for (var f = 0; f < len5; f++) {
                        $(document.getElementsByClassName('visibleifjs addresourcemodchooser')[f].getElementsByClassName('section-modchooser-link')[0]).click(function () {
                            inn.onclick_ventana_add(f);
                        });
                    }
                }



                //Reune todos los elementos del curso 
                const elementos = document.querySelectorAll("li.activity.activity-wrapper");
                /*  document.querySelectorAll(".activity-item:hover").style.border='1px solid red'; */
                for (var i = 0; i < elementos.length; i++) {
                    document.getElementById('overlay-loader_block').style.display = 'block';
                    elementos[i].addEventListener("dragstart", (event) => {
                        changeElement();
                        event.preventDefault();
                    });

                    elementos[i].addEventListener("onselect", (event) => {
                        event.preventDefault();
                    });

                    elementos[i].setAttribute("draggable", "false");
                    elementos[i].classList.remove("dropready");
                    elementos[i].classList.remove("draggable");

                }

                //Reune cada elemento del curso para poder atrapar bordes y estilo de cursor
                const elementos2 = document.querySelectorAll("div.activity-item");
                for (var i = 0; i < elementos2.length; i++) {
                    elementos2[i].style.cursor = "default";
                    document.getElementById('overlay-loader_block').style.display = 'block';
                }

                const elementos3 = document.querySelectorAll("div.activitytitle");

                for (var i = 0; i < elementos3.length; i++) {
                    elementos3[i].setAttribute('onmouseover', 'borders()');
                }

            }

            document.getElementById('overlay-loader_block').style.display = 'none';
        });
        document.getElementById('overlay-loader_block').style.display = 'none';
    }, 2000);

};

Innovame.prototype.editBotones = function (misActividades) {
    document.getElementById('overlay-loader_block').style.display = 'block';
    let actividades = [];
    for (const i in misActividades) actividades.push(misActividades[i].contextinstanceid);

    let editable = document.getElementsByClassName('course-section-header d-flex dropready');
    editable.forEach(element => {
        if (!actividades.includes(element.getAttribute('data-itemid'))) {//lapiz moodle editar seccion
            if (element.getElementsByClassName('quickediticon visibleifjs')
                && element.getElementsByClassName('quickediticon visibleifjs')[0]) {
                (element.getElementsByClassName('quickediticon visibleifjs')[0]).classList.add('hidden');
                (element.getElementsByClassName('quickediticon visibleifjs')[0]).innerHTML = '';
            }
        }



    });


    //botón acciones por actividad 3 puntitos
    let actions = document.getElementsByClassName('cm_action_menu');
    actions.forEach(element => {
        if (!actividades.includes(element.getAttribute('data-cmid'))) {//buscar en mis actividades si 
            (element.getElementsByClassName('section-cm-edit-actions')[0]).classList.add('hidden');
            (element.getElementsByClassName('section-cm-edit-actions')[0]).innerHTML = '';
        } else {
            document.getElementById('overlay-loader_block').style.display = 'block';
            var editoBank = element.getElementsByClassName('editing_update'); //añadir a los 3 puntos de las actividades propias
            console.log('editoBank---', editoBank)
            if (editoBank && editoBank[0]) editoBank[0].href = editoBank[0].href.replace('/course/mod.php', '/local/backup_course/bank/mod.php');

        }
    });

    if (tipo_permiso == 1) {
        //console.log('actividades',actividades)
        //cuando el permiso es parcialmente y se creó actividades propias se elimina el lapiz y reemplazar
        //var id_lapQ = document.elementsByName('tagA');
        var id_lapQ = document.querySelectorAll('.tagA, .tagA.ta');
        id_lapQ.forEach(element => {
            document.getElementById('overlay-loader_block').style.display = 'block';
            var idLapiz = element.getAttribute('id');  // Obtiene el atributo 'id' del elemento
            var idLapiznum = idLapiz.split('_').pop(); // Extrae el último número del 'id'
            var contLapiz2 = element.parentElement.parentElement; //contenedor de la actividad
            var contMenu3 = contLapiz2.getElementsByClassName('cm_action_menu'); //menú edición
            //console.log('parentElement',element.parentElement.parentElement)
            // Verifica si el número extraído está en el array 'actividades' propias
            if (actividades.includes(idLapiznum)) {
                document.getElementById('overlay-loader_block').style.display = 'block';
                /*if(element.classList.contains('ta')) element.remove(); // Elimina el elemento
                else if(contLapiz2 && contLapiz2.classList && contLapiz2.classList.contains('activity')){ //Cambiar lápiz por banco
                    element.style.backgroundImage = "url('../local/backup_course/pix/bankA.png')";
                    element.href = '../local/backup_course/bank/index.php';
                    element.setAttribute('data-original-title', 'Ir al banco de Actividades');  // Agrega el atributo personalizado.
                    element.setAttribute('title', 'Ir al banco de Actividades');  // Agrega el atributo personalizado.
                    element.setAttribute("data-toggle", "tooltip");
                    
                    if(contMenu3 && contMenu3[0]){
                        var irEditar = contMenu3[0].getElementsByClassName('editing_update');
                        if (irEditar && irEditar[0]) {
                            irEditar[0].href = irEditar[0].href.replace('/course/mod.php', '/local/backup_course/bank/mod.php');
                        }

                    }
                }else*/ element.remove(); // Elimina el elemento
            }

        });
    }


    let sections = document.getElementsByClassName('section course-section');
    sections.forEach(function callback(value, index) {
        let header = value.getElementsByClassName('course-section-header');
        let menu = value.getElementsByClassName('section_action_menu');
        //let editSection = value.getElementsByClassName('quickediticon visibleifjs');
        let editSection = value.getElementsByClassName('inplaceeditable inplaceeditable-text');
        let editActivity = value.getElementsByClassName('cm-edit-action');
        let btnEditActivity = value.getElementsByClassName('section-cm-edit-actions');
        let sectionbadges = value.getElementsByClassName('sectionbadges');

        //eliminar opcion de dragg and drop
        header.forEach(element => {
            element.classList.remove('dropready', 'draggable');
        });



        if (section_new == 0 || (section_new == 1 && index != sections.length - 2)) {
            if (menu[0]) menu[0].remove();

            //opciones editar actividad
            let editActivity = value.getElementsByClassName('cm-edit-action');
            editActivity.forEach(element => {
                document.getElementById('overlay-loader_block').style.display = 'block';
                if ((element.className).indexOf('editing_movecm') != -1 ||
                    //(element.className).indexOf('editing_delete') != -1 ||
                    (element.className).indexOf('editing_moveright') != -1 ||
                    (element.className).indexOf('editing_duplicate') != -1 ||
                    (element.className).indexOf('editing_assign') != -1) {
                    element.classList.add('hidden');
                    element.innerHTML = '';
                }

                if ((element.className).indexOf('editing_show') != -1 || (element.className).indexOf('editing_hide') != -1) {
                    element.setAttribute('onclick', "window.location.reload();");
                    //getMisActividades();
                }

            });
        } else {
            //sectionbadges[0].innerHTML += '<span class="badge badge-pill badge-primary">Nueva</span>';
            if (menu && menu[0]) {
                //Quitar funciones botón edición section
                let optA = menu[0].getElementsByTagName('a');
                optA.forEach(element => {
                    document.getElementById('overlay-loader_block').style.display = 'block';
                    if ((element.className).indexOf('move waitstate') != -1 ||
                        (element.className).indexOf('editing_highlight') != -1 ||
                        (element.className).indexOf('editing_showhide') != -1) {
                        element.classList.add('hidden');
                        element.innerHTML = '';
                    }
                    if (element.className.indexOf('editing_delete ') != -1) {
                        //element.setAttribute("onclick", 'inn.update_section_new(' + course_id + ')');
                        //element.setAttribute("onclick", 'inn.btnDelete();');
                    }
                });
            }

            /*
            //Quitar botoón edición UVD
            let tagA = value.getElementsByClassName('activity-item');
            tagA.forEach(element => {
                if (element.getElementsByClassName('tagA').length > 0) element.getElementsByClassName('tagA')[0].remove();
                if (element.getElementsByClassName('tagA ta').length > 0) element.getElementsByClassName('tagA')[0].remove();
            });*/

            editActivity.forEach(element => {
                document.getElementById('overlay-loader_block').style.display = 'block';
                if ((element.className).indexOf('editing_movecm') != -1 ||
                    (element.className).indexOf('editing_moveright') != -1 ||
                    (element.className).indexOf('editing_duplicate') != -1 ||
                    (element.className).indexOf('editing_assign') != -1) {
                    element.classList.add('hidden');
                    element.innerHTML = '';
                }

                if ((element.className).indexOf('editing_show') != -1 || (element.className).indexOf('editing_hide') != -1) {
                    element.setAttribute('onclick', "window.location.reload();");
                    //getMisActividades();
                }
            });
        }
        document.getElementById('overlay-loader_block').style.display = 'none';
    });
}

Innovame.prototype.btnDelete = function () {
    let actionDeleteSections = document.querySelectorAll('[data-action=deleteSection]');
    actionDeleteSections.forEach(element => {
        element.addEventListener('click', function (e) {
            e.preventDefault();
            setTimeout(function () {
                let modalDeleteSection = document.querySelector('[data-region="modal-container"].modal.moodle-has-zindex.show');
                if (modalDeleteSection) {
                    let btn = modalDeleteSection.querySelector('[data-action="save"]');
                    btn.setAttribute("onclick", 'inn.update_section_new(' + course_id + ')');
                } else {
                    inn.update_section_new(course_id);
                }
            }, 1000);
        });
    });
}

Innovame.prototype.get_section_new = function (courseid) {
    const urlActual = window.location;
    let path = urlActual.pathname.split('/');

    let newUrl = "";

    if (path[1] !== 'course' && path[1] !== 'local' && path[1] !== 'mod') {

        newUrl = urlActual.origin + '/' + path[1] + '/local/backup_course/methods/QRY/class.query.php';

    } else {

        newUrl = urlActual.origin + '/local/backup_course/methods/QRY/class.query.php';
    }
    newUrl = '../local/backup_course/methods/QRY/class.query.php'

    $.ajax({
        url: newUrl,
        data: {
            key: 'Q10',
            courseid: courseid
        },
        type: 'POST',
        success: function (json) {
            let data = JSON.parse(json);
            section_new = (data) ? data.section : 0;
        }
    });

}

Innovame.prototype.update_section_new = function (courseid) {
    $.ajax({
        url: '../local/backup_course/methods/UPD/class.update.php',
        data: {
            key: 'U05',
            courseid: courseid
        },
        type: 'POST',
        success: function (json) {
            let data = JSON.parse(json);
            location.reload();
        }
    });

}
/*
 * Si el nodo está en el hijo y es un curso importado no permitir seleccionar texto para que no puedan copiar
 * @returns {undefined}
 */
Innovame.prototype.no_copy = function () {
    document.getElementsByTagName("body")[0].setAttribute("oncopy", "return false");
    document.getElementsByTagName("body")[0].setAttribute("oncut", "return false");
};


Innovame.prototype.delete_menu = function () {


    let isActividad = false;
    const urlActual = window.location;
    let path = urlActual.pathname.split('/');
    let update = getParameterByName('update');
    let cmid = getParameterByName('cmid');

    let newUrl = "";

    if (path[1] !== 'course' && path[1] !== 'local' && path[1] !== 'mod') {

        newUrl = urlActual.origin + '/' + path[1] + '/local/backup_course/methods/QRY/class.query.php';

    } else {

        newUrl = urlActual.origin + '/local/backup_course/methods/QRY/class.query.php';
    }

    if (update || cmid) {
        var moduleid = update ? update : cmid
        $.ajax({
            url: newUrl,
            data: {
                key: 'Q13',
                moduleid: moduleid,
            },
            type: 'POST',
            success: function (json) {
                let data = JSON.parse(json);
                console.log('data', data)
                isActividad = (Object.keys(data).length > 0) ? true : false;
                if (Object.keys(data).length > 0) {
                } else {
                    console.log('->>>>>> esa actividad no es mia ', isActividad);
                }
                botones(isActividad);
            }
        });
    } else {
        botones(isActividad);

    }
}

function botones(isActividad) {
    const urlActual = window.location;
    //botón configuración
    let nav = document.getElementsByClassName('nav-item');
    for (var i = 0; i < nav.length; i++) {
        if (nav[i].getAttribute('data-key') === 'editsettings' && urlActual.href.indexOf('/mod/') == -1) {
            if (!isActividad) nav[i].style.display = 'none';
        }
    }

    const navItem = document.querySelectorAll('li.nav-item>a.nav-link');
    if (navItem != null) {
        navItem.forEach(element => {
            const tag = element.href;
            if (tag.indexOf('/course/modedit.php?update') != -1) {
                if (!isActividad) element.style.display = "none";
            }

            if (tag.indexOf('grade/grading/manage.php?contextid') != -1) {
                if (!isActividad) element.remove();
            }
        });
    }

    const navDropDown = document.querySelectorAll('li.nav-item>a.dropdown-toggle');

    if (navDropDown != null) {
        const urlActual = window.location.href;
        // Verifica si la URL contiene "/mod/"
        if (urlActual.includes('/mod/')) {
            navDropDown.forEach(element => {
                if (!isActividad) element.style.display = "none";
            });
        }

        if (urlActual.includes('/mod/assign') || urlActual.includes('/mod/quiz')) {

            navDropDown.forEach(element => {
                if (!isActividad) element.style.display = "flex";
            });

            const lista_desplegable = document.querySelectorAll('.nav-item');
            //Solo visualizar Overrides en las tareas
            // Recorre todos los elementos <li>
            lista_desplegable.forEach(li => {
                // Si el elemento tiene el atributo data-key="mod_assign_useroverrides"
                if (li.getAttribute('data-key') === 'mod_assign_useroverrides') {
                    // Muestra el elemento
                    li.style.display = '';
                } else if (li.getAttribute('data-key') === 'filtermanage'
                    || li.getAttribute('data-key') === 'roleoverride'
                    || li.getAttribute('data-key') === 'backup'
                    || li.getAttribute('data-key') === 'restore'
                    || li.getAttribute('data-key') === 'roleassign'
                    || li.getAttribute('data-key') === 'questionbank'
                    || li.getAttribute('data-key') === 'mod_quiz_edit') {

                    if (li.getAttribute('data-key') === 'mod_quiz_edit') {
                        console.log('----', li)
                        console.log('isActividad', isActividad)
                    }

                    li.remove();
                }

            });

        }
        if (urlActual.includes('/quiz/edit.php') && isActividad) {
            var a_tag = document.getElementById('page-content').getElementsByTagName('a');
            for (var a = 0; a < a_tag.length; a++) {
                if (a_tag[a] && a_tag[a].href && a_tag[a].href.indexOf('/mod/quiz/edit.php') != -1) {
                    a_tag[a].parentElement.style.display = 'block';
                }
            }
        }

        if (urlActual.includes('/mod/book')) {

            const action_list = document.querySelectorAll('.action-list');

            action_list.forEach(element => {
                element.remove();
            });
        }

        if (urlActual.includes('/question/edit')) {

            let th = document.querySelectorAll('thead>tr>th.editmenu');
            th.forEach(element => {
                element.remove();
            });

            th = document.querySelectorAll('thead>tr>th.questionstatus');

            th.forEach(element => {
                element.remove();
            });

            th = document.querySelectorAll('thead>tr>th.checkbox');

            th.forEach(element => {
                element.remove();
            });



            let td = document.querySelectorAll('tbody');
            console.log('td', td)
            td.forEach(element => {

                let td = element.querySelectorAll('td.editmenu');

                td.forEach(element => {
                    element.remove();
                });

                td = element.querySelectorAll('td.questionstatus');

                td.forEach(element => {
                    element.remove();
                });

                td = element.querySelectorAll('td.checkbox');

                td.forEach(element => {
                    element.remove();
                });

                td = element.querySelectorAll('td.qnameidnumbertags');

                td.forEach(element => {
                    const span = element.querySelectorAll('span')[0];
                    span.removeAttribute("data-inplaceeditable");
                });

            });

            const createnewquestion = document.querySelector('.createnewquestion');
            createnewquestion.remove();

            const acciones = document.getElementById('bulkactionsui-container');
            acciones.remove();

            const container_opc = document.querySelector('.container-fluid.tertiary-navigation');
            container_opc.remove();
        }

    }

    //Este bloque permite mostrar los botones de acción de actividades especificas
    let btn = document.getElementById('fgroup_id_buttonar');

    if (urlActual.href.indexOf('/attendance') != -1) {

        if (btn) btn.style.display = 'flex';

    } else if (urlActual.href.indexOf('/forum') != -1) {

        if (btn) btn.style.display = 'flex';

    } else if (urlActual.href.indexOf('/glossary') != -1) {

        if (btn) btn.style.display = 'flex';

    } else if (urlActual.href.indexOf('/assign') != -1) {

        if (btn) btn.style.display = 'flex';

    } else {


    }
}


/*
 * Si el nodo está en el hijo y es un curso importado no permitir copia de seguridad ni ser importado desde otro curso en ese nodo
 * @param {type} Y
 * @param {int} id_course
 * @param {string} dir
 * @returns {undefined}
 */
Innovame.prototype.no_import = function (Y, id_course, dir) {
    var reg = document.getElementById('region-main-course-format');
    var segu = document.getElementById('region-main');
    if (reg) {
        reg.innerHTML = '<div id="adver_alert"><h3>No es posible importar este curso</h3>' +
            '<a href="' + dir + '/course/view.php?id=' + id_course + '" id="button_actualizar">Volver al curso</a>' +
            '<a href="' + dir + '/local/backup_course/forms/search_courses/view_search_course.php?id_nodo=' + id_course + '" id="boton_volver">' +
            'Ir al método de importación de Innovame' +
            '</a></div>';
    } else if (segu) {
        segu.innerHTML = '<div id="adver_alert"><h3>No es posible realizar una copia de este curso</h3>' +
            '<a href="' + dir + '/course/view.php?id=' + id_course + '" id="button_actualizar">Volver al curso</a>' +
            '</div>';
    }
};


Innovame.prototype.feedback_edit = function () {
    if (!document.getElementById("overlay-loader_block") && document.getElementById('page-content')) {
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
    document.getElementById("overlay-loader_block").style.display = "block";
    //console.log("overlay-loader_block",'feedback_edit')
    var ta = document.getElementsByClassName('itemactions');
    for (var i = 0; i < ta.length; i++) {
        ta[i].innerHTML = "";
    }
    var tag_fo = document.getElementsByClassName('form-inline');
    tag_fo && tag_fo[0] ? tag_fo[0].innerHTML = "" : '';

}

Innovame.prototype.quiz_edit = function () {
    if (!document.getElementById("overlay-loader_block") && document.getElementById('page-content')) {
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
    document.getElementById("overlay-loader_block").style.display = "block";
    //console.log("overlay-loader_block",'1508')
    var ta = document.getElementsByClassName('editquestion');
    for (var i = 0; i < ta.length; i++) {
        ta[i].innerHTML = "";
    }
    document.getElementById("overlay-loader_block").style.display = "none";
}

var span = document.getElementsByClassName("close_innovame")[0];

function CallWindow(pathFrame, tit = 'Editar ajustes') {

    if (!document.getElementById("modalEdition") && document.getElementById('page-content')) {
        $("#page-content").append(`<!-- Modal -->
        <div class="modal fade" id="modalEdition" tabindex="-1" role="dialog" aria-labelledby="modalEditionLabel" aria-hidden="true">
          <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content modalBodyAjustes">
              <div class="modal-header">
                <h5 class="modal-title" id="modalEditionLabel">${tit}</h5>
                <button type="button" onclick="closeModal()" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
              <iframe scrolling="auto" src="" frameborder="0" /* height="100%" */ width="100%" id="frame-forms-edit" class="frame-forms-edit"></iframe> 
              </div>
            </div>
          </div>
        </div>`);
    }

    let elemento = document.getElementById('modalEdition');
    let tieneClase = elemento.classList.contains('show');

    if (!tieneClase) {
        elemento.classList.add('show');
        document.getElementById('frame-forms-edit').src = pathFrame;
        const newIframe = document.getElementById('frame-forms-edit');
        //Adecuar el iframe
        newIframe.onload = function () {
            const contenidoIframe = newIframe.contentWindow.document;
            const menuNav = contenidoIframe.querySelector('.secondary-navigation');
            if (menuNav != null) {
                menuNav.style.display = "none";
            }

            const botonesLaterales = contenidoIframe.querySelector('.drawer-toggles');
            if (botonesLaterales != null) {
                botonesLaterales.remove();
            }

            const contenedorPagina = contenidoIframe.querySelector('#page');
            if (contenedorPagina != null) {
                contenedorPagina.style.marginTop = '0px';
            }

        }
    }
}

Innovame.prototype.ocultarBotonesLeccion = function () {
    let urlActual = window.location;

    let cadenaComparar = "/mod/lesson";
    // Comparamos la URL actual con la cadena de texto
    if (urlActual.href.indexOf(cadenaComparar) !== -1) {
        const botonesLeccion = document.querySelector('div.container-fluid.tertiary-navigation');
        if (botonesLeccion.length !== null) {
            botonesLeccion.remove();
        }
    }

}

Innovame.prototype.ocultarTodaEdit = function (Y, permiso) {//permiso 0: desbloqueado, 1: parcial, 2:Total
    document.getElementById("overlay-loader_block").style.display = "block";
    //console.log("overlay-loader_block",'1584')
    setTimeout(function () {
        document.getElementById("overlay-loader_block").style.display = "block";
        // Eliminar elementos con la clase 'cm_action_menu actions' tres puntos
        var puntos = document.getElementsByClassName('cm_action_menu actions');
        while (puntos.length > 0 && permiso == 2) puntos[0].remove();

        // Eliminar elementos con la clase 'tagA' lapiz de modificar fechas
        var lapicesTagA = document.getElementsByClassName('tagA');
        for (var i = lapicesTagA.length - 1; i >= 0; i--) {
            var element = lapicesTagA[i];
            // Si el elemento solo tiene la clase 'tagA', lo eliminamos
            if (element.classList.length === 1 && permiso == 0) {
                document.getElementById('overlay-loader_block').style.display = 'block';
                element.remove();
            }
        }

        // Eliminar elementos con la clase 'quickediticon visibleifjs' lapiz de cambiar nombre
        var lapicesQuickEdit = document.getElementsByClassName('quickediticon visibleifjs');
        while (lapicesQuickEdit.length > 0) lapicesQuickEdit[0].remove();

        // Eliminar elementos con la clase 'section_action_menu' 3 puntos moodle editar seccion
        var puntosSectEdit = document.getElementsByClassName('section_action_menu');
        while (puntosSectEdit.length > 0) {
            document.getElementById('overlay-loader_block').style.display = 'block';
            puntosSectEdit[0].remove();
        }

        document.getElementById("overlay-loader_block").style.display = "none";
    }, 4200);
}

function closeModal() {

    let elemento = document.getElementById('modalEdition');
    let tieneClase = elemento.classList.contains('show');

    if (tieneClase) {
        elemento.classList.remove('show');
        document.getElementById('frame-forms-edit').src = "";
    }

}

window.onclick = function (event) {
    if (event.target == document.getElementById('myModal_innovame')) {
        document.getElementById('myModal_innovame').style.display = "none";
    }
}

function cerrar_innovame() { }
cerrar_innovame.prototype.cerrar = function () {
    document.getElementById('myModal_innovame').style.display = "none";
};
var inn = new Innovame();
var cInno = new cerrar_innovame();
