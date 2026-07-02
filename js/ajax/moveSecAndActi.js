class moveSecAndActi {

    move_dis() {

        /* mov.cargarBootstrapCSS(); */
        mov.agregarCSS();
        mov.cargarJQuery();
        /* mov.cargarBootstrapCSS();
        mov.agregarCSS();
        mov.cargarJQuery();
        mov.cargarPopper();
        mov.cargarBootstrap();   
         */
        /* mov.cargarJQuery(); */
        /*         mov.cargarPopper();
                mov.cargarBootstrap();  */
        const dragElement = document.querySelectorAll('li.activity.activity-wrapper');

        const dragElemenHead = document.querySelectorAll('li.section.course-section');

        const dragElemenHead2 = document.querySelectorAll('div.course-section-header');

        const allElements = [dragElement, dragElemenHead, dragElemenHead2];

        let checkboxes = [];

        if (allElements !== null) {

            allElements.forEach(function (elemento) {

                elemento.forEach(function (elemento2) {

                    elemento2.addEventListener('dragend', (event) => {

                        var currentUrl = new URLSearchParams(window.location.search);
                        var id = currentUrl.get("id");
                        var rootFolder = location.protocol + "//" + location.hostname + "/" + location.pathname.split("/")[1];

                        $.ajax({
                            url: rootFolder + '/backup_course/update/methods/QRY/class.query.php',
                            data: {
                                key: 'Q01',
                                courseid_h: id
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

                        let new2 = mov.generarModal(id, checkboxes);

                        document.body.appendChild(new2);

                        let modal = document.getElementById("miModal");

                        modal.style.display = "block";

                        let botonCerrar = document.getElementById("modal-cerrar");

                        botonCerrar.addEventListener("click", function () {

                            let modal = document.getElementById("miModal");
                            let modalC = document.getElementsByClassName("modal-contenido");
                            let modalN = document.getElementsByClassName("nodosCheck");

                            /* modalC.remove(); */
                            modalN.innerHTML = '';
                            modal.innerHTML = '';
                            modalC.innerHTML = '';
                            modal.style.display = "none";
                            modal.remove();
                            checkboxes = [];

                        });

                        let botonActualizar = document.getElementById("buttonAct");

                        botonActualizar.addEventListener("click", function () {

                            let checkUpd = [];

                            let nodosCheck = document.getElementsByClassName("nodosCheck");

                            for (let i = 0; i < nodosCheck.length; i++) {

                                if (nodosCheck[i].checked) {

                                    console.log(nodosCheck[i].id);

                                    checkUpd.push({
                                        "urlHijo": nodosCheck[i].value,
                                        "cursoIdPadre": id
                                    });
                                }
                            }

                            if (checkUpd.length != 0) {

                                let mensaje = document.getElementById("mensaje");
                                mensaje.innerHTML = '';

                                $.ajax({
                                    url: rootFolder + '/backup_course/update/methods/QRY/class.query.php',
                                    data: {
                                        key: 'Q04',
                                        checkUpd: checkUpd
                                    },
                                    type: 'POST',
                                    async: false,
                                    success: function (json) {
                                        botonCerrar.click();
                                    },
                                });

                            } else {

                                let mensaje = document.getElementById("mensaje");
                                if (mensaje) {
                                    mensaje.innerHTML = 'Debe seleccionar minimo un nodo';
                                }
                            }
                        });

                    });
                });

            });

        } else {

            console.log('El elemento no existe.');

        }
    }

    generarModal(id, checkboxes) {

        // Crear el elemento div
        var divElement = document.createElement("div");

        // Establecer los atributos del div
        divElement.setAttribute("id", "miModal");
        divElement.setAttribute("class", "modalN");

        // Crear el elemento div para el contenido del modal
        var contenidoDiv = document.createElement("div");
        contenidoDiv.setAttribute("class", "modal-contenido");

        // Crear el elemento span para el botón de cerrar
        var spanElement = document.createElement("span");
        spanElement.setAttribute("class", "modal-cerrar");
        spanElement.setAttribute("id", "modal-cerrar");
        spanElement.textContent = "×";

        // Crear el elemento h2 para el título del modal
        var h2Element = document.createElement("h2");
        h2Element.textContent = "Mover actividad";

        // Crear el elemento p para el contenido del modal
        var pElement = document.createElement("p");
        pElement.textContent = "¿Desea realizar este movimiento en los hijos?";

        // Crear el elemento p para el contenido del modal
        var pElement2 = document.createElement("p");
        pElement2.textContent = "Seleccione los nodos a los cuales aplicará esta actualización";

        // Crear el elemento div que contendrá los nodos
        var divElementNodos = document.createElement("div");
        /* divElementNodos.setAttribute("class", "divNodos"); */
        divElementNodos.setAttribute("id", "divNodosId");

        let cantidadCheckboxes = 0;

        checkboxes.forEach(function (check) {

            let nodosCheck = document.createElement("input");

            nodosCheck.type = "checkbox";
            nodosCheck.name = "checkbox" + check.id;
            nodosCheck.id = "checkbox" + check.id;
            nodosCheck.value = check.url_hijo;
            nodosCheck.className = "nodosCheck";

            // Crear un elemento de etiqueta <label>
            var label = document.createElement("label");
            label.htmlFor = "checkbox" + check.id;
            label.textContent = check.url_hijo;

            let nodosDiv = document.createElement("div");

            nodosDiv.appendChild(nodosCheck);
            nodosDiv.appendChild(label);

            divElementNodos.appendChild(nodosDiv);

            cantidadCheckboxes++;

        });

        let mensaje = document.createElement("div");
        let textoMensaje = document.createElement("h3");
        textoMensaje.style.color = 'red';
        textoMensaje.style.textAlign = 'center';
        textoMensaje.classList.add("message");
        textoMensaje.id = "mensaje";
        mensaje.appendChild(textoMensaje);
        divElementNodos.appendChild(mensaje);

        let divBtn = document.createElement("div");
        divBtn.style.textAlign = 'end';

        let buttonAct = document.createElement("button");
        buttonAct.type = "button";
        buttonAct.classList.add("buttonAct", "btn", "btn-primary");
        buttonAct.textContent = "Actualizar";
        buttonAct.id = "buttonAct";


        divBtn.appendChild(buttonAct);
        divElementNodos.appendChild(divBtn);

        /*      divButtons.appendChild(buttonOk);
             divButtons.appendChild(buttonCancel); */

        contenidoDiv.appendChild(spanElement);
        contenidoDiv.appendChild(h2Element);
        contenidoDiv.appendChild(pElement);
        contenidoDiv.appendChild(pElement2);
        contenidoDiv.appendChild(divElementNodos);
        /* contenidoDiv.appendChild(divButtons); */

        divElement.appendChild(contenidoDiv);

        return divElement;

    }

    cargarJQuery() {
        var scriptElement = document.createElement('script');
        scriptElement.src = 'https://code.jquery.com/jquery-3.6.3.min.js';

        document.head.appendChild(scriptElement);
    }

    cargarBootstrapCSS() {
        var linkElement = document.createElement("link");
        linkElement.rel = "stylesheet";
        linkElement.href = "https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css";
        /*         linkElement.integrity = "sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm";
                linkElement.crossOrigin = "anonymous"; */

        document.head.appendChild(linkElement);
    }

    cargarPopper() {
        var scriptElement = document.createElement("script");
        scriptElement.src = "https://unpkg.com/@popperjs/core@2";
        document.body.appendChild(scriptElement);
    }

    cargarBootstrap() {
        var scriptElement = document.createElement("script");
        scriptElement.src = "https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js";
        scriptElement.integrity = "sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy";
        scriptElement.crossOrigin = "anonymous";

        document.body.appendChild(scriptElement);
    }

    agregarCSS = function () {
        // Crear un elemento <style>
        let estilo = document.createElement("style");

        // Agregar el código CSS deseado
        estilo.innerHTML = `
        .modalN {
            display: none;
            position: fixed;
            z-index: 3;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
          }
          
          .modal-contenido {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
          }
          
          .modal-cerrar {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
          }
    
        `;

        // Agregar el elemento <style> al elemento <head>
        document.head.appendChild(estilo);
    };


}

let mov = new moveSecAndActi();