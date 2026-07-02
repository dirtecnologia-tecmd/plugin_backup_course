/* 
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Other/javascript.js to edit this template
 @Creado: 13/08/2024 8:30:29 a. m.
 @Autora: Daniela Sierra Vergel 
 */
class CRUD {
    constructor() {
        this.categoriasAll = {};
        this.listar_instancias();
    }
    
    listar_instancias(parent = 0) {  
        let datosFormulario = {};
        datosFormulario['key'] = 'Q02';
        // Enviar los datos por AJAX a un archivo PHP  
        fetch("methods/crud.php", {  
          method: 'POST',  
          headers: { 'Content-Type': 'application/json'  },  
          body: JSON.stringify(datosFormulario)  
        })  
        .then(response => {  
          if (!response.ok) { throw new Error('Network response was not ok');}  
          return response.json();  
        })  
        .then(data => {
          this.mostrarInstancias(data);
          this.listar_categorys();
        })  
        .catch((error) => {  console.error('Error:', error);      });  
    } 
  
    mostrarInstancias(instancias) {
        const container = document.getElementById(`list_instancias`);
        container.innerHTML = ''; // Limpiar el contenedor antes de llenarlo

        for (let k in instancias) {
            let v = instancias[k];
            const li = document.createElement('option');
            li.value = `${v['id']}`;
            li.innerHTML = `${v['nombre']}`;
            container.appendChild(li);
        }
    }
  
  
    listar_categorys(parent = 0) {  
        let datosFormulario = {};
        datosFormulario['key'] = 'Q01';
        datosFormulario['parent'] = parent;
        // Enviar los datos por AJAX a un archivo PHP  
        fetch("methods/crud.php", {  
          method: 'POST',  
          headers: { 'Content-Type': 'application/json'  },  
          body: JSON.stringify(datosFormulario)  
        })  
        .then(response => {  
          if (!response.ok) { throw new Error('Network response was not ok');}  
          return response.json();  
        })  
        .then(data => {
          this.mostrarCategorias(parent, data);
          this.listar_guardados()
        })  
        .catch((error) => {  console.error('Error:', error);      });  
    } 
  
    mostrarCategorias(parent, categorias) {
        const container = document.getElementById(`accordionCateg${parent}`);
        container.innerHTML = ''; // Limpiar el contenedor antes de llenarlo

        for (let k in categorias) {
            let v = categorias[k];

            // Crear el botón de la categoría
            const button = document.createElement('button');
            button.className = 'accordionCateg-button';
            button.innerText = `${v['name']}`;

            // Crear el span para el conteo
            const span = document.createElement('span');
            span.innerHTML = `${v['coursecount']}`;

            // Crear el checkbox
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.className = 'category-checkbox';
            checkbox.id = `check${v['id']}`;

            // Añadir el checkbox y el span al botón
            button.appendChild(span);
            button.appendChild(checkbox);

            button.onclick = () => {// Configurar el evento onclick del botón
                this.listar_categorys(v['id']);
                const div = document.getElementById(`accordionCateg${v['id']}`);
                div.classList.toggle('active');
            };

            checkbox.addEventListener('change', () => {// Añadir un evento change al checkbox
                if (checkbox.checked)  this.handleCheckedCategory(this.categoriasAll[v['id']]); // Llama a la función cuando el checkbox está activo
                else this.quitarCheckedCategory(this.categoriasAll[v['id']]);
            });

            // Crear el contenedor de contenido
            const div = document.createElement('div');
            div.id = `accordionCateg${v['id']}`;
            div.className = 'accordionCateg-content'; // Añadir clase de contenido

            // Añadir el botón y el contenedor al contenedor principal
            container.appendChild(button);
            container.appendChild(div);
        }


        if (parent > 0){
            if (this.categoriasAll[parent] && this.categoriasAll[parent]['cursos']) {
                this.mostrarCursos(this.categoriasAll[parent]['cursos']);
            }
        }

        this.categoriasAll = Object.assign({}, this.categoriasAll, categorias);
    }
 
    mostrarCursos(cursos) {
        const container = document.getElementById(`listCourse`);
        container.innerHTML = ''; // Limpiar el contenedor antes de llenarlo

        for (let k in cursos) {
             let v = cursos[k];
            const li = document.createElement('li');
            li.innerHTML = `${v['fullname']}<span>${v['shortname']}</span>`;
            
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.className = 'course-checkbox';
            checkbox.id = `checkCourse${v['id']}`;
            
            li.appendChild(checkbox);
            container.appendChild(li);
            
            // Añadir un evento change al checkbox
            checkbox.addEventListener('change', () => {
                if (checkbox.checked) this.seleccionados(v['id'], v['shortname']) // Llama a la función cuando el checkbox está activo
                else this.quitarCheckedCourse(v['id'], v['shortname']);
            });

        }
    }
  
    buscarC(escr){
        let datosFormulario = {};
        datosFormulario['key'] = 'Q03';
        datosFormulario['search'] = escr;
        // Enviar los datos por AJAX a un archivo PHP  
        fetch("methods/crud.php", {  
          method: 'POST',  
          headers: { 'Content-Type': 'application/json'  },  
          body: JSON.stringify(datosFormulario)  
        })  
        .then(response => {  
          if (!response.ok) { throw new Error('Network response was not ok');}  
          return response.json();  
        })  
        .then(data => {
          this.mostrarCursos(data);
        })  
        .catch((error) => {  console.error('Error:', error);      });  
    }
    
    seleccionados(id, short) {
        let seleccionados = document.getElementById('seleccionados');
        let view_selecci = document.getElementById('view_selecci');

        // Convertir el valor de los campos en arrays, manejando el caso de vacío
        let seleccionadosArray = seleccionados.value ? seleccionados.value.split(',').map(item => item.trim()) : [];
        let viewSeleccionArray = view_selecci.value ? view_selecci.value.split(',').map(item => item.trim()) : [];

        // Solo añadir el ID si no está presente
        if (!seleccionadosArray.includes(id)) {
            seleccionadosArray.push(id);
        }

        // Solo añadir el nombre corto si no está presente
        if (!viewSeleccionArray.includes(short)) {
            viewSeleccionArray.push(short);
        }

        // Actualizar los campos con los valores modificados
        seleccionados.value = seleccionadosArray.join(',');
        view_selecci.value = viewSeleccionArray.join(', ');
    }
    
    limpiarSeleccion() {
        document.getElementById('seleccionados').value = '';
        document.getElementById('view_selecci').value = '';
    }
    
    obligatorios() {
        let selectElement = document.getElementById('list_instancias');
        let instances = Array.from(selectElement.selectedOptions).map(option => option.value);
    
        let courses = document.getElementById('seleccionados').value;
        let permiso = document.getElementById('estado').value;
        let actividades = document.getElementById('actividades').value;
        let secciones = document.getElementById('secciones').value;
        let recursos = document.getElementById('recursos').value;
        let reemplazar = document.getElementById('reemplazar');
        let todos = document.getElementById('todosCourses');
        // Comprobar si alguno de los campos está vacío
        if (instances.length === 0 || !permiso || !actividades || !secciones || !recursos) {
            msjBC.error('Todos los campos son obligatorios', 'Diligencie todos los campos')
        }else if(!todos.checked && !courses){
            msjBC.error('Seleccionar Cursos', 'Si no aplica el permiso a toda la instancia, debe seleccionar los cursos')
        }else{
            
            let datosFormulario = {};
            datosFormulario['key'] = 'C01';
            datosFormulario['instances'] = instances;
            datosFormulario['courses'] = courses;
            datosFormulario['permiso'] = permiso;
            datosFormulario['actividades'] = actividades;
            datosFormulario['secciones'] = secciones;
            datosFormulario['recursos'] = recursos;
            datosFormulario['reemplazar'] = reemplazar.checked ? 1 : 0;

            // Enviar los datos por AJAX a un archivo PHP  
            fetch("methods/crud.php", {  
                method: 'POST',  
                headers: { 'Content-Type': 'application/json'  },  
                body: JSON.stringify(datosFormulario)  
            })  
            .then(response => {  
                if (!response.ok) { throw new Error('Network response was not ok');}  
                return response.json();  
            })  
            .then(data => {
                
                document.getElementById('list_instancias').value = '';
                document.getElementById('estado').value = 0;
                document.getElementById('actividades').value = '';
                document.getElementById('secciones').value = '';
                document.getElementById('recursos').value = '';
                msjBC.ok('Permisos Creados', 'Se crearon los pemisos')
                this.listar_guardados();
                this.limpiarSeleccion();
            })  
            .catch((error) => {  console.error('Error:', error);      });  
        }

    }
    
    
    listar_guardados(){
        let datosFormulario = {};
        datosFormulario['key'] = 'Q04';
        // Enviar los datos por AJAX a un archivo PHP  
        fetch("methods/crud.php", {  
          method: 'POST',  
          headers: { 'Content-Type': 'application/json'  },  
          body: JSON.stringify(datosFormulario)  
        })  
        .then(response => {  
          if (!response.ok) { throw new Error('Network response was not ok');}  
          return response.json();  
        })  
        .then(data => {
          this.mostrarGuardados(data);
        })  
        .catch((error) => {  console.error('Error:', error);      }); 
    }
    
    mostrarGuardados(lista) {
        const container = document.getElementById('listPermis');
        container.innerHTML = ''; // Limpiar el contenedor antes de llenarlo

        for (let k in lista) {
            let v = lista[k];
            const tr = document.createElement('tr');// Crear una nueva fila de la tabla
            tr.id = 'permiso_' + v['id'];
            // Crear y agregar celdas con los valores correspondientes
            tr.innerHTML = `
                <td> ${v['nombre'] ? v['nombre'] : ''}</td>
                <td> ${v['fullname'] ? v['fullname'] : 'Toda la instancia'} </td>
                <td> ${v['shortname'] ? v['shortname'] : ''}</td>
                <td>
                    <select class="form-select" id="estado_${v['id']}" name="estado_${v['id']}">
                        <option value="0" ${v['permiso'] == 0 ? 'selected' : ''}>Desbloqueado</option>
                        <option value="1" ${v['permiso'] == 1 ? 'selected' : ''}>Restringido parcialmente</option>
                        <option value="2" ${v['permiso'] == 2 ? 'selected' : ''}>Restringido completamente</option>
                    </select>
                </td>
                
                <td>
                    <select class="form-select" id="reemplazar_${v['id']}" name="reemplazar_${v['id']}">
                        <option value="0" ${v['reemplazar'] == 0 ? 'selected' : ''}>NO</option>
                        <option value="1" ${v['reemplazar'] == 1 ? 'selected' : ''}>SI</option>
                    </select>
                </td>
            
                <td contenteditable="true">${v['cant_secciones']}</td>
                <td contenteditable="true">${v['cant_actividades']}</td>
                <td contenteditable="true">${v['cant_recursos']}</td>
                <td>
                    <i class="fa fa-trash-o"  onclick="crud.deletePermiso(${v['id']})" title="Eliminar Permiso"></i>
                    <i class="fa fa-floppy-o" onclick="crud.updatePermiso(${v['id']})" title="Guardar Edición"></i>
                </td>`;
            
            container.appendChild(tr);// Añadir la fila a la tabla
        }
    }

    
   
    
    handleCheckedCategory(categoria){
        if(categoria && categoria['cursos']){
            let courses = categoria['cursos'];
            for (let k in courses) {
                crud.seleccionados(courses[k]['id'], courses[k]['shortname'])
            }
        }
    }
    
    quitarCheckedCategory(categoria) {
        if (categoria && categoria['cursos']) {
            let courses = categoria['cursos'];

            let seleccionados = document.getElementById('seleccionados');
            let view_selecci = document.getElementById('view_selecci');

            // Convertir el valor de 'seleccionados' en un array
            let seleccionadosArray = seleccionados.value.split(',').map(item => item.trim());
            let viewSeleccionArray = view_selecci.value.split(',').map(item => item.trim());

            // Eliminar los IDs y nombres cortos de los arrays
            for (let k in courses) {
                let courseId = courses[k]['id'];
                let courseShortname = courses[k]['shortname'];

                seleccionadosArray = seleccionadosArray.filter(id => id !== courseId);
                viewSeleccionArray = viewSeleccionArray.filter(short => short !== courseShortname);
            }

            // Actualizar los campos con los valores modificados
            seleccionados.value = seleccionadosArray.join(',');
            view_selecci.value = viewSeleccionArray.join(', ');
        }
    }
    
    quitarCheckedCourse(courseId, courseShortname) {
        // Obtener los elementos de los campos de selección
        let seleccionados = document.getElementById('seleccionados');
        let view_selecci = document.getElementById('view_selecci');

        // Convertir los valores de los campos en arrays
        let seleccionadosArray = seleccionados.value.split(',').map(item => item.trim());
        let viewSeleccionArray = view_selecci.value.split(',').map(item => item.trim());

        // Filtrar los arrays para eliminar el ID y el nombre corto del curso
        seleccionadosArray = seleccionadosArray.filter(id => id !== courseId);
        viewSeleccionArray = viewSeleccionArray.filter(short => short !== courseShortname);

        // Actualizar los campos con los valores modificados
        seleccionados.value = seleccionadosArray.join(',');
        view_selecci.value = viewSeleccionArray.join(', ');
    }
    
    
    
    deletePermiso(id){
        let datosFormulario = {};
        datosFormulario['key'] = 'D01';
        datosFormulario['id'] = id;
        // Enviar los datos por AJAX a un archivo PHP  
        fetch("methods/crud.php", {  
          method: 'POST',  
          headers: { 'Content-Type': 'application/json'  },  
          body: JSON.stringify(datosFormulario)  
        })  
        .then(response => {  
          if (!response.ok) { throw new Error('Network response was not ok');}  
          return response.json();  
        })  
        .then(data => {
            msjBC.error('Permiso Eliminado', 'Se eliminó el permiso')
            this.listar_guardados();
        })  
        .catch((error) => {  console.error('Error:', error);      }); 
    }
    
    updatePermiso(id){
        let fila = document.getElementById('permiso_' +id)
        let celdas = fila.getElementsByTagName('td');
        let reempla = document.getElementById('reemplazar_' +id)
        let estad = document.getElementById('estado_' +id)
        let datosFormulario = {};
        datosFormulario['key'] = 'U01';
        datosFormulario['id'] = id;
        datosFormulario['cant_secciones'] = estad.value =='2' ? 0:celdas[5].innerText;
        datosFormulario['cant_actividades']=estad.value =='2' ? 0:celdas[6].innerText;
        datosFormulario['cant_recursos']  = estad.value =='2' ? 0:celdas[7].innerText;
        datosFormulario['permiso'] = estad.value;
        datosFormulario['reemplazar'] = reempla.value;
        // Enviar los datos por AJAX a un archivo PHP  
        fetch("methods/crud.php", {  
          method: 'POST',  
          headers: { 'Content-Type': 'application/json'  },  
          body: JSON.stringify(datosFormulario)  
        })  
        .then(response => {  
          if (!response.ok) { throw new Error('Network response was not ok');}  
          return response.json();  
        })  
        .then(data => {
            msjBC.informacion('Permiso Editado', 'Se editó el permiso')
            this.listar_guardados();
        })  
        .catch((error) => {  console.error('Error:', error);      }); 
    }
    
    handleEstadoChange(){
        const estadoSelect = document.getElementById('estado');
        const seccionesInput = document.getElementById('secciones');
        const actividadesInput = document.getElementById('actividades');
        const recursosInput = document.getElementById('recursos');

        if (estadoSelect.value === '2') { // Restringido completamente
            seccionesInput.value = 0;
            actividadesInput.value = 0;
            recursosInput.value = 0;

            seccionesInput.disabled = true;
            actividadesInput.disabled = true;
            recursosInput.disabled = true;
        } else { // Desbloqueado o Restringido parcialmente
            seccionesInput.disabled = false;
            actividadesInput.disabled = false;
            recursosInput.disabled = false;
        }
    }



}

const crud = new CRUD();
