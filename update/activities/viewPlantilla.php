<?php
//print_r($_POST);
require_once('../../../../config.php');
//require_once '../../renderers.php';
require_once($CFG->dirroot . '/lib/outputrenderers.php');
global $PAGE, $USER, $DB, $OUTPUT, $CFG, $FULLME, $SESSION;
require_login(0, false);
if (isguestuser()) {
    // Login as real user!
    $SESSION->wantsurl = (string)new moodle_url('/proponente/viewPlantilla.php');
    redirect(get_login_url());
}
$context = context_user::instance($USER->id, MUST_EXIST);
$PAGE->set_context($context);
$courseid  = required_param('courseid', PARAM_INT);
$section = optional_param('section', 0, PARAM_TEXT);
$plantillaid = optional_param('plantillaid', 0, PARAM_TEXT);
$course = get_course($courseid);

$context_course = context_course::instance($courseid, MUST_EXIST);
//require_capability('moodle/course:viewhiddensections', $context_course);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Elegir Plantilla</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link rel="stylesheet" href="../../css/recordrtc.css">
</head>
<body class="hold-transition skin-blue sidebar-mini" onload="adAcP.add_editHtmlPlantilla();adAcP.alert_icon_assitente()">
<div class="wrapper">

    <?php
    require_once('../../../../config.php');
    include "../admin/headerAndMenu.php";

    ?>

  <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
    <!-- Content Header (Page header) -->
        <section class="content-header">
            <h2 id='title_header_course'>
                Actividad para el curso <?php echo $course->fullname.'<br> Sección: '.$section; ?> 
            </h2>
            
            <h3 id='title_header_plantilla'>
                <?php echo $_POST['namePlantilla']; ?>
            </h3>
            <h3 id='type_header_plantilla'>
                <?php echo $_POST['typePlantilla']; ?>
            </h3>
            
            <!--<div id="content_enun_compe" onload="">
                <h2>Seleccione la competencia para su actividad</h2>
                <button type="button" class="btn btn-success" onclick="adAcP.viewModal();">
                    Ver Competencias
                </button> 
            </div>-->
            <p>A continuación puede editar la plantilla tipo para su actividad</p>
            <div id="ocultoDiv" name="ocultoDiv" contenteditable="true"></div>
            <ol class="breadcrumb">
                <li><a href="<?php  echo $CFG->wwwroot.'/my/';   ?>"><i class="fa fa-dashboard"></i> Area Personal</a></li>
                <li><a href="<?php  echo $CFG->wwwroot.'/course/view.php?id='.$courseid.'#section='.$section;   ?>"> Curso: <?php echo $course->fullname; ?></a></li>
                <!--<li><a href="<?php  echo $CFG->wwwroot.'/local/activities_uvd/template/proponente/proponente.php?courseid='.$courseid.'&section='.$section;   ?>"> Listado de plantillas</a></li>-->
                <li class="active" >Configurar Plantilla</li>
            </ol>
        </section>
        <!-- Main content -->
        <section class="content" id="contenido_script">

        </section>
        <section>
            <button type="button" class="btn btn-success" id="btn_saveActi" onclick="conFir_tpl.confirmar_saveActi()"><i class="fa fa-save"></i>Guardar y continuar despúes</button> 
            <button type="button" class="btn btn-primary" id="btn_proponerActi" onclick="conFir_tpl.confirmar_enviarCorrec()"><i class="fa fa-share-square"></i>Enviar a Corrección</button> 
            <!--<button type="button" class="btn" id="add_dinamic"  data-toggle='modal' data-target='#modal-default'>Añadir actividad dinámica</button>-->
        </section>
            
        

    <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->
    <footer class="main-footer">
        <div class="pull-right hidden-xs">
            <b>Version</b> 1.0.0
        </div>
        <strong>
            Copyright &copy; 2018-2022
            <a href="http://www.uniminuto.edu/">Campus UVD - Uniminuto</a>
        </strong>
    </footer>

    <!-- /.control-sidebar -->
    <!-- Add the sidebars background. This div must be placed
         immediately after the control sidebar -->
    <div class="control-sidebar-bg"></div>
</div>
<div class="modal" id="modal_competencias">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="adAcP.closeModal()">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Seleccione la competencia</h4>
            </div>
            <div class="modal-body" >
                <form id="list_competencias_ws">

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" onclick="adAcP.saveCometenciasModal()">Guardar </button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>


<!--ventana de grabacion -->
<div id="myModal" class="modal">

    <!-- Modal content -->
    <div class="modal-content">
        <span class="close" onclick="adAcP.cerrar_grabaci_()">&times;</span>
        <button id="btn-start-recording">Inicar Grabación</button>
        <button id="btn-pause-recording" style="display: none; font-size: 15px;">Pause</button>

        <div id='content_bts_edit' style="text-align: center; display: none;">
            <button id="save-to-disk">Guardar en su Computador</button>
            <button id="upload-to-php">Insertar</button>
            <button id="open-new-tab">Ver en una nueva pestaña</button>
        </div>

        <div style="width: 100%" id="recording-player"></div>
    </div>

</div>



<div class="modal" id="overlay_asis">
    <div class="modal-dialog">
        <div class="modal-content" id="content_asis">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="adAcP.closeModal_ass()">
                    <i class="fa fa-times"></i>
                </button>
                <h4 class="modal-title" id="head_overlay_asis">Titulo</h4>
            </div>
            <div class="modal-body" >
                <div id="overlay_asis_cont">

                </div>
            </div>
            <hr>
            <div class="footer product-info" id="contenido_descripciones">
                <strong>Descrición:</strong>
                <div class="modal-footer" id="foot_1_overlay_asis"></div>
                <hr>
                <strong>Ejemplo:</strong>
                <div class="modal-footer" id="foot_2_overlay_asis"></div>
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="adAcP.save_modal_ass()">Guardar</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
    
<!--<div id="overlay_asis"></div>
<div class="modal fade in" id="modal-default" style="padding-right: 17px;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
                <h4 class="modal-title">Default Modal</h4>
            </div>
            <div class="modal-body">
                <iframe style="border:0;width:100%;height:100%;font-size:20px" scrolling="yes" frameborder="0" allowTransparency="true" id="ifrm_dimanic" src="<?php echo $CFG->wwwroot;?>/local/activities_uvd/template/pages/modedit.php?add=hvp&type=&course=<?php echo $courseid;?>#section=<?php echo $section;?>&return=0&sr=0"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>-->
<script>
    CKEDITOR.inline( 'ocultoDiv' );
    <?php 
        if($_POST['namePlantilla'] == 'Editando Actividad'){
            ?>
            document.getElementById('contenido_script').innerHTML = unescape("<?php print_r($_POST['html']);?>");
            <?php
        }else{
            ?>
            document.getElementById('contenido_script').innerHTML = '<span class="info-box-text" id="titleActivity">'+
                                                                '<i class="ion"></i>'+
                                                                '<div contenteditable="true" class="info-box-text"> Título de la actividad</div>'+
                                                            '</span>'+
                                                            unescape("<?php print_r($_POST['html']);?>");
        <?php
        
        }
        if (array_key_exists('activitiesid', $_POST)) {
            $activitiesid = optional_param('activitiesid', 0, PARAM_INT);
            $id = optional_param('id', 0, PARAM_INT);
            $hvpid = optional_param('hvpid', 0, PARAM_INT);
            if(!empty($hvpid)){
                ?> 
                var $hvpid = <?php echo $hvpid; ?>;
                <?php
            }
            ?>
            var $activitiesid = <?php echo $activitiesid; ?>;
            var $id = <?php echo $id; ?>;
            <?php
        }
        
    ?>
    
    
    var $plantillaid = <?php echo $plantillaid; ?>;
    var $courseid = <?php echo $courseid; ?>;
    var $section = <?php echo $section; ?>;
    
</script>

<script src="../../js/methods/admin/app_frm_admin_rubric.js" type="text/javascript"></script>
<script src="../../js/methods/proponente/viewPlantilla.js"></script>

<script src="../../js/recordrtc/adapter-latest.js"></script>
<script src="../../js/recordrtc/RecordRTC.js"></script>
<script src="../../js/recordrtc/DetectRTC.js"></script>
<script src="../../js/recordrtc/getHTMLMediaElement.js"></script>

<script src="../../js/recordrtc/index.js"></script>

<!--<script src="../../js/recordrtc/commits.js"></script>-->
<script src="https://cdn.webrtc-experiment.com/commits.js" async></script>
<script src="https://apis.google.com/js/client:plusone.js"></script>
<!--<script src="../../js/recordrtc/plusone.js"></script>-->
<script>
        var chromeMediaSource = 'screen';
        window.addEventListener('message', function(message) {
            if(message.origin.toString().indexOf(MY_DOMAIN) === -1) return;
            chromeMediaSource = 'desktop';
            if(typeof getScreenId == 'function' && !!message.data.sourceId) {
                getScreenId(chromeMediaSource, message.data.sourceId);
            }
        });
    </script>

</body>
</html>

