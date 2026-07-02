<?php

/* 
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/EmptyPHP.php to edit this template
@Creado: 11/10/2024 5:11:07 p. m.
@Autora: Daniela Sierra Vergel 
 */
require_once('../../../config.php');
$courseid = required_param('courseid', PARAM_INT);
$url = new moodle_url('/local/backup_course/bank/view.php');


if (isguestuser()) {
    // Login as real user!
    $SESSION->wantsurl = (string)new moodle_url('/bank/view.php');
    redirect(get_login_url());
}

$idcourse = required_param('courseid', PARAM_INT);
$course   = $DB->get_record('course', array('id' => $idcourse), '*', MUST_EXIST);
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
?>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <h2>Administrar tus actividades</h2>
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
                <div class="table-responsive" id="cardsContainer1">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Actividad</th>
                                <th>Tipo de Actividad</th>
                                <th>Curso</th>
                                <th>Creador</th>
                                <th>Utilizada por ti</th>
                                <th>Utilizada por otros</th>
                                <th>Privacidad</th>
                                <th>Retirar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $actividades = $DB->get_records_sql('SELECT oa.*, c.shortname 
                                                                    FROM {bc_own_activities} oa 
                                                                    INNER JOIN {course} c ON c.id = oa.idcourse_h 
                                                                    WHERE oa.idnumber_teacher=:idnumber_teacher AND oa.retirar = 0
                                                                    ', array('idnumber_teacher' => $USER->idnumber));
                            foreach ($actividades as $key => $value): ?>
                                <tr>
                                    <td><?php echo $value->name_activity; ?></td>
                                    <td><?php echo $value->type_activity; ?></td>
                                    <td><?php echo $value->shortname; ?></td>
                                    <td><?php echo $value->email_teacher; ?></td>
                                    <td><?php echo $value->cant_owner; ?> veces</td>
                                    <td><?php echo $value->cant_others; ?> veces</td>
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
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            </div>

            <!-- Pestaña 2: Todas las Actividades publicas-->
            <div class="tab-pane fade" id="relacionadas" role="tabpanel" aria-labelledby="relacionadas-tab">
                <div class="mb-4 mt-4">
                    <input type="text" id="searchInput2" class="form-control" placeholder="Buscar por nombre, tipo, curso o profesor">
                </div>
                <div class="table-responsive" id="cardsContainer2">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Actividad</th>
                                <th>Tipo de Actividad</th>
                                <th>Curso</th>
                                <th>Creador</th>
                                <th>Utilizada</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $actividades = $DB->get_records_sql('SELECT DISTINCT o1.*, c.shortname FROM {bc_own_activities} o1
                                                                    INNER JOIN {course} c ON c.id = o1.idcourse_h
                                                                    WHERE o1.private = 0 AND o1.retirar = 0');//de todos los cursos
                            if (!empty($actividades)) {
                                foreach ($actividades as $key => $value) {
                                    $value->cant_others = $value->cant_others != null ? (int)$value->cant_others : 0;
                                    $value->cant_owner = $value->cant_owner != null ? (int)$value->cant_owner : 0;
                                    $util = $value->cant_others + $value->cant_owner; ?>

                                    <tr id="bankAct_<?php echo $value->id; ?>">
                                        <td><?php echo $value->name_activity; ?></td>
                                        <td><?php echo $value->type_activity; ?></td>
                                        <td><?php echo $value->shortname; ?></td>
                                        <td><?php echo $value->email_teacher; ?></td>
                                        <td><?php echo $util; ?> veces</td>
                                        <td>
                                            <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#modalAct-<?php echo $key; ?>">Ver</button>
                                        </td>
                                    </tr>

                                    <!-- Modal -->
                                    <div class="modal fade" id="modalAct-<?php echo $key; ?>" tabindex="-1" aria-labelledby="modalLabel-<?php echo $key; ?>" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="modalLabel-<?php echo $key; ?>">Actividad <?php echo $value->name_activity; ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body"><?php echo $value->intro_activity; ?></div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                <?php }
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
        // Filtrar actividades en la primera pestaña
        document.getElementById('searchInput1').addEventListener('keyup', function() {
            var input = this.value.toLowerCase(); 
            var rows = document.querySelectorAll('#cardsContainer1 table tbody tr');

            rows.forEach(function(row) {
                var name = row.querySelector('td:nth-child(1)').textContent.toLowerCase();
                var tipo = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                var course = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                var creator = row.querySelector('td:nth-child(4)').textContent.toLowerCase();

                if (name.includes(input) ||tipo.includes(input) || course.includes(input) || creator.includes(input)) {
                    row.style.display = ''; 
                } else row.style.display = 'none'; 
            });
        });

        // Filtrar actividades en la segunda pestaña
        document.getElementById('searchInput2').addEventListener('keyup', function() {
            var input = this.value.toLowerCase(); 
            var rows = document.querySelectorAll('#cardsContainer2 table tbody tr');

            rows.forEach(function(row) {
                var name = row.querySelector('td:nth-child(1)').textContent.toLowerCase();
                var type = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                var course = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                var creator = row.querySelector('td:nth-child(4)').textContent.toLowerCase();

                if (name.includes(input) || type.includes(input) || course.includes(input) || creator.includes(input)) {
                    row.style.display = ''; 
                } else row.style.display = 'none';
            });
        });
        
        function confirmarCambioRetiro(idAct, valorSeleccionado) {
            const mensaje = valorSeleccionado == 1 
                ? '¿Estás seguro de que deseas retirar del banco? Si guardas este cambio, no podrás volver a cambiarlo.' 
                : '¿Deseas dejarlo en el banco?';

            if (confirm(mensaje) && valorSeleccionado == 1) {
                cambiarRetirar(idAct, valorSeleccionado);// Si el usuario confirma, llamar a cambiarRetirar
            } else {
                const select = document.getElementById('retirar-' + idAct);// Si el usuario cancela, volver a establecer la selección anterior
                select.value = valorSeleccionado == 1 ? '0' : '1'; // revertir al valor anterior
            }
        }


        
        function cambiarRetirar(idAct){
            fetch('restaurarMbz.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
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
            const mensaje = valorSeleccionado == 1 
                ? '¿Estás seguro de que deseas retirar del banco? Si guardas este cambio, no podrás volver a cambiarlo.' 
                : '¿Deseas dejarlo en el banco?';

            if (confirm(mensaje)) {
                cambiarPublic(idAct, valorSeleccionado);// Si el usuario confirma, llamar a cambiarPublic
            } else {
                const select = document.getElementById('privacy-' + idAct);// Si el usuario cancela, volver a establecer la selección anterior
                select.value = valorSeleccionado == 1 ? '0' : '1'; // revertir al valor anterior
            }
        }


        
        function cambiarPublic(idAct, valorSeleccionado){
            fetch('restaurarMbz.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
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
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
<?php

echo $OUTPUT->footer();

