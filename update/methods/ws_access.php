<?php

require_once($CFG->dirroot . '/local/backup_course/methods/query_bl_wl.php');

/**
 * Description of ws_access
 *
 * @author jorge.osorio
 */

class ws_accessUpdate
{

    private $type_act = null;
    private $id_act_h = null;

    /*
     * Run the class -> perms
     * @params -> array(url_padre,token,ip,estado)
     * return {int};
     */
    public function perms($param)
    {

        $obj = new self();
        $idfunc = $param['function'];

        switch ($idfunc) {
            case 'U01':
                $resp = $obj->update_acti_nodo($param);
                break;
            case 'U02':
                $resp = $obj->update_recibe_notificate($param['datos']);
                break;
            case 'U03':
                $resp = $obj->update_ob_relation($param['datos'], $param['id_curso_sh'], $param['id_nodo_rel']);
                break;
            case 'U04':
                $resp = $obj->update_courses_nodo($param);
                break;
            case 'C11':
                $resp = $obj->create_activity_nodo($param);
                break;
        }
        return $resp;
    }

    /*
     * Recibe los parámetros del web service y los recorre
     * @params {array} $params
     * return {};
     */
    private function update_acti_nodo($params)
    {
        global $CFG, $DB;
        require_once($CFG->dirroot . "/lib/modinfolib.php");
        $registro = (object)$params;
        $obj = new self();

        $contador = 0;

        foreach (json_decode($registro->obj_act_p) as $kobj =>  $vobj) {

            if (!empty($vobj)) {
                $contador++;
            }
        }

        $cant_update_obj = $contador;

        $return = new stdClass();
        $return->cant = 0;
        $return->cursos_total = array();
        $return->cursos_act_propues = array();
        $return->cursos_actualizados = array();

        $id_cursos_hijo = [];

        foreach ($registro->obj_act_h as $key => $value) {

            $return->cursos_total[] = $value->courseid_sh;
            $cant_update = 0;
            $notificar_padre = new stdClass();
            $notificar_padre->id_updates_nodo = $registro->id_updates_nodos;
            $notificar_padre->url_padre = $registro->url_padre;
            $notificar_padre->id_curso_sh = $value->courseid_sh;
            $notificar_padre->id_nodo_rel = $registro->id_nodo_rel;
            $notificar_padre->id_log = $registro->id_updates_log;
            $ob_import = json_decode($value->objet_ph);

            foreach ($ob_import as $ke => $va) {
                $obj_act_p = json_decode($registro->obj_act_p);
                if (property_exists($va, 'sections')) {
                    $respuesta = $obj->cant_sections($va->sections, $obj_act_p);
                    if (!empty($respuesta)) {
                        $cant_update += $obj->igualar_id_acties($respuesta->actie->datos, $obj_act_p, $ob_import, $notificar_padre->id_curso_sh, $va->sections);

                        $add_obj = $obj->add_obj($ob_import, $ke, $respuesta->numSection, $respuesta->actie->position, $key);

                        if ($cant_update == $cant_update_obj) {
                            $notificar_padre->estado = 3;
                            $return->cant += $obj->notificatePadre($notificar_padre, $add_obj);
                            $return->cursos_actualizados[] = $value->courseid_sh;
                        } else {
                            foreach ($obj_act_p as $kva => $vva) {
                                if ($kva == 'quiz') {
                                    $notificar_padre->estado = 3;
                                    $return->cant += $obj->notificatePadre($notificar_padre, $add_obj);
                                    $return->cursos_actualizados[] = $value->courseid_sh;
                                }
                            }
                        }
                    }
                }
            }

            foreach (json_decode($registro->obj_act_p) as $kfr => $vfr) {

                $tipoAct = "";

                if ($kfr == 'forum') {
                    $tipoAct = 'forum';
                } else if ($kfr == 'assign') {
                    $tipoAct = 'assign';
                } else {
                }

                if ($tipoAct != "") {

                    if (property_exists($vfr, 'rubric')) {

                        $cant_update = $cant_update + 2;

                        $courseIdPadre = $vfr->courseId;

                        $courseIdHijo = 0;

                        foreach ($registro->obj_act_h as $kph1 => $vph1) {

                            $ob_ph1 = json_decode($vph1->objet_ph);

                            $sec = $ob_ph1->sectionAndActi->sections;

                            foreach ($sec as $ksec => $vsec) {

                                if (property_exists($vsec, 'activities')) {

                                    foreach ($vsec->activities as $ksa => $vsa) {

                                        foreach ($vsa as $kvsa => $vvsa) {

                                            if ($vvsa->table == "$tipoAct" && $vvsa->id_como_p  == $courseIdPadre) {

                                                $courseIdHijo = $vvsa->id_como;

                                                // Validar si $courseIdHijo ya existe en $id_cursos_hijo
                                                if (!in_array($courseIdHijo, $id_cursos_hijo)) {
                                                    // Si no existe, agregarlo al array
                                                    array_push($id_cursos_hijo, $courseIdHijo);
                                                    $jsonR = json_decode($registro->obj_act_p);
                                                    //Datos de la tabla grading_definitions del padre
                                                    $idGF = $jsonR->grading_definitions;
                                                    //Datos de la tabla gradingform_rubric_criteria
                                                    $gradingCriteria = $jsonR->gradingform_rubric_criteria;
                                                    //Datos de la tabla 
                                                    $GradingLevels = $jsonR->gradingform_rubric_levels;
                                                    $contextId = $DB->get_record('context', array('instanceid' => $courseIdHijo, 'contextlevel' => '70'));
                                                    $grAreasId = $DB->get_record('grading_areas', array('contextid' => $contextId->id));
                                                    $component = "";
                                                    $areaname = "";
                                                    if (property_exists($jsonR, 'forum')) {
                                                        $component = "mod_forum";
                                                        $areaname = "forum";
                                                    }

                                                    if (property_exists($jsonR, 'assign')) {
                                                        $component = "mod_assign";
                                                        $areaname = "submissions";
                                                    }

                                                    if (empty($grAreasId)) {

                                                        $datos = array('contextid' => "$contextId->id", 'component' => "$component", 'areaname' => "$areaname", 'activemethod' => 'rubric');

                                                        $grIdN = $DB->insert_record('grading_areas',  $datos);

                                                        $grAreasId = $DB->get_record('grading_areas', array('id' => $grIdN));
                                                    } else {

                                                        $DB->update_record('grading_areas', array('id' => $grAreasId->id, 'activemethod' => 'rubric'));

                                                        $grAreasId = $DB->get_record('grading_areas', array('id' => $grAreasId->id));
                                                    }

                                                    if (!empty($grAreasId)) {

                                                        foreach ($idGF as $kgf => $vgf) {

                                                            if (is_object($vgf)) {

                                                                $idDefP = $vgf->p;

                                                                unset($idGF->$kgf);

                                                                $jsonR->courseId;

                                                                $kph = $kph1;

                                                                $vph = $vph1;

                                                                $ob_ph = $ob_ph1;

                                                                if (property_exists($ob_ph, 'grading_definitions')) {

                                                                    //Si en el objeto del hijo existe el id que se envia desde el padre
                                                                    if (property_exists($ob_ph->grading_definitions, $idDefP)) {
                                                                        $idHijo = $ob_ph->grading_definitions->$idDefP->h;
                                                                        //Fin consulta
                                                                        $idGF->id = $idHijo;
                                                                        $idGF->areaid = $grAreasId->id;
                                                                        $DB->update_record('grading_definitions', $idGF);
                                                                        $cant_update++;
                                                                        // Contar las llaves de los objetos de los criterios
                                                                        $cantidadHijoCriteria = count($DB->get_records("gradingform_rubric_criteria", array("definitionid" => $idGF->id)));
                                                                        $cantidadPadreCriteria = count(get_object_vars($gradingCriteria));

                                                                        //---------------------ACTUALIZACIÓN DE CRITERIOS--------------------------
                                                                        //Si la cantidad del padre es mayor a la del hijo significa que hay un nuevo elemento
                                                                        //Se deben actualizar los elementos y agregar el o los nuevos elementos.

                                                                        $criteriaAdd = array();

                                                                        $criteriaDel = array();

                                                                        if ($cantidadPadreCriteria > $cantidadHijoCriteria) {

                                                                            foreach ($gradingCriteria as $kph2 => $vpkh) {

                                                                                foreach ($ob_ph->gradingform_rubric_criteria as $kgc => $vkgc) {
                                                                                    //Se actualiza
                                                                                    if ($kph2 == $kgc) {

                                                                                        $vpkh->id = $vkgc->h;

                                                                                        $vpkh->definitionid = $idGF->id;

                                                                                        $DB->update_record('gradingform_rubric_criteria', $vpkh);

                                                                                        $criteriaAdd = $gradingCriteria;
                                                                                    }
                                                                                }
                                                                            }

                                                                            //DEPURACIÓN DE RUBRICA
                                                                            foreach ($criteriaAdd as $kcri => $vcri) {
                                                                                //Se agrega
                                                                                $vcri->definitionid = $idGF->id;
                                                                                $idInsertCriteria = $DB->insert_record('gradingform_rubric_criteria', $vcri);
                                                                                $ph_object = json_decode($registro->obj_act_h[$kph]->objet_ph);
                                                                                $ph_object->gradingform_rubric_criteria->$kcri['p'] = $vcri->id;
                                                                                $ph_object->gradingform_rubric_criteria->$kcri['h'] = $idInsertCriteria;
                                                                                $ph_objectJ = json_encode($ph_object);
                                                                                $registro->obj_act_h[$kph]->objet_ph = $ph_objectJ;
                                                                                $DB->update_record('bc_rel_padre_hijo', $registro->obj_act_h[$kph]);
                                                                            }
                                                                        } else if ($cantidadPadreCriteria == $cantidadHijoCriteria) {
                                                                            //Se hace una actualización de los criterios

                                                                            foreach ($gradingCriteria as $kph2 => $vpkh) {

                                                                                foreach ($ob_ph->gradingform_rubric_criteria as $kgc => $vkgc) {

                                                                                    if ($kph2 == $kgc) {

                                                                                        $vpkh->id = $vkgc->h;
                                                                                        $vpkh->definitionid = $idGF->id;
                                                                                        $DB->update_record('gradingform_rubric_criteria', $vpkh);
                                                                                    }
                                                                                }
                                                                            }
                                                                        } else {
                                                                            //se hace la eliminación
                                                                            $criteriaHijo = $DB->get_records("gradingform_rubric_criteria", array("definitionid" => $idGF->id));

                                                                            $newObjectCriteria =  new stdClass();

                                                                            foreach ($ob_ph->gradingform_rubric_criteria as  $kgcn => $vkgcn) {

                                                                                foreach ($criteriaHijo as $kcrit => $vcrit) {
                                                                                    if ($vcrit->id ==  $vkgcn->h) {
                                                                                        $newObjectCriteria->$kgcn->p = $vkgcn->p;
                                                                                        $newObjectCriteria->$kgcn->h = $vkgcn->h;
                                                                                    }
                                                                                }
                                                                            }

                                                                            foreach ($gradingCriteria as $kph2 => $vpkh) {

                                                                                foreach ($newObjectCriteria as $kgc => $vkgc) {

                                                                                    if ($kgc == $kph2) {

                                                                                        $vpkh->id = $vkgc->h;
                                                                                        $vpkh->definitionid = $idGF->id;
                                                                                        $DB->update_record('gradingform_rubric_criteria', $vpkh);

                                                                                        unset($newObjectCriteria->$kgc);
                                                                                    } else {

                                                                                        $criteriaDel = $newObjectCriteria;
                                                                                    }
                                                                                }
                                                                            }

                                                                            foreach ($criteriaDel as $kdel => $vdel) {

                                                                                $idDel = $vdel->h;

                                                                                $ph_object = json_decode($registro->obj_act_h[$kph]->objet_ph);

                                                                                unset($ph_object->gradingform_rubric_criteria->$kdel);

                                                                                $ph_objectJ = json_encode($ph_object);

                                                                                $registro->obj_act_h[$kph]->objet_ph = $ph_objectJ;

                                                                                $DB->update_record('bc_rel_padre_hijo', $registro->obj_act_h[$kph]);

                                                                                $DB->delete_records('gradingform_rubric_criteria', array('id' => $idDel));

                                                                                $DB->delete_records('gradingform_rubric_levels', array('criterionid' => $idDel));
                                                                            }
                                                                        }

                                                                        $cant_update++;
                                                                        //----------------------------------FIN DE ACTUALIZACIÓN DE CRITERIOS---------------------------------//


                                                                        $criLevels = $DB->get_records("gradingform_rubric_criteria", array("definitionid" => $idGF->id));

                                                                        $cantidadLevelsHijo = 0;

                                                                        $levelsNewArr = array();
                                                                        $levelsNewArr2 = new stdClass();

                                                                        foreach ($criLevels as $kcl => $vcl) {

                                                                            $levelsHijo = $DB->get_records("gradingform_rubric_levels", array("criterionid" => $vcl->id));

                                                                            if (!empty($levelsHijo)) {

                                                                                array_push($levelsNewArr, $levelsHijo);

                                                                                $cantidadLevelsHijo += count($levelsHijo);
                                                                            }
                                                                        }

                                                                        $levelsNewArr = (object) $levelsNewArr;

                                                                        foreach ($levelsNewArr as $klnr => $vlnr) {

                                                                            foreach ($vlnr as $kvlnr => $valk) {

                                                                                $levelsNewArr2->$kvlnr = $valk;
                                                                            }
                                                                        }

                                                                        $cantidadLevelsPadre = 0;

                                                                        $arrayLevels = array();

                                                                        foreach ($GradingLevels as $kgls => $vgls) {

                                                                            foreach ($vgls as $valgs) {

                                                                                $arrayLevels[$valgs->id] = $valgs;

                                                                                $cantidadLevelsPadre++;
                                                                            }
                                                                        }

                                                                        // Ordenar las llaves en el objeto
                                                                        ksort($arrayLevels);

                                                                        $arrayLevels = (object) $arrayLevels;

                                                                        $elemntosAdd = new stdClass();
                                                                        $elemntosDel = new stdClass();

                                                                        if ($cantidadLevelsPadre > $cantidadLevelsHijo) {
                                                                            foreach ($arrayLevels as $kgrl => $valgrl) {
                                                                                foreach ($ob_ph->gradingform_rubric_levels as $kgrh => $vkgr) {

                                                                                    if ($kgrh == $kgrl) {
                                                                                        //Se actualiza

                                                                                        $newObjUpd = clone $valgrl;

                                                                                        $newObjUpd->id =  $vkgr->h;

                                                                                        unset($newObjUpd->criterionid);

                                                                                        $DB->update_record('gradingform_rubric_levels', $newObjUpd);

                                                                                        unset($arrayLevels->$kgrl);

                                                                                        unset($ob_ph->gradingform_rubric_levels->$kgrh);

                                                                                        $elemntosAdd = $arrayLevels;
                                                                                    }
                                                                                }
                                                                            }
                                                                            //Se agrega
                                                                            foreach ($elemntosAdd as $kadd => $valadd) {

                                                                                $newCrId = 0;

                                                                                $crId = $valadd->criterionid;

                                                                                $newObPh = $DB->get_record('bc_rel_padre_hijo', array('id' => $vph->id));

                                                                                $newObPh = json_decode($newObPh->objet_ph);

                                                                                foreach ($newObPh->gradingform_rubric_criteria as $kgcl => $vkgcl) {

                                                                                    if ($crId ==  $vkgcl->p) {
                                                                                        $newCrId = $vkgcl->h;
                                                                                    }
                                                                                }

                                                                                $newObj = clone $valadd;

                                                                                $newObj->criterionid = $newCrId;

                                                                                $idgrl = $DB->insert_record('gradingform_rubric_levels', $newObj);

                                                                                $ph_object = json_decode($registro->obj_act_h[$kph]->objet_ph);

                                                                                $ph_object->gradingform_rubric_levels->$kadd['p'] = $newObj->id;
                                                                                $ph_object->gradingform_rubric_levels->$kadd['h'] = $idgrl;

                                                                                $ph_objectJ = json_encode($ph_object);
                                                                                $registro->obj_act_h[$kph]->objet_ph = $ph_objectJ;
                                                                                $DB->update_record('bc_rel_padre_hijo', $registro->obj_act_h[$kph]);
                                                                            }
                                                                        } else if ($cantidadLevelsPadre == $cantidadLevelsHijo) {

                                                                            foreach ($arrayLevels as $kgrl => $valgrl) {

                                                                                foreach ($ob_ph->gradingform_rubric_levels as $kgrh => $vkgr) {

                                                                                    if ($kgrh == $kgrl) {
                                                                                        //Se actualiza
                                                                                        $newObjUpd = clone $valgrl;

                                                                                        $newObjUpd->id =  $vkgr->h;

                                                                                        unset($newObjUpd->criterionid);

                                                                                        $DB->update_record('gradingform_rubric_levels', $newObjUpd);
                                                                                    }
                                                                                }
                                                                            }
                                                                        } else {

                                                                            $newObjectDelLevels = new stdClass();

                                                                            foreach ($ob_ph->gradingform_rubric_levels as $kgrln => $vkgln) {
                                                                                foreach ($levelsNewArr2 as $klna => $vlna) {
                                                                                    if ($vlna->id == $vkgln->h) {
                                                                                        $newObjectDelLevels->$kgrln->p = $vkgln->p;
                                                                                        $newObjectDelLevels->$kgrln->h = $vkgln->h;
                                                                                    }
                                                                                }
                                                                            }

                                                                            foreach ($arrayLevels as $kgrl => $valgrl) {
                                                                                foreach ($newObjectDelLevels as $kgrh => $vkgr) {

                                                                                    if ($kgrh == $kgrl) {
                                                                                        //Se actualiza
                                                                                        $newObjUpd = clone $valgrl;

                                                                                        $newObjUpd->id =  $vkgr->h;

                                                                                        unset($newObjUpd->criterionid);

                                                                                        $DB->update_record('gradingform_rubric_levels', $newObjUpd);

                                                                                        unset($newObjectDelLevels->$kgrh);
                                                                                    } else {

                                                                                        $elemntosDel = $newObjectDelLevels;
                                                                                    }
                                                                                }
                                                                            }

                                                                            foreach ($elemntosDel as $kdel => $valdel) {

                                                                                $del = $valdel->h;

                                                                                $DB->delete_records('gradingform_rubric_levels', array('id' => $del));

                                                                                $ph_object = json_decode($registro->obj_act_h[$kph]->objet_ph);

                                                                                unset($ph_object->gradingform_rubric_levels->$kdel);

                                                                                $ph_objectJ = json_encode($ph_object);
                                                                                $registro->obj_act_h[$kph]->objet_ph = $ph_objectJ;
                                                                                $DB->update_record('bc_rel_padre_hijo', $registro->obj_act_h[$kph]);
                                                                            }
                                                                        }
                                                                        $cant_update++;
                                                                    } else {

                                                                        //Se crea toda la rubrica en caso de que si existan rubricas, pero esta esta es nueva

                                                                        $idGF->areaid = $grAreasId->id;

                                                                        $idIn = $DB->insert_record('grading_definitions', $idGF);

                                                                        $cant_update++;

                                                                        $ob_ph->grading_definitions->$idDefP['p'] = $idDefP;
                                                                        $ob_ph->grading_definitions->$idDefP['h'] = $idIn;

                                                                        $ph_object = json_decode($registro->obj_act_h[$kph]->objet_ph);

                                                                        $ph_object->grading_definitions->$idDefP['p'] = $idDefP;
                                                                        $ph_object->grading_definitions->$idDefP['h'] = $idIn;

                                                                        foreach ($gradingCriteria as $kcr => $vkgcr) {

                                                                            $vkgcr->definitionid = $idIn;

                                                                            $idc = $DB->insert_record('gradingform_rubric_criteria', $vkgcr);

                                                                            $ph_object->gradingform_rubric_criteria->$kcr['p'] = $vkgcr->id;
                                                                            $ph_object->gradingform_rubric_criteria->$kcr['h'] = $idc;

                                                                            foreach ($GradingLevels as $kgl => $vgl) {

                                                                                foreach ($vgl as $kvgl) {

                                                                                    if ($kvgl->criterionid == $vkgcr->id) {

                                                                                        // Clonar el objeto
                                                                                        $objetoClonado = clone $kvgl;

                                                                                        $objetoClonado->criterionid = $idc;

                                                                                        $idLevel =  $DB->insert_record('gradingform_rubric_levels', $objetoClonado);
                                                                                        $idObjClonado = $objetoClonado->id;
                                                                                        $ph_object->gradingform_rubric_levels->$idObjClonado['p'] = $objetoClonado->id;
                                                                                        $ph_object->gradingform_rubric_levels->$idObjClonado['h'] = $idLevel;
                                                                                    }
                                                                                }
                                                                            }
                                                                        }
                                                                        $cant_update++;
                                                                        $cant_update++;

                                                                        $ph_objectJ = json_encode($ph_object);
                                                                        $registro->obj_act_h[$kph]->objet_ph = $ph_objectJ;
                                                                        $DB->update_record('bc_rel_padre_hijo', $registro->obj_act_h[$kph]);
                                                                    }
                                                                } else {

                                                                    $idGF->areaid = $grAreasId->id;

                                                                    $idIn = $DB->insert_record('grading_definitions', $idGF);
                                                                    $cant_update++;

                                                                    $ob_ph->grading_definitions->$idDefP['p'] = $idDefP;
                                                                    $ob_ph->grading_definitions->$idDefP['h'] = $idIn;

                                                                    $ph_object = json_decode($registro->obj_act_h[$kph]->objet_ph);

                                                                    $ph_object->grading_definitions->$idDefP['p'] = $idDefP;
                                                                    $ph_object->grading_definitions->$idDefP['h'] = $idIn;

                                                                    foreach ($gradingCriteria as $kcr => $vkgcr) {

                                                                        $vkgcr->definitionid = $idIn;

                                                                        $idc = $DB->insert_record('gradingform_rubric_criteria', $vkgcr);

                                                                        $ph_object->gradingform_rubric_criteria->$kcr['p'] = $vkgcr->id;
                                                                        $ph_object->gradingform_rubric_criteria->$kcr['h'] = $idc;

                                                                        foreach ($GradingLevels as $kgl => $vgl) {

                                                                            foreach ($vgl as $kvgl) {

                                                                                if ($kvgl->criterionid == $vkgcr->id) {

                                                                                    // Clonar el objeto
                                                                                    $objetoClonado = clone $kvgl;

                                                                                    $objetoClonado->criterionid = $idc;

                                                                                    $idLevel =  $DB->insert_record('gradingform_rubric_levels', $objetoClonado);

                                                                                    $idObjClonado = $objetoClonado->id;

                                                                                    $ph_object->gradingform_rubric_levels->$idObjClonado['p'] = $objetoClonado->id;
                                                                                    $ph_object->gradingform_rubric_levels->$idObjClonado['h'] = $idLevel;
                                                                                }
                                                                            }
                                                                        }
                                                                    }
                                                                    $cant_update++;
                                                                    $cant_update++;

                                                                    $ph_objectJ = json_encode($ph_object);
                                                                    $registro->obj_act_h[$kph]->objet_ph = $ph_objectJ;
                                                                    $DB->update_record('bc_rel_padre_hijo', $registro->obj_act_h[$kph]);
                                                                }
                                                                /* } */
                                                            }
                                                        }
                                                    }
                                                    /* } */
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }

                    $add_obj = $ob_import;

                    if ($cant_update == $cant_update_obj) {

                        $notificar_padre->estado = 3;
                        $return->cant += $obj->notificatePadre($notificar_padre, $add_obj);
                        $return->cursos_actualizados[] = $value->courseid_sh;
                    }
                }

                //Continua aqui!!!!!!!!!!!!!!!
                if ($kfr == 'scorm') {

                    $idScormPadre = 0;

                    $idScormPadreArray = array();

                    $courseModulesHijo = 0;

                    $padreObj = json_decode($registro->obj_act_p);

                    $course_modules = $padreObj->course_modules->id;

                    $contUpd = 0;

                    $cantidadScormUpdate = count($registro->obj_act_h);

                    foreach ($registro->obj_act_h as $kph2 => $vph2) {

                        $ob_ph = json_decode($vph2->objet_ph);

                        $sec = $ob_ph->sectionAndActi->sections;

                        foreach ($sec as $ksec => $vsec) {

                            if (property_exists($vsec, 'activities')) {

                                foreach ($vsec->activities as $kac => $vac) {

                                    foreach ($vac as $kvac1 => $vvac) {

                                        if ($vvac->id_como_p  == $course_modules) {

                                            $courseModulesHijo = $vvac->id_como;

                                            if (property_exists($vvac, 'info_actividad')) {

                                                $act = $vvac->info_actividad;

                                                foreach ($act as $kact => $vact) {

                                                    if (property_exists($vfr, 'scorm_scoes_table') && $kact == 'scoes') {

                                                        $sc_sc_ta = $vfr->scorm_scoes_table;

                                                        foreach ($sc_sc_ta as $ksc => $vksc) {

                                                            foreach ($vact as $kvac => $vkvac) {

                                                                if (!property_exists($vact, $vksc->id)) {

                                                                    $scoesHijo = $DB->get_record('scorm_scoes', array('id' => $vkvac->h));

                                                                    $idScormPadre = $scoesHijo->scorm;

                                                                    array_push($idScormPadreArray, $vkvac->h);

                                                                    $vksc->scorm = $idScormPadre;

                                                                    /* $DB->delete_records('scorm_scoes', array('id' => $vkvac->h)); */

                                                                    $newId = $DB->insert_record('scorm_scoes', $vksc);

                                                                    unset($ob_ph->sectionAndActi->sections[$ksec]->activities[$kac][$kvac1]->info_actividad->scoes->$kvac);

                                                                    $ph_objectJ = json_encode($ob_ph);
                                                                    $registro->obj_act_h[$kph2]->objet_ph = $ph_objectJ;
                                                                    $DB->update_record('bc_rel_padre_hijo', $registro->obj_act_h[$kph2]);

                                                                    $ob_ph->sectionAndActi->sections[$ksec]->activities[$kac][$kvac1]->info_actividad->scoes->$ksc->p = $ksc;

                                                                    $ob_ph->sectionAndActi->sections[$ksec]->activities[$kac][$kvac1]->info_actividad->scoes->$ksc->h = $newId;
                                                                }
                                                            }

                                                            /* break; */
                                                        }
                                                    }

                                                    foreach ($idScormPadreArray as $ksp =>  $vsp) {
                                                        $DB->delete_records('scorm_scoes', array('id' => $vsp));
                                                    }

                                                    if (property_exists($vfr, 'scorm_scoes_data_table') && $kact == 'scorm_scoes_data') {

                                                        $sc_sc_ta = $vfr->scorm_scoes_data_table;

                                                        $idScoid = 0;

                                                        foreach ($sc_sc_ta->{0} as $ksc => $vksc) {

                                                            foreach ($vact as $kvac => $vkvac) {
                                                                $idScoid++;
                                                            }
                                                        }

                                                        if ($idScoid != 0) {

                                                            foreach ($sc_sc_ta as $kscat => $vscat) {

                                                                foreach ($vscat as $kvsact => $vvsact) {
                                                                    foreach ($act->scoes as $kacts => $vacts) {
                                                                        if ($vvsact->scoid == $kacts) {

                                                                            $idPadreSD = $vvsact->id;

                                                                            $vvsact->scoid = $vacts->h;

                                                                            $newId = $DB->insert_record('scorm_scoes_data', $vvsact);

                                                                            $ob_ph->sectionAndActi->sections[$ksec]->activities[$kac][$kvac1]->info_actividad->scorm_scoes_data->$idPadreSD->p = $idPadreSD;

                                                                            $ob_ph->sectionAndActi->sections[$ksec]->activities[$kac][$kvac1]->info_actividad->scorm_scoes_data->$idPadreSD->h = $newId;

                                                                            break;
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }

                                                foreach ($vact as $kvac => $vkvac) {

                                                    unset($ob_ph->sectionAndActi->sections[$ksec]->activities[$kac][$kvac1]->info_actividad->scorm_scoes_data->$kvac /* $kvac */);

                                                    $ph_objectJ = json_encode($ob_ph);
                                                    $registro->obj_act_h[$kph2]->objet_ph = $ph_objectJ;
                                                    $DB->update_record('bc_rel_padre_hijo', $registro->obj_act_h[$kph2]);

                                                    $DB->delete_records('scorm_scoes_data', array('id' => $vkvac->h));
                                                }

                                                if (property_exists($vfr, 'file_dir') &&  $kact == 'file_dir') {

                                                    if ($vfr->file_dir != $vact) {

                                                        $ob_ph->sectionAndActi->sections[$ksec]->activities[$kac][$kvac1]->info_actividad->file_dir = $vfr->file_dir;

                                                        $ph_objectJ = json_encode($ob_ph);
                                                        $registro->obj_act_h[$kph2]->objet_ph = $ph_objectJ;
                                                        $DB->update_record('bc_rel_padre_hijo', $registro->obj_act_h[$kph2]);

                                                        /* if (!file_exists($CFG->dataroot . $vact)) { */

                                                        require_once $CFG->dirroot . '/local/backup_course/folder_S3/controlador2_m.php';

                                                        $id_nodo = $vfr->id_nodo;
                                                        $id_rel = $vfr->rel_id;
                                                        $moodle_data = $vfr->url_scorm;
                                                        $archivo = $vfr->archivo;
                                                        $reference = $vfr->reference;

                                                        $file_dir = $vact;

                                                        $name_archive = $archivo . '_' . $id_nodo . '_' . $id_rel . '.zip';
                                                        $to = $CFG->dataroot . '/temp/';

                                                        $s3 = new Controlador2_m(); //hacer transfer
                                                        $s3->run('transfer', $name_archive, $to, $id_nodo);

                                                        $fs = get_file_storage();

                                                        $context = context_module::instance($courseModulesHijo);

                                                        $name_arc = str_replace('.zip', '_' . $id_nodo . '.zip', $reference);

                                                        $from_zip_file = $to . $name_archive;

                                                        $file_record = array(
                                                            'contextid' => $context->id,
                                                            'component' => 'mod_scorm',
                                                            'filearea' => 'package',
                                                            'itemid' => 0,
                                                            'filepath' => '/',
                                                            'filename' => $name_arc,
                                                            'timecreated' => time(),
                                                            'timemodified' => time()
                                                        );

                                                        $packagefile = $fs->create_file_from_pathname($file_record, $from_zip_file);

                                                        $fs->delete_area_files($context->id, 'mod_scorm', 'content');

                                                        $packer = get_file_packer('application/zip');

                                                        $packagefile->extract_to_storage($packer, $context->id, 'mod_scorm', 'content', 0, '/');

                                                        $contUpd++;

                                                        if ($contUpd == $cantidadScormUpdate) {

                                                            $s3->run('delete', $moodle_data, $name_archive, $id_nodo);

                                                            unlink($to . $name_archive);
                                                        }

                                                        /*  } */

                                                        $string = $file_dir;

                                                        // Divide el string usando la barra diagonal como delimitador
                                                        $partes = explode('/', $string);

                                                        // Obtiene la última parte del array resultante
                                                        $ultima_cadena = end($partes);

                                                        $idscm = $DB->get_record('course_modules', array('instance' =>  $idScormPadre));

                                                        $idContext = $DB->get_record('context', array('instanceid' =>  $idscm->id));

                                                        $DB->delete_records('files', array('contenthash' => $ultima_cadena, 'contextid' => $idContext->id));

                                                        $section = $DB->get_record('scorm', array('id' => $idScormPadre));

                                                        $section->sha1hash = $archivo;

                                                        $DB->update_record('scorm', $section);
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        /* break; */
                    }
                }

                if ($kfr == 'folder') {

                    require_once $CFG->dirroot . '/local/backup_course/folder_S3/controlador2_m.php';

                    $s3 = new Controlador2_m(); //hacer transfer

                    //Eliminamos los archivos desde los directorios
                    foreach (json_decode($registro->obj_act_p) as $kobj =>  $vobj) {

                        if ($kobj == 'folder') {

                            $filesDir = (object) $vobj->files_dir;

                            if (!empty($filesDir)) {

                                foreach ($filesDir as $kfd => $vfd) {

                                    unlink($CFG->dataroot . $vfd);
                                }
                            }
                        }
                    }

                    foreach ($registro->obj_act_h as $kph2 => $vph2) {

                        $objPh = json_decode($vph2->objet_ph);

                        $seccAct = $objPh->sectionAndActi->sections;

                        $contFolder = 0;

                        foreach ($seccAct as $ksca => $vsca) {

                            if (property_exists($vsca, 'activities')) {

                                $activities = $vsca->activities;

                                foreach ($activities as $k => $v) {

                                    foreach ($v as $ka => $va) {

                                        if ($va->table == 'folder' && property_exists($va, 'info_actividad')) {

                                            foreach (json_decode($registro->obj_act_p) as $kobj2 =>  $vobj2) {

                                                $padreObj = json_decode($registro->obj_act_p);
                                                $course_modules = $padreObj->course_modules->id;

                                                if ($kobj2 == 'folder' && $course_modules == $va->id_como_p) {

                                                    $folder = $DB->get_record('folder', array('id' => $va->id_acti));

                                                    $course_mod = $DB->get_record('course_modules', array('instance' => $folder->id, 'course' => $folder->course));

                                                    $context_fold = $DB->get_record('context', array('instanceid' => $course_mod->id));

                                                    $filesHijo = $DB->get_records('files', array('contextid' => $context_fold->id));

                                                    $filDel = $DB->get_records('files', array('contextid' => $context_fold->id));

                                                    $DB->delete_records('files', array('contextid' => $context_fold->id));

                                                    $urls_file = (object) $vobj2->urls_file;
                                                    $name_archive_folder = (object) $vobj2->name_archive_folder;
                                                    $files_dir = (object) $vobj2->files_dir;
                                                    $hashed = (object) $vobj2->hashed;
                                                    $cantidadFold = count(get_object_vars($vobj2->files_folder));

                                                    foreach ($vobj2->files_folder as $kfh => $vkf) {

                                                        foreach ($name_archive_folder as $knf => $vnf) {

                                                            foreach ($files_dir as $kfd => $vfd) {

                                                                foreach ($hashed as $khs => $vhs) {

                                                                    $parts = explode('_', $vnf);

                                                                    $firstPart = $parts[0];

                                                                    $lastPart = basename($vfd);
                                                                    ///aquiiiiiiiii el error 
                                                                    $idNodo = $parts[2];

                                                                    if (!empty($urls_file)) {

                                                                        foreach ($urls_file as $kmdf => $vmdf) {

                                                                            $fileName = basename($vmdf);

                                                                            $par = explode("_", $fileName);

                                                                            // Obtener la primera parte
                                                                            $name = $par[0];

                                                                            if ($name == $vkf->contenthash && $lastPart == $firstPart && $vkf->contenthash == $firstPart) {

                                                                                $mainString = $vnf;
                                                                                $substring = $vhs;

                                                                                $position = strpos($mainString, $substring);

                                                                                $associatedSubstring = substr($mainString, $position, strlen($substring));

                                                                                if ($position !== false) {

                                                                                    $name_archive = $vnf;
                                                                                    $to = $CFG->dataroot . '/temp/';
                                                                                    $fs = get_file_storage();

                                                                                    //hacer transfer
                                                                                    $s3->run('transfer', $name_archive, $to, $idNodo);

                                                                                    //Cambiamos el nombre del archivo
                                                                                    $rutaArchivo = $to . $name_archive;
                                                                                    $nuevoNombre = $lastPart;
                                                                                    rename($rutaArchivo, dirname($rutaArchivo) . '/' . $nuevoNombre);

                                                                                    $from_zip_file = $to . $nuevoNombre;

                                                                                    $file_record = array(
                                                                                        'contextid' => $context_fold->id,
                                                                                        'component' => 'mod_folder',
                                                                                        'filearea' => $vkf->filearea,
                                                                                        'itemid' => 0,
                                                                                        'filepath' => $vkf->filepath,
                                                                                        'filename' => $vkf->filename,
                                                                                        'timecreated' => time(),
                                                                                        'timemodified' => time()
                                                                                    );

                                                                                    if (filesize($from_zip_file) > 0) {

                                                                                        $packagefile = $fs->create_file_from_pathname($file_record, $from_zip_file);

                                                                                        $packagefile = (array) $packagefile;

                                                                                        foreach ($packagefile as  $pck => $vpck) {

                                                                                            if (property_exists($vpck, 'id')) {

                                                                                                foreach ($filDel as $kfdls => $vkfdls) {
                                                                                                    $iddel = $vkfdls->id;
                                                                                                    unset($objPh->sectionAndActi->sections[$ksca]->activities[$k][$ka]->info_actividad->files_folder->$iddel);
                                                                                                }

                                                                                                $objPh->sectionAndActi->sections[$ksca]->activities[$k][$ka]->info_actividad->files_folder->$kfh->p = $vkf->id;
                                                                                                $objPh->sectionAndActi->sections[$ksca]->activities[$k][$ka]->info_actividad->files_folder->$kfh->h = $vpck->id;

                                                                                                $ph_objectJ = json_encode($objPh);
                                                                                                $registro->obj_act_h[$kph2]->objet_ph = $ph_objectJ;
                                                                                                $DB->update_record('bc_rel_padre_hijo', $registro->obj_act_h[$kph2]);
                                                                                            }
                                                                                        }
                                                                                    }

                                                                                    $moodle_data = $vmdf;

                                                                                    $s3->run('delete', $moodle_data, $name_archive, $idNodo);

                                                                                    if (file_exists($rutaArchivo)) {
                                                                                        unlink($rutaArchivo);
                                                                                    }

                                                                                    if (file_exists($from_zip_file)) {
                                                                                        unlink($from_zip_file);
                                                                                    }
                                                                                }
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            foreach ($ob_import as $ke => $va) {
                $obj_act_p = json_decode($registro->obj_act_p);
                if ($ke == 'demas_info' && property_exists($obj_act_p, 'grade_items')) { // calificaciones de la actividad
                    $cant_update += $obj->update_grade_items($va->groupings_groups->grade_items, $value->courseid_sh, $obj_act_p->grade_items, $va->cat_p, $va->cat_h);
                    if ($cant_update == $cant_update_obj) {
                        $notificar_padre->estado = 3;
                        $return->cant += $obj->notificatePadre($notificar_padre, $add_obj);
                        $return->cursos_actualizados[] = $value->courseid_sh;
                    }
                }
            }

            rebuild_course_cache($value->courseid_sh);
        }

        return $return;
    }

    /*
     * Recibe las secciones del objeto en la tabla bc_rel_padre_hijo y las recorres
     * @params {obj} $sections
     * @params {obj} $obj_act_p
     * return {obj} objeto con la información a actualizar en la actividad
     */
    private function add_obj($ob_import, $ke, $nu, $pos, $id_rel)
    {

        global $DB;
        $insert = new stdClass();
        $insert->id = $id_rel;
        $insert->objet_ph = json_encode($ob_import);
        if ($DB->update_record('bc_rel_padre_hijo', $insert)) {
            return $ob_import;
        }
    }

    /*
     * Recibe las secciones del objeto en la tabla bc_rel_padre_hijo y las recorres
     * @params {obj} $sections
     * @params {obj} $obj_act_p
     * return {obj} objeto con la información a actualizar en la actividad
     */
    private function cant_sections($sections, $obj_act_p)
    {
        $obj = new self();
        $obj_comparar = new stdClass();
        foreach ($sections as $k => $value) {

            if (!empty($value->activities[0])) {

                $obj_comparar->actie = $obj->cant_activities($value->activities[0], $obj_act_p);
            }

            if (!empty($obj_comparar->actie)) {
                $obj_comparar->numSection = $k;
                return $obj_comparar;
            }
        }
    }

    /*
     * Recibe la información de las actividades 
     * @params {obj} $activities
     * @params {obj} $obj_act_p
     * return {obj} objeto con la información a actualizar en la actividad
     */
    private function cant_activities($activities, $obj_act_p)
    {
        $obj = new self();
        $res = new stdClass();
        foreach ($activities as $k => $value) {
            if (property_exists($value, 'table')) {

                $name_act = $value->table;

                if (property_exists($obj_act_p, $name_act) && property_exists($obj_act_p->$name_act, 'id')) {

                    if ($obj_act_p->$name_act->id == $value->id_acti_p) {
                        $res->datos = $value;
                        $res->position = $k;
                        return $res;
                    }
                }
            }
        }
    }
    /*
     * Cambia el id de los objetos del curso padre con la información a actualizar por el id del curso en el hijo
     * @params {obj} $objrel
     * @params {obj} $obj_act_p
     * @params {obj} $demas_info
     * return {int} $cant_update cantidad de elementos actualizados 
     */
    private function igualar_id_acties($objrel, $info_act_p, $obj_todo_info, $id_course_h, $sectiones)
    {
        global $DB;
        $obj = new self();
        $demas_info = $obj_todo_info->demas_info;
        $tp_acti = $objrel->table;

        $this->id_act_h = $objrel->id_acti;
        $this->type_act = $tp_acti;
        $cant_update = 0;
        $idLPages = "";

        $id_padre = $obj_todo_info->cursos->id_padre;
        $id_hijo = $obj_todo_info->cursos->id_hijo;

        $obj_act_p = $info_act_p;

        if (property_exists($objrel, 'info_actividad')) {
            $obj_act_p = $info_act_p;
            foreach ($objrel->info_actividad as $key => $value) {

                if ($key == 'question' && property_exists($obj_act_p, 'question')) {

                    $obj->updateQuestion_Course($obj_act_p, $obj_todo_info->bancoPregu, $id_course_h, $sectiones, $obj_act_p, null);
                }

                if (property_exists($obj_act_p, $key)) {

                    $name_table = $key;
                    if (!empty($obj_act_p->$key)) {
                        $datos_tabla_insert = clone $obj_act_p->$key;
                        $datos_delete = "";
                        if (is_object($value)) {
                            $datos_delete = clone $value;
                        }

                        if ($key == 'quiz_feedback') { //actualizar quiz_feedback, se deben eliminar y volver a crear
                            foreach ($value as $k => $v) {
                                if (property_exists($objrel->info_actividad->$key, $k)) {
                                    if ($DB->delete_records('quiz_feedback', array('id' => $v->h))) {
                                        $pos = $v->p;
                                        unset($objrel->info_actividad->$key->$pos);
                                    }
                                }
                            }
                            $DB->execute('DELETE  fee FROM {quiz_feedback} fee
                                            INNER JOIN {quiz} qui ON fee.quizid = qui.id
                                            WHERE qui.course = ' . $id_course_h);
                        }
                        foreach ($datos_tabla_insert as $ke => $val) {
                            if (
                                $key == 'assign_plugin_config' || $key == 'grading_areas' ||
                                $key == 'choice_options'  || $key == 'choicegroup_options' ||
                                $key == 'feedback_item'   || $key == 'lesson_answers' || $key == 'lesson_pages' ||
                                $key == 'quiz_feedback'   || $key == 'quiz_slots' ||
                                $key == 'workshopform_comments' || $key == 'workshopform_accumulative' || $key == 'workshopform_numerrors' || $key == 'workshopform_rubric'
                            ) {

                                if ($key == 'quiz_feedback') { //actualizar quiz_feedback, se deben eliminar y volver a crear

                                    $pId = $val->id;
                                    $val->quizid = $objrel->id_acti;
                                    $objrel->info_actividad->$key->$pId = new stdClass();
                                    $objrel->info_actividad->$key->$pId->p = $pId;
                                    $hId = $DB->insert_record('quiz_feedback', $val);
                                    if ($hId) {
                                        $objrel->info_actividad->$key->$pId->h = $hId;
                                        unset($datos_tabla_insert->$ke);
                                    }
                                }
                                foreach ($value as $k => $v) {
                                    if ($key == 'grading_areas') {
                                        $grading_areas = new stdClass();
                                        $grading_areas->id = $v->h;
                                        $grading_areas->activemethod = $datos_tabla_insert->activemethod;
                                        if ($obj->updateActies('grading_areas', $grading_areas)) {
                                            unset($datos_tabla_insert->activemethod);
                                        } else {

                                            $cant_update -= 1;
                                        }
                                    } else {

                                        if ($key == 'lesson_answers' && $k == $val->id) {

                                            $val->id = $v->h;
                                            $val->lessonid = $objrel->id_acti;
                                            $p_pageid = $val->pageid;
                                            $p_jumpto = $val->jumpto;

                                            if (property_exists($objrel->info_actividad->lesson_pages, $p_jumpto)) {

                                                $val->jumpto = $objrel->info_actividad->lesson_pages->$p_jumpto->h;
                                            }

                                            if (property_exists($objrel->info_actividad->lesson_pages, $p_pageid)) {

                                                $val->pageid = $objrel->info_actividad->lesson_pages->$p_pageid->h;
                                            }

                                            if ($obj->updateActies($key, $val)) {
                                                unset($datos_tabla_insert->$ke);
                                                unset($datos_delete->$k);
                                            } else {
                                                $cant_update -= 1;
                                            }
                                        } else if ($v->p == $val->id && $key != 'quiz_feedback') {
                                            $val->id = $v->h;
                                            if (property_exists($val, 'assignment')) {
                                                $val->assignment = $objrel->id_acti;
                                                unset($val->subtype, $val->plugin, $val->name);
                                            } else if (property_exists($val, 'choiceid')) {
                                                $val->choiceid = $objrel->id_acti;
                                            } else if (property_exists($val, 'choicegroupid')) {
                                                $val->choicegroupid = $objrel->id_acti;
                                                $position = array_search($val->groupid, $demas_info->groups_p);
                                                if (is_int($position)) {
                                                    $val->groupid = $demas_info->groups_h[$position];
                                                }
                                            } else if (property_exists($val, 'feedback')) {
                                                $val->feedback = $objrel->id_acti;
                                            } else if (property_exists($val, 'lessonid')) {
                                                $val->lessonid = $objrel->id_acti;
                                            } else if (property_exists($val, 'quizid') && $key == 'quiz_slots') {

                                                $val->quizid = $objrel->id_acti;
                                                $banco_pregu = $obj_todo_info->bancoPregu->question;
                                                $questionid = $val->questionid;
                                                if (property_exists($objrel->info_actividad->question, $questionid)) {
                                                    $val->questionid = $objrel->info_actividad->question->$questionid->h;
                                                } else {
                                                    for ($j = 0; $j < count($banco_pregu); $j++) {
                                                        if ($questionid == $banco_pregu[$j]->p) {
                                                            $val->questionid = $banco_pregu[$j]->h;
                                                        }
                                                    }
                                                }
                                            } else if (property_exists($val, 'workshopid')) {
                                                $val->workshopid = $objrel->id_acti;
                                            }
                                            if ($key == 'lesson_pages') {
                                                $pPrevpageid = $val->prevpageid;
                                                if (property_exists($objrel->info_actividad->lesson_pages, $pPrevpageid)) {
                                                    $val->prevpageid = $objrel->info_actividad->lesson_pages->$pPrevpageid->h;
                                                }
                                                $pNextpageid = $val->nextpageid;
                                                if (property_exists($objrel->info_actividad->lesson_pages, $pNextpageid)) {
                                                    $val->nextpageid = $objrel->info_actividad->lesson_pages->$pNextpageid->h;
                                                }
                                            }

                                            if ($obj->updateActies($key, $val)) {
                                                unset($datos_tabla_insert->$ke);
                                                unset($datos_delete->$k);
                                            } else {
                                                $cant_update -= 1;
                                            }
                                        }
                                    }
                                }
                            } else {
                                $datos_delete = 0;
                                $datos_tabla_insert = 0;
                            }
                        }

                        if (($key == 'choice_options' || $key == 'choicegroup_options' || $key == 'feedback_item' ||
                                $key == 'lesson_pages' || $key == 'lesson_answers' ||
                                $key == 'workshopform_comments' || $key == 'workshopform_accumulative' || $key == 'workshopform_numerrors' || $key == 'workshopform_rubric')
                            && count(get_object_vars($datos_delete)) > 0
                        ) {

                            foreach ($datos_delete as $ke => $datos) {

                                $DB->delete_records($key, array('id' => $datos->h));

                                unset($objrel->info_actividad->$key->$ke);
                            }
                        }
                        if (($key == 'assign_plugin_config' || $key == 'choice_options' ||
                            $key == 'choicegroup_options' || $key == 'feedback_item'  ||
                            $key == 'lesson_pages'        || $key == 'lesson_answers' ||
                            $key == 'workshopform_comments' || $key == 'workshopform_accumulative' || $key == 'workshopform_numerrors' || $key == 'workshopform_rubric') && count(get_object_vars($datos_tabla_insert)) > 0) {

                            foreach ($datos_tabla_insert as $ke => $datos) {

                                unset($datos->id);
                                if (property_exists($datos, 'groupid')) { //choicegroup_options
                                    $position = array_search($datos->groupid, $demas_info->groups_p);
                                    if (is_int($position)) {
                                        $datos->groupid = $demas_info->groups_h[$position];
                                    }
                                }
                                if (property_exists($datos, 'enrolmentkey') && !empty($datos->enrolmentkey)) {
                                    $datos->enrolmentkey = null;
                                }

                                if (property_exists($datos, 'assignment')) {
                                    $datos->assignment = $objrel->id_acti;
                                }
                                if (property_exists($datos, 'choiceid')) {
                                    $datos->choiceid = $objrel->id_acti;
                                }
                                if (property_exists($datos, 'choicegroupid')) {
                                    $datos->choicegroupid = $objrel->id_acti;
                                }
                                if (property_exists($datos, 'feedback')) {
                                    $datos->feedback = $objrel->id_acti;
                                }
                                if (property_exists($datos, 'quizid')) {
                                    $datos->quizid = $objrel->id_acti;
                                }
                                if (property_exists($datos, 'lessonid')) { //lesson_pages y lesson_answers

                                    $datos->lessonid = $objrel->id_acti;
                                    if (property_exists($datos, 'prevpageid') && property_exists($datos, 'nextpageid')) {
                                        $pPrevpageid = $datos->prevpageid;
                                        if (property_exists($objrel->info_actividad->lesson_pages, $pPrevpageid)) {
                                            $datos->prevpageid = $objrel->info_actividad->lesson_pages->$pPrevpageid->h;
                                        }
                                        $pNextpageid = $datos->nextpageid;
                                        if (property_exists($objrel->info_actividad->lesson_pages, $pNextpageid)) {
                                            $datos->nextpageid = $objrel->info_actividad->lesson_pages->$pNextpageid->h;
                                        }
                                        $p_pageid = $datos->pageid;

                                        if (property_exists($objrel->info_actividad->lesson_pages, $p_pageid)) {
                                            /* $datos->pageid = $objrel->info_actividad->lesson_pages->$p_pageid->h; */
                                            $datos->pageid = 999;
                                        }
                                    } else {
                                        $datos->prevpageid = 0;
                                        $datos->nextpageid = 0;
                                    }
                                }
                                $objrel->info_actividad->$key->$ke = new stdClass();

                                //Inserción de id padre e hijo
                                if ($key == 'lesson_pages') {

                                    $objrel->info_actividad->$key->$ke->p = $ke;
                                    $objrel->info_actividad->$key->$ke->h = $DB->insert_record($key, $datos);

                                    $idLPages = $objrel->info_actividad->$key->$ke->h;
                                } else {

                                    if ($key == 'lesson_answers') {

                                        if ($idLPages == "") {
                                            $pageL = $datos->pageid;

                                            if (property_exists($objrel->info_actividad->lesson_pages, $pageL)) {
                                                $datos->pageid = $objrel->info_actividad->lesson_pages->$pageL->h;
                                            }
                                        } else {

                                            $datos->pageid = $idLPages;
                                        }

                                        $p_jumptoC = $datos->jumpto;

                                        if (property_exists($objrel->info_actividad->lesson_pages, $p_jumptoC)) {
                                            $datos->jumpto = $objrel->info_actividad->lesson_pages->$p_jumptoC->h;
                                        }

                                        $objrel->info_actividad->$key->$ke->p = $ke;
                                        $objrel->info_actividad->$key->$ke->h = $DB->insert_record($key, $datos);
                                    } else {

                                        $objrel->info_actividad->$key->$ke->p = $ke;
                                        $objrel->info_actividad->$key->$ke->h = $DB->insert_record($key, $datos);
                                    }
                                }

                                unset($datos_tabla_insert->$ke);
                            }
                        }
                        $cant_update += 1;
                    }
                } else if ($key != $tp_acti) {

                    if ($key != 'course_modules') {
                        if ($key != 'grade_items') {
                            $cant_update += $obj->add_config_workshop($objrel, $obj_act_p, $tp_acti);
                        }
                    }
                }
            }
        }

        if (property_exists($obj_act_p, 'course_modules')) {

            $obj_act_p = $info_act_p;
            $name_table = 'course_modules';

            $obj_act_p->course_modules->id = $objrel->id_como;
            if (property_exists($obj_act_p->course_modules, 'course')) {
                unset($obj_act_p->course_modules->course);
            }
            if (property_exists($obj_act_p->course_modules, 'module')) {
                unset($obj_act_p->course_modules->module);
            }
            if (property_exists($obj_act_p->course_modules, 'instance')) {
                unset($obj_act_p->course_modules->instance);
            }

            //////////no actualizar visible

            /* if (property_exists($obj_act_p->course_modules, 'visible')) {
                unset($obj_act_p->course_modules->visible);
            }

            if (property_exists($obj_act_p->course_modules, 'visibleold')) {
                unset($obj_act_p->course_modules->visibleold);
            } */

            if (property_exists($obj_act_p->course_modules, 'groupingid')) {
                if (property_exists($demas_info, 'groupings_p')) {
                    $position = array_search($obj_act_p->course_modules->groupingid, $demas_info->groupings_p);
                    if (is_int($position)) {
                        $obj_act_p->course_modules->groupingid = $demas_info->groupings_h[$position];
                    }
                }
            }

            if (property_exists($obj_act_p->course_modules, 'availability') && !empty($obj_act_p->course_modules->availability)) {
                $availability = json_decode($obj_act_p->course_modules->availability);
                if (property_exists($availability, 'c')) {
                    for ($i = 0; $i < count($availability->c); $i++) {
                        if (property_exists($availability->c[$i], 'type') && $availability->c[$i]->type == 'grouping') {
                            $position = array_search($availability->c[$i]->id, $demas_info->groupings_p);
                            if (is_int($position)) {
                                $availability->c[$i]->id = (int)$demas_info->groupings_h[$position];
                            }
                        }
                        if (property_exists($availability->c[$i], 'type') && $availability->c[$i]->type == 'group') {
                            $position = array_search($availability->c[$i]->id, $demas_info->groups_p);
                            if (is_int($position)) {
                                $availability->c[$i]->id = (int)$demas_info->groups_h[$position];
                            }
                        }
                        if (property_exists($availability->c[$i], 'type') && $availability->c[$i]->type == 'grade') {
                            for ($h = 0; $h < count($demas_info->groupings_groups->grade_items); $h++) {
                                if ($availability->c[$i]->id == $demas_info->groupings_groups->grade_items[$h]->p) {
                                    $availability->c[$i]->id = (int)$demas_info->groupings_groups->grade_items[$h]->h;
                                }
                            }
                        }
                        if (property_exists($availability->c[$i], 'type') && $availability->c[$i]->type == 'completion') {
                            $availability->c[$i]->cm = (int)$obj->search_section($obj_todo_info->sectionAndActi->sections, $availability->c[$i]->cm);
                        }
                        //restricciones varias
                        if (property_exists($availability->c[$i], 'c')) {
                            for ($j = 0; $j < count($availability->c[$i]->c); $j++) {

                                if (property_exists($availability->c[$i]->c, $j) && property_exists($availability->c[$i]->c[$j], 'type')) {
                                    if ($availability->c[$i]->c[$j]->type == 'grouping') {
                                        $position = array_search($availability->c[$i]->c[$j]->id, $demas_info->groupings_p);
                                        if (is_int($position)) {
                                            $availability->c[$i]->c[$j]->id = (int)$demas_info->groupings_h[$position];
                                        }
                                    }
                                    if ($availability->c[$i]->c[$j]->type == 'group') {
                                        $position = array_search($availability->c[$i]->c[$j]->id, $demas_info->groups_p);
                                        if (is_int($position)) {
                                            $availability->c[$i]->c[$j]->id = (int)$demas_info->groups_h[$position];
                                        }
                                    }
                                    if ($availability->c[$i]->c[$j]->type == 'grade') {
                                        for ($h = 0; $h < count($demas_info->groupings_groups->grade_items); $h++) {
                                            if ($availability->c[$i]->c[$j]->id == $demas_info->groupings_groups->grade_items[$h]->p) {
                                                $availability->c[$i]->c[$j]->id = (int)$demas_info->groupings_groups->grade_items[$h]->h;
                                            }
                                        }
                                    }
                                    if ($availability->c[$i]->c[$j]->type == 'completion') {
                                        $availability->c[$i]->c[$j]->cm = (int) $obj->search_section($obj_todo_info->sectionAndActi->sections, $availability->c[$i]->c[$j]->cm);
                                    }
                                }
                            }
                        }
                    }
                }

                $obj_act_p->course_modules->availability = json_encode($availability);
            }
            $obj_act = $obj_act_p->course_modules;
            if (!empty($obj->updateActies($name_table, $obj_act))) {
                $cant_update += 1;
            } else {
                $cant_update -= 1;
            }
        }

        if (property_exists($obj_act_p, $tp_acti)) {
            $obj_act_p = $info_act_p;
            $obj_act_p->$tp_acti->id = $objrel->id_acti;
            if ($tp_acti == 'game') {

                foreach ($sectiones as $ks => $vs) {

                    $activi = $vs->activities;

                    foreach ($activi as $ka => $va) {

                        foreach ($va as $newkey => $val) {

                            if ($obj_act_p->$tp_acti->glossaryid != 0) {

                                $glosaryId = $obj_act_p->$tp_acti->glossaryid;

                                if ($val->table == 'glossary' && $val->id_acti_p == $glosaryId) {

                                    $obj_act_p->$tp_acti->glossaryid = $val->id_acti;
                                }
                            }

                            if ($obj_act_p->$tp_acti->quizid != 0) {

                                $quizId = $obj_act_p->$tp_acti->quizid;

                                if ($val->table == 'quiz' && $val->id_acti_p == $quizId) {

                                    $obj_act_p->$tp_acti->quizid = $val->id_acti;
                                }
                            }
                        }
                    }
                }


                if (property_exists($obj_act_p->$tp_acti, 'questioncategoryid') && !empty($obj_act_p->$tp_acti->questioncategoryid)) {
                    $banco_pregu = $obj_todo_info->bancoPregu->question_categories;
                    $questioncategoryid = $obj_act_p->$tp_acti->questioncategoryid;
                    for ($j = 0; $j < count($banco_pregu); $j++) {
                        if ($questioncategoryid == $banco_pregu[$j]->p) {
                            $obj_act_p->$tp_acti->questioncategoryid = $banco_pregu[$j]->h;
                        }
                    }
                }
                if (property_exists($obj_act_p->$tp_acti, $obj_act_p->$tp_acti->sourcemodule . 'id')) {
                    $obj->sectionGame($sectiones, $objrel->id_acti, $obj_act_p->$tp_acti->sourcemodule);
                }
            } else if (property_exists($obj_act_p->$tp_acti, 'teamsubmissiongroupingid') && property_exists($demas_info, 'groupings_p')) {
                $position = array_search($obj_act_p->$tp_acti->teamsubmissiongroupingid, $demas_info->groupings_p);
                if (is_int($position)) {
                    $obj_act_p->$tp_acti->teamsubmissiongroupingid = $demas_info->groupings_h[$position];
                }
            }
            if (property_exists($obj_act_p->$tp_acti, 'name') || $tp_acti == 'label') {
                if ($tp_acti == 'lti') { /////////// buscar la configuracion del lti
                    if (property_exists($obj_act_p, 'lti_types') && is_object($obj_act_p->lti_types) && property_exists($obj_act_p->$tp_acti, 'typeid') && property_exists($obj_act_p->lti_types, 'name')) {
                        $cant_update += 1;
                        if (property_exists($obj_act_p, 'lti_types')) {
                            $obj_act_p->lti_types = (object)$obj_act_p->lti_types;
                            $lti = $DB->get_record('lti', array('id' => $obj_act_p->$tp_acti->id));
                            $lti_types = $DB->get_record('lti_types', array('id' => $lti->typeid));
                            if (!empty($lti_types) && $lti_types->name == $obj_act_p->lti_types->name) {
                                $lti->typeid = $lti_types->id;
                            } else {
                                $lti_types = $DB->get_record_sql('SELECT * FROM {lti_types} WHERE name = :name LIMIT 1', array('name' => $obj_act_p->lti_types->name));
                                if (!empty($lti_types)) {
                                    $lti->typeid = $lti_types->id;
                                } else {
                                    $lti->typeid = $DB->insert_record('lti_types', $obj_act_p->lti_types);
                                }
                            }
                            $obj_act_p->$tp_acti->typeid = $lti->typeid;
                        }
                    }
                }
                if (!empty($obj->updateActies($tp_acti, $obj_act_p->$tp_acti))) {
                    $cant_update += 1;
                } else {
                    $cant_update -= 1;
                }
            } else {
                $cant_update += 1;
            }
        }

        return $cant_update;
    }

    /*
     * Añadir configuración de workshop
     * @params {string} $key
     * @params {obj} $value
     * @params {obj} $objrel
     * @params {string} $tp_acti
     * return {int} cantidad de elementos actualizados
     */
    private function add_config_workshop($objrel, $obj_act_p, $tp_acti)
    {
        global $DB;
        $ac_key = $tp_acti . 'id';
        $res = 0;

        if (!property_exists($objrel, 'info_actividad')) {
            $objrel->info_actividad = new stdClass();
        }
        foreach ($obj_act_p as $k => $v) {
            if (
                !property_exists($objrel->info_actividad, $k) && $tp_acti != $k
                && $k != 'course_modules' && $k != 'grade_items'
            ) {
                $objrel->info_actividad->$k = new stdClass();

                if ($k == 'workshopform_rubric_levels') {
                    foreach ($v as $ke => $va) {
                        foreach ($va as $k_e => $v_a) {
                            foreach ($objrel->info_actividad->workshopform_rubric as $llav => $info) {
                                if ($info->p == $v_a->dimensionid) {
                                    $llave = $v_a->id;
                                    $v_a->dimensionid = $info->h;
                                    $objrel->info_actividad->$k->$llave = new stdClass();
                                    $objrel->info_actividad->$k->$llave->p = $v_a->id;
                                    $objrel->info_actividad->$k->$llave->h = $DB->insert_record($k, $v_a);
                                }
                            }
                        }
                    }
                } else {

                    if (is_object($v) && !property_exists($v, 'id')) {
                        foreach ($v as $ke => $va) {
                            if (property_exists($va, 'id')) {
                                $llave = $va->id;
                                $va->$ac_key = $objrel->id_acti;
                                $objrel->info_actividad->$k->$llave = new stdClass();
                                $objrel->info_actividad->$k->$llave->p = $va->id;
                                $objrel->info_actividad->$k->$llave->h = $DB->insert_record($k, $va);
                            }
                        }
                        $res = 1;
                    }
                }
            }
        }
        return $res;
    }


    /*
     * Actualiza las actividades
     * @params {string} $name_table
     * @params {obj} $obj_act
     * return {int} cantidad de elementos actualizados
     */
    private function updateActies($name_table, $obj_act)
    {
        global $DB;
        if (!empty($name_table) && !empty($obj_act)) {
            try {
                return $DB->update_record($name_table, $obj_act);
            } catch (Exception $e) {
                $courseid = property_exists($obj_act, 'courseid') ? $obj_act->courseid : 0;
                $courseid = empty($courseid) && property_exists($obj_act, 'course') ? $obj_act->course : 0;
                $DB->insert_record('bc_excepcions_errors', array('error' => json_encode($e->getMessage()), 'courseid' => $courseid, 'userid' => 0, 'description' => 'No se puede actualizar en ' . $name_table));
                return false;
            }
        }
    }


    /*
     * Comparar el objeto para las calificaciones de la actividad
     * @params {obj} $itemes
     * @params {int} $id_course
     * return {int} $cant_update cantidad de elementos actualizados
     */

    private function update_grade_items($itemes, $id_course, $obj_act_p, $cat_p, $cat_h)
    {
        global $DB;
        $obj = new self();
        $cant_update = 0;


        //for($i = 0; $i<count($itemes); $i++){

        $pos3 = array_search($obj_act_p->id, array_column($itemes, 'p'));
        if (is_int($pos3)) {
            $obj_act_p->id = $itemes[$pos3]->h;
            $position = array_search($obj_act_p->categoryid, $cat_p);
            if (is_int($position)) {
                $obj_act_p->categoryid = $cat_h[$position];
                if (property_exists($obj_act_p, 'iteminstance')) {
                    unset($obj_act_p->iteminstance);
                }
                if (property_exists($obj_act_p, 'courseid')) {
                    unset($obj_act_p->courseid);
                }

                if (empty($obj_act_p->decimals)) {
                    $obj_act_p->decimals = null;
                }
                if (empty($obj_act_p->categoryid)) {
                    $obj_act_p->categoryid = null;
                }
                if (empty($obj_act_p->scaleid)) {
                    $obj_act_p->scaleid = null;
                }
                if (empty($obj_act_p->outcomeid)) {
                    $val['outcomeid'] = null;
                }
                if (empty($obj_act_p->calculation)) {
                    $val['calculation'] = null;
                }
                if (property_exists($obj_act_p, 'grademax')) {
                    $obj_act_p->grademax = round($obj_act_p->grademax, 4);
                }
                if (property_exists($obj_act_p, 'gradepass')) {
                    $obj_act_p->gradepass = round($obj_act_p->gradepass, 4);
                }

                if (!empty($obj->updateActies('grade_items', $obj_act_p))) {
                    $cant_update += 1;
                } else {
                    $cant_update -= 1;
                }
            }
        }
        //}
        return $cant_update;
    }


    /*
     * Buscar las calificaiones que coinciden
     * @params {int} $id_course
     * @params {string} $type_act
     * @params {int} $id_act_h
     * @params {$id} $id
     * return {obj} $grade_items elemetos encontrados
     */
    private function buscar_grade_items($id_course, $type_act, $id_act_h, $id)
    {
        global $DB;
        $grade_items = $DB->get_record('grade_items', array('id' => $id, 'itemmodule' => $type_act));
        if (!empty($grade_items)) {
            return $grade_items;
        } else {
            return null;
        }
    }



    /*
     * Notificar al padre que un curso en el nodose actualizó
     * @params {obj} $notificar_padre
     * return {} 
     */
    private function notificatePadre($notificar_padre, $add_obj)
    {
        global $DB, $CFG;
        require_once("$CFG->libdir/filelib.php");
        $tok = sha1('2017.UVD_TokeN_noDos');
        $url = $notificar_padre->url_padre . '/webservice/rest/server.php?wstoken=' . $tok . '&wsfunction=local_update_recibir_notifi_nodos&moodlewsrestformat=json';
        if (!property_exists($notificar_padre, 'estado')) {
            $notificar_padre->estado =  0;
        }
        $notificar_padre->id_update_hijo = $DB->insert_record('update_hijo', $notificar_padre);
        $enviar = new stdClass();
        $enviar->objRel = $add_obj;
        $enviar->datosInsert = $notificar_padre;
        $params = array(
            'ack' => $notificar_padre->estado,
            'response' => json_encode($enviar),
        );


        return 1;
    }



    /*
     * Recibe la información para actualizar en updates_nodos
     * Suma la cantidad de cursos actualizados 
     * Actualiza en el padre la cantidad de cursos terminados
     * @params {obj} $datos
     * return {string} tru si actualizó o No hay datos que coinciden
     */
    private function update_recibe_notificate($datos)
    {
        global $DB, $CFG;
        $datos_updates_nodos = new stdClass();
        $arr_updates_nodos = $DB->get_records('updates_nodos', array('id' => $datos->id_updates_nodo));
        if (!empty($arr_updates_nodos)) {
            $updates_nodos = $arr_updates_nodos[$datos->id_updates_nodo];
            $datos_updates_nodos->id = $datos->id_updates_nodo;

            $datos_updates_nodos->cant_courses_terminados = $updates_nodos->cant_courses_terminados + 1;
            if ($datos_updates_nodos->cant_courses_terminados == $updates_nodos->cant_courses_actual) {
                $datos_updates_nodos->estado = $datos->estado;
            }
            return $DB->update_record('updates_nodos', $datos_updates_nodos);
        } else {
            return 'No hay datos que coinciden';
        }
    }


    /*
     * Recibe la información para actualizar en updates_nodos
     * Suma la cantidad de cursos actualizados 
     * Actualiza en el padre la cantidad de cursos terminados
     * @params {obj} $datos
     * return {string} tru si actualizó o No hay datos que coinciden
     */
    private function update_ob_relation($datos, $id_curso_sh, $id_nodo_rel)
    {
        global $DB, $CFG;
        $upRel = new stdClass();
        $arr_updates_nodos = $DB->get_record('bc_rel_padre_hijo', array('registroid' => $id_nodo_rel, 'courseid_sh' => $id_curso_sh));
        if (!empty($arr_updates_nodos)) {
            //$arr_updates_nodos = each($arr_updates_nodos);
            $upRel->id = $arr_updates_nodos->id;
            $upRel->objet_ph = json_encode($datos);
            return $DB->update_record('bc_rel_padre_hijo', $upRel);
        } else {
            return null;
        }
    }

    /*
     * Recibe la información general del curso para actualizarlos en el nodo
     * Recorre cada curso en el nodo y lo actualiza
     * @params {array} $params
     * return {};
     */
    private function update_courses_nodo($params)
    {
        global $DB, $CFG;

        require_once($CFG->dirroot . "/cache/classes/helper.php");

        $registro = (object)$params;

        $obj = new self();

        $return = new stdClass();

        $return->cant = 0;

        $return->cursos_total = array();

        $return->cursos_actualizados = array();

        foreach ($registro->obj_act_h as $key => $value) {

            $return->cursos_total[] = $value->courseid_sh;
            $notificar_padre = new stdClass();
            $notificar_padre->id_updates_nodo = $registro->id_updates_nodos;
            $notificar_padre->url_padre = $registro->url_padre;
            $notificar_padre->id_curso_sh = $value->courseid_sh;
            $notificar_padre->id_nodo_rel = $registro->id_nodo_rel;
            $notificar_padre->id_log = $registro->id_updates_log;

            $ob_import_rel = json_decode($value->objet_ph);
            $ob_import_p = json_decode($registro->obj_act_p);

            foreach ($ob_import_p as $ke => $val) {

                if ($ke == 'grade_items') {
                    $obj->updateGrade_itemsCourse($val, $ob_import_rel->demas_info, $value->courseid_sh);
                } else if ($ke == 'grade_items_delete') {
                    $obj->updateGrade_itemsCourse($val, $ob_import_rel->demas_info, $value->courseid_sh);
                } else if ($ke == 'grade_categories') {
                    $obj->updateGrade_categoriesCourse($val, $ob_import_rel->demas_info, $value->courseid_sh);
                } else if ($ke == 'groups') {
                    $obj->updateGroup_Course($val, $ob_import_rel->demas_info, $value->courseid_sh);
                } else if ($ke == 'groupings') {
                    $obj->updateGroupings_Course($val, $ob_import_rel->demas_info, $value->courseid_sh);
                } else if ($ke == 'groupings_groups') {
                    $obj->updateGroupings_groups_Course($val, $ob_import_rel->demas_info, $value->courseid_sh);
                } else if ($ke == 'question_categories') {
                    $obj->updateQuestion_categories_Course($val, $ob_import_rel, $value->courseid_sh);
                } else if ($ke == 'question') {
                    $obj->updateQuestion_Course($val, $ob_import_rel->bancoPregu, $value->courseid_sh, $ob_import_rel->sectionAndActi->sections, null);
                } else if ($ke == 'quiz_slots') {
                    $obj->update_slot_banck($val, $ob_import_rel);
                } else if ($ke == 'course' && array_key_exists('enablecompletion', (array)$val)) {

                    //ACTUALIZAR EL BLOQUE DE RECURSOS CON LOS DATOS DEL PADRE
                    $context = context_course::instance($value->courseid_sh);
                    $block_instance = $DB->get_record('block_instances', [
                        'parentcontextid' => $context->id,
                        'blockname' => 'bloque_recursos'
                    ]);

                    $block_options = json_decode($registro->block_options);

                    if (!empty($block_options)) {
                        if ($block_instance) {
                            // Actualizar bloque existente
                            $DB->update_record('block_instances', [
                                'id' => $block_instance->id,
                                'configdata' => $block_options->configdata,
                                'pagetypepattern' => $block_options->pagetypepattern,
                                'showinsubcontexts' => $block_options->showinsubcontexts,
                                'subpagepattern' => $block_options->subpagepattern
                            ]);
                        } else {
                            // Insertar nuevo bloque
                            $block_options->parentcontextid = $context->id;
                            $DB->insert_record('block_instances', $block_options);
                        }

                        // Configurar bloque recursos
                        $this->configBlockRecursos($value->courseid_sh, $value->objet_ph);
                    } else if ($block_instance) {
                        // No hay opciones, eliminar el bloque existente
                        $DB->delete_records('block_instances', ['id' => $block_instance->id]);
                    }

                    //ACTUALIZAR LAS OPCIONES DE FORMATO
                    $format_options = $registro->format_options;
                    $current_format_options = $DB->get_records('course_format_options', ['courseid' => $value->courseid_sh]);
                    if (!empty($current_format_options)) {
                        $sql = "DELETE FROM {course_format_options} WHERE courseid = :courseid AND name != :name";
                        $params = ['courseid' => $value->courseid_sh, 'name' => 'numsections'];
                        $DB->execute($sql, $params);
                    }

                    $format_options = json_decode($format_options);
                    foreach ($format_options as $key => $val_format_options) {

                        $options = [
                            'courseid' => $value->courseid_sh,
                            'format' => $val_format_options->format,
                            'sectionid' => $val_format_options->sectionid,
                            'name' => $val_format_options->name,
                            'value' => $val_format_options->value
                        ];

                        $DB->insert_record('course_format_options', $options);
                    }

                    $obj->update_course_edit((array)$val, $value->courseid_sh);
                }
            }

            cache_helper::invalidate_by_definition('core', 'groupdata', array(), array($value->courseid_sh));

            /* $add_obj = $obj->add_obj($ob_import_rel, null, null, null, $key); */
            $add_obj = $ob_import_rel;
            $notificar_padre->estado = 3;
            $return->cant += $obj->notificatePadre($notificar_padre, $add_obj);
            $return->cursos_actualizados[] = $value->courseid_sh;
            rebuild_course_cache($value->courseid_sh);
        }

        return $return;
    }

    private function configBlockRecursos($course_id, $objet_ph)
    {
        global $DB;
        $objet_ph = json_decode($objet_ph);
        $context = context_course::instance($course_id);
        $block_instance = $DB->get_record('block_instances', ['parentcontextid' => $context->id, 'blockname' => 'bloque_recursos']);
        if (!$block_instance) {
            return false; // No se encontró el bloque
        }

        $config = unserialize(base64_decode($block_instance->configdata));
        $nueva_config = new stdClass();

        foreach ($config as $key => $value) {
            if (strpos($key, 'activity_') === 0) {
                $id = (int) str_replace('activity_', '', $key);
                $matchFound = false;
                foreach ($objet_ph->sectionAndActi->sections as $section) {
                    if (!empty($section->activities)) {
                        foreach ($section->activities as $grupo) {
                            foreach ($grupo as $actividad) {
                                $actividad = (object) $actividad;
                                if (isset($actividad->id_como_p) && $actividad->id_como_p == $id) {
                                    $newKey = 'activity_' . $actividad->id_como;
                                    $nueva_config->$newKey = $value;
                                    $matchFound = true;
                                    break 3; // Rompe section, grupo y actividad
                                }
                            }
                        }
                    }
                }

                if (!$matchFound) {
                    // Si no hubo match, se conserva la clave original
                    $nueva_config->$key = $value;
                }
            } else {
                // Claves que no son de actividad, se copian tal cual
                $nueva_config->$key = $value;
            }
        }

        if (isset($config->order_data)) {
            $ordenes = json_decode($config->order_data, true);
            $nuevo_ordenes = [];

            foreach ($ordenes as $oldid => $info) {
                $matchFound = false;

                foreach ($objet_ph->sectionAndActi->sections as $section) {
                    if (!empty($section->activities)) {
                        foreach ($section->activities as $grupo) {
                            foreach ($grupo as $actividad) {
                                $actividad = (object) $actividad;
                                if (isset($actividad->id_como_p) && $actividad->id_como_p == (int)$oldid) {
                                    $nuevo_ordenes[$actividad->id_como] = $info;
                                    $matchFound = true;
                                    break 3;
                                }
                            }
                        }
                    }
                }

                if (!$matchFound) {
                    // Si no hay match, conserva el ID original
                    $nuevo_ordenes[$oldid] = $info;
                }
            }

            $nueva_config->order_data = json_encode($nuevo_ordenes);
        }
        // Serializar y guardar de nuevo en configdata
        $block_instance->configdata = base64_encode(serialize($nueva_config));
        $DB->update_record('block_instances', $block_instance);
    }


    /*
     * UpdateCourse en el nodo -> update_course_edit
     * Actualiza la informacion general del curso en el hijo, con la información del padre
     * @params {int} $id
     * @params {array} $padre
     */
    private function update_course_edit($val, $id_course)
    {
        global $DB;
        $val['id'] = $id_course;
        if ($DB->update_record('course', $val)) {
            $format = $DB->get_record('course_format_options', array('courseid' => $id_course, 'name' => 'numsections'));
            if (array_key_exists('numsections', $val) && !empty($format)) {
                $DB->update_record('course_format_options', array('id' => $format->id, 'value' => $val['numsections']));
            } else if (array_key_exists('numsections', $val) && empty($format)) {
                if ($id = $DB->insert_record('course_format_options', array('courseid' => $id_course, 'name' => 'numsections', 'format' => $val['format'], 'value' => $val['numsections']))) {
                    return true;
                }
            }
        }

        return true;
    }
    /*
     * Recibe la información grade_items del curso para actualizarlos en el nodo
     * Guarda la informacion de los itemes del librto de calificaciones
     * @params {obj} $ob_import_p -> Objeto con la información del padre
     * @params {obj} $ob_import_r -> objeto con los itmes del hijo y el padre
     * @params {int} $id_course_h -> id del curso en el hijo
     * return {};
     */
    private function update_quiz_slots($slots, $obj_import)
    {
        global $DB, $CFG;
        $obj = new self();
        $banco_pregu = $obj_import->bancoPregu->question;
        $banco_pregu = (array)$banco_pregu;
        require_once($CFG->dirroot . '/mod/quiz/locallib.php');

        $insert = clone $slots;

        $idquiz = 0;
        $idSlot = 0;
        foreach ($insert as $llave => $valor) {
            $idquiz = $valor->quizid;
            $idSlot = $valor->id;
        }
        for ($j = 0; $j < count($banco_pregu); $j++) {
            /* if ($insert->questionid == $banco_pregu[$j]->p) { */
            /* $insert->questionid = $banco_pregu[$j]->h; */
            $obj_act_p = new stdClass();
            $obj_act_p->quiz = new stdClass();
            $obj_act_p->quiz->id = $idquiz;

            foreach ($obj_import as $ke => $va) {
                $respuesta = $obj->cant_sections($va->sections, $obj_act_p);

                if (!empty($respuesta) /* && $key */ && property_exists($respuesta->actie, 'datos')) {

                    $id = $idSlot;

                    $section = $respuesta->numSection;
                    $posit = $respuesta->actie->position;
                    $insert->quizid = $va->sections[$section]->activities[0][$posit]->id_acti;

                    if ($respuesta->actie->datos->info_actividad->quiz_slots->$id) {
                        $insert->id = $respuesta->actie->datos->info_actividad->quiz_slots->$id->h;
                        if (!$DB->update_record('quiz_slots', $insert)) {
                            $respuesta->actie->datos->info_actividad->quiz_slots->$id->h = $DB->insert_record('quiz_slots', $insert);
                        }
                    } else {

                        foreach ($insert as $llave => $valor) {
                            $idQS = $valor->id;

                            $valor->quizid = $DB->get_record('quiz', array('course'));
                            $DB->insert_record('quiz_slots', $valor);
                        }
                    }

                    return 1;
                }
            }
        }
    }

    private function update_slot_banck($slots, $obj_import)
    {
        global $DB, $CFG;
        $obj = new self();
        require_once($CFG->dirroot . '/mod/quiz/locallib.php');
        $banco_pregu = $obj_import->bancoPregu;

        $insert = clone $slots;
        for ($j = 0; $j < count($obj_import->bancoPregu->question); $j++) {
            if ($slots->questionid == $obj_import->bancoPregu->question[$j]->p) {
                $insert->questionid = $obj_import->bancoPregu->question[$j]->h;
            }
        }
        $obj_act_p = new stdClass();
        $obj_act_p->quiz = new stdClass();
        $obj_act_p->quiz->id = $slots->quizid;
        if (is_array($obj_import) || is_object($obj_import)) {
            foreach ($obj_import as $ke => $va) {
                if (property_exists($va, 'sections')) {
                    $respuesta = $obj->cant_sections($va->sections, $obj_act_p);
                    if (!empty($respuesta) && property_exists($respuesta, 'actie') && property_exists($respuesta->actie, 'datos')) {
                        $id = $slots->id;
                        $section = $respuesta->numSection;
                        $posit = $respuesta->actie->position;
                        $insert->quizid = $va->sections[$section]->activities[0][$posit]->id_acti;
                        if ($respuesta->actie->datos->info_actividad->quiz_slots->$id) {
                            $slotsid = $respuesta->actie->datos->info_actividad->quiz_slots->$id->h;
                            $pos3 = array_search($insert->questioncategoryid, array_column($banco_pregu->question_categories, 'p'));
                            if (is_int($pos3)) {
                                $insert->questioncategoryid = $banco_pregu->question_categories[$pos3]->h;
                            } else {
                                $questioncategoryid = $respuesta->actie->datos->info_actividad->question_categories;
                                foreach ($questioncategoryid as $key => $value) {
                                    if ($slots->questioncategoryid == $value->p) {
                                        $insert->questioncategoryid = $value->h;
                                    }
                                }
                            }
                            $insert->category = $insert->questioncategoryid;
                            $insert->includesubcategories = $insert->includingsubcategories;
                            if ($insert->questionid != $slots->questionid) { //actualizar las preguntas
                                $question = $DB->get_record('question', array('id' => $insert->questionid));
                                $qtypeobj = question_bank::get_qtype('random');
                                $question = $qtypeobj->save_question($question, $insert);
                            }

                            if ($insert->questionid != $slots->questionid) {
                                $insert->id = $slotsid;
                                if (!$DB->update_record('quiz_slots', $insert)) {
                                    $insert->id = $DB->insert_record('quiz_slots', $insert);
                                }
                            } else {
                                $question = $respuesta->actie->datos->info_actividad->question;
                                foreach ($question as $key => $value) {
                                    if ($slots->questionid == $value->p) {
                                        $insert->questionid = $value->h;
                                    }
                                }
                                if ($insert->questionid != $slots->questionid && $insert->questioncategoryid != $slots->questioncategoryid) {
                                    $question = $DB->get_record('question', array('id' => $insert->questionid));
                                    $qtypeobj = question_bank::get_qtype('random');
                                    $question = $qtypeobj->save_question($question, $insert); //actualizar las preguntas
                                    $insert->id = $slotsid;
                                    if (!$DB->update_record('quiz_slots', $insert)) {
                                        $insert->id = $DB->insert_record('quiz_slots', $insert);
                                    }
                                }
                            }
                            question_bank::notify_question_edited($insert->questionid);
                        }
                    }
                }
            }
        }
    }

    /*
     * Recibe la información grade_items del curso para actualizarlos en el nodo
     * Guarda la informacion de los itemes del librto de calificaciones
     * @params {obj} $ob_import_p -> Objeto con la información del padre
     * @params {obj} $ob_import_r -> objeto con los itmes del hijo y el padre
     * @params {int} $id_course_h -> id del curso en el hijo
     * return {};
     */
    private function updateGrade_itemsCourse($ob_import_p, $ob_import_r, $courseId)
    {
        global $DB, $CFG;
        require_once($CFG->dirroot . "/cache/classes/helper.php");
        $obj = new self();
        /* rebuild_course_cache($courseId); */
        $obj_p = clone $ob_import_p;
        $ob_import_rel = $ob_import_r->groupings_groups->grade_items;
        $cat_p = $ob_import_r->cat_p;
        $cat_h = $ob_import_r->cat_h;

        $rel = $DB->get_record('bc_rel_padre_hijo', array('courseid_sh' => $courseId));

        $ob_ph = json_decode($rel->objet_ph);

        $grade_items = $ob_ph->demas_info->groupings_groups->grade_items;

        $cont = count($grade_items);

        if (property_exists($obj_p, 'delete')) {

            foreach ($obj_p as $key => $value) {
                //Si está eliminando la categoría
                if (strpos($value, "C") !== false) {
                    $newVal = preg_replace("/[^0-9]/", "", $value);
                    for ($i = 0; $i < count($cat_p); $i++) {
                        if ($newVal == $cat_p[$i]) {
                            $idCatH =  $cat_h[$i];
                            $DB->delete_records('grade_categories', ['id' => $idCatH]);
                            $re = $DB->get_record('grade_items', ['iteminstance' => $idCatH]);
                            $DB->delete_records('grade_items', ['id' => $re->id]);
                            unset($ob_ph->demas_info->cat_p[$i]);
                            unset($ob_ph->demas_info->cat_h[$i]);

                            foreach ($ob_ph->demas_info->groupings_groups->grade_items as $kgi => $vgi) {
                                if ($vgi->h == $re->id) {
                                    unset($ob_ph->demas_info->groupings_groups->grade_items[$kgi]);
                                }
                            }

                            $rel->objet_ph = json_encode($ob_ph);

                            $DB->update_record('bc_rel_padre_hijo', $rel);
                        }
                    }
                } else {

                    for ($i = 0; $i < count($ob_import_rel); $i++) {
                        if ($ob_import_rel[$i]->p == $value) {
                            $DB->delete_records('grade_items', ['id' => $ob_import_rel[$i]->h]);
                            unset($ob_ph->demas_info->groupings_groups->grade_items[$i]);
                            $rel->objet_ph = json_encode($ob_ph);
                            $DB->update_record('bc_rel_padre_hijo', $rel);
                        }
                    }
                }
            }
        } else {

            //ACTUALIZACIÓN 
            foreach ($obj_p as $key => $value) {
                for ($i = 0; $i < count($ob_import_rel); $i++) {
                    if ($key == $ob_import_rel[$i]->p) {
                        $value->id = $ob_import_rel[$i]->h;
                        $position = array_search($value->categoryid, $cat_p);
                        if (is_int($position)) {
                            $value->categoryid = $cat_h[$position];
                            if ($DB->update_record('grade_items', $value)) {
                                unset($obj_p->$key);
                            }
                        } else if (empty($value->categoryid)) {
                            $value->categoryid = null;
                            if ($DB->update_record('grade_items', $value)) {
                                unset($obj_p->$key);
                            }
                        }
                    }
                }
            }
            //CREACIÓN
            foreach ($obj_p as $key => $value) {
                $position = array_search($value->categoryid, $cat_p);
                if (is_int($position)) {
                    $value->courseid = $courseId;
                    $value->categoryid = $cat_h[$position];
                    if ($id = $DB->insert_record('grade_items', $value)) {
                        $grade_items[$cont]["p"] = $value->id;
                        $grade_items[$cont]["h"] = $id;
                        $ob_ph->demas_info->groupings_groups->grade_items = $grade_items;
                        $rel->objet_ph = json_encode($ob_ph);
                        $DB->update_record('bc_rel_padre_hijo', $rel);
                        unset($obj_p->$key);
                    }
                } else if (empty($value->categoryid)) {
                    $value->courseid = $courseId;
                    $value->categoryid = null;
                    $cat = end($cat_h);
                    $value->iteminstance =  $cat;

                    if ($id = $DB->insert_record('grade_items', $value)) {
                        $grade_items[$cont]["p"] = $value->id;
                        $grade_items[$cont]["h"] = $id;
                        $ob_ph->demas_info->groupings_groups->grade_items = $grade_items;
                        $rel->objet_ph = json_encode($ob_ph);
                        $DB->update_record('bc_rel_padre_hijo', $rel);
                        unset($obj_p->$key);
                    }
                }
            }
        }

        rebuild_course_cache($courseId);
    }

    /*
     * Recibe la información grade_categories del curso para actualizarlos en el nodo
     * Guarda la informacion de los categoriasdel libro de calificaciones
     * @params {obj} $ob_import_p -> Objeto con la información del padre
     * @params {obj} $ob_import_r -> objeto con las categorias del hijo y el padre
     * @params {int} $id_course_h -> id del curso en el hijo
     * return {};
     */
    private function updateGrade_categoriesCourse($ob_import_p, $ob_import_r, $id_course_h)
    {
        global $DB, $CFG;
        $obj = new self();

        $rel = $DB->get_record('bc_rel_padre_hijo', array('courseid_sh' => $id_course_h));
        $ob_ph = json_decode($rel->objet_ph);

        $obj_p = clone $ob_import_p;
        $cat_p = $ob_import_r->cat_p;
        $cat_h = $ob_import_r->cat_h;
        foreach ($obj_p as $key => $value) {
            $position = array_search($key, $cat_p);
            if (is_int($position)) {
                $value->id = $cat_h[$position];
                $value->courseid = $id_course_h;
                $path = explode('/', $value->path);
                for ($j = 0; $j < count($path); $j++) {
                    $position2 = array_search($path[$j], $cat_p);
                    if (is_int($position2)) {
                        $path[$j] = $cat_h[$position2];
                    }
                }
                $value->path = implode("/", $path);
                $position_path = array_search($value->parent, $cat_p);
                if (is_int($position_path)) {
                    $value->parent = $cat_h[$position_path];
                }
                if ($DB->update_record('grade_categories', $value)) {
                    unset($obj_p->$key);
                    //unset($cat_p[$position],$cat_h[$position]);
                }
            }
        }

        if (count(get_object_vars($obj_p)) > 0) {
            foreach ($obj_p as $key => $value) {
                $path = explode('/', $value->path);
                for ($j = 0; $j < count($path); $j++) {
                    if ($path[$j] == $key) {
                        $value->path = '//////////';
                        $id_cate = $DB->insert_record('grade_categories', $value);
                        $path[$j] = $id_cate;
                        $value->id = $id_cate;
                        $len_p = count($ob_import_r->cat_p);
                        $len_h = count($ob_import_r->cat_h);
                        $len_grade = count($ob_import_r->groupings_groups->grade_items);
                        //$ob_import_r->groupings_groups->grade_items[$len_grade]->p;
                        //$ob_import_r->groupings_groups->grade_items[$len_grade]->h = ;
                        $ob_import_r->cat_h[$len_h] = $id_cate;
                        $ob_import_r->cat_p[$len_p] = $key;

                        $ob_ph->demas_info->cat_p = $ob_import_r->cat_p;
                        $ob_ph->demas_info->cat_h = $ob_import_r->cat_h;
                        $rel->objet_ph = json_encode($ob_ph);
                        $DB->update_record('bc_rel_padre_hijo', $rel);
                    } else {
                        $position = array_search($path[$j], $cat_p);
                        if (is_int($position)) {
                            $path[$j] = $cat_h[$position];
                        }
                    }
                }
                $position_path = array_search($value->parent, $cat_p);
                if (is_int($position_path)) {
                    $value->parent = $cat_h[$position_path];
                }
                $value->courseid = $id_course_h;
                $value->path = implode("/", $path);
                $DB->update_record('grade_categories', $value);
            }
        }
        return 1;
    }


    /*
     * Recibe la información los grupos del curso para actualizarlos en el nodo
     * Guarda la informacion de los grupos en el curso
     * @params {obj} $ob_import_p -> Objeto con la información del padre
     * @params {obj} $ob_import_r -> objeto con los grupos del hijo y el padre
     * @params {int} $id_course_h -> id del curso en el hijo
     * return {};
     */
    private function updateGroup_Course($ob_import_p, $ob_import_r, $id_course_h)
    {
        global $DB, $CFG;
        $obj = new self();

        $obj_p = clone $ob_import_p;
        $groups_p = $ob_import_r->groups_p;
        $groups_h = $ob_import_r->groups_h;

        foreach ($obj_p as $key => $value) {
            $position = array_search($key, $groups_p);
            if (is_int($position)) {
                $value->id = $groups_h[$position];
                $value->courseid = $id_course_h;
                if (empty($value->description)) {
                    $value->description = null;
                }
                if (empty($value->enrolmentkey)) {
                    $value->enrolmentkey = null;
                }

                if ($DB->update_record('groups', $value)) {
                    unset($obj_p->$key);
                    //unset($groups_p[$position],$groups_h[$position]);
                }
            }
        }

        if (count(get_object_vars($obj_p)) > 0) {
            foreach ($obj_p as $key => $value) {
                $value->courseid = $id_course_h;
                if (empty($value->description)) {
                    $value->description = null;
                }
                if (empty($value->enrolmentkey)) {
                    $value->enrolmentkey = null;
                }
                $len_p = count($ob_import_r->groups_p);
                $len_h = count($ob_import_r->groups_h);

                if ($id_gru = $DB->insert_record('groups', $value)) {
                    $ob_import_r->groups_h[$len_h] = $id_gru;
                    $ob_import_r->groups_p[$len_p] = $key;
                    unset($obj_p->$key);
                    //unset($groups_p[$position],$groups_h[$position]);
                }
            }
        }
        return 1;
    }

    /*
     * Recibe la información las agrupaciones del curso para actualizarlos en el nodo
     * Guarda la informacion de las agrupaciones en el curso
     * @params {obj} $ob_import_p -> Objeto con la información del padre
     * @params {obj} $ob_import_r -> objeto con las agrupaciones del hijo y el padre
     * @params {int} $id_course_h -> id del curso en el hijo
     * return {};
     */
    private function updateGroupings_Course($ob_import_p, $ob_import_r, $id_course_h)
    {
        global $DB, $CFG;
        $obj = new self();
        $obj_p = clone $ob_import_p;

        if (property_exists($ob_import_r, 'groupings_p') && property_exists($ob_import_r, 'groupings_h')) {
            $groupings_p = $ob_import_r->groupings_p;
            $groupings_h = $ob_import_r->groupings_h;
            foreach ($obj_p as $key => $value) {
                $position = array_search($key, $groupings_p);
                if (is_int($position)) {
                    $value->id = $groupings_h[$position];
                    $value->courseid = $id_course_h;
                    if (empty($value->description)) {
                        $value->description = null;
                    }
                    if (empty($value->enrolmentkey)) {
                        $value->enrolmentkey = null;
                    }
                    if ($DB->update_record('groupings', $value)) {
                        unset($obj_p->$key);
                    }
                }
            }
        }


        if (count(get_object_vars($obj_p)) > 0) {
            foreach ($obj_p as $key => $value) {
                $value->courseid = $id_course_h;
                if (empty($value->description)) {
                    $value->description = null;
                }
                if (empty($value->enrolmentkey)) {
                    $value->enrolmentkey = null;
                }
                $len_p = property_exists($ob_import_r, 'groupings_p') ? count($ob_import_r->groupings_p) : 0;
                $len_h = property_exists($ob_import_r, 'groupings_h') ? count($ob_import_r->groupings_h) : 0;

                if ($id_gru = $DB->insert_record('groupings', $value)) {
                    $ob_import_r->groupings_h[$len_h] = $id_gru;
                    $ob_import_r->groupings_p[$len_p] = $key;
                    unset($obj_p->$key);
                }
            }
        }
        return 1;
    }



    /*
     * Recibe la información los Groupings_groups del curso para actualizarlos en el nodo
     * Guarda la informacion de los Groupings_groups en el curso
     * @params {obj} $ob_import_p -> Objeto con la información del padre
     * @params {obj} $ob_import_r -> objeto con los Groupings_groups del hijo y el padre
     * @params {int} $id_course_h -> id del curso en el hijo
     * return {};
     */
    private function updateGroupings_groups_Course($ob_import_p, $ob_import_r, $id_course_h)
    {
        global $DB, $CFG;
        $obj = new self();
        $obj_p = clone $ob_import_p;
        $groups_p = $ob_import_r->groups_p;
        $groups_h = $ob_import_r->groups_h;
        $groupings_p = $ob_import_r->groupings_p;
        $groupings_h = $ob_import_r->groupings_h;
        $groupings_groups = $ob_import_r->groupings_groups;
        if (property_exists($groupings_groups, 'groupings_groups')) {
            foreach ($obj_p as $key => $value) {
                foreach ($groupings_groups->groupings_groups as $k => $v) {
                    if ($v->p == $key) {
                        unset($obj_p->$key);
                    }
                }
            }
        }
        if (count(get_object_vars($obj_p)) > 0) {
            foreach ($obj_p as $key => $value) {
                $position = array_search($value->groupingid, $groupings_p);
                if (is_int($position)) {
                    $value->groupingid = $groupings_h[$position];
                    $pos2 = array_search($value->groupid, $groups_p);
                    if (is_int($pos2)) {
                        $value->groupid = $groups_h[$pos2];
                        $value->courseid = $id_course_h;

                        if ($idgr = $DB->insert_record('groupings_groups', $value)) {
                            if (property_exists($obj_p, $key)) {
                                unset($obj_p->$key);
                            }
                            if (!property_exists($groupings_groups, 'groupings_groups')) {
                                $groupings_groups = new stdClass();
                            }
                            $len = count($groupings_groups->groupings_groups);
                            if ($len == 0) {
                                $groupings_groups->groupings_groups = array();
                            }
                            $groupings_groups->groupings_groups[$len] = new stdClass();
                            $groupings_groups->groupings_groups[$len]->p = $value->id;
                            $groupings_groups->groupings_groups[$len]->h = $idgr;
                        }
                    }
                }
            }
        }
        return 1;
    }

    /*
     * Recibe la información los Question_categories del curso para actualizarlos en el nodo
     * Guarda la informacion de los Question_categories en el curso
     * @params {obj} $ob_import_p -> Objeto con la información del padre
     * @params {obj} $ob_import_r -> objeto con los Question_categories del hijo y el padre
     * @params {int} $id_course_h -> id del curso en el hijo
     * return {};
     */
    private function updateQuestion_categories_Course($ob_import_p, $ob_import_r, $id_course_h)
    {
        global $DB, $CFG;
        $obj = new self();
        $obj_p = clone $ob_import_p;
        $bancPregu = $ob_import_r->bancoPregu;

        $question_categories = property_exists($bancPregu, 'question_categories') ? $bancPregu->question_categories : '';

        if (property_exists($bancPregu, 'question_categories')) {

            foreach ($obj_p as $key => $value) {

                for ($i = 0; $i < count($question_categories); $i++) {

                    if ($question_categories[$i]->p == $key) {

                        $value->id = $question_categories[$i]->h;
                        unset($value->contextid);
                        if ($value->parent != 0) {
                            for ($h = 0; $h < count($question_categories); $h++) {
                                if ($value->parent == $question_categories[$h]->p) {
                                    $value->parent = $question_categories[$h]->h;
                                }
                            }
                        }

                        unset($value->stamp);

                        if ($DB->update_record('question_categories', $value)) {

                            unset($obj_p->$key);
                        }
                    }
                }
            }
        }

        if (count(get_object_vars($obj_p)) > 0) {
            $contex = $DB->get_record('context', array('contextlevel' => 50, 'instanceid' => $id_course_h));
            $newParent = 0;
            if (!empty($contex)) {
                $idCont = ($contex);
                $pos = count($ob_import_r->bancoPregu->question_categories);
                foreach ($obj_p as $key => $value) {

                    $value->contextid = $idCont->id;

                    if ($value->parent == 0) {
                        $newParent = $DB->insert_record('question_categories', $value);
                        $ob_import_r->bancoPregu->question_categories[$pos]["p"] = $value->id;
                        $ob_import_r->bancoPregu->question_categories[$pos]["h"] = $newParent;

                        $relPH = $DB->get_record('bc_rel_padre_hijo', array('courseid_sh' => $id_course_h));
                        $ob_ph = json_decode($relPH->objet_ph);

                        $ob_ph->bancoPregu->question_categories = $ob_import_r->bancoPregu->question_categories;

                        $relPH->objet_ph = json_encode($ob_ph);

                        $DB->update_record('bc_rel_padre_hijo', $relPH);

                        $pos++;
                    }

                    if ($value->parent != 0) {
                        for ($h = 0; $h < count($question_categories); $h++) {
                            if ($value->parent == $question_categories[$h]->p) {
                                $value->parent = $question_categories[$h]->h;
                            }
                        }
                    }

                    unset($value->id);

                    if ($idcat = $DB->insert_record('question_categories', $value)) {

                        $len = count($ob_import_r->bancoPregu->question_categories);
                        $ob_import_r->bancoPregu->question_categories[$len] = new stdClass();
                        $ob_import_r->bancoPregu->question_categories[$len]->p = $key;
                        $ob_import_r->bancoPregu->question_categories[$len]->h = $idcat;

                        $relPH = $DB->get_record('bc_rel_padre_hijo', array('courseid_sh' => $id_course_h));

                        $ob_ph = json_decode($relPH->objet_ph);

                        $ob_ph->bancoPregu->question_categories = $ob_import_r->bancoPregu->question_categories;

                        $relPH->objet_ph = json_encode($ob_ph);

                        $DB->update_record('bc_rel_padre_hijo', $relPH);

                        unset($obj_p->$key);
                    }
                }

                foreach ($obj_p as $key => $value) {

                    $value->contextid = $idCont->id;

                    if ($value->parent != 0) {
                        $value->parent = $newParent;
                        $idCat = $DB->insert_record('question_categories', $value);
                        $ob_import_r->bancoPregu->question_categories[$pos]["p"] = $value->id;
                        $ob_import_r->bancoPregu->question_categories[$pos]["h"] = $idCat;

                        $relPH = $DB->get_record('bc_rel_padre_hijo', array('courseid_sh' => $id_course_h));
                        $ob_ph = json_decode($relPH->objet_ph);

                        $ob_ph->bancoPregu->question_categories = $ob_import_r->bancoPregu->question_categories;

                        $relPH->objet_ph = json_encode($ob_ph);

                        $DB->update_record('bc_rel_padre_hijo', $relPH);

                        $pos++;
                    }
                }
            }
        }

        return 1;
    }

    /*
     * Recibe la información los Question_categories del curso para actualizarlos en el nodo
     * Guarda la informacion de los Question_categories en el curso
     * @params {obj} $ob_import_p -> Objeto con la información del padre
     * @params {obj} $bancoPregu -> objeto con los Question_categories del hijo y el padre
     * @params {int} $id_course_h -> id del curso en el hijo
     * return {};
     */
    private function updateQuestion_Course($ob_import_p, $bancoPregu, $id_course_h, $sectionAndActi, $quiz =  null)
    {
        global $DB, $CFG;

        $idCursoPadre = $DB->get_record('bc_rel_padre_hijo', array('courseid_sh' => $id_course_h));

        $relPH = $DB->get_records('bc_rel_padre_hijo', array('courseid_sp' => $idCursoPadre->courseid_sp));

        $obj = new self();

        $quiz = empty($quiz) ? new stdClass() : $quiz;

        $idquiz = array();

        $newCont = 0;

        foreach ($sectionAndActi as $keyQ => $valQ) {

            foreach ($valQ as $kValQ => $valKeyQ) {

                if (is_array($valKeyQ)) {

                    foreach ($valKeyQ as $kValKey => $valVKey) {

                        for ($i = 0; $i < count($valVKey); $i++) {

                            if ($valVKey[$i]->table == 'quiz') {
                                $idquiz[$newCont]['id_acti_p'] = $valVKey[$i]->id_acti_p;
                                $idquiz[$newCont]['id_acti'] = $valVKey[$i]->id_acti;
                                $newCont++;
                            }
                        }
                    }
                }
            }
        }

        $obj_p = (object) $ob_import_p;
        $obj_p = clone $obj_p;

        require_once($CFG->dirroot . '/question/engine/bank.php');
        if (property_exists($bancoPregu, 'question_multianswer')) {
            $question_multianswer = $bancoPregu->question_multianswer;
        }

        $idques = 0;

        $len = 0;
        $idBank = 0;
        $idSlot = 0;
        $idInsertSlot = 0;
        $idQuizActual  = 0;
        $categoriaId = 0;
        $idBankEntries = 0;

        $newImport = (object) $ob_import_p;
        $newImport = clone $ob_import_p;

        foreach ($ob_import_p as $k => $v) {

            if (!empty($v)) {

                if ($k == 'idBankEntries') {
                    $idBankEntries = $v->id;
                }

                if ($k == 'question') { // id, category

                    foreach ($relPH as $krph => $vrph) {

                        if ($vrph->courseid_sh == $id_course_h) {

                            $idques = $DB->insert_record('question', $v);

                            $axi_bancoPregu = (object) $bancoPregu->question;

                            $axi_bancoPregu = clone $axi_bancoPregu;

                            $axi_bancoPregu = (array)$axi_bancoPregu;

                            $len = count($axi_bancoPregu);

                            $bancoPregu->question[$len]->p = $v->id;

                            $bancoPregu->question[$len]->h = $idques;

                            $vrph->objet_ph = json_decode($vrph->objet_ph);
                            $vrph->objet_ph->bancoPregu->question = $bancoPregu->question;
                            $vrph->objet_ph = json_encode($vrph->objet_ph);

                            $DB->update_record('bc_rel_padre_hijo',  $vrph);
                        }
                    }
                } else if ($k == 'question_categories') {

                    $contex = $DB->get_record('context', array('contextlevel' => 50, 'instanceid' => $id_course_h));

                    $categories = $DB->get_records_sql("SELECT id, name FROM {question_categories} WHERE contextid = $contex->id AND parent != 0 ");

                    $id_categories = "";

                    foreach ($categories as $kcat => $valcat) {

                        if ($valcat->name == $v->name) {

                            $id_categories = $valcat->id;
                        }
                    }

                    $axi_bancoPregu = (array) $bancoPregu->question_categories;

                    $len = count($axi_bancoPregu);

                    $relPH = $DB->get_records('bc_rel_padre_hijo', array('courseid_sp' => $idCursoPadre->courseid_sp));

                    if (empty($categories)) {

                        foreach ($relPH as $krph2 => $vrph2) {

                            if ($vrph2->courseid_sh == $id_course_h) {

                                foreach ($v as $kv => $vk) {

                                    $vk->contextid = $contex->id;

                                    if ($vk->parent == 0) {

                                        $categoriaId = $DB->insert_record('question_categories', $vk);

                                        $bancoPregu->question_categories[$len]->p = $vk->id;

                                        $bancoPregu->question_categories[$len]->h = $categoriaId;

                                        $vrph2->objet_ph = json_decode($vrph2->objet_ph);
                                        $vrph2->objet_ph->bancoPregu->question_categories = $bancoPregu->question_categories;
                                        $vrph2->objet_ph = json_encode($vrph2->objet_ph);

                                        $DB->update_record('bc_rel_padre_hijo',  $vrph2);
                                    }
                                }

                                foreach ($v as $kv => $vk) {

                                    $vk->contextid = $contex->id;

                                    if ($vk->parent != 0) {

                                        $vk->parent = $categoriaId;

                                        $categoriaId = $DB->insert_record('question_categories', $vk);

                                        $bancoPregu->question_categories[$len + 1]->p = $vk->id;

                                        $bancoPregu->question_categories[$len + 1]->h = $categoriaId;

                                        $vrph2->objet_ph = json_decode($vrph2->objet_ph);
                                        $vrph2->objet_ph->bancoPregu->question_categories = $bancoPregu->question_categories;
                                        $vrph2->objet_ph = json_encode($vrph2->objet_ph);

                                        $DB->update_record('bc_rel_padre_hijo',  $vrph2);
                                    }
                                }
                            }
                        }
                    } else {

                        $categoriaId = /* $categories->id */ $id_categories;
                    }
                } else if ($k == 'question_bank_entries') {

                    //poner vacio $V
                    if (count(get_object_vars($v)) != 0) {

                        $axi_bancoPregu = (array) $bancoPregu->question_bank_entries;

                        $len = count($axi_bancoPregu);

                        $relPH = $DB->get_records('bc_rel_padre_hijo', array('courseid_sp' => $idCursoPadre->courseid_sp));

                        foreach ($relPH as $krph3 => $vrph3) {

                            if ($vrph3->courseid_sh == $id_course_h) {

                                $v->questioncategoryid = $categoriaId;

                                $objeto_rel = json_decode($vrph3->objet_ph);

                                $question_bank_entries = $objeto_rel->bancoPregu->question_bank_entries;

                                $idBank = 0;

                                foreach ($question_bank_entries as $key_idbank => $val_idbank) {

                                    if ($val_idbank->p == $idBankEntries) {
                                        $idBank = $val_idbank->h;
                                    }
                                }

                                if ($idBank == 0) {

                                    $idBank = $DB->insert_record('question_bank_entries', $v);

                                    $bancoPregu->question_bank_entries[$len]->p = $v->id;

                                    $bancoPregu->question_bank_entries[$len]->h = $idBank;

                                    $vrph3->objet_ph = json_decode($vrph3->objet_ph);

                                    $vrph3->objet_ph->bancoPregu->question_bank_entries = $bancoPregu->question_bank_entries;

                                    $vrph3->objet_ph = json_encode($vrph3->objet_ph);

                                    $DB->update_record('bc_rel_padre_hijo',  $vrph3);
                                }
                            }
                        }
                    }
                } else if ($k == 'question_versions') {

                    if (!empty($v)) {

                        $axi_bancoPregu = (array) $bancoPregu->question_versions;

                        $len = count($axi_bancoPregu);

                        $relPH = $DB->get_records('bc_rel_padre_hijo', array('courseid_sp' => $idCursoPadre->courseid_sp));

                        foreach ($relPH as $krph4 => $vrph4) {
                            if ($vrph4->courseid_sh == $id_course_h) {
                                $v->questionid = $idques;
                                $v->questionbankentryid = $idBank;

                                $idvers = $DB->insert_record('question_versions', $v);

                                $axi_bancoPregu[$len]->p = $v->id;
                                $axi_bancoPregu[$len]->h = $idvers;
                                $vrph4->objet_ph = json_decode($vrph4->objet_ph);
                                $vrph4->objet_ph->bancoPregu->question_versions = $axi_bancoPregu;
                                $vrph4->objet_ph = json_encode($vrph4->objet_ph);
                                $DB->update_record('bc_rel_padre_hijo',  $vrph4);
                            }
                        }
                    }
                } else if ($k == 'quiz_slots') {

                    if (!empty($v)) {

                        for ($i = 0; $i < count($idquiz); $i++) {
                            if ($v->quizid == $idquiz[$i]['id_acti_p']) {
                                $v->quizid = $idquiz[$i]['id_acti'];

                                $slotsQuizHijos = $DB->get_record_sql("SELECT slot FROM {quiz_slots} WHERE quizid =  $v->quizid ORDER BY slot DESC LIMIT 1 ");

                                if (empty($slotsQuizHijos)) {
                                    $v->slot = 1;
                                } else {
                                    $v->slot = $slotsQuizHijos->slot + 1;
                                }

                                $idQuizActual = $v->quizid;
                                $idInsertSlot = $DB->insert_record('quiz_slots', $v);
                            }
                        }
                    }
                } else if ($k == 'question_references') {

                    if (!empty($v)) {

                        $courseModules = $DB->get_record_sql("SELECT id FROM {course_modules} WHERE instance = $idQuizActual");

                        $contex = $DB->get_record_sql("SELECT id FROM {context} WHERE contextlevel = 70 AND instanceid = $courseModules->id ORDER BY id DESC LIMIT 1");

                        $v->usingcontextid = $contex->id;
                        $v->itemid = $idInsertSlot;
                        $v->questionbankentryid = $idBank;
                        $DB->insert_record('question_references', $v);
                    }
                } else {

                    foreach ($v as $keye => $value) {
                        if (!empty($value)) {
                            if ($k == 'question_answers') { // id, question
                                $value->question = $idques;
                                if ($idans = $DB->insert_record($k, $value)) {
                                    unset($obj_p->$k->$keye);
                                    $pos = $value->id;
                                    $bancoPregu->question_answers->$pos = new stdClass();
                                    $bancoPregu->question_answers->$pos->p = $value->id;
                                    $bancoPregu->question_answers->$pos->h = $idans;
                                }
                            } else if ($k == 'question_truefalse') { //id , question, trueanswer, falseanswer

                                $value->question = $idques;
                                if ($true = $obj->foreachID($bancoPregu->question_answers, $value->trueanswer)) {
                                    $value->trueanswer = $true;
                                } else if ($true = $obj->sectionBanck($sectionAndActi, 'question_answers', $value->trueanswer)) {
                                    $value->trueanswer = $true;
                                }
                                if ($fal = $obj->foreachID($bancoPregu->question_answers, $value->falseanswer)) {
                                    $value->falseanswer = $fal;
                                } else if ($fal = $obj->sectionBanck($sectionAndActi, 'question_answers', $value->falseanswer)) {
                                    $value->falseanswer = $fal;
                                }
                                if ($id = $obj->foreachID($bancoPregu->question_truefalse, $value->id)) {
                                    $value->id = $id;
                                    if ($DB->update_record('question_truefalse', $value)) {
                                        unset($obj_p->$k->$keye);
                                    }
                                } else if ($id = $obj->sectionBanck($sectionAndActi, 'question_truefalse', $value->id)) {
                                    $value->id = $id;
                                    if ($DB->update_record('question_truefalse', $value)) {
                                        unset($obj_p->$k->$keye);
                                    }
                                } else {
                                    if ($idcat = $DB->insert_record($k, $value)) {
                                        unset($obj_p->$k->$keye);
                                        $pos = $value->id;
                                        $bancoPregu->question_truefalse->$pos = new stdClass();
                                        $bancoPregu->question_truefalse->$pos->p = $value->id;
                                        $bancoPregu->question_truefalse->$pos->h = $idcat;
                                    }
                                }
                            } else if ($k == 'question_multianswer') { // id, question, sequence
                                $value->question = $idques;
                                $parti = explode(',', $value->sequence);
                                $sequence = array();
                                for ($r = 0; $r < count($parti); $r++) {
                                    if ($que1 = $obj->forID($bancoPregu->question, $parti[$r])) {
                                        $sequence[] = $que1;
                                    } else if ($que1 = $obj->sectionBanck($sectionAndActi, 'question', $parti[$r])) {
                                        $sequence[] = $que1;
                                    }
                                }
                                $value->sequence = implode(",", $sequence);
                                if ($mul = $obj->foreachID($question_multianswer, $value->id)) {
                                    $value->id = $mul;
                                    if ($DB->update_record('question_multianswer', $value)) {
                                        unset($obj_p->$k->$keye);
                                    }
                                } else if ($mul = $obj->sectionBanck($sectionAndActi, 'question_multianswer', $value->id)) {
                                    $value->id = $mul;
                                    if ($DB->update_record('question_multianswer', $value)) {
                                        unset($obj_p->$k->$keye);
                                    }
                                } else {
                                    if ($idcat = $DB->insert_record($k, $value)) {
                                        unset($obj_p->$k->$keye);
                                        $pos = $value->id;
                                        $bancoPregu->question_multianswer->$pos = new stdClass();
                                        $bancoPregu->question_multianswer->$pos->p = $value->id;
                                        $bancoPregu->question_multianswer->$pos->h = $idcat;
                                    }
                                }
                            } else if (
                                $k == 'qtype_ddimageortext' || $k == 'qtype_ddimageortext_drags' || $k == 'qtype_ddimageortext_drops'
                                || $k == 'qtype_ddmarker' || $k == 'qtype_ddmarker_drags' || $k == 'qtype_ddmarker_drops' || $k == 'qtype_essay_options'
                                || $k == 'qtype_match_options' || $k == 'qtype_match_subquestions' || $k == 'qtype_multichoice_options'
                                || $k == 'qtype_randomsamatch_options' || $k == 'qtype_shortanswer_options'
                            ) {
                                $qtype = $bancoPregu->$k;
                                $value->questionid = $idques;
                                if ($mul = $obj->foreachID($qtype, $value->id)) {
                                    $value->id = $mul;
                                    if ($DB->update_record($k, $value)) {
                                        unset($obj_p->$k->$keye);
                                    }
                                } else if ($mul = $obj->sectionBanck($sectionAndActi, $k, $value->id)) {
                                    $value->id = $mul;
                                    if ($DB->update_record($k, $value)) {
                                        unset($obj_p->$k->$keye);
                                    }
                                } else {
                                    if ($idcat = $DB->insert_record($k, $value)) {
                                        unset($obj_p->$k->$keye);
                                        $pos = $value->id;
                                        $bancoPregu->$k->$pos = new stdClass();
                                        $bancoPregu->$k->$pos->p = $value->id;
                                        $bancoPregu->$k->$pos->h = $idcat;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return 1;
    }


    private function forID($obComparar, $value)
    {
        $obComparar = (array)$obComparar;
        $res = null;
        foreach ($obComparar as $key => $value1) {
            if (is_object($value1) && $value1->p == $value) {
                return $value1->h;
            }
        }
    }
    private function foreachID($obComparar, $value)
    {
        foreach ($obComparar as $key => $v) {
            if ($v->p == $value) {
                return $v->h;
            }
        }
    }


    private function sectionBanck($sections, $buscar, $id)
    {
        $obj = new self();
        foreach ($sections as $k => $value) {
            $obj_comparar = $obj->cant_activitiesBanck($value->activities[0], $buscar, $id);
            if (!empty($obj_comparar)) {
                return $obj_comparar;
            }
        }
    }

    private function cant_activitiesBanck($activities, $buscar, $id)
    {
        foreach ($activities as $k => $value) {
            if ($value->table == 'quiz' && property_exists($value, 'info_actividad')) {
                if (property_exists($value->info_actividad, $buscar)) {
                    foreach ($value->info_actividad->$buscar as $key => $v) {
                        if ($v->p == $id) {
                            return $v->h;
                        }
                    }
                }
            }
        }
    }


    private function sectionGame($sections, $id, $type)
    {
        $obj = new self();
        foreach ($sections as $k => $value) {
            $obj_comparar = $obj->cant_activitiesGame($value->activities[0], $id, $type);
            if (!empty($obj_comparar)) {
                return $obj_comparar;
            }
        }
    }

    private function cant_activitiesGame($activities, $id, $type)
    {
        foreach ($activities as $k => $value) {
            if ($value->table == $type && $value->id_acti_p == $id) {
                return $value->id_acti;
            }
        }
    }


    private function search_section($sections, $id)
    {
        $obj = new self();
        foreach ($sections as $k => $value) {
            $id_como = $obj->search_activity($value->activities[0], $id);
            if (!empty($id_como)) {
                return $id_como;
            }
        }
        return 0;
    }

    private function search_activity($activities, $id)
    {
        foreach ($activities as $k => $value) {
            if (property_exists($value, 'id_como_p') && $value->id_como_p == $id) {
                return $value->id_como;
            }
        }
        return 0;
    }


    private function scorm_add_instance_nueva($scorm, $id, $dir_sftp)
    {
        /*  $section, $fromform->instance, $to . $name_archive */
        global $CFG, $DB;
        $objCRE = new self;
        require_once($CFG->dirroot . '/mod/scorm/lib.php');
        require_once($CFG->dirroot . '/mod/scorm/locallib.php');
        require_once("$CFG->libdir/moodlelib.php");

        $cmid       = $scorm->coursemodule;
        $cmidnumber = $scorm->cmidnumber;
        $courseid   = $scorm->course;

        $context = context_module::instance($cmid);
        $scorm = scorm_option2text($scorm);

        $DB->set_field('course_modules', 'instance', $id, array('id' => $cmid));

        // Reload scorm instance.
        $record = $DB->get_record('scorm', array('id' => $id));

        // Extra fields required in grade related functions.
        $record->course     = $courseid;
        $record->cmidnumber = $cmidnumber;
        $record->cmid       = $cmid;

        $fs = get_file_storage();

        $name_arc = str_replace('.zip', '_' . $courseid . '.zip', $record->reference);

        $from_zip_file = $dir_sftp;

        $file_record = array(
            'contextid' => $context->id,
            'component' => 'mod_scorm',
            'filearea' => 'package',
            'itemid' => 0,
            'filepath' => '/',
            'filename' => $name_arc,
            'timecreated' => time(),
            'timemodified' => time()
        );

        $packagefile = $fs->create_file_from_pathname($file_record, $from_zip_file);

        $newhash = $packagefile->get_contenthash();
        $fs->delete_area_files($context->id, 'mod_scorm', 'content');

        $packer = get_file_packer('application/zip');
        $packagefile->extract_to_storage($packer, $context->id, 'mod_scorm', 'content', 0, '/');
        $record->revision++;
        $record->sha1hash = $newhash;

        return TRUE;
    }

    /* 
     * Crear actividad en nodo
     * 
     */
    private function create_activity_nodo($param)
    {
        global $DB, $CFG;
        $registro = (object)$param;

        $obj = new self();
        require_once($CFG->dirroot . '/course/modlib.php');

        $return = new stdClass();
        $return->cant = 0;
        $return->cursos_total = array();
        $return->cursos_act_propues = array();
        $return->cursos_actualizados = array();
        $fromform1 = json_decode($registro->obj_act_p);

        $modmoodleform = "$CFG->dirroot/mod/$fromform1->modulename/mod_form.php";

        if (file_exists($modmoodleform)) {
            require_once($modmoodleform);
        } else {
            print_error('noformdesc');
        }
        $coursemodule = $fromform1->coursemodule;
        /////////
        $instance = $fromform1->instance;
        /////////

        $gradecat = property_exists($fromform1, 'gradecat') ? $fromform1->gradecat : 0;
        $groupingid = property_exists($fromform1, 'groupingid') ? $fromform1->groupingid : 0;
        $teamsubmissiongroupingid = property_exists($fromform1, 'teamsubmissiongroupingid') ? $fromform1->teamsubmissiongroupingid : 0; //para assign
        $availability = json_decode($fromform1->availabilityconditionsjson);
        $module = $DB->get_record('modules', array('name' => $fromform1->modulename), '*', MUST_EXIST);
        $fromform1->module = $module->id;
        $id_curso_hijo = 0;
        $contFolder = 0;

        $cantScorm = 0;

        foreach ($registro->obj_act_h as $key => $value) {

            $fromform2 = clone $fromform1;
            $return->cursos_total[] = $value->courseid_sh;
            $course = $DB->get_record('course', array('id' => $value->courseid_sh), '*', MUST_EXIST);
            $fromform2->course = $value->courseid_sh;
            $fromform2->coursemodule = 0;
            $ob_import = json_decode($value->objet_ph);
            $id_curso_hijo = $ob_import->cursos->id_hijo;
            if (property_exists($ob_import->demas_info, 'cat_p')) { //categoria en el libro de calificaciones

                $position = array_search($gradecat, $ob_import->demas_info->cat_p);
                if (is_int($position)) {
                    $fromform2->gradecat = $ob_import->demas_info->cat_h[$position];
                }
            }
            if (property_exists($ob_import->demas_info, 'groupings_p')) { //id de agrupaciones

                $position1 = array_search($teamsubmissiongroupingid, $ob_import->demas_info->groupings_p);
                if (is_int($position1)) {
                    $fromform2->teamsubmissiongroupingid = $ob_import->demas_info->groupings_h[$position1];
                }
                $position = array_search($groupingid, $ob_import->demas_info->groupings_p);
                if (is_int($position)) {
                    $fromform2->groupingid = $ob_import->demas_info->groupings_h[$position];
                }
            }
            //restricciones de acceso
            if ($availability != '{"op":"&","c":[],"showc":[]}') {

                if (property_exists($availability, 'c')) {
                    for ($i = 0; $i < count($availability->c); $i++) {
                        if (property_exists($availability->c[$i], 'type') && $availability->c[$i]->type == 'grouping' && property_exists($ob_import->demas_info, 'groupings_p')) {
                            $position = array_search($availability->c[$i]->id, $ob_import->demas_info->groupings_p);
                            if (is_int($position)) {
                                $availability->c[$i]->id = (int)$ob_import->demas_info->groupings_h[$position];
                            }
                        }
                        if (property_exists($availability->c[$i], 'type') && $availability->c[$i]->type == 'group' && property_exists($ob_import->demas_info, 'groups_p')) {
                            $position = array_search($availability->c[$i]->id, $ob_import->demas_info->groups_p);
                            if (is_int($position)) {
                                $availability->c[$i]->id = (int)$ob_import->demas_info->groups_h[$position];
                            }
                        }
                        if (property_exists($availability->c[$i], 'type') && $availability->c[$i]->type == 'grade' && property_exists($ob_import->demas_info, 'groupings_groups') && property_exists($ob_import->demas_info->groupings_groups, 'grade_items')) {
                            for ($h = 0; $h < count($ob_import->demas_info->groupings_groups->grade_items); $h++) {
                                if ($availability->c[$i]->id == $ob_import->demas_info->groupings_groups->grade_items[$h]->p) {
                                    $availability->c[$i]->id = (int)$ob_import->demas_info->groupings_groups->grade_items[$h]->h;
                                }
                            }
                        }
                        if (property_exists($availability->c[$i], 'type') && $availability->c[$i]->type == 'completion' && property_exists($ob_import->sectionAndActi, 'sections')) {
                            $availability->c[$i]->cm = (int)$obj->search_section($ob_import->sectionAndActi->sections, $availability->c[$i]->cm);
                        }
                        //restricciones varias
                        if (property_exists($availability->c[$i], 'c')) {
                            for ($j = 0; $j < count($availability->c[$i]->c); $j++) {
                                if (property_exists($availability->c[$i]->c, $j) && property_exists($availability->c[$i]->c[$j], 'type') && $availability->c[$i]->c[$j]->type == 'grouping' && property_exists($ob_import->demas_info, 'groupings_p')) {
                                    $position = array_search($availability->c[$i]->c[$j]->id, $ob_import->demas_info->groupings_p);
                                    if (is_int($position)) {
                                        $availability->c[$i]->c[$j]->id = (int)$ob_import->demas_info->groupings_h[$position];
                                    }
                                }
                                if (property_exists($availability->c[$i]->c, $j) && property_exists($availability->c[$i]->c[$j], 'type') && $availability->c[$i]->c[$j]->type == 'group' && property_exists($ob_import->demas_info, 'groups_p')) {
                                    $position = array_search($availability->c[$i]->c[$j]->id, $ob_import->demas_info->groups_p);
                                    if (is_int($position)) {
                                        $availability->c[$i]->c[$j]->id = (int)$ob_import->demas_info->groups_h[$position];
                                    }
                                }
                                if (
                                    property_exists($availability->c[$i]->c, $j) && property_exists($availability->c[$i]->c[$j], 'type') && $availability->c[$i]->c[$j]->type == 'grade'
                                    && property_exists($ob_import->demas_info, 'groupings_groups') && property_exists($ob_import->demas_info->groupings_groups, 'grade_items')
                                ) {
                                    for ($h = 0; $h < count($ob_import->demas_info->groupings_groups->grade_items); $h++) {
                                        if ($availability->c[$i]->c[$j]->id == $ob_import->demas_info->groupings_groups->grade_items[$h]->p) {
                                            $availability->c[$i]->c[$j]->id = (int)$ob_import->demas_info->groupings_groups->grade_items[$h]->h;
                                        }
                                    }
                                }
                                if (property_exists($availability->c[$i]->c, $j) && property_exists($availability->c[$i]->c[$j], 'type') && $availability->c[$i]->c[$j]->type == 'completion' && property_exists($ob_import->sectionAndActi, 'sections')) {
                                    $availability->c[$i]->c[$j]->cm = (int) $obj->search_section($ob_import->sectionAndActi->sections, $availability->c[$i]->c[$j]->cm);
                                }
                            }
                        }
                    }
                }
                $fromform2->availabilityconditionsjson = json_encode($availability);
                $availability = json_decode($fromform1->availabilityconditionsjson);
            }

            $fromform2->introeditor = (array)$fromform2->introeditor;
            $fromform2->activityeditor = (array)$fromform2->activityeditor;
            $fromform2->instructauthorseditor = (array)$fromform2->instructauthorseditor;
            $fromform2->instructreviewerseditor = (array)$fromform2->instructreviewerseditor;
            $fromform2->conclusioneditor = (array)$fromform2->conclusioneditor;
            $fromform2->page_after_submit_editor = (array)$fromform2->page_after_submit_editor;

            if ($fromform1->modulename == "page") {
                $fromform2->page = (array)$fromform1->page;
            }

            if ($fromform2->modulename == 'quiz') {
                //Se elimina esta propiedad para que no de error en la creación hacia el hijo
                unset($fromform2->feedbacktext);
            }

            if ($fromform2->modulename == 'game') {
                $fromform2->toptext = (array) $fromform2->toptext;
                $fromform2->bottomtext = (array) $fromform2->bottomtext;
            }

            if ($fromform2->modulename == 'scorm') {

                unset($fromform2->packagefile);
                $fromform2->sha1hash = $fromform2->archivo;
            }

            $fromform = add_moduleinfo($fromform2, $course, null);

            if ($fromform->modulename == 'scorm') {

                $DB->update_record('scorm',  array('id' => $fromform->instance, 'version' => $fromform->version, 'sha1hash' => $fromform->sha1hash));

                $section = $DB->get_record('scorm', array('id' =>  $fromform->instance));

                require_once $CFG->dirroot . '/local/backup_course/folder_S3/controlador2_m.php';

                $id_nodo = $fromform1->id_nodo;
                $id_rel = $fromform1->rel_id;
                $moodle_data = $fromform1->url_scorm;
                $archivo = $fromform1->archivo;

                $name_archive = $archivo . '_' . $id_nodo . '_' . $id_rel . '.zip';
                $to = $CFG->dataroot . '/temp/';

                foreach ($fromform1->scorm_scoes_table as $kssc => $vssc) {

                    $vssc->scorm = $section->id;

                    $id_ss = $DB->insert_record('scorm_scoes', $vssc);

                    foreach ($fromform1->scorm_scoes_data_table as $kssd => $vssd) {
                        if (!empty($vssd)) {
                            foreach ($vssd as $kvss => $vss) {
                                $vss->scoid = $id_ss;
                                $DB->insert_record('scorm_scoes_data', $vss);
                            }
                        }
                    }
                }

                $s3 = new Controlador2_m(); //hacer transfer
                $s3->run('transfer', $name_archive, $to, $id_nodo);

                $section->scormtype = 'local';
                $section->visible = $fromform->visible;
                $section->cmidnumber = $fromform->cmidnumber;
                $section->groupmode = $fromform->groupmode;
                $section->groupingid = $fromform->groupingid;
                $section->availabilityconditionsjson = $fromform->availabilityconditionsjson;
                $section->tags = '';
                $section->section = 0;
                $section->coursemodule = $fromform->coursemodule;
                $section->module = $fromform->module;
                $section->modulename = 'scorm';
                $section->add = 'scorm';
                $section->update = 0;
                $section->return = 0;
                $section->sr = 0;
                $section->instance = $fromform->instance;

                $objCRE = new self();

                $cantScormCreate = count($registro->obj_act_h);

                $objCRE->scorm_add_instance_nueva($section, $fromform->instance, $to . $name_archive);

                $cantScorm++;

                if ($cantScormCreate == $cantScorm) {
                    $s3->run('delete', $moodle_data, $name_archive, $id_nodo);
                    unlink($to . $name_archive);
                }
            }

            if ($fromform->modulename == "page") $DB->update_record(
                $fromform1->modulename,
                array(
                    'id' => $fromform->instance,
                    'content' => $fromform2->page['text'],
                    'contentformat' => $fromform2->page['format']
                )
            );

            $data_obj = new stdClass();
            $data_obj->table = $fromform2->modulename;
            $data_obj->id_acti_p = $instance; //la del padre
            $data_obj->id_acti = $fromform->instance;
            $data_obj->id_como_p = $coursemodule; //la del padre
            $data_obj->id_como = $fromform->coursemodule;

            if ($fromform->modulename == 'scorm') {

                $scorm = $DB->get_record('scorm', array('id' =>  $fromform->instance));

                $scorm_scoes = (object) $DB->get_records('scorm_scoes', array('scorm' =>  $scorm->id));

                foreach ($fromform1->scoes as $kfs => $vfs) {

                    foreach ($scorm_scoes as $kfs2 => $vfs2) {

                        $data_obj->info_actividad->scoes->$kfs->p = $kfs;
                        $data_obj->info_actividad->scoes->$kfs->h = $vfs2->id;

                        $scorm_scoes_data = (object) $DB->get_records('scorm_scoes_data', array('scoid' =>  $vfs2->id));

                        foreach ($fromform1->scoes_data as $kds2 => $vds2) {

                            foreach ($scorm_scoes_data as $kds => $vds) {

                                $data_obj->info_actividad->scorm_scoes_data->$kds2->p = $kds2;
                                $data_obj->info_actividad->scorm_scoes_data->$kds2->h = $vds->id;
                                unset($scorm_scoes_data->$kds);
                                break;
                            }
                        }

                        unset($scorm_scoes->$kfs2);
                        break;
                    }
                }

                $data_obj->info_actividad->file_dir = $fromform1->file_dir;
            }

            if ($fromform->modulename == 'folder') {

                require_once $CFG->dirroot . '/local/backup_course/folder_S3/controlador2_m.php';
                $files_fold = $fromform1->files_fold;
                $s3 = new Controlador2_m();

                if (!empty($files_fold)) {

                    $files_folder = $fromform1->files_folder;

                    $cantidadFold = count(get_object_vars($files_folder));

                    $name_archive_folder = (object) $fromform1->name_archive_folder;

                    $files_dir = (object) $fromform1->files_dir;

                    $urls_file = (object) $fromform1->urls_file;

                    foreach ($files_fold as $kf => $vkf) {

                        foreach ($files_folder as $kff => $vff) {

                            foreach ($name_archive_folder as $knf => $vnf) {

                                foreach ($files_dir as $kfd => $vfd) {

                                    $parts = explode('_', $vnf);

                                    $firstPart = $parts[0];

                                    $lastPart = basename($vfd);

                                    if (
                                        $vkf->id == $kff  && $vkf->contenthash == $firstPart
                                        && $vkf->contenthash == $lastPart
                                    ) {

                                        $idNodo = $parts[1];

                                        $folder = $DB->get_record('folder', array('id' => $fromform->instance));

                                        /* $course_mod = $DB->get_record('course_modules', array('instance' => $folder->id)); */

                                        $course_mod = $DB->get_record('course_modules', array('instance' => $folder->id, 'course' => $folder->course));

                                        $context_fold = $DB->get_record('context', array('instanceid' => $course_mod->id));

                                        $vkf->contextid = $context_fold->id;

                                        if (!empty($urls_file)) {

                                            foreach ($urls_file as $kmdf => $vmdf) {

                                                $fileName = basename($vmdf);

                                                $par = explode("_", $fileName);

                                                // Obtener la primera parte
                                                $name = $par[0];

                                                if ($name == $vkf->contenthash) {

                                                    $name_archive = $vnf;
                                                    $to = $CFG->dataroot . '/temp/';
                                                    $fs = get_file_storage();
                                                    //hacer transfer
                                                    $s3->run('transfer', $name_archive, $to, $idNodo);

                                                    //Cambiamos el nombre del archivo
                                                    $rutaArchivo = $to . $name_archive;
                                                    $nuevoNombre = $lastPart;
                                                    rename($rutaArchivo, dirname($rutaArchivo) . '/' . $nuevoNombre);

                                                    $from_zip_file = $to . $nuevoNombre;

                                                    $file_record = array(
                                                        'contextid' => $context_fold->id,
                                                        'component' => 'mod_folder',
                                                        'filearea' => $vkf->filearea,
                                                        'itemid' => 0,
                                                        'filepath' => $vkf->filepath,
                                                        'filename' => $vkf->filename,
                                                        'timecreated' => time(),
                                                        'timemodified' => time()
                                                    );

                                                    if (filesize($from_zip_file) > 0) {

                                                        $packagefile = $fs->create_file_from_pathname($file_record, $from_zip_file);

                                                        $packagefile = (array) $packagefile;

                                                        foreach ($packagefile as  $pck => $vpck) {
                                                            if (property_exists($vpck, 'id')) {
                                                                $data_obj->info_actividad->files_folder->$kff->p = $kff;
                                                                $data_obj->info_actividad->files_folder->$kff->h = $vpck->id;
                                                            }
                                                        }
                                                    }

                                                    $moodle_data = $vmdf;

                                                    $contFolder++;

                                                    //Calculamos el total de iteraciones que tendría la creación
                                                    $tot = count($registro->obj_act_h) * $cantidadFold;

                                                    if ($contFolder == $tot) {
                                                        $s3->run('delete', $moodle_data, $name_archive, $idNodo);
                                                    }

                                                    unlink($from_zip_file);
                                                    unlink($rutaArchivo);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }


            if ($fromform2->modulename == 'feedback') {

                $data_obj->info_actividad->feedback_item = $fromform2->feedback_item;
            }

            if ($fromform2->modulename == 'lti') {

                $table_lti = $DB->get_record('lti', array('id' => $fromform->instance));

                $table_lti_types = $DB->get_record('lti_types', array('id' => $table_lti->typeid));

                $data_obj->info_actividad->lti_types->p = $fromform1->lti_types->p;

                $idLtiNew = 0;

                if ($table_lti_types->name == $fromform1->lti_name) {

                    $idLtiNew = $table_lti_types->id;

                    $data_obj->info_actividad->lti_types->h = $idLtiNew;
                } else {

                    $table_lti_types_name = $DB->get_record_sql('SELECT * FROM {lti_types} WHERE name = :name LIMIT 1', array('name' => $fromform1->lti_name));

                    if (!empty($table_lti_types_name)) {

                        $idLtiNew = $table_lti_types_name->id;
                        $data_obj->info_actividad->lti_types->h = $idLtiNew;
                    }

                    /*                     if (empty($table_lti_types_name)) {

                        unset($fromform1->table_lti_types->id);

                        $DB->insert_record('lti_types', (array) $fromform1->table_lti_types);
                        $idLtiNew2 = $DB->get_record_sql("SELECT id FROM {lti_types} ORDER BY id DESC LIMIT 1 ");
                        $idLtiNew = $idLtiNew2->id;
                        $data_obj->info_actividad->lti_types->h = $idLtiNew;
                    } else {

                        $idLtiNew = $table_lti_types_name->id;
                        $data_obj->info_actividad->lti_types->h = $idLtiNew;
                    } */
                }


                /*                 $info_actividad[$k]['p'] = $v['id'];
                $lti = $DB->get_record('lti', array('id' => $id_acti));
                $lti_types = $DB->get_record('lti_types', array('id' => $lti->typeid));

                if (!empty($lti_types) && $lti_types->name == $v['name']) {

                    $lti->typeid = $lti_types->id;
                } else {

                    $lti_types = $DB->get_record_sql('SELECT * FROM {lti_types} WHERE name = :name LIMIT 1', array('name' => $v['name']));
                    if (!empty($lti_types)) {

                        $lti->typeid = $lti_types->id;
                    }
                }
                $typeId = $lti->typeid;
                $info_actividad[$k]['h'] = $lti->typeid;
                $DB->update_record('lti', $lti); */


                $DB->update_record(
                    "lti",
                    array(
                        'id' => $table_lti->id,
                        'typeid' => $data_obj->info_actividad->lti_types->h
                    )
                );

                /*                 $lti_types_config = $DB->get_record("lti_types_config", array('typeid' => $table_lti_types->id));
                $cont = 0;
                $typeId = 0;

                if (!empty($lti_types_config)) {

                    $typeId = $lti_types_config->typeid;

                    foreach ($fromform1->lti_types_config as $b => $m) {

                        $m->typeid = $typeId;

                        $DB->update_record("lti_types_config", array(
                            "id" => $lti_types_config->id + $cont,
                            "typeid" => $m->typeid,
                            "name" => $m->name,
                            "value" => $m->value
                        ));

                        $cont++;
                    }
                } else {

                    foreach ($fromform1->lti_types_config as $b => $m) {
                        $m->typeid = $idLtiNew;
                        $DB->insert_record('lti_types_config', array(
                            "typeid" => $m->typeid,
                            "name" => $m->name,
                            "value" => $m->value
                        ));
                    }
                } */
            }

            if ($fromform2->modulename == 'assign') {

                $data_obj->info_actividad->assign_plugin_config = $fromform1->idAssignPluginConfigP;
                $data_obj->info_actividad->grading_areas = $fromform1->gradingAreasPH;
                $contextident = $fromform1->contextId;
                $idAssignPluginConfigH = $DB->get_records_sql("SELECT * FROM {assign_plugin_config} WHERE assignment = $fromform->instance");

                $cantReg = 0;
                $primerId = 0;
                foreach ($idAssignPluginConfigH as $key => $va) {
                    $cantReg++;
                    if ($cantReg == 1) {
                        $primerId = $va->id;
                    }
                }

                $array_padre_plugin_conf = $fromform1->idAssignPluginConfigP;
                $i = 0;
                foreach ($array_padre_plugin_conf as $k => $r) {
                    if ($i != $cantReg) {
                        $data_obj->info_actividad->assign_plugin_config->$k->h = $primerId;
                        $primerId++;
                        $i++;
                    }
                }

                $context = $DB->get_record_sql("SELECT id FROM {context} ORDER BY id DESC LIMIT 1");

                $gradingAreas = $DB->get_records_sql("SELECT id FROM {grading_areas} WHERE contextid = $context->id");

                $idGrdingAreasH = 0;

                foreach ($gradingAreas as $p => $q) {
                    $idGrdingAreasH = $q->id;
                }

                $array_grading_areas = $fromform1->gradingAreasPH;

                foreach ($array_grading_areas as $c => $k) {
                    $data_obj->info_actividad->grading_areas->$c->h = $idGrdingAreasH;
                }
            }

            if ($fromform2->modulename == 'quiz') {

                $table_quiz = $DB->get_records_sql("SELECT id FROM {quiz} WHERE id = $fromform->instance AND course = $id_curso_hijo");

                foreach ($table_quiz as $kq => $vq) {

                    foreach ($fromform2->table_quiz_feedback as $kfr => $vfr) {

                        $DB->insert_record('quiz_feedback', array(
                            "quizid" => $vq->id,
                            "feedbacktext" => $vfr->feedbacktext,
                            "feedbacktextformat" => $vfr->feedbacktextformat,
                            "mingrade" => $vfr->mingrade,
                            "maxgrade" => $vfr->maxgrade,
                        ));
                    }

                    $table_quiz_sections = $DB->get_records_sql("SELECT id FROM {quiz_sections} WHERE quizid = $vq->id");
                    $table_quiz_feedback = $DB->get_records_sql("SELECT id FROM {quiz_feedback} WHERE quizid = $vq->id");
                }

                foreach ($table_quiz_sections as $kqs => $vqs) {
                    $id_quiz_section = $vqs->id;
                }

                foreach ($table_quiz_feedback as $kqf => $vqf) {
                    $id_quiz_feedback = $vqf->id;
                }

                $array_quiz_sections = $fromform1->quiz_sections;

                foreach ($array_quiz_sections as $aks => $vks) {

                    $array_quiz_sections->$aks->h = $id_quiz_section;
                }

                $data_obj->info_actividad->quiz_section = $array_quiz_sections;

                $array_quiz_feedback = $fromform1->quiz_feedback;

                foreach ($array_quiz_feedback as $akf => $vkf) {
                    $array_quiz_feedback->$akf->h = $id_quiz_feedback;
                }

                $data_obj->info_actividad->quiz_feedback = $array_quiz_feedback;
            }

            if ($fromform2->modulename == 'game') {

                $idGlossary = $fromform2->glossaryid;

                $idQuizId = $fromform2->quizid;

                $idQuestionCategory =  $fromform2->questioncategoryid;

                $questionCategory = $ob_import->bancoPregu->question_categories;

                foreach ($questionCategory as $kc => $vc) {

                    if ($idQuestionCategory == $vc->p) {

                        $DB->update_record('game', array('id' => $fromform2->instance, 'questioncategoryid' => $vc->h));
                    }
                }

                $secc = $ob_import->sectionAndActi->sections;

                foreach ($secc as $ks => $vs) {

                    $activi = $vs->activities;

                    foreach ($activi as $ka => $va) {

                        foreach ($va as $newkey => $val) {

                            if ($val->table == 'glossary' && $val->id_acti_p == $idGlossary) {

                                $DB->update_record('game', array('id' => $fromform2->instance, 'glossaryid' => $val->id_acti));
                            }

                            if ($val->table == 'quiz' && $val->id_acti_p == $idQuizId) {

                                $DB->update_record('game', array('id' => $fromform2->instance, 'quizid' => $val->id_acti));
                            }
                        }
                    }
                }
            }

            if ($fromform2->modulename == 'lesson') {

                $idposPadre = 0;

                foreach ($fromform1->lesson_pages as $kl => $vl) {

                    $idposPadre = $kl;
                }

                $data_obj->info_actividad->lesson_pages->$idposPadre->p = 0;

                $data_obj->info_actividad->lesson_pages->$idposPadre->h = 0;

                $idposPadre2 = 0;

                foreach ($fromform1->lesson_answers as $kl2 => $vl2) {

                    $idposPadre2 = $kl2;
                }

                $data_obj->info_actividad->lesson_answers->$idposPadre2->p = 0;

                $data_obj->info_actividad->lesson_answers->$idposPadre2->h = 0;
            }

            //Demás info
            //Groupings groups
            //Grade items

            $cantidad_obj = count($ob_import->demas_info->groupings_groups->grade_items);

            $gradeItemPH = $fromform1->gradeItemPH;

            $gradeItemHijo = $DB->get_records_sql("SELECT id FROM {grade_items} WHERE iteminstance = $fromform->instance");

            if (count($gradeItemPH) > 1) {

                $idGradeHijo = 0;

                $j = 0;

                foreach ($gradeItemHijo as $f => $h) {

                    $idGradeHijo = $h->id;

                    $ob_import->demas_info->groupings_groups->grade_items[$cantidad_obj + $j]->p = $gradeItemPH[$j][0]->p;

                    $ob_import->demas_info->groupings_groups->grade_items[$cantidad_obj + $j]->h = $idGradeHijo;

                    $j++;
                }
            } else {

                $idGradeHijo = 0;

                foreach ($gradeItemHijo as $f => $h) {

                    $idGradeHijo = $h->id;
                }

                $ob_import->demas_info->groupings_groups->grade_items[$cantidad_obj]->p = $gradeItemPH[0][0]->p;
                $ob_import->demas_info->groupings_groups->grade_items[$cantidad_obj]->h = $idGradeHijo;
            }

            array_push($ob_import->sectionAndActi->sections[$fromform2->section]->activities[0], $data_obj); // añadir al objeto la creación
            $value->objet_ph = json_encode($ob_import);

            if ($DB->update_record('bc_rel_padre_hijo', $value)) {
                $return->cursos_actualizados[] = $value->courseid_sh;
                $return->cant += 1;
                $return->object_p = $DB->get_records_sql("SELECT * FROM {bc_rel_padre_hijo} WHERE courseid_sp = $registro->idCourse_p");
                rebuild_course_cache($value->courseid_sh);
            }
        }

        return $return;
    }
}
