<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class lesson_add_page_form_cluster_uvd extends lesson_add_page_form_base {

    public $qtype = LESSON_PAGE_CLUSTER;
    public $qtypestring = 'cluster';
    protected $standard = false;

    public function custom_definition() {
        global $PAGE;

        $mform = $this->_form;
        $lesson = $this->_customdata['lesson'];
        $jumptooptions = lesson_page_type_branchtable::get_jumptooptions(optional_param('firstpage', false, PARAM_BOOL), $lesson);

        $mform->addElement('hidden', 'firstpage');
        $mform->setType('firstpage', PARAM_BOOL);

        $mform->addElement('hidden', 'qtype');
        $mform->setType('qtype', PARAM_TEXT);

        $mform->addElement('text', 'title', get_string("pagetitle", "lesson"), array('size'=>70));
        $mform->setType('title', PARAM_TEXT);

        $this->editoroptions = array('noclean'=>true, 'maxfiles'=>EDITOR_UNLIMITED_FILES, 'maxbytes'=>$PAGE->course->maxbytes);
        $mform->addElement('editor', 'contents_editor', get_string("pagecontents", "lesson"), null, $this->editoroptions);
        $mform->setType('contents_editor', PARAM_RAW);

        $this->add_jumpto(0);
    }


    public function construction_override($pageid, lesson $lesson) {
        global $PAGE, $CFG, $DB;
        //require_sesskey();

        $timenow = time();

        if ($pageid == 0) {
            if ($lesson->has_pages()) {
                if (!$page = $DB->get_record("lesson_pages", array("prevpageid" => 0, "lessonid" => $lesson->id))) {
                    print_error('cannotfindpagerecord', 'lesson');
                }
            } else {
                // This is the ONLY page
                $page = new stdClass;
                $page->id = 0;
            }
        } else {
            if (!$page = $DB->get_record("lesson_pages", array("id" => $pageid))) {
                print_error('cannotfindpagerecord', 'lesson');
            }
        }
        $newpage = new stdClass;
        $newpage->lessonid = $lesson->id;
        $newpage->prevpageid = $pageid;
        if ($pageid != 0) {
            $newpage->nextpageid = $page->nextpageid;
        } else {
            $newpage->nextpageid = $page->id;
        }
        $newpage->qtype = $this->qtype;
        $newpage->timecreated = $timenow;
        $newpage->title = get_string("clustertitle", "lesson");
        $newpage->contents = get_string("clustertitle", "lesson");
        $newpageid = $DB->insert_record("lesson_pages", $newpage);
        $newpage->id = $newpageid;
        // update the linked list...
        if ($pageid != 0) {
            $DB->set_field("lesson_pages", "nextpageid", $newpageid, array("id" => $pageid));
        }

        if ($pageid == 0) {
            $page->nextpageid = $page->id;
        }
        if ($page->nextpageid) {
            // the new page is not the last page
            $DB->set_field("lesson_pages", "prevpageid", $newpageid, array("id" => $page->nextpageid));
        }
        // ..and the single "answer"
        $newanswer = new stdClass;
        $newanswer->lessonid = $lesson->id;
        $newanswer->pageid = $newpageid;
        $newanswer->timecreated = $timenow;
        $newanswer->jumpto = LESSON_CLUSTERJUMP;
        $newanswerid = $DB->insert_record("lesson_answers", $newanswer);
        $lesson->add_message(get_string('addedcluster', 'lesson'), 'notifysuccess');
        return $newpage;
        //redirect($CFG->wwwroot.'/local/backup_course/update/layouts/lesson/edit.php?id='.$PAGE->cm->id);
    }
}