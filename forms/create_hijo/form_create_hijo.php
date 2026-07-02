<?php
//require_once('../../../../config.php');
require_once("$CFG->libdir/formslib.php");

class simplehtml_form_create_hijo extends moodleform {

    public function definition() {
       
        $mform = $this->_form; // Don't forget the underscore!     //array('action'=>new moodle_url('/blocks/noticias_uvd/insert/insert.php?idsede='.$idsede))
        $mform->addElement('header', 'create', 'Administración de Moodle hijos');
        
        $mform->addElement('hidden', 'id_reg', '',array('id'=>'id_reg'));
        $mform->setType('id_reg', PARAM_INT);
        
        $mform->addElement('text', 'nombre_hijo','Digite el nombre del sitio del Moodle hijo', array('maxlength'=>'50'));
        $mform->setType('nombre_hijo', PARAM_TEXT);
        $mform->addRule('nombre_hijo', 'Es necesario digitar un texto', 'required', null, '');
        
        /*$mform->addElement('text', 'ip_hijo','Digite la IP del Moodle hijo', array('maxlength'=>'15'));
        $mform->setType('ip_hijo', PARAM_TEXT);
        $mform->addRule('ip_hijo', 'Es necesario digitar un texto', 'required', null, '');*/
        
        $mform->addElement('text', 'url_hijo', 'Digite la URL del Moodle hijo <br>(Ejemplo: http://aulas.uvd.edu/mdl_pruebas)',array('maxlength'=>'100'));
        $mform->setType('url_hijo', PARAM_TEXT);
        $mform->addRule('url_hijo', 'Es necesario digitar un texto', 'required', null, '');
        
        $mform->addElement('date_selector', 'startdate','Fecha de inicio de cursos', array('optional'=>false));
        $mform->addRule('startdate', 'Es necesario seleccionar una fecha', 'required', null, '');
        $mform->addElement('date_selector', 'enddate8', 'Fecha fin de 8 semanas', array('optional'=>false));
        $mform->addRule('enddate8', 'Es necesario seleccionar una fecha', 'required', null, '');
        $mform->addElement('date_selector', 'enddate16','Fecha fin de 16 semanas', array('optional'=>false));
        $mform->addRule('enddate16', 'Es necesario seleccionar una fecha', 'required', null, '');
        
        $options = array(1 => 'Activo', 0 => 'Inactivo');
        
        $select = $mform->addElement('select', 'estado_token', 'Seleccione el estado del Token', $options);
        $select->setSelected(1);
        $mform->setType('estado_token', PARAM_INT);
        
        $options = array(1 => 'Si', 0 => 'No');
        $select1 = $mform->addElement('select', 'edition_acti', 'Activar la edición de actividades?', $options);
        $select1->setSelected(1);
        $mform->setType('edition_acti', PARAM_INT);
          
        $buttonarray=array();
        $buttonarray[] = &$mform->createElement('button', 'submitbutton_create_hijo', 'Guardar',array('id'=>'create_token', 'class'=>'guardar_token'));
        $buttonarray[] = &$mform->createElement('button', 'submitbutton_cancelar_token', 'Cancelar', array('id'=>'cancelar_token'));
        $mform->addGroup($buttonarray, 'buttonar_guardar_hijos', '', array(''), false);
        $mform->closeHeaderBefore('buttonar');
        
    }
       
} 
