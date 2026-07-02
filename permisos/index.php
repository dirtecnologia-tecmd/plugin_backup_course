<?php

/* 
@Creado: 2/08/2024 6:13:11 p. m.
@Autora: Daniela Sierra Vergel 
 */

require_once('../../../config.php');

$PAGE->requires->css('/local/backup_course/css/style.css');
$PAGE->requires->css('/local/backup_course/css/tostadas.css');
$PAGE->requires->js('/local/backup_course/js/mensajes.js');
$PAGE->requires->js('/local/backup_course/permisos/js/crud_permisos.js');
$context = context_system::instance();
$PAGE->set_context($context);
require_login(null, false);
$PAGE->set_pagelayout('admin');
$PAGE->set_title('Pemisos avansados');
$PAGE->set_url('/local/backup_course/permisos/index.php');
echo $OUTPUT->header();
echo $OUTPUT->heading('Pemisos avansados');

echo '<div id="snackbar"></div>';
$url = new moodle_url($FULLME);
?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Pestañas de Navegación -->
    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="new-tab" data-toggle="tab" href="#new" role="tab" aria-controls="home" aria-selected="true">Nuevos</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="guardados-tab" data-toggle="tab" href="#guardados" role="tab" aria-controls="guardados" aria-selected="false">Guardados</a>
        </li>
    </ul>
    

    <!-- Contenido de las Pestañas -->
    <div class="tab-content" id="myTabContent">
        <div class="tab-pane fade show active" id="new" role="tabpanel" aria-labelledby="new-tab">
           
            <div class="container mt-5">        
                <div class="row">
                    <h3 class="mb-6">Nuevos Permisos</h3>
                    <!-- Primera columna: Formulario -->
                    <div class="col-md-5">

                        <div class="mb-3">
                            <label for="tipo" class="form-label">Seleccione la instancia:</label>
                            <select class="form-select" id="list_instancias" name="list_instancias" multiple> </select>
                        </div>
                        <div class="row">
                            <!-- Campo de Estado -->
                            <div class="mb-3 col-md-7">
                                <label for="estado" class="form-label">Estado:</label>
                                <select class="form-select" id="estado" name="estado" onchange="crud.handleEstadoChange()">
                                    <option value="0">Desbloqueado</option>
                                    <option value="1">Restringido parcialmente</option>
                                    <option value="2">Restringido completamente</option>
                                </select>
                            </div>

                            <!-- Campos numéricos de Secciones, Actividades y Recursos -->
                            <div class="mb-3 col-md-5">
                                <label for="secciones" class="form-label">Cantidad Secciones:</label>
                                <input type="number" class="form-control" id="secciones" name="secciones" min="0" max="99" title="Cantidad de secciones permitidas a crear">
                            </div>

                            <div class="mb-3 col-md-6">
                                <label for="actividades" class="form-label">Cantidad Actividades:</label>
                                <input type="number" class="form-control" id="actividades" name="actividades" min="0" max="99" title="Cantidad de actividades permitidas a crear">
                            </div>

                            <div class="mb-3 col-md-6">
                                <label for="recursos" class="form-label">Cantidad Recursos:</label>
                                <input type="number" class="form-control" id="recursos" name="recursos" min="0" max="99" title="Cantidad de recursos permitidos a crear">
                            </div>
                            
                            <div class="mb-3 col-md-12" style="display: flex;">
                                <input type="checkbox" class="category-checkbox" checked id="reemplazar" style="margin-right: 8px;">
                                <label for="reemplazar" class="form-label" >Reemplazar Actividades desde el banco</label>
                            </div>
                            <div class="mb-3 col-md-12" style="display: flex;">
                                <input type="checkbox" class="category-checkbox" id="todosCourses" style="margin-right: 8px;">
                                <label for="todosCourses" class="form-label">Aplicar permiso a todos los cursos de las instancias seleccionadas</label>
                            </div>
                        </div>
                    </div>

                    <!-- Segunda columna: Lista de permisos guardados -->
                    <div class="col-md-7" >
                        <div class="mb-4">
                            <label for="searchC" class="form-label">Buscar Curso</label>
                            <input type="text" id="searchC" oninput="crud.buscarC(this.value)" class="form-control" placeholder="Escribe para buscar...">
                        </div>
                        <div class="row" style="height: 45vh; overflow-y: auto;">
                            <div class="col-md-5">
                                <label for="accordionCateg" class="form-label">Seleccione la categoría</label>
                                <div class="accordionCateg" id="accordionCateg0"> </div>
                            </div>
                            <div class="col-md-7">
                                <label for="listCourse" class="form-label">Seleccione el curso</label>
                                <ul class="listCourse" id="listCourse"> </ul>
                            </div>
                        </div>

                    </div>
                    <div class="">
                        <div class="mb-4">
                            <label for="seleccionados" class="form-label">Cursos Seleccionados</label>
                            <div class="input-group">
                                <input type="text" id="view_selecci" class="form-control" disabled>
                            </div>
                            <input type="hidden" id="seleccionados" class="form-control">
                        </div>

                        <!-- Botón de envío -->
                        <button class="btn btn-primary" onclick="crud.obligatorios()">Guardar</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="guardados" role="tabpanel" aria-labelledby="guardados-tab">

            <div class="row">
                <h3 class="mb-6">Permisos Guardados</h3>
                <table class="table table-striped" >
                    <thead>
                        <tr>
                            <th>Instancia</th>
                            <th>Curso</th>
                            <th>ShortName</th>
                            <th>Permiso</th>
                            <th>Reemplazar</th>
                            <th>Secciones</th>
                            <th>Actividades</th>
                            <th>Recursos</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody id="listPermis"></tbody>
                </table>
                
            </div>
        </div>
    </div>
<?php

echo $OUTPUT->footer();