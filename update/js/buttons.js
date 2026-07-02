/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/*Funciones para los botones de guardar*/

function actions_bottons() { }
AFB = new actions_bottons();

/*
 * Método para cambiar el valor del boton cancelar
 * @returns {undefined}
 */
actions_bottons.prototype.tagAlink = function () {
    window.onload = function () {

        setTimeout(function () {

            if (typeof arrIcons != 'undefined' && window.location.href.indexOf('/course/view.php') != -1) {
                console.log("Hola soy 1");
                var tag = document.getElementsByTagName('a');
                for (var i = 0; i < tag.length; i++) {
                    if ((tag[i].href.indexOf('course/mod.php') != -1) && tag[i].href.indexOf('update=') != -1) {
                        var hre = tag[i].href;
                        var newHref = hre.replace("course/mod.php", "local/backup_course/update/layouts/modedit.php");
                        tag[i].href = newHref;
                    } else if (tag[i].href.indexOf('course/edit.ph') != -1) {
                        var hre = tag[i].href;
                        var newHref = hre.replace("course/edit", "local/backup_course/update/layouts/edit");
                        tag[i].href = newHref;
                    } else
                        if (tag[i].href.indexOf('grade/edit/tree/index.php') != -1) {
                            var hre = tag[i].href;
                            var newHref = hre.replace("grade/edit/tree/index.php", "local/backup_course/update/layouts/index.php");
                            tag[i].href = newHref;
                        } else
                            if (tag[i].href.indexOf('grade/edit/tree/categ') != -1) {
                                var hre = tag[i].href;
                                var newHref = hre.replace("grade/edit/tree/category", "local/backup_course/update/layouts/category");
                                tag[i].href = newHref;
                            } else
                                if (tag[i].href.indexOf('group/index.php') != -1 && tag[i].href.indexOf('/layouts/group/index.php') == -1) {
                                    var hre = tag[i].href;
                                    var newHref = hre.replace("group/index.php", "local/backup_course/update/layouts/group/index.php");
                                    tag[i].href = newHref;
                                } else
                                    if (tag[i].href.indexOf('/group/groupings.php') != -1 && tag[i].href.indexOf('/layouts/group/groupings.php') == -1) {
                                        var hre = tag[i].href;
                                        var newHref = hre.replace("group/groupings.php", "local/backup_course/update/layouts/group/groupings.php");
                                        tag[i].href = newHref;
                                    } else
                                        if (tag[i].href.indexOf('question/edit.php') != -1) {
                                            var hre = tag[i].href;
                                            var newHref = hre.replace("question/edit.php", "local/backup_course/update/layouts/question/edit.php");
                                            tag[i].href = newHref;
                                        } else
                                            if (tag[i].href.indexOf('question/category.php') != -1) {
                                                var hre = tag[i].href;
                                                var newHref = hre.replace("question/category.php", "local/backup_course/update/layouts/question/category.php");
                                                tag[i].href = newHref;
                                            } else
                                                if (tag[i].href.indexOf('question/bank/editquestion/question.php') != -1 && tag[i].href.indexOf('local/backup_course/upd') == -1) {
                                                    var hre = tag[i].href;
                                                    var newHref = hre.replace("question/bank/editquestion/question.php", "local/backup_course/update/layouts/question/question.php");
                                                    tag[i].href = newHref;
                                                } else
                                                    if (tag[i].href.indexOf('question/addquestion.php') != -1 && tag[i].href.indexOf('local/backup_course/upd') == -1) {
                                                        var hre = tag[i].href;
                                                        var newHref = hre.replace("question/addquestion.php", "local/backup_course/update/layouts/question/question.php");
                                                        tag[i].href = newHref;
                                                    } else
                                                        if (tag[i].href.indexOf('/grade/grading/form/rubric/edit') != -1 && tag[i].href.indexOf('local/backup_course/upd') == -1) {
                                                            var hre = tag[i].href;
                                                            var newHref = hre.replace("/grade/grading/form/rubric/edit", "local/backup_course/update/layouts/rubric/edit");
                                                            tag[i].href = newHref;
                                                        }
                }

            } else {

                //Para poder editar las categorías
                let selectCustom = document.querySelectorAll('select.custom-select.urlselect');

                // Itera a través de los elementos <select>
                selectCustom.forEach(function (selectElement) {
                    // Selecciona todas las opciones dentro del <select>
                    var options = selectElement.querySelectorAll("option");
                    // Itera a través de las opciones y obtiene sus valores
                    options.forEach(function (option) {

                        var value = option.value;

                        if (value.includes("/question/bank/managecategories/category")) {
                            option.value = value.replace("/question/bank/managecategories/category", "/local/backup_course/update/layouts/question/bank/category");

                            if (window.location.href.indexOf('local/backup_course/update/layouts/question/bank/category.php') != -1) {
                                option.selected = true;
                            }

                        }

                        if (value.includes("/question/edit")) {
                            option.value = value.replace("/question/edit", "/local/backup_course/update/layouts/question/edit");

                            if (window.location.href.indexOf('local/backup_course/update/layouts/question/edit.php') != -1) {
                                option.selected = true;
                            }

                        }


                    });
                });

                //Para editar las categorias
                let editarBtn = document.querySelectorAll("ul>li>a");
                editarBtn.forEach(element => {
                    let dirUrl = element.href;
                    if (dirUrl.includes("/question/bank/managecategories/category")) {
                        element.href = dirUrl.replace("/question/bank/managecategories/category", "/local/backup_course/update/layouts/question/bank/category");
                    }
                });

                //Para crear una categoría
                let categoria = document.querySelectorAll('div.navitem');

                categoria.forEach(function (a) {
                    let tagC = a.querySelectorAll("a.btn");
                    tagC.forEach(function (a2) {
                        let newTag = a2.href;
                        if (newTag.includes("/question/bank/managecategories/category")) {
                            a2.href = newTag.replace("/question/bank/managecategories/category", "/local/backup_course/update/layouts/question/bank/category");
                        }
                    });
                });

                if (window.location.href.indexOf('local/backup_course/update/layouts/question/bank/category.php') != -1) {

                    let formData = document.querySelectorAll("form.mform");
                    formData.forEach(function (data) {
                        let action = data.action;
                        let idForm = data.id;

                        if (action.includes("/question/bank/managecategories/category.php")) {
                            data.action = action.replace("/question/bank/managecategories/category", "/local/backup_course/update/layouts/question/bank/category");

                            document.getElementById({
                                idForm
                            }).addEventListener("submit", function (event) {
                                event.preventDefault(); // Evitar que el formulario se envíe

                                var formData = new FormData(event.target);

                                formData.forEach(function (value, key) {
                                    console.log(key + ": " + value);
                                });
                            });

                        }

                    });


                    let url = window.location.href;
                    // Crear un nuevo objeto URLSearchParams con la URL
                    let params = new URLSearchParams(new URL(url).search);
                    // Obtener valores de parámetros individuales
                    let courseid = params.get("courseid");
                    let edit = params.get("edit");

                    // Validar si los parámetros existen
                    if (courseid !== null && edit == 0) {
                        let buttonCreate = document.getElementById("id_submitbutton");


                        // let loader = document.getElementById('overlay-loader_block_modedit');
                        //if (loader) {
                        //  loader.style.display = 'block';
                        //} 

                    }

                }

                let miArray = [];
                var tag = document.getElementsByTagName('a');
                let numDelete = 0;
                const url_actual = window.location.href;
                for (var i = 0; i < tag.length; i++) {
                    if ((tag[i].href.indexOf('course/mod.php') != -1 || tag[i].href.indexOf('course/modedit.php') != -1) && tag[i].href.indexOf('update=') != -1) {

                        var hre = tag[i].href;
                        var newHref = '#';
                        if (tag[i].href.indexOf('course/mod.php') != -1) {
                            newHref = hre.replace("course/mod.php", "local/backup_course/update/layouts/modedit.php");
                        } else {
                            newHref = hre.replace("course/modedit.php", "local/backup_course/update/layouts/modedit.php");
                        }
                        tag[i].href = newHref;
                    } else
                        if (tag[i].href.indexOf('course/edit.ph') != -1) {
                            var hre = tag[i].href;
                            var newHref = hre.replace("course/edit", "local/backup_course/update/layouts/edit");
                            tag[i].href = newHref;
                        } else
                            if (tag[i].href.indexOf('grade/edit/tree/inde') != -1) {
                                var hre = tag[i].href;
                                var newHref = hre.replace("grade/edit/tree/index", "local/backup_course/update/layouts/index");
                                tag[i].href = newHref;
                            } else
                                if (tag[i].href.indexOf('grade/edit/tree/categ') != -1) {
                                    var hre = tag[i].href;
                                    var newHref = hre.replace("grade/edit/tree/category", "local/backup_course/update/layouts/category");
                                    tag[i].href = newHref;
                                } else
                                    if (tag[i].href.indexOf('group/index.php') != -1 && tag[i].href.indexOf('/layouts/group/index.php') == -1) {
                                        var hre = tag[i].href;
                                        var newHref = hre.replace("group/index.php", "local/backup_course/update/layouts/group/index.php");
                                        tag[i].href = newHref;
                                    } else
                                        if (tag[i].href.indexOf('/group/groupings.php') != -1 && tag[i].href.indexOf('/layouts/group/groupings.php') == -1) {
                                            var hre = tag[i].href;
                                            var newHref = hre.replace("group/groupings.php", "local/backup_course/update/layouts/group/groupings.php");
                                            tag[i].href = newHref;
                                        } else
                                            if (tag[i].href.indexOf('/group/assign.php') != -1) {
                                                var hre = tag[i].href;
                                                var newHref = hre.replace("group/assign.php", "local/backup_course/update/layouts/group/assign.php");
                                                tag[i].href = newHref;
                                            } else
                                                if (tag[i].href.indexOf('question/edit.php') != -1 && tag[i].href.indexOf('local/backup_course/upd') == -1) {
                                                    var hre = tag[i].href;
                                                    var newHref = hre.replace("question/edit.php", "local/backup_course/update/layouts/question/edit.php");
                                                    tag[i].href = newHref;
                                                } else
                                                    if (tag[i].href.indexOf('question/category.php') != -1 && tag[i].href.indexOf('local/backup_course/upd') == -1) {
                                                        var hre = tag[i].href;
                                                        var newHref = hre.replace("question/category.php", "local/backup_course/update/layouts/question/category.php");
                                                        tag[i].href = newHref;
                                                    } else if (tag[i].href.indexOf('question/bank/editquestion/question.php') != -1 && tag[i].href.indexOf('local/backup_course/upd') == -1) {
                                                        console.log(tag[i].href.indexOf('question/bank/editquestion/question.php'));
                                                        console.log(tag[i].href.indexOf('local/backup_course/upd'));
                                                        var hre = tag[i].href;
                                                        var newHref = hre.replace("question/bank/editquestion/question.php", "local/backup_course/update/layouts/question/question.php");
                                                        tag[i].href = newHref;
                                                        let boton1 = document.querySelectorAll(".singlebutton form");

                                                        const currentUrl = window.location.href;
                                                        const rootUrl = currentUrl.split('/').slice(0, 4).join('/');

                                                        const currentPath = window.location.pathname;

                                                        if (boton1.length > 0) {

                                                            boton1[0].setAttribute("action", "../../../update/layouts/question/question.php");

                                                            let btnsub = boton1[0].getElementsByTagName("button");

                                                            btnsub[0].addEventListener("click", function () {
                                                                let urlAct = "../../../update/layouts/question/question.php";
                                                                let form1 = document.querySelectorAll("form#chooserform");
                                                                form1[0].setAttribute("action", urlAct);
                                                            });
                                                        }

                                                    } else if (url_actual.indexOf('local/backup_course/update/layouts/question/edit.php') != -1 && tag[i].href.indexOf('local/backup_course/upd') == -1 && tag[i].href.indexOf('question/bank/editquestion/question.php') == -1) {

                                                        var hre = tag[i].href;
                                                        var newHref = hre.replace("question/bank/editquestion/question.php", "local/backup_course/update/layouts/question/question.php");
                                                        tag[i].href = newHref;
                                                        let boton1 = document.querySelectorAll(".singlebutton form");

                                                        const currentUrl = window.location.href;
                                                        const rootUrl = currentUrl.split('/').slice(0, 4).join('/');

                                                        const currentPath = window.location.pathname;

                                                        if (boton1.length > 0) {

                                                            boton1[0].setAttribute("action", "../../../update/layouts/question/question.php");

                                                            let btnsub = boton1[0].getElementsByTagName("button");

                                                            btnsub[0].addEventListener("click", function () {
                                                                let urlAct = "../../../update/layouts/question/question.php";
                                                                let form1 = document.querySelectorAll("form#chooserform");
                                                                form1[0].setAttribute("action", urlAct);
                                                            });
                                                        }

                                                    } else if (tag[i].href.indexOf('question/addquestion.php') != -1 && tag[i].href.indexOf('local/backup_course/upd') == -1) {
                                                        var hre = tag[i].href;
                                                        var newHref = hre.replace("question/addquestion.php", "local/backup_course/update/layouts/question/question.php");
                                                        tag[i].href = newHref;
                                                    } else if (tag[i].href.indexOf('mod/feedback/edit') != -1 && tag[i].href.indexOf('local/backup_course/upd') == -1) {
                                                        var hre = tag[i].href;
                                                        var newHref = hre.replace("mod/feedback/edit", "local/backup_course/update/layouts/feedback/edit");
                                                        tag[i].href = newHref;
                                                    } else if (tag[i].href.indexOf('mod/lesson/edit') != -1 && tag[i].href.indexOf('local/backup_course/upd') == -1) {
                                                        var hre = tag[i].href;
                                                        var newHref = hre.replace("mod/lesson/edit", "local/backup_course/update/layouts/lesson/edit");
                                                        tag[i].href = newHref;
                                                    } else if (tag[i].href.indexOf('mod/lesson/lesson') != -1 && tag[i].href.indexOf('local/backup_course/upd') == -1) {
                                                        var hre = tag[i].href;
                                                        var newHref = hre.replace("mod/lesson/lesson", "local/backup_course/update/layouts/lesson/lesson");
                                                        tag[i].href = newHref;
                                                    }
                                                    /* else if (tag[i].href.indexOf('mod/lesson/edit') != -1 && tag[i].href.indexOf('qtype=20') != -1 ) {
                                                                           var hre = tag[i].href;
                                                                           var newHref = hre.replace("mod/lesson/edit", "local/backup_course/update/layouts/lesson/editpage");
                                                                           tag[i].href = newHref;
                                                                       } */
                                                    else if (tag[i].href.indexOf('mod/workshop/editform') != -1 && tag[i].href.indexOf('local/backup_course/upd') == -1) {
                                                        var hre = tag[i].href;
                                                        var newHref = hre.replace("mod/workshop/editform", "local/backup_course/update/layouts/workshop/editform");
                                                        tag[i].href = newHref;
                                                        if (document.getElementById('mform1')) {
                                                            var action = document.getElementById('mform1').action;
                                                            action = action.replace("mod/workshop/editform", "local/backup_course/update/layouts/workshop/editform");
                                                            document.getElementById('mform1').action = action;
                                                        }
                                                    } else
                                                        if (tag[i].href.indexOf('mod/workshop/allocation') != -1 && tag[i].href.indexOf('local/backup_course/upd') == -1) {
                                                            var hre = tag[i].href;
                                                            var newHref = hre.replace("mod/workshop/allocation", "local/backup_course/update/layouts/workshop/allocation");
                                                            tag[i].href = newHref;
                                                        } else
                                                            if (tag[i].href.indexOf('mod/quiz/edit') != -1 && tag[i].href.indexOf('local/backup_course/upd') == -1) {
                                                                var hre = tag[i].href;
                                                                var newHref = hre.replace("mod/quiz/edit", "local/backup_course/update/layouts/quiz/edit");
                                                                tag[i].href = newHref;

                                                                var men = document.getElementsByClassName('last-add-menu')[0];
                                                                if (men && men.getElementsByTagName('ul')[1]) {
                                                                    men.getElementsByTagName('ul')[1].getElementsByTagName('li')[1].addEventListener("click", function (evento) {
                                                                        AFB.envenClick();
                                                                    });
                                                                }

                                                                var elmen = document.getElementsByClassName('pagenumber');
                                                                for (var v = 0; v < elmen.length; v++) {
                                                                    if (elmen[v] && elmen[v].getElementsByTagName('ul')[1]) {
                                                                        elmen[v].getElementsByTagName('ul')[1].getElementsByTagName('li')[1].addEventListener("click", function (evento) {
                                                                            AFB.envenClick();
                                                                        });
                                                                    }

                                                                }
                                                            } else if (tag[i].href.indexOf('/grade/grading/form/rubric/edit') != -1 && tag[i].href.indexOf('local/backup_course/upd') == -1) {
                                                                var hre = tag[i].href;
                                                                var newHref = hre.replace("/grade/grading/form/rubric/edit", "/local/backup_course/update/layouts/rubric/edit");
                                                                tag[i].href = newHref;
                                                            } else if (tag[i].href.indexOf('grade/grading/manage') != -1 && tag[i].href.indexOf('local/backup_course/') == -1) {
                                                                var hre = tag[i].href;
                                                                var newHref = hre.replace("grade/grading/manage", "local/backup_course/update/layouts/rubric/manage");
                                                                tag[i].href = newHref;

                                                            } else if (tag[i].href.indexOf('remove=') != -1 && window.location.href.indexOf('rubric/pick') == -1 && window.location.href.indexOf('grading/pick') == -1) {

                                                                numDelete++;

                                                            }

                }

                if (numDelete > 0) {

                    var script_UPDO = document.createElement('script');
                    script_UPDO.onload = function () {
                        ObAc.agregarCSS();
                    };

                    script_UPDO.src = "../../js/objetsUpdates/updateObjet.js";
                    document.getElementsByTagName('head')[0].appendChild(script_UPDO);

                    let tag2 = document.getElementsByTagName('a');

                    let tagsDelete = 0;

                    let boton = "";

                    for (var i = 0; i < tag2.length; i++) {

                        if (tag2[i].href.indexOf('remove=') != -1 && window.location.href.indexOf('rubric/pick') == -1 && window.location.href.indexOf('grading/pick') == -1) {

                            /*  if (!) { */

                            boton = document.createElement("button");
                            boton.type = "button";
                            boton.classList.add("deleteHijo", "btn", "btn-danger");
                            boton.innerHTML = '<i class=" icon fa fa-trash fa-fw" aria-hidden="true"></i>';
                            tag2[i].parentNode.insertAdjacentElement("afterend", boton);

                            miArray.push(tag2[i].href);

                            tagsDelete++;

                            /*  } */

                        }
                    }

                    var currentUrl = new URLSearchParams(window.location.search);
                    var cmid = currentUrl.get("cmid");
                    var rootFolder = location.protocol + "//" + location.hostname + "/" + location.pathname.split("/")[1];
                    let checkboxes = [];

                    let instancia = 0;
                    let courseId = 0;

                    console.log("Soy rootfolder", rootFolder);

                    $.ajax({
                        url: rootFolder + '/backup_course/update/methods/QRY/class.query.php',
                        data: {
                            key: 'Q02',
                            courseid_h: cmid
                        },
                        type: 'POST',
                        async: false,
                        success: function (id) {
                            id = JSON.parse(id);
                            instancia = id.instance;
                            courseId = id.course;
                            $.ajax({
                                url: rootFolder + '/backup_course/update/methods/QRY/class.query.php',
                                data: {
                                    key: 'Q01',
                                    courseid_h: id.course
                                },
                                type: 'POST',
                                async: false,
                                success: function (json) {

                                    json = JSON.parse(json);

                                    Object.entries(json).forEach(function ([key, value]) {

                                        checkboxes.push(value);

                                    });
                                },
                            });
                        },
                    });

                    /*  console.log("Hola soy checkboxes 1 => " + JSON.stringify(checkboxes));  */

                    let tag3 = document.getElementsByClassName("deleteHijo");

                    for (let i = 0; i < tag3.length; i++) {

                        // Crear el elemento div
                        var divElement = document.createElement("div");

                        // Establecer los atributos del div
                        divElement.setAttribute("id", "miModal" + i);
                        divElement.setAttribute("class", "modalN");

                        // Crear el elemento div para el contenido del modal
                        var contenidoDiv = document.createElement("div");
                        contenidoDiv.setAttribute("class", "modal-contenido");

                        // Crear el elemento span para el botón de cerrar
                        var spanElement = document.createElement("span");
                        spanElement.setAttribute("class", "modal-cerrar");
                        spanElement.setAttribute("id", "modal-cerrar" + i);
                        spanElement.textContent = "×";

                        // Crear el elemento h2 para el título del modal
                        var h2Element = document.createElement("h2");
                        h2Element.textContent = "Eliminar pregunta";

                        // Crear el elemento p para el contenido del modal
                        var pElement = document.createElement("p");
                        pElement.textContent = "¿Desea eliminar esta pregunta?";

                        // Crear el elemento p para el contenido del modal
                        var pElement2 = document.createElement("p");
                        pElement2.textContent = "Seleccione los nodos a los cuales aplicará esta eliminación, en caso de no seleccionar ninguno la eliminación solo se hará en este curso";

                        // Crear el elemento div que contendrá los nodos
                        var divElementNodos = document.createElement("div");
                        /* divElementNodos.setAttribute("class", "divNodos"); */
                        divElementNodos.setAttribute("id", "divNodosId" + i);
                        let cantidadCheckboxes = 0;
                        checkboxes.forEach(function (check) {
                            let nodosCheck = document.createElement("input");
                            nodosCheck.type = "checkbox";
                            nodosCheck.name = "checkbox" + check.id + i;
                            nodosCheck.id = "checkbox" + check.id + i;
                            nodosCheck.value = i + 1;
                            nodosCheck.className = "nodosCheck";

                            // Crear un elemento de etiqueta <label>
                            var label = document.createElement("label");
                            label.htmlFor = "checkbox" + check.id + i;
                            label.textContent = check.url_hijo;

                            divElementNodos.appendChild(nodosCheck);
                            divElementNodos.appendChild(label);

                            cantidadCheckboxes++;
                        });


                        // Crear el contenedor de los botones
                        var divButtons = document.createElement("div");
                        divButtons.setAttribute("class", "modal-footer");

                        // Crear los botones de acción
                        var buttonOk = document.createElement("button");
                        buttonOk.type = "button";
                        buttonOk.classList.add("buttonOk", "btn", "btn-primary");
                        buttonOk.textContent = "Eliminar";
                        buttonOk.addEventListener("click", function () {
                            let listaChecked = [];
                            checkboxes.forEach(function (check) {

                                let nodoSelec = document.getElementById("checkbox" + check.id + i);

                                if (nodoSelec.checked) {
                                    listaChecked.push({
                                        "slot": nodoSelec.value,
                                        "idQuiz": instancia,
                                        "course": courseId
                                    });
                                }
                            });

                            if (listaChecked.length != 0) {
                                $.ajax({
                                    url: rootFolder + '/backup_course/update/methods/QRY/class.query.php',
                                    data: {
                                        key: 'Q03',
                                        listaChecked: listaChecked,
                                    },
                                    type: 'POST',
                                    async: false,
                                    success: function (json) {
                                        console.log("Soy JSON return =>" + json);
                                        json = JSON.parse(json);


                                    },
                                });
                            }


                        });

                        var buttonCancel = document.createElement("button");
                        buttonCancel.type = "button";
                        buttonCancel.id = "modal-cerrar2" + i;
                        buttonCancel.classList.add("buttonCancel", "btn", "btn-secondary");
                        buttonCancel.textContent = "Cancelar";

                        // Añadir los elementos creados como hijos en la estructura del modal

                        divButtons.appendChild(buttonOk);
                        divButtons.appendChild(buttonCancel);

                        contenidoDiv.appendChild(spanElement);
                        contenidoDiv.appendChild(h2Element);
                        contenidoDiv.appendChild(pElement);
                        contenidoDiv.appendChild(pElement2);
                        contenidoDiv.appendChild(divElementNodos);
                        contenidoDiv.appendChild(divButtons);

                        divElement.appendChild(contenidoDiv);

                        document.body.appendChild(divElement);

                        tag3[i].addEventListener("click", function () {

                            var rootFolder = location.protocol + "//" + location.hostname + "/" + location.pathname.split("/")[1];

                            var modal = document.getElementById("miModal" + i);
                            modal.style.display = "block";

                        });

                        var botonCerrar = document.getElementById("modal-cerrar" + i);

                        botonCerrar.addEventListener("click", function () {

                            var modal = document.getElementById("miModal" + i);
                            modal.style.display = "none";

                        });

                        var botonCerrar2 = document.getElementById("modal-cerrar2" + i);

                        botonCerrar2.addEventListener("click", function () {

                            var modal = document.getElementById("miModal" + i);
                            modal.style.display = "none";

                        });
                    }

                    console.log("Hola soy  tagsDelete => " + tagsDelete);
                    console.log("Hola soy  tag3 => " + tag3.length);
                    console.log("Hola soy  miArray => " + JSON.stringify(miArray));
                    console.log("Hola soy  numDelete => " + JSON.stringify(numDelete));

                }
            }
        }, 1500);

        if (window.location.href.indexOf('mod/lesson/editpage') != -1 && window.location.href.indexOf('&qtype=20') != -1) {

            var hre = window.location.href;
            var newHref = hre.replace("mod/lesson/editpage", "local/backup_course/update/layouts/lesson/editpage");

            window.location.href = newHref;
        }

        if (window.location.href.indexOf('mod/lesson/editpage') != -1 && window.location.href.indexOf('&qtype=0') != -1) {

            var hre = window.location.href;
            var newHref = hre.replace("mod/lesson/editpage", "local/backup_course/update/layouts/lesson/editpage");

            window.location.href = newHref;
        }

        if (window.location.href.indexOf('local/backup_course/update/layouts/index.php') != -1) {

            if (document.getElementById('gradetreeform')) {
                var action = document.getElementById('gradetreeform').action;
                action = action.replace("grade/edit/tree", "local/backup_course/update/layouts");
                document.getElementById('gradetreeform').action = action;
            }

            if (document.getElementById('gradetreesubmit')) {
                var btnSave = document.getElementById('gradetreesubmit').getElementsByTagName('input');
                if (btnSave[0]) {
                    btnSave[0].addEventListener("click", function (evento) {
                        AFB.diplayLoader();
                    });
                }
            }

            let calificacion = document.querySelectorAll('div.singlebutton>form');

            if (calificacion.length > 0) {
                calificacion.forEach(element => {
                    if (element.hasAttribute("action")) {
                        let frmAction = element.action;

                        if (frmAction.includes('grade/edit/tree/item')) {
                            element.action = frmAction.replace("/grade/edit/tree/item", "/local/backup_course/update/layouts/grade/edit/item");
                        }
                        if (frmAction.includes('grade/edit/tree/category')) {
                            element.action = frmAction.replace("/grade/edit/tree/category", "/local/backup_course/update/layouts/grade/edit/category");
                        }

                    }
                });
            }

            let edits = document.querySelectorAll('a.dropdown-item.menu-action');

            if (edits.length > 0) {
                edits.forEach(element => {
                    let url = element.href;
                    if (url.includes('grade/edit/tree/item')) {
                        element.href = url.replace("/grade/edit/tree/item", "/local/backup_course/update/layouts/grade/edit/item");
                    }
                });
            }

            if (document.getElementById('menumoveafter') && window.location.href.indexOf('local/backup_course/update/layouts') != -1) {
                $("#menumoveafter").change(function (event) {
                    AFB.diplayLoader();
                });
            }

        }

        if (window.location.href.indexOf('/local/backup_course/update/layouts/modedit.php') != -1) {
            $("#id_submitbutton2").click(function (event) {
                var loader = document.getElementById('overlay-loader_block_modedit');
                if (loader) {
                    loader.style.display = 'block';
                }
            });
        }
        if (window.location.href.indexOf('/quiz/edit.php') != -1) {
            var lapi = document.getElementsByClassName('editing_maxmark');
            for (var n = 0; n < lapi.length; n++) {
                var valo_ant = document.getElementsByClassName('instancemaxmark')[n].innerHTML;
                lapi[n].setAttribute('onclick', 'AFB.focusAction(' + n + ',\'' + valo_ant + '\')');
            }
        }
        if (window.location.href.indexOf('local/backup_course/update/layouts/group/group') != -1) {
            $("#id_submitbutton").click(function (event) {
                var loader = document.getElementById('overlay-loader_block_modedit');
                if (loader) {
                    loader.style.display = 'block';
                }
            });
        }
        if (window.location.href.indexOf('local/backup_course/update/layouts/group/assign.php') != -1) {
            $("#add").click(function (event) {
                var loader = document.getElementById('overlay-loader_block_modedit');
                if (loader) {
                    loader.style.display = 'block';
                }
            });
        }
        if (window.location.href.indexOf('local/backup_course/update/layouts/question/edit.php') != -1 || window.location.href.indexOf('question/category.php') != -1) {
            var formularios = document.getElementsByTagName('form');
            for (var h = 0; h < formularios.length; h++) {
                if (formularios[h].action.indexOf('local/backup_course/update/layouts') == -1) {
                    formularios[h].action = formularios[h].action.replace("question/", "local/backup_course/update/layouts/question/");
                }
            }

        }
        //calificaiones
        if (window.location.href.indexOf('/grade/') != -1) {
            var formulario = document.getElementById('gradesactionselect');
            if (formulario) {
                var select = formulario.getElementsByTagName('select');
                if (select && select[0]) {
                    var op = select[0].getElementsByTagName('option');
                    for (var h = 0; h < op.length; h++) {
                        if (op[h] && op[h].value && op[h].value.indexOf('/grade/edit/tree/index.php') != -1)
                            op[h].value = op[h].value.replace("/grade/edit/tree/index.php", "/local/backup_course/update/layouts/index.php");
                    }
                }
            }
        }
        console.log('aquuu');
        if (window.location.href.indexOf('/user/index') != -1 || window.location.href.indexOf('/enrol/') != -1 ||
            window.location.href.indexOf('/group/') != -1 || window.location.href.indexOf('/admin/roles/') != -1) {
            var conten = document.getElementById('action_bar');
            if (conten) {
                var select = conten.getElementsByTagName('select');
                if (select && select[0]) {
                    var op = select[0].getElementsByTagName('option');
                    for (var h = 0; h < op.length; h++) {
                        if (op[h] && op[h].value && op[h].value.indexOf('/group/index.php') != -1)
                            op[h].value = op[h].value.replace("/group/index.php", "/local/backup_course/update/layouts/group/index.php");
                        if (op[h] && op[h].value && op[h].value.indexOf('/group/groupings.php') != -1)
                            op[h].value = op[h].value.replace("/group/groupings.php", "/local/backup_course/update/layouts/group/groupings.php");
                    }
                }
            }

        }


    };
};

/* function agregarScriptJQuery() {
    var scriptElement = document.createElement('script');
    scriptElement.src = 'https://code.jquery.com/jquery-3.6.1.min.js';
  
    document.head.appendChild(scriptElement);
  } */

function tagsDelete() {

    $.ajax({
        async: false,
        url: '../../methods/DEL/class.delete.php',
        data: {
            key: 'D01'
        },
        type: 'POST',
        success: function (json) { },
        error: function (json, textStatus, errorThrown) { },
        complete: function (json, status) {

        }
    });

}

function cargarBootstrapCSS() {
    var linkElement = document.createElement("link");
    linkElement.rel = "stylesheet";
    linkElement.href = "https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css";
    linkElement.integrity = "sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm";
    linkElement.crossOrigin = "anonymous";

    document.head.appendChild(linkElement);
}

function cargarPopper() {
    var scriptElement = document.createElement("script");
    /* scriptElement.src = "https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js"; */
    scriptElement.src = "https://unpkg.com/@popperjs/core@2";
    /* scriptElement.integrity = "sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q";
    scriptElement.crossOrigin = "anonymous"; */

    document.body.appendChild(scriptElement);
}

function cargarBootstrap() {
    var scriptElement = document.createElement("script");
    scriptElement.src = "https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js";
    scriptElement.integrity = "sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl";
    scriptElement.crossOrigin = "anonymous";

    document.body.appendChild(scriptElement);
}

function insertarModal(id) {

    var contenidoHTML = `
    <div id="miModal${id}" class="modalN">
        <div class="modal-contenido">
            <span class="modal-cerrar" id="modal-cerrar${id}" >&times;</span>
            <h2>Modal Título</h2>
            <p>Este es el contenido del modal.</p>
        </div>
    </div>`;

    document.body.innerHTML += contenidoHTML;
}



/*
 * Método para capturar cambio de ponderación en pregunta del quiz, en url /local/backup_course/update/layouts/quiz/edit.php
 * @param {int} num, posición del div del evento
 * @param {string} nota_ant, nota actual
 * @returns {undefined}
 */
actions_bottons.prototype.focusAction = function (num, nota_ant) {
    setTimeout(function () {
        var elem = document.getElementsByClassName('instancemaxmarkcontainer')[num];
        if (elem.getElementsByTagName('form')[0] && elem.getElementsByTagName('form')[0].getElementsByTagName('input')[0]) {
            var formu = elem.getElementsByTagName('form')[0].getElementsByTagName('input')[0];
            formu.setAttribute('onblur', 'AFB.onblurAction(' + num + ', \'' + nota_ant + '\')');
        } else {
            console.log('aun no existe el tag de formulario');
        }

    }, 800);
};


/*
 * Método para capturar cambio de ponderación en pregunta del quiz, en url /local/backup_course/update/layouts/quiz/edit.php
 * @param {int} num, posición del div del evento
 * @returns {undefined}
 */
actions_bottons.prototype.onblurAction = function (num, not_ant) {
    setTimeout(function () {
        var val_actual = document.getElementsByClassName('instancemaxmark')[num];
        console.log('val_actual', val_actual, not_ant);
        if (not_ant != val_actual.innerHTML) {
            var slot = val_actual.parentNode;
            while (slot.getAttribute("id") == null || slot.getAttribute("id").indexOf('slot') == -1) {
                slot = slot.parentNode;
            }
            AFB.diplayLoader();
            var pt_slot = slot.getAttribute("id").split('-');
            console.log('pt_slot', pt_slot[1]);
            $.ajax({
                async: false,
                url: '../../methods/CRE/class.create.php',
                data: {
                    key: 'Q01',
                    slot: pt_slot[1]
                },
                type: 'POST',
                success: function (json) { },
                error: function (json, textStatus, errorThrown) { },
                complete: function (json, status) {
                    var objUp = JSON.parse(json.responseText);
                    if (objUp && objUp.quizid) {
                        var script_UPD = document.createElement('script');
                        script_UPD.src = "../../js/objetsUpdates/UPD.js";
                        document.getElementsByTagName('head')[0].appendChild(script_UPD);
                        var script_CRE = document.createElement('script');
                        script_CRE.src = "../../js/objetsUpdates/CRE.js";
                        document.getElementsByTagName('head')[0].appendChild(script_CRE);
                        var script_updateObjet = document.createElement('script');
                        script_updateObjet.onload = function () {
                            var script_saveLog = document.createElement('script');
                            script_saveLog.onload = function () {
                                SLog.confir_nodos_actu(objUp.courseid, objUp.courseid, "course", objUp.userid, json.responseText, 0, "../../../", location.href);
                            };
                            script_saveLog.src = "../../js/objetsUpdates/saveLog.js";
                            document.getElementsByTagName('head')[0].appendChild(script_saveLog);
                        };
                        script_updateObjet.src = "../../js/objetsUpdates/updateObjet.js";
                        document.getElementsByTagName('head')[0].appendChild(script_updateObjet);

                    } else {
                        document.getElementById('overlay-loader_block_modedit').innerHTML = json.responseText;
                    }


                }
            });
        }
        console.log('val_actual', val_actual.innerHTML);
        console.log('not_ant', not_ant);
    }, 800);


};



/*
 * Método para cambiar el valor del boton cancelar
 * @returns {undefined}
 */
actions_bottons.prototype.btnCancel = function () {
    if (document.getElementById('id_cancel')) {
        document.getElementById('id_cancel').setAttribute("value", "Volver al curso sin guardar");
    }
};

/*
 * Comparar la informacion del formulario con el objeto de la actividad
 * @param {obj} event
 * @param {string} type_act
 * @returns {obC01}
 */
actions_bottons.prototype.objSave = function (type_act, data, id_course) {

    var obC01 = {};

    var obAct = ObAc.switchActivitiesUpdate(type_act, id_course);

    console.log('type_act->', type_act);

    console.log('obAct->', obAct);

    if (typeof data.serializeArray === 'function') {

        console.log("FUNCTION");

        for (var k in obAct) {

            obC01[k] = {};
            obC01[k] = AFB.objColumnaForm(obAct[k], data.serializeArray());
        }

    } else {

        console.log("NO FUNCTION");

        for (var k in obAct) {
            console.log("Hola soy k " + k);
            console.log("Hola soy obAct[k] " + JSON.stringify(obAct[k]));
            obC01[k] = {};
            obC01[k] = AFB.objColumna(k, obAct[k], data);
        }

    }

    if (obC01.course_modules) {

        if (!obC01.course_modules.showdescription) {
            obC01.course_modules.showdescription = 0;
        }
    }

    console.log('objSave->obC01', obC01);

    return obC01;
};

/*
 * Recorrer el objeto para encontrar el nombre de la columna en la bd si es un formulario
 * @param {obj} obj
 * @param {array} data
 * @returns {undefined}
 */
actions_bottons.prototype.objColumnaForm = function (obj, data) {
    var objColumnaForm = {};
    $.each(obj, function (key, val) {
        for (var i = 0; i < data.length; i++) {
            if (key == data[i].name) {
                objColumnaForm[val] = data[i].value;
            }
        }
    });
    return objColumnaForm;
};

/*
 * Recorrer el objeto para encontrar el nombre de la columna en la bd
 * @param {string} k nombre de la tabla
 * @param {obj} obj
 * @param {array} data
 * @returns {undefined}
 */
actions_bottons.prototype.objColumna = function (nombre, obj, data) {

    var obColumnas = {};

    $.each(obj, function (key, val) {
        $.each(data, function (k, v) {
            if (key == k) {

                if (nombre == 'course_modules' && val == 'visible') {

                    obColumnas[val] = v;
                    obColumnas['visibleold'] = v;

                } else {

                    obColumnas[val] = v;

                }
            }

        });
    });

    console.log("Hola soy data => " + JSON.stringify(obColumnas));

    return obColumnas;
};


actions_bottons.prototype.prueba = function (pos, dat, name) {
    var ob = {};
    for (var j = 1; j < pos.length; j++) {
        var pos1 = pos[j].split("]");
        ob[pos1[0]] = dat;
        //console.log(pos[0],pos1[0]);
    }
    return ob;
}

/*
 * Llamar método deacuerdo a la pagina y el boton
 * @param {type} Y
 * @param {int} id_course
 * @param {int} id_act
 * @param {string} type_act
 * @param {int} section
 * @param {int} id_user
 * @returns {undefined}
 */
actions_bottons.prototype.clickInButton = function (Y, id_course, id_act, type_act, section, id_user) {
    window.onload = function () {



        if (window.location.href.indexOf('/mod/feedback/edit_item.php') != -1) {
            $("#id_update_item").click(function (event) { // /mod/feedback/edit_item.php Actualizar una pregunta feedback
                //event.preventDefault();
                var form = $('#mform1');
                AFB.saveAndReturnActivities(Y, id_course, id_act, type_act, section, id_user, event, form, '../../')
            });
        }
        if (window.location.href.indexOf('/mod/feedback/edit_item.php') != -1) {
            $("#id_save_item").click(function (event) { // /mod/feedback/edit_item.php crear nueva una pregunta feedback
                var form = $('#mform1');
                AFB.saveAndReturnActivities(Y, id_course, id_act, type_act, section, id_user, event, form, '../../')
            });
        }
        if (window.location.href.indexOf('/mod/feedback/edit.php?') != -1) {
            var form = $('#feedback_edit_form');
            var eventMenu = document.getElementsByClassName('menu  align-tr-br');
            for (var i = 1; i < eventMenu.length; i++) {
                var liTag = eventMenu[i].getElementsByTagName('li');
                for (var j = 1; j < (liTag.length - 1); j++) {
                    liTag[j].addEventListener("click", function (event) {
                        AFB.saveAndReturnActivities(Y, id_course, id_act, type_act, section, id_user, event, form, '../../');
                    });
                }
                liTag[liTag.length - 1].addEventListener("click", function (event) {
                    var deleteBtn = document.getElementsByClassName('btn btn-primary m-r-1');
                    setTimeout(function () {
                        deleteBtn[0].addEventListener("click", function (event) {
                            AFB.saveAndReturnActivities(Y, id_course, id_act, type_act, section, id_user, event, form, '../../');
                        });
                    }, 1000);
                });
            }

        }
        if (window.location.href.indexOf('/mod/lesson/editpage.php?') != -1) {
            $("#id_submitbutton").click(function (event) { ///mod/lesson/editpage.php Editar preguntas y Cluster dentro de una página  lesson
                var form = $('#mform2');
                AFB.saveAndReturnActivities(Y, id_course, id_act, type_act, section, id_user, event, form, '../../');
            });
        }
        if (window.location.href.indexOf('mod/lesson/lesson.php?') != -1 && window.location.href.indexOf('confirmdelete&pageid') != -1) { //eliminar page
            var formulario = document.getElementsByTagName('form')[0].getElementsByTagName('input');
            formulario[0].addEventListener("click", function (event) {
                var form = $('form');
                AFB.saveAndReturnActivities(Y, id_course, id_act, type_act, section, id_user, event, form, '../../');
            });
        }
        if (window.location.href.indexOf('grade/edit/tree/index.php') != -1) { ///CALificaciones
            var btnSave = document.getElementById('gradetreesubmit').getElementsByTagName('input');
            btnSave[0].addEventListener("click", function (evento) {
                //evento.preventDefault();
                var form = $('#gradetreeform');
                AFB.saveAndReturnActivities(Y, id_course, id_act, type_act, section, id_user, evento, form, '../../../');
            });

        }

    };
};

/*
 * Método para crear el objeto del curso
 * @param {int} id_course
 * @returns {undefined}
 */
actions_bottons.prototype.objCourse = function (id_course, id_user, lugar, objUp) {
    var obAct = ObAc.switchActivitiesUpdate('course', id_course);
    var info = OUPDUpd.obU01(id_course, obAct, id_user, objUp.id_updates_log);
    //console.log('info>>',info);
    $.ajax({
        async: true,
        url: lugar + '/local/backup_course/update/methods/' + info.type + info.ws_url,
        data: info,
        type: 'POST',
        success: function (json) { },
        error: function (json, textStatus, errorThrown) { },
        complete: function (json, status) {

        }
    });
};

/*
 * Método para guardar en las tablas updates_courses y updates_log, los cambios realizados en la actividad
 * @param {type} Y
 * @param {int} id_course
 * @param {int} id_act
 * @param {string} type_act
 * @param {int} section
 * @param {int} id_user
 * @returns {undefined}
 */
actions_bottons.prototype.saveAndReturnActivities = function (Y, id_course, id_act, type_act, section, id_user, event, form, lugar) {

    var obC01 = type_act != 'course' ? AFB.objSave(type_act, form, id_course) : obC01 = ObAc.switchActivitiesUpdate('course', id_course);
    var info = OCREUp.obC01(id_course, id_act, type_act, obC01, id_user);

    console.log("Hola soy save and return => " + info);

    $.ajax({
        //async: true,
        url: lugar + 'local/backup_course/update/methods/' + info.type + info.ws_url,
        data: info,
        type: 'POST',
        success: function (json) { },
        error: function (json, textStatus, errorThrown) { },
        complete: function (json, status) {
            if (status != 'error') {
                try {
                    var objUp = JSON.parse(json.responseText);
                    if (type_act == 'course') {
                        //console.log('->>>>>>>>>>');
                        AFB.objCourse(id_course, id_user, '../../../', objUp);
                    } else {
                        AFB.notificNodos(id_course, objUp, obC01, lugar, id_act, type_act);
                    }

                } catch (e) {

                } finally {

                }
            }
        }
    });
};

/*
 * Metodo para guardar en l atabla update_nodos y notificar la actualización a los nodos
 * @param {int} id_course
 * @param {obj} obj
 * @param {obj} obC01
 * @returns {undefined}
 */
actions_bottons.prototype.notificNodos = function (id_course, obj, obC01, lugar, id_act, type_act) {
    var info = OCREUp.obC02(id_course, obj.id_updates_log, obC01, id_act, type_act);
    //console.log('info',info);
    $.ajax({
        //async: true,
        url: lugar + 'local/backup_course/update/methods/' + info.type + info.ws_url,
        data: info,
        type: 'POST',
        success: function (json) { },
        error: function (json, textStatus, errorThrown) { },
        complete: function (json, status) {
            if (status != 'error') {
                try {
                    console.log(json.responseText);
                } catch (e) {

                } finally {

                }
            }
        }
    });
};


/*
 * Metodo para leer los link de ventanas modales para geregar una pregunta a un quiz
 * @returns {undefined}
 */
actions_bottons.prototype.envenClick = function () {
    setTimeout(function () {
        var tag2 = document.getElementsByTagName('a');
        for (var m = 0; m < tag2.length; m++) {
            if (tag2[m].href.indexOf('mod/quiz/edit.') != -1) {
                var hre = tag2[m].href;
                var newHref = hre.replace("mod/quiz/edit.", "local/backup_course/update/layouts/quiz/edit.");
                tag2[m].href = newHref;
            }
        }
        var formularios = document.getElementsByTagName('form');
        for (var h = 0; h < formularios.length; h++) {
            var inp = formularios[h].getElementsByClassName('btn-primary');
            if (inp[0]) {
                inp[0].addEventListener("click", function (evento) {
                    AFB.diplayLoader();
                });
            }
        }
        console.log('ya');
    }, 3500);
};

/*
 * Mostrar el loader
 * @type type
 */
actions_bottons.prototype.diplayLoader = function () {
    document.getElementById("overlay-loader_block_modedit").style.display = "block";
};



/*Alertas para confirmar*/
var conFirSave = {
    confirmarReturn: function (event) {
        /*Preguntar al usuario si está seguro de enviar las actualizaciones*/
        $.confirm({
            title: 'ACTUALIZAR',
            content: 'Tenga en cuenta, que se enviará una actualización a los nodos de este padre',
            icon: 'fa fa-question',
            theme: 'modern',
            closeIcon: true,
            animation: 'scale',
            type: 'red',
            buttons: {
                actualizar: {
                    text: 'Actualizar',
                    btnClass: 'btn-dark btn-update',
                    action: function () {
                        console.log(event, evento);
                        event.originalEvent.explicitOriginalTarget;

                    }
                },
                cancel: {
                    btnClass: 'btn-red',
                    text: 'Cancelar',
                    action: function () {
                        evento = null;
                    }
                }
            }
        });
    }
}