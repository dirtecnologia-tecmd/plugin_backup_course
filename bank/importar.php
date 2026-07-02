<?php

/* 
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/EmptyPHP.php to edit this template
@Creado: 11/10/2024 5:11:07 p. m.
@Autora: Daniela Sierra Vergel 
 */
require_once('../../../config.php');
$section  = required_param('section', PARAM_INT);
$courseid = required_param('courseid', PARAM_INT);
$url = new moodle_url('/local/backup_course/bank/importar.php');


if (isguestuser()) {
    // Login as real user!
    $SESSION->wantsurl = (string)new moodle_url('/bank/importar.php');
    redirect(get_login_url());
}

$idcourse = required_param('courseid', PARAM_INT);
$section = required_param('section', PARAM_INT);
$course   = $DB->get_record('course', array('id' => $idcourse), '*', MUST_EXIST);
$seccion   = $DB->get_record('course_sections', array('course' => $idcourse, 'section' => $section), '*', MUST_EXIST);
context_helper::preload_course($course->id);
$context = context_course::instance($course->id, MUST_EXIST);
$PAGE->set_context($context);
require_login($course);

$PAGE->set_url($url);
$PAGE->set_heading($USER->id);
$PAGE->set_pagelayout('report');
$PAGE->requires->css('/local/backup_course/css/style.css');
echo '<div id="snackbar"></div>';
echo $OUTPUT->header();
//Vamos a obtener la cantidad de secciones que tiene el curso en su importación
$relacion_importacion = $DB->get_field('bc_rel_padre_hijo', 'objet_ph', array('courseid_sh' => $idcourse));
$objet_ph = json_decode($relacion_importacion);
$cantidad_secciones = count($objet_ph->sectionAndActi->sections);
$cantidad_secciones = $cantidad_secciones - 1; //restamos 1 porque la sección 0 nos aumentaria el contador y habría desfase.

$seccion_actual = $section;
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
<h2>Utilizar actividades en el curso y sección <?php echo $seccion->name != null ? $seccion->name : $seccion->section; ?> </h2>
<div class="container mt-5">
    <!-- Navegación de pestañas -->
    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="actividades-tab" data-bs-toggle="tab" data-bs-target="#actividades" type="button" role="tab" aria-controls="actividades" aria-selected="true">Mis Actividades o recursos</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="relacionadas-tab" data-bs-toggle="tab" data-bs-target="#relacionadas" type="button" role="tab" aria-controls="relacionadas" aria-selected="false">Actividades y recursos públicos</button>
        </li>
    </ul>

    <!-- Contenido de las pestañas -->
    <div class="tab-content" id="myTabContent">
        <!-- Pestaña 1: Actividades del profe  -->
        <div class="tab-pane fade show active" id="actividades" role="tabpanel" aria-labelledby="actividades-tab">
            <div class="mb-4 mt-4">
                <input type="text" id="searchInput1" class="form-control" placeholder="Buscar por nombre, tipo o curso">
            </div>
            <div id="pagination1" class="pagination-container"></div>

            <div class="table-responsive" id="cardsContainer1">
                <table class="table table-bordered table-hover" id="table1">
                    <thead>
                        <tr>
                            <th>Actividad</th>
                            <th>Tipo de Actividad</th>
                            <th>Curso</th>
                            <th>Creador</th>
                            <th>Utilizada</th>
                            <th>Acciones</th>
                            <th>Privacidad</th>
                            <th>Retirar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php

                        //Validamos si estamos en el rango de secciones que tiene el curso en su importación
                        $condicion = "";
                        if ($seccion_actual <= $cantidad_secciones) {
                            $condicion = "AND oa.type_activity in ('page', 'url')";
                        }


                        $actividades = $DB->get_records_sql(
                            'SELECT oa.*, c.shortname 
                                                                    FROM {bc_own_activities} oa 
                                                                    INNER JOIN {course} c ON c.id = oa.idcourse_h 
                                                                    WHERE oa.idnumber_teacher=:idnumber_teacher AND oa.retirar = 0 ' . $condicion,
                            array('idnumber_teacher' => $USER->idnumber)
                        );
                        foreach ($actividades as $key => $value) {
                            $value->cant_others = $value->cant_others != null ? (int)$value->cant_others : 0;
                            $value->cant_owner = $value->cant_owner != null ? (int)$value->cant_owner : 0;
                            $util = $value->cant_others + $value->cant_owner;
                            echo '<tr id="bankAct_' . $value->id . '" 
                                          data-name="' . strtolower($value->name_activity) . '" 
                                          data-type="' . strtolower($value->type_activity) . '" 
                                          data-course="' . strtolower($value->shortname) . '" 
                                          data-creator="' . strtolower($value->email_teacher) . '">'; ?>
                            <td><?php echo $value->name_activity; ?></td>
                            <td><?php echo $value->type_activity; ?></td>
                            <td><?php echo $value->shortname; ?></td>
                            <td><?php echo $value->email_teacher; ?></td>
                            <td><?php echo $util; ?> veces</td>
                            <td>
                                <div class="btn btn-primary" onclick="restaurar_mbz('<?php echo trim($value->url); ?>', <?php echo $idcourse; ?>, <?php echo $seccion->id; ?>)">Usar</div>
                            </td>
                            <td>
                                <select id="privacy-<?php echo $key; ?>" class="form-select form-select-sm" onchange="confirmarCambioPriva(<?php echo $key; ?>, this.value)">
                                    <option value="0" <?php echo (($value->private == 0) ? 'selected' : ''); ?>>Público</option>
                                    <option value="1" <?php echo (($value->private == 1) ? 'selected' : ''); ?>>Privado</option>
                                </select>
                            </td>
                            <td>
                                <select id="retirar-<?php echo $key; ?>" class="form-select form-select-sm" onchange="confirmarCambioRetiro(<?php echo $key; ?>, this.value)">
                                    <option value="0" <?php echo (($value->retirar == 0) ? 'selected' : ''); ?>>Dejar en Banco</option>
                                    <option value="1" <?php echo (($value->retirar == 1) ? 'selected' : ''); ?>>Retirar del Banco</option>
                                </select>
                            </td>
                            </tr>

                        <?php } ?>
                    </tbody>
                </table>

            </div>

        </div>

        <!-- Pestaña 2: Todas las Actividades publicas-->
        <div class="tab-pane fade" id="relacionadas" role="tabpanel" aria-labelledby="relacionadas-tab">
            <div class="mb-4 mt-4">
                <input type="text" id="searchInput2" class="form-control" placeholder="Buscar por nombre, tipo, curso o profesor">
            </div>
            <div id="pagination2" class="pagination-container"></div>

            <div class="table-responsive" id="cardsContainer2">
                <table class="table table-bordered table-hover" id="table2">
                    <thead>
                        <tr>
                            <th>Actividad</th>
                            <th>Tipo de Actividad</th>
                            <th>Descripción Actividad</th>
                            <th>Fecha Cración</th>
                            <th>Curso</th>
                            <th>Sección</th>
                            <th>Profesor</th>
                            <th>IdBanner</th>
                            <th>Utilizada Autor</th>
                            <th>Utilizada Otros</th>
                            <th>Utilizada Total</th>
                            <th>URL</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php

                        //Validamos si estamos en el rango de secciones que tiene el curso en su importación
                        $condicion = "";
                        if ($seccion_actual <= $cantidad_secciones) {
                            $condicion = "AND o1.type_activity in ('page', 'url')";
                        }

                        $all_actividades2 = $DB->get_records_sql('SELECT o1.*, c.shortname 
                                         FROM {bc_own_activities} o1
                                         INNER JOIN {course} c ON c.id = o1.idcourse_h
                                         WHERE o1.private = 0 AND o1.retirar = 0 ' . $condicion);


                        if (!empty($all_actividades2)) {
                            foreach ($all_actividades2 as $key => $value) {
                                echo '<tr id="bankAct_' . $value->id . '" 
                                          data-name="' . strtolower($value->name_activity) . '" 
                                          data-type="' . strtolower($value->type_activity) . '" 
                                          data-course="' . strtolower($value->shortname) . '" 
                                          data-creator="' . strtolower($value->email_teacher) . '">';
                                echo '<td>' . $value->name_activity . '</td>';
                                echo '<td>' . $value->type_activity . '</td>';
                                echo '<td>' . $value->intro_activity . '</td>';
                                echo '<td>' . date('d-m-Y', $value->timecreate) . '</td>';
                                echo '<td>' . $value->shortname . '</td>';
                                echo '<td>' . $value->idsection_h . '</td>';
                                echo '<td>' . $value->fullname_teacher . '</td>';
                                echo '<td>' . $value->idnumber_teacher . '</td>';
                                echo '<td>' . $value->cant_owner . '</td>';
                                echo '<td>' . $value->cant_others . '</td>';
                                echo '<td>' . ($value->cant_others + $value->cant_owner) . ' veces</td>';
                                echo '<td>' . $value->url . ' </td>';
                                echo '<td> <div class="btn btn-primary" onclick="restaurar_mbz(\'' . trim($value->url) . '\', ' . $idcourse . ', ' . $seccion->id . ')">Usar</div></td>';
                                echo '</tr>';
                            }
                        } else {
                            echo '<tr><td colspan="6" class="text-center">No tiene actividades creadas</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>

            </div>

        </div>
    </div>
</div>

<!-- JavaScript para filtrar actividades en ambas pestañas -->
<script>
    function restaurar_mbz(urlS3, idcourse, idsection) {
        document.getElementById("overlay-loader_block").style.display = "block";
        fetch('restaurarMbz.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    s3url: urlS3,
                    courseid: idcourse,
                    sectionid: idsection,
                    method: 'Restaurar'
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log('data', data);
                console.log('Actividad restaurada exitosamente.');
                window.parent.location.reload();
            })
            .catch(error => {
                console.error('Error al restaurar la actividad:', error);
            });
    }

    function confirmarCambioRetiro(idAct, valorSeleccionado) {
        const mensaje = valorSeleccionado == 1 ?
            '¿Estás seguro de que deseas retirar del banco? Si guardas este cambio, no podrás volver a cambiarlo.' :
            '¿Deseas dejarlo en el banco?';

        if (confirm(mensaje) && valorSeleccionado == 1) {
            document.getElementById('overlay-loader_block').style.display = 'block';
            cambiarRetirar(idAct, valorSeleccionado); // Si el usuario confirma, llamar a cambiarRetirar
        } else {
            const select = document.getElementById('retirar-' + idAct); // Si el usuario cancela, volver a establecer la selección anterior
            select.value = valorSeleccionado == 1 ? '0' : '1'; // revertir al valor anterior
        }
    }



    function cambiarRetirar(idAct) {
        fetch('restaurarMbz.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    idAct: idAct,
                    method: 'Retirar'
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log('Actividad retirada exitosamente.');
                window.parent.location.reload();
            })
            .catch(error => {
                console.error('Error al retirada la actividad:', error);
            });
    }

    function confirmarCambioPriva(idAct, valorSeleccionado) {
        const mensaje = valorSeleccionado == 1 ?
            '¿Estás seguro de que deseas cambiar la privacidad de la actividad? ' :
            '¿Deseas seguir con la misma privacidad?';

        if (confirm(mensaje)) {
            document.getElementById('overlay-loader_block').style.display = 'block';
            cambiarPublic(idAct, valorSeleccionado); // Si el usuario confirma, llamar a cambiarPublic
        } else {
            const select = document.getElementById('privacy-' + idAct); // Si el usuario cancela, volver a establecer la selección anterior
            select.value = valorSeleccionado == 1 ? '0' : '1'; // revertir al valor anterior
        }
    }



    function cambiarPublic(idAct, valorSeleccionado) {
        fetch('restaurarMbz.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    idAct: idAct,
                    selecc: valorSeleccionado,
                    method: 'Publicar'
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log('Actividad despublicada exitosamente.');
                window.parent.location.reload();
            })
            .catch(error => {
                console.error('Error al despublicada la actividad:', error);
            });
    }






    function paginateTable(tableId, rowsPerPage, paginationId) {
        const table = document.getElementById(tableId);
        const rows = table.querySelectorAll('tbody tr');
        const totalRows = rows.length;
        const totalPages = Math.ceil(totalRows / rowsPerPage);
        const pagination = document.getElementById(paginationId);

        function showPage(page) {
            rows.forEach((row, index) => {
                row.style.display = (index >= (page - 1) * rowsPerPage && index < page * rowsPerPage) ? '' : 'none';
            });
        }

        pagination.innerHTML = '';
        for (let i = 1; i <= totalPages; i++) {
            const button = document.createElement('button');
            button.textContent = i;
            button.className = 'btn btn-primary mx-1';
            button.addEventListener('click', () => {
                showPage(i);
            });
            pagination.appendChild(button);
        }

        showPage(1);
    }

    document.addEventListener('DOMContentLoaded', () => {
        paginateTable('table1', 10, 'pagination1'); // Tabla 1 con 5 filas por página
        paginateTable('table2', 10, 'pagination2'); // Tabla 2 con 5 filas por página
    });


    function filterTable(searchInputId, tableId) {
        const input = document.getElementById(searchInputId).value.toLowerCase();
        const rows = document.querySelectorAll(`#${tableId} tbody tr`);

        rows.forEach(row => {
            const name = row.getAttribute('data-name');
            const type = row.getAttribute('data-type');
            const course = row.getAttribute('data-course');
            const creator = row.getAttribute('data-creator');

            if (name.includes(input) || type.includes(input) || course.includes(input) || creator.includes(input)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    document.getElementById('searchInput1').addEventListener('keyup', () => filterTable('searchInput1', 'table1'));
    document.getElementById('searchInput2').addEventListener('keyup', () => filterTable('searchInput2', 'table2'));
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
<style>
    footer,
    header,
    .help-button,
    .secondary-navigation,
    #recursos-uniminuto-format,
    .course-content-footer,
    .drawer.drawer-left.show,
    .drawer-left-toggle.open-nav,
    .drawer-right-toggle,
    .btn-chatbot {
        display: none !important;
    }

    #page-wrapper #page {
        margin: 0 !important;
    }

    .pagination-container {
        margin-bottom: 10px;
    }

    .pagination .page-item.active .page-link {
        background-color: #007bff;
        color: white;
    }
</style>
<?php

echo $OUTPUT->footer();
