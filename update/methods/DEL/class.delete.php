<?php
require_once('../../../../../config.php');
require_once("$CFG->libdir/filelib.php");

class deleteUpdate
{

    public static function run()
    {
        $objCRE = new self();
        $idfunc = $_POST['key'];

        switch ($idfunc) {
            case 'D01':
                $resp = $objCRE->eliminarPregunta();
                break;
        }
        if (!empty($resp)) {
            echo json_encode($resp);
        }
    }


    private function eliminarPregunta()
    {
        return 1;
    }
}

deleteUpdate::run();
