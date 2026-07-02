<?php
//require_once('../../../../config.php');
require_once("$CFG->libdir/formslib.php");

class simplehtml_form_config_s3 extends moodleform
{

    public function definition()
    {

        $mform = $this->_form; // Don't forget the underscore!     //array('action'=>new moodle_url('/blocks/noticias_uvd/insert/insert.php?idsede='.$idsede))
        $mform->addElement('header', 'create', 'Configuración de S3');

        $mform->addElement('hidden', 'id_s3', '', array('id' => 'id_s3'));
        $mform->setType('id_s3', PARAM_INT);

        $mform->addElement('text', 'bucket', 'Bucket', array('maxlength' => '100'));
        $mform->setType('bucket', PARAM_TEXT);
        $mform->addRule('bucket', 'El Bucket es obligatorio', 'required', null, '');

        $mform->addElement('passwordunmask', 'public_key', 'Clave pública', array('maxlength' => '200'));
        $mform->setType('public_key', PARAM_TEXT);
        $mform->addRule('public_key', 'La Clave Pública es requerida', 'required', null, '');

        $mform->addElement('passwordunmask', 'private_key', 'Clave Privada', array('maxlength' => '200'));
        $mform->setType('private_key', PARAM_TEXT);
        $mform->addRule('private_key', 'La Clave Privada es requerida', 'required', null, '');

        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('button', 'submitbutton_create_s3', 'Guardar', array('id' => 'create_s3'));
        $buttonarray[] = &$mform->createElement('button', 'submitbutton_cancelar_s3', 'Cancelar', array('id' => 'cancelar_s3'));
        $mform->addGroup($buttonarray, 'buttonar_guardar_s3', '', array(''), false);
        $mform->closeHeaderBefore('buttonar');
    }
}
