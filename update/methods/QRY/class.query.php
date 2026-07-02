<?php
require_once('../../../../../config.php');
require_once("$CFG->libdir/filelib.php");
require_once("$CFG->libdir/pagelib.php");
require_once("$CFG->libdir/blocklib.php");

class queryNodo
{

    public static function run()
    {
        $obj = new self();
        $idfunc = $_POST['key'];
        switch ($idfunc) {
            case 'Q01':
                $resp = $obj->view_nodos_activos();
                break;
            case 'Q02':
                $resp = $obj->returnCourse();
                break;
            case 'Q03':
                $resp = $obj->returnIdSlot();
                break;
            case 'Q04':
                $resp = $obj->enviarMovimientoAct();
                break;
        }

        echo json_encode($resp);
    }


    /*
     * listar los nodos activos
     */
    private function view_nodos_activos()
    {
        global $DB;
        $tokens = $DB->get_records_sql(
            'SELECT DISTINCT reg.* 
                                            FROM {bc_rel_padre_hijo} rel
                                            LEFT JOIN {bc_registro_pc} reg ON reg.id = rel.registroid 
                                            WHERE rel.courseid_sp = :courseid_sp AND reg.estado = 1 ',
            array('courseid_sp' => $_POST['courseid_h'])
        );
        return $tokens;
    }


    private function returnCourse()
    {
        global $DB;
        $curso = $DB->get_record_sql('SELECT course, instance FROM {course_modules} WHERE id =  ' . $_POST["courseid_h"] . '');
        return $curso;
    }

    private function returnIdSlot()
    {
        global $DB;

        $array = $_POST['listaChecked'];

        for ($i = 0; $i < count($array); $i++) {

            $quiz = $DB->get_record_sql('SELECT id FROM {quiz_slots} where quizid = ' . $array[$i]['idQuiz'] . ' AND slot =' . $array[$i]['slot'] . '');

            $quizId = intval($quiz->id);

            $slot = $DB->get_records_sql('SELECT * FROM {quiz_slots} WHERE quizid = ' . $array[$i]['idQuiz'] . ' AND slot =' . $array[$i]['slot'] . ' ORDER BY id ASC');

            foreach ($slot as $key => $value) {

                $quices = $DB->get_record_sql('SELECT sumgrades FROM {quiz} WHERE id = ' . $array[$i]['idQuiz'] . '');

                $maxmark = ($quices->sumgrades - $value->maxmark);

                $table = 'quiz';
                $data = new stdClass();
                $data->id = $array[$i]['idQuiz'];
                $data->sumgrades = $maxmark;

                $DB->update_record($table, $data);
            }

            $DB->delete_records_select('question_references', 'itemid =' . $quizId . '');

            $DB->delete_records_select('quiz_slots', 'quizid =' . $array[$i]['idQuiz'] . ' AND slot = ' . $array[$i]['slot'] . '');

            $slots = $DB->get_records_sql('SELECT * FROM {quiz_slots} WHERE quizid = ' . $array[$i]['idQuiz'] . ' ORDER BY id ASC');

            $cont = 1;

            foreach ($slots as $keys => $values) {

                $table = 'quiz_slots';
                $data = new stdClass();
                $data->id = $values->id; // ID del usuario que se va a actualizar
                $data->slot = $cont;

                $DB->update_record($table, $data);

                $cont++;
            }

            $nodos = $DB->get_records_sql(
                'SELECT DISTINCT reg.* 
                                                FROM {bc_rel_padre_hijo} rel
                                                LEFT JOIN {bc_registro_pc} reg ON reg.id = rel.registroid 
                                                WHERE rel.courseid_sp = :courseid_sp AND reg.estado = 1 ',
                array('courseid_sp' => $array[$i]['course'])
            );

            foreach ($nodos as $kn => $vn) {
                $tok = sha1('2017.UVD_TokeN_noDos');
                $url = $vn->url_hijo . '/webservice/rest/server.php?wstoken=' . $tok . '&wsfunction=local_delete_question_quiz&moodlewsrestformat=json';

                $params = array(
                    'slot' => $array[$i]['slot'],
                    'idQuiz' => $array[$i]['idQuiz'],
                    'course' => $array[$i]['course']
                );
                $curl = new curl;
                $results = json_decode($curl->post($url, $params));

                return $results;
            }
        }
    }

    private function enviarMovimientoAct()
    {
        global $DB;

        $check = $_POST['checkUpd'];

        $tok = sha1('2017.UVD_TokeN_noDos');

        $results = '';

        foreach ($check as $kch => $vch) {

            $idCurso = $vch["cursoIdPadre"];
            $urlHijo = $vch["urlHijo"];

            $sections = $DB->get_records("course_sections", array('course' => $idCurso), 'section ASC');

            $url = $urlHijo . '/webservice/rest/server.php?wstoken=' . $tok . '&wsfunction=local_mov_act&moodlewsrestformat=json';

            $params = array(
                'idCurso' => $idCurso,
                'course_sections' => json_encode($sections),
                'course_sections2' => json_encode($sections),
            );

            $curl = new curl;

            $results = json_decode($curl->post($url, $params));
        }

        return $results;
    }
}
queryNodo::run();
