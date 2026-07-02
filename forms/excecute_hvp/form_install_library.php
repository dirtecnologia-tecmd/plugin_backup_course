<?php

//require_once( '/../../../../config.php');
require_once("$CFG->libdir/formslib.php");


class simplehtml_form_install_library extends moodleform {

    public function definition() {
        $mform = $this->_form;

        $buttonarray=array();

        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', 'Instalar',array('onclick'=>'document.getElementById("overlay-loader_block_modedit").style.display = "block";')); //
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton_cancelar_config', 'Cancelar', array('id'=>'cancelar_busqueda'));
        $mform->addGroup($buttonarray, 'buttonar_search_course', '', array(''), false);
        $mform->closeHeaderBefore('buttonar');
        
        
    }
       
} 

