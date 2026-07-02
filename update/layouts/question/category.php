<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This script allows a teacher to create, edit and delete question categories.
 *
 * @package    moodlecore
 * @subpackage questionbank
 * @copyright  1999 onwards Martin Dougiamas {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once('../../../../../config.php');
require_once($CFG->dirroot."/question/editlib.php");
require_once($CFG->dirroot."/question/category_class.php");

require_once '../loader.php';
    echo '<link href="'.$CFG->wwwroot .'/local/backup_course/css/style.css" rel="stylesheet" type="text/css" />'.
        '<link href="'.$CFG->wwwroot .'/local/backup_course/css/jquery-confirm.css" rel="stylesheet" type="text/css" />'.
        '<link href="'.$CFG->wwwroot .'/local/backup_course/css/tostadas.css" rel="stylesheet" type="text/css" />'.
            '<script src="'.$CFG->wwwroot .'/lib/jquery/jquery-3.6.1.js"></script>'.
             '<script src="'.$CFG->wwwroot .'/local/backup_course/update/js/buttons.js"></script>'.
             '<script src="'.$CFG->wwwroot .'/local/backup_course/update/js/objetsUpdates/updateObjet.js"></script>'.
             '<script src="'.$CFG->wwwroot .'/local/backup_course/update/js/objetsUpdates/saveLog.js"></script>   '.
             '<script src="'.$CFG->wwwroot .'/local/backup_course/update/js/objetsUpdates/CRE.js"></script>'.
             '<script src="'.$CFG->wwwroot .'/local/backup_course/js/objetos/QRY.js"></script>'.
             '<script src="'.$CFG->wwwroot .'/local/backup_course/js/jquery-confirm.js"></script>'.
             '<script src="'.$CFG->wwwroot .'/local/backup_course/js/mensajes.js"></script>'.
             '<script src="'.$CFG->wwwroot .'/local/backup_course/update/js/objetsUpdates/UPD.js"></script>'.
             '<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>'.
             '<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>';


list($thispageurl, $contexts, $cmid, $cm, $module, $pagevars) =
        question_edit_setup('categories', '/question/category.php');
// Get values from form for actions on this page.
$param = new stdClass();
$param->moveup = optional_param('moveup', 0, PARAM_INT);
$param->movedown = optional_param('movedown', 0, PARAM_INT);
$param->moveupcontext = optional_param('moveupcontext', 0, PARAM_INT);
$param->movedowncontext = optional_param('movedowncontext', 0, PARAM_INT);
$param->tocontext = optional_param('tocontext', 0, PARAM_INT);
$param->left = optional_param('left', 0, PARAM_INT);
$param->right = optional_param('right', 0, PARAM_INT);
$param->delete = optional_param('delete', 0, PARAM_INT);
$param->confirm = optional_param('confirm', 0, PARAM_INT);
$param->cancel = optional_param('cancel', '', PARAM_ALPHA);
$param->move = optional_param('move', 0, PARAM_INT);
$param->moveto = optional_param('moveto', 0, PARAM_INT);
$param->edit = optional_param('edit', 0, PARAM_INT);

$url = new moodle_url($thispageurl);
foreach ((array)$param as $key=>$value) {
    if (($key !== 'cancel' && $value !== 0) || ($key === 'cancel' && $value !== '')) {
        $url->param($key, $value);
    }
}
$PAGE->set_url($url);

$qcobject = new question_category_object_uvd($pagevars['cpage'], $thispageurl,
        $contexts->having_one_edit_tab_cap('categories'), $param->edit,
        $pagevars['cat'], $param->delete, $contexts->having_cap('moodle/question:add'));
if ($param->left || $param->right || $param->moveup || $param->movedown) {
    require_sesskey();

    foreach ($qcobject->editlists as $list) {
        // Processing of these actions is handled in the method where appropriate and page redirects.
        $list->process_actions($param->left, $param->right, $param->moveup, $param->movedown);
    }
}

if ($param->moveupcontext || $param->movedowncontext) {
    require_sesskey();

    if ($param->moveupcontext) {
        $catid = $param->moveupcontext;
    } else {
        $catid = $param->movedowncontext;
    }
    die;
    $oldcat = $DB->get_record('question_categories', array('id' => $catid), '*', MUST_EXIST);
    $qcobject->update_category($catid, '0,'.$param->tocontext, $oldcat->name, $oldcat->info);
    // The previous line does a redirect().
}

if ($param->delete) { // aqui elimina
    if (!$category = $DB->get_record("question_categories", array("id" => $param->delete))) {
        print_error('nocate', 'question', $thispageurl->out(), $param->delete);
    }

    question_remove_stale_questions_from_category($param->delete);
    $questionstomove = $DB->count_records("question", array("category" => $param->delete));

    // Second pass, if we still have questions to move, setup the form.
    if ($questionstomove) {
        $categorycontext = context::instance_by_id($category->contextid);
        $qcobject->moveform = new question_move_form($thispageurl,
            array('contexts' => array($categorycontext), 'currentcat' => $param->delete));
        if ($qcobject->moveform->is_cancelled()) {
            redirect($thispageurl);
        } else if ($formdata = $qcobject->moveform->get_data()) {
            list($tocategoryid, $tocontextid) = explode(',', $formdata->category);
            $qcobject->move_questions_and_delete_category($formdata->delete, $tocategoryid);
            $thispageurl->remove_params('cat', 'category');
            redirect($thispageurl);
        }
    }
} else {
    $questionstomove = 0;
}

if ($qcobject->catform->is_cancelled()) {
    redirect($thispageurl);
} else if ($catformdata = $qcobject->catform->get_data()) {

    echo html_writer::script('document.getElementById("overlay-loader_block_modedit").style.display = "block";');

    $returnurl = (string)$thispageurl;
    $returnurl = new moodle_url('/local/backup_course/update/layouts/question/category.php', array('courseid' => $COURSE->id));
    
    $catformdata->infoformat = $catformdata->info['format'];
    $catformdata->info       = $catformdata->info['text'];
    $new_create = 0; //actualizar actividad
    if (!$catformdata->id) {//new category
        $new_create = 1; //new category
        $qcobject->add_category($catformdata->parent, $catformdata->name,
                $catformdata->info, false, $catformdata->infoformat);
    } else {
        $qcobject->update_category($catformdata->id, $catformdata->parent,
                $catformdata->name, $catformdata->info, $catformdata->infoformat);
    }
    echo '<div id="snackbar"></div>';
    echo html_writer::script('SLog.confir_nodos_actu('.$COURSE->id.', '.$COURSE->id.', "course", '.$USER->id.',"'.addslashes(json_encode($catformdata)).'", 0, "../../../","'.$returnurl.'", 0);'     );
    
    die;
    redirect($thispageurl);
} else if ((!empty($param->delete) and (!$questionstomove) and confirm_sesskey())) { // aqui elimina
    $qcobject->delete_category($param->delete);//delete the category now no questions to move
    $thispageurl->remove_params('cat', 'category');
    redirect($thispageurl);
}

if ($param->edit) { // form de editar categoria
    $PAGE->navbar->add(get_string('editingcategory', 'question'));
}

$PAGE->set_title(get_string('editcategories', 'question'));
$PAGE->set_heading($COURSE->fullname);
echo $OUTPUT->header();

// Print horizontal nav if needed.
$renderer = $PAGE->get_renderer('core_question', 'bank');
echo $renderer->extra_horizontal_navigation();

// Display the UI.
if (!empty($param->edit)) {
    $qcobject->edit_single_category($param->edit);
} else if ($questionstomove){
    $qcobject->display_move_form($questionstomove, $category);
} else {
    // Display the user interface.
    echo '<script src="'.$CFG->wwwroot .'/local/backup_course/create/js/up_create.js"></script>';
    echo '<script>r_url.cambiar_btn_enviar();</script>';
    $qcobject->display_user_interface();
}
echo $OUTPUT->footer();





/**
 * Class representing q question category
 *
 * @copyright  1999 onwards Martin Dougiamas {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class question_category_object_uvd {

    /**
     * @var array common language strings.
     */
    public $str;

    /**
     * @var array nested lists to display categories.
     */
    public $editlists = array();
    public $newtable;
    public $tab;
    public $tabsize = 3;

    /**
     * @var moodle_url Object representing url for this page
     */
    public $pageurl;

    /**
     * @var question_category_edit_form Object representing form for adding / editing categories.
     */
    public $catform;

    /**
     * Constructor
     *
     * Gets necessary strings and sets relevant path information
     */
    public function __construct($page, $pageurl, $contexts, $currentcat, $defaultcategory, $todelete, $addcontexts) {
        global $CFG, $COURSE, $OUTPUT;

        $this->tab = str_repeat('&nbsp;', $this->tabsize);

        $this->str = new stdClass();
        $this->str->course         = get_string('course');
        $this->str->category       = get_string('category', 'question');
        $this->str->categoryinfo   = get_string('categoryinfo', 'question');
        $this->str->questions      = get_string('questions', 'question');
        $this->str->add            = get_string('add');
        $this->str->delete         = get_string('delete');
        $this->str->moveup         = get_string('moveup');
        $this->str->movedown       = get_string('movedown');
        $this->str->edit           = get_string('editthiscategory', 'question');
        $this->str->hide           = get_string('hide');
        $this->str->order          = get_string('order');
        $this->str->parent         = get_string('parent', 'question');
        $this->str->add            = get_string('add');
        $this->str->action         = get_string('action');
        $this->str->top            = get_string('top');
        $this->str->addcategory    = get_string('addcategory', 'question');
        $this->str->editcategory   = get_string('editcategory', 'question');
        $this->str->cancel         = get_string('cancel');
        $this->str->editcategories = get_string('editcategories', 'question');
        $this->str->page           = get_string('page');

        $this->pageurl = $pageurl;

        $this->initialize($page, $contexts, $currentcat, $defaultcategory, $todelete, $addcontexts);
    }

    /**
     * Old syntax of class constructor. Deprecated in PHP7.
     *
     * @deprecated since Moodle 3.1
     */
    public function question_category_object($page, $pageurl, $contexts, $currentcat, $defaultcategory, $todelete, $addcontexts) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($page, $pageurl, $contexts, $currentcat, $defaultcategory, $todelete, $addcontexts);
    }

    /**
     * Initializes this classes general category-related variables
     */
    public function initialize($page, $contexts, $currentcat, $defaultcategory, $todelete, $addcontexts) {
        $lastlist = null;
        foreach ($contexts as $context){
            $this->editlists[$context->id] = new question_category_list('ul', '', true, $this->pageurl, $page, 'cpage', QUESTION_PAGE_LENGTH, $context);
            $this->editlists[$context->id]->lastlist =& $lastlist;
            if ($lastlist!== null){
                $lastlist->nextlist =& $this->editlists[$context->id];
            }
            $lastlist =& $this->editlists[$context->id];
        }

        $count = 1;
        $paged = false;
        foreach ($this->editlists as $key => $list){
            list($paged, $count) = $this->editlists[$key]->list_from_records($paged, $count);
        }
        $this->catform = new question_category_edit_form($this->pageurl, compact('contexts', 'currentcat'));
        if (!$currentcat){
            $this->catform->set_data(array('parent'=>$defaultcategory));
        }
    }

    /**
     * Displays the user interface
     *
     */
    public function display_user_interface() {

        /// Interface for editing existing categories
        $this->output_edit_lists();


        echo '<br />';
        /// Interface for adding a new category:
        $this->output_new_table();
        echo '<br />';

    }

    /**
     * Outputs a table to allow entry of a new category
     */
    public function output_new_table() {
        $this->catform->display();
    }

    /**
     * Outputs a list to allow editing/rearranging of existing categories
     *
     * $this->initialize() must have already been called
     *
     */
    public function output_edit_lists() {
        global $OUTPUT;

        echo $OUTPUT->heading_with_help(get_string('editcategories', 'question'), 'editcategories', 'question');

        foreach ($this->editlists as $context => $list){
            $listhtml = $list->to_html(0, array('str'=>$this->str));
            if ($listhtml){
                echo $OUTPUT->box_start('boxwidthwide boxaligncenter generalbox questioncategories contextlevel' . $list->context->contextlevel);
                $fullcontext = context::instance_by_id($context);
                echo $OUTPUT->heading(get_string('questioncatsfor', 'question', $fullcontext->get_context_name()), 3);
                echo $listhtml;
                echo $OUTPUT->box_end();
            }
        }
        echo $list->display_page_numbers();
     }

    /**
     * gets all the courseids for the given categories
     *
     * @param array categories contains category objects in  a tree representation
     * @return array courseids flat array in form categoryid=>courseid
     */
    public function get_course_ids($categories) {
        $courseids = array();
        foreach ($categories as $key=>$cat) {
            $courseids[$key] = $cat->course;
            if (!empty($cat->children)) {
                $courseids = array_merge($courseids, $this->get_course_ids($cat->children));
            }
        }
        return $courseids;
    }

    public function edit_single_category($categoryid) {
    /// Interface for adding a new category
        global $COURSE, $DB;
        /// Interface for editing existing categories
        if ($category = $DB->get_record("question_categories", array("id" => $categoryid))) {

            $category->parent = "{$category->parent},{$category->contextid}";
            $category->submitbutton = get_string('savechanges');
            $category->categoryheader = $this->str->edit;
            $this->catform->set_data($category);
            $this->catform->display();
        } else {
            print_error('invalidcategory', '', '', $categoryid);
        }
    }

    /**
     * Sets the viable parents
     *
     *  Viable parents are any except for the category itself, or any of it's descendants
     *  The parentstrings parameter is passed by reference and changed by this function.
     *
     * @param    array parentstrings a list of parentstrings
     * @param   object category
     */
    public function set_viable_parents(&$parentstrings, $category) {

        unset($parentstrings[$category->id]);
        if (isset($category->children)) {
            foreach ($category->children as $child) {
                $this->set_viable_parents($parentstrings, $child);
            }
        }
    }

    /**
     * Gets question categories
     *
     * @param    int parent - if given, restrict records to those with this parent id.
     * @param    string sort - [[sortfield [,sortfield]] {ASC|DESC}]
     * @return   array categories
     */
    public function get_question_categories($parent=null, $sort="sortorder ASC") {
        global $COURSE, $DB;
        if (is_null($parent)) {
            $categories = $DB->get_records('question_categories', array('course' => $COURSE->id), $sort);
        } else {
            $select = "parent = ? AND course = ?";
            $categories = $DB->get_records_select('question_categories', $select, array($parent, $COURSE->id), $sort);
        }
        return $categories;
    }

    /**
     * Deletes an existing question category
     *
     * @param int deletecat id of category to delete
     */
    public function delete_category($categoryid) {
        global $CFG, $DB;
        question_can_delete_cat($categoryid);
        if (!$category = $DB->get_record("question_categories", array("id" => $categoryid))) {  // security
            print_error('unknowcategory');
        }
        /// Send the children categories to live with their grandparent
        $DB->set_field("question_categories", "parent", $category->parent, array("parent" => $category->id));

        /// Finally delete the category itself
        $DB->delete_records("question_categories", array("id" => $category->id));
    }

    public function move_questions_and_delete_category($oldcat, $newcat){
        question_can_delete_cat($oldcat);
        $this->move_questions($oldcat, $newcat);
        $this->delete_category($oldcat);
    }

    public function display_move_form($questionsincategory, $category){
        global $OUTPUT;
        $vars = new stdClass();
        $vars->name = $category->name;
        $vars->count = $questionsincategory;
        echo $OUTPUT->box(get_string('categorymove', 'question', $vars), 'generalbox boxaligncenter');
        $this->moveform->display();
    }

    public function move_questions($oldcat, $newcat){
        global $DB;
        $questionids = $DB->get_records_select_menu('question',
                'category = ? AND (parent = 0 OR parent = id)', array($oldcat), '', 'id,1');
        question_move_questions_to_category(array_keys($questionids), $newcat);
    }

    /**
     * Creates a new category with given params
     */
    public function add_category($newparent, $newcategory, $newinfo, $return = false, $newinfoformat = FORMAT_HTML) {
        global $DB;
        if (empty($newcategory)) {
            print_error('categorynamecantbeblank', 'question');
        }
        list($parentid, $contextid) = explode(',', $newparent);
        //moodle_form makes sure select element output is legal no need for further cleaning
        require_capability('moodle/question:managecategory', context::instance_by_id($contextid));

        if ($parentid) {
            if(!($DB->get_field('question_categories', 'contextid', array('id' => $parentid)) == $contextid)) {
                print_error('cannotinsertquestioncatecontext', 'question', '', array('cat'=>$newcategory, 'ctx'=>$contextid));
            }
        }

        $cat = new stdClass();
        $cat->parent = $parentid;
        $cat->contextid = $contextid;
        $cat->name = $newcategory;
        $cat->info = $newinfo;
        $cat->infoformat = $newinfoformat;
        $cat->sortorder = 999;
        $cat->stamp = make_unique_id_code();
        $categoryid = $DB->insert_record("question_categories", $cat);

        // Log the creation of this category.
        $params = array(
            'objectid' => $categoryid,
            'contextid' => $contextid
        );
        $event = \core\event\question_category_created::create($params);
        $event->trigger();

        if ($return) {
            return $categoryid;
        } else {
            //redirect($this->pageurl);//always redirect after successful action
        }
    }

    /**
     * Updates an existing category with given params
     */
    public function update_category($updateid, $newparent, $newname, $newinfo, $newinfoformat = FORMAT_HTML) {
        global $CFG, $DB;
        if (empty($newname)) {
            print_error('categorynamecantbeblank', 'question');
        }

        // Get the record we are updating.
        $oldcat = $DB->get_record('question_categories', array('id' => $updateid));
        //$lastcategoryinthiscontext = question_is_only_toplevel_category_in_context($updateid);
        $lastcategoryinthiscontext =   question_is_only_child_of_top_category_in_context($updateid);
        if (!empty($newparent) && !$lastcategoryinthiscontext) {
            list($parentid, $tocontextid) = explode(',', $newparent);
        } else {
            $parentid = $oldcat->parent;
            $tocontextid = $oldcat->contextid;
        }

        // Check permissions.
        $fromcontext = context::instance_by_id($oldcat->contextid);
        require_capability('moodle/question:managecategory', $fromcontext);

        // If moving to another context, check permissions some more, and confirm contextid,stamp uniqueness.
        $newstamprequired = false;
        if ($oldcat->contextid != $tocontextid) {
            $tocontext = context::instance_by_id($tocontextid);
            require_capability('moodle/question:managecategory', $tocontext);

            // Confirm stamp uniqueness in the new context. If the stamp already exists, generate a new one.
            if ($DB->record_exists('question_categories', array('contextid' => $tocontextid, 'stamp' => $oldcat->stamp))) {
                $newstamprequired = true;
            }
        }

        // Update the category record.
        $cat = new stdClass();
        $cat->id = $updateid;
        $cat->name = $newname;
        $cat->info = $newinfo;
        $cat->infoformat = $newinfoformat;
        $cat->parent = $parentid;
        $cat->contextid = $tocontextid;
        if ($newstamprequired) {
            $cat->stamp = make_unique_id_code();
        }
        $DB->update_record('question_categories', $cat);

        // If the category name has changed, rename any random questions in that category.
        if ($oldcat->name != $cat->name) {
            $where = "qtype = 'random' AND category = ? AND " . $DB->sql_compare_text('questiontext') . " = ?";

            $randomqtype = question_bank::get_qtype('random');
            $randomqname = $randomqtype->question_name($cat, false);
            $DB->set_field_select('question', 'name', $randomqname, $where, array($cat->id, '0'));

            $randomqname = $randomqtype->question_name($cat, true);
            $DB->set_field_select('question', 'name', $randomqname, $where, array($cat->id, '1'));
        }

        if ($oldcat->contextid != $tocontextid) {
            // Moving to a new context. Must move files belonging to questions.
            question_move_category_to_context($cat->id, $oldcat->contextid, $tocontextid);
        }

        // Cat param depends on the context id, so update it.
        $this->pageurl->param('cat', $updateid . ',' . $tocontextid);
        //redirect($this->pageurl);
    }
}


