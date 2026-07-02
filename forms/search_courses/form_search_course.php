<?php

//require_once( '/../../../../config.php');
require_once("$CFG->libdir/formslib.php");


class simplehtml_form_search_course extends moodleform {

    public function definition() {
        $id     = required_param('id_nodo', PARAM_INT);
        /*$mform = $this->_form; // Don't forget the underscore!     //array('action'=>new moodle_url('/blocks/noticias_uvd/insert/insert.php?idsede='.$idsede))
        $mform->addElement('header', 'config', 'Buscar Curso');
           
        $mform->addElement('text', 'search_course','Buscar curso', array('maxlength'=>'50'));
        $mform->setType('search_course', PARAM_TEXT);
        $mform->addRule('search_course', 'Es necesario digitar un texto', 'required', null, '');
   
        */
        $mform = $this->_form;
        $mform->addElement('header', 'config', 'Buscar Curso');
        $mform->addElement('text', 'search', 'Digite el curso a importar');
        $mform->setType('search', PARAM_NOTAGS);
        $mform->addRule('search', '', 'minlength', 4);
        $mform->addElement('hidden', 'id_nodo', $id);
        $mform->setType('id_nodo', PARAM_INT);
        
        $buttonarray=array();
        $buttonarray[] = &$mform->createElement('button', 'submitbutton_buscar_curso', 'Buscar',array('id'=>'search_course_in_p'));
        $buttonarray[] = &$mform->createElement('button', 'submitbutton_cancelar_config', 'Cancelar', array('id'=>'cancelar_busqueda'));
        $mform->addGroup($buttonarray, 'buttonar_search_course', '', array(''), false);
        $mform->closeHeaderBefore('buttonar');
        
        
    }
    
       
} 

