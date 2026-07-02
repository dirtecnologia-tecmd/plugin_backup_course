<?php
//require_once('../../../../config.php');
require_once("$CFG->libdir/formslib.php");

class simplehtml_form_config_sftp extends moodleform {

    public function definition() {
       
        $mform = $this->_form; // Don't forget the underscore!     //array('action'=>new moodle_url('/blocks/noticias_uvd/insert/insert.php?idsede='.$idsede))
        $mform->addElement('header', 'create', 'Configuración del SFTP');
        
        $mform->addElement('hidden', 'id_sftp', '',array('id'=>'id_sftp'));
        $mform->setType('id_sftp', PARAM_INT);
        
        $mform->addElement('text', 'server','Digite el nombre del servidos SFTP', array('maxlength'=>'50'));
        $mform->setType('server', PARAM_TEXT);
        $mform->addRule('server', 'Es necesario digitar un texto', 'required', null, '');
        
        $mform->addElement('text', 'port','Digite el puerto', array('maxlength'=>'15'));
        $mform->setType('port', PARAM_TEXT);
        $mform->addRule('port', 'Es necesario digitar un texto', 'required', null, '');

        $mform->addElement('text', 'username','Digite el nombre del usuario', array('maxlength'=>'50'));
        $mform->setType('username', PARAM_TEXT);
        $mform->addRule('username', 'Es necesario digitar un texto', 'required', null, '');
        
        $mform->addElement('passwordunmask', 'password','Digite la contraseña del usuario', array('maxlength'=>'50'));
        $mform->setType('password', PARAM_ALPHANUM);
        $mform->addRule('password', 'Es necesario digitar un texto', 'required', null, '');
          
        $buttonarray=array();
        $buttonarray[] = &$mform->createElement('button', 'submitbutton_create_sftp', 'Guardar',array('id'=>'create_sftp', 'class'=>'guardar_token'));
        $buttonarray[] = &$mform->createElement('button', 'submitbutton_cancelar_sftp', 'Cancelar', array('id'=>'cancelar_sftp'));
        $mform->addGroup($buttonarray, 'buttonar_guardar_sftp', '', array(''), false);
        $mform->closeHeaderBefore('buttonar');
        
    }
       
} 
