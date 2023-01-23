<?php

/**
 * MumieTask plugin
 *
 * @copyright   2022 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Vasilije Nedeljkovic(vasilije.nedeljkovic@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * This GUI provides a way to list grades and submission dates for a single user in a MUMIE task
 */

require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskGradeSync.php');
class ilMumieTaskGradeListGUI extends ilTable2GUI
{
    private $user_id;

    public function __construct($parentObj)
    {
        parent::__construct($parentObj, 'displayGradeList');
        $this->user_id = $_GET['user_id'];
        $this->setId("user" . $_GET["ref_id"]);
    }

    public function init()
    {
        $this->createList();
    }

    private function createList()
    {
        global $lng;

        $this->setFormName('participants');

        $this->addColumn($lng->txt('rep_robj_xmum_frm_grade_overview_list_submission_date'), 'date');
        $this->addColumn($lng->txt('rep_robj_xmum_frm_grade_overview_list_use_grade'), 'useGrade');
        $this->addColumn($lng->txt('rep_robj_xmum_frm_list_grade'), 'grade');

        $this->tpl->addBlockFile(
            "TBL_CONTENT",
            "tbl_content",
            "tpl.mumie_grade_list.html",
            "Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask"
        );
        $user_grades = ilMumieTaskGradeSync::getGradesForUser($this->user_id, $this->parent_obj->object);
        if ($this->privateGradepoolSet($this->parent_obj)) {
            if (empty($user_grades)) {
                $this->setEmptyTable();
            } else {
                foreach ($user_grades as $xapi_grade) {
                    $this->setTableRow($this->parent_obj, $xapi_grade);
                }
            }
        }
        $this->enable('header');
        $this->setEnableHeader(true);
    }

    private function privateGradepoolSet($parentObj)
    {
        return $parentObj->object->getPrivateGradepool() != -1;
    }

    private function setEmptyTable()
    {
        global $lng;
        $this->tpl->setCurrentBlock("tbl_content");
        $this->css_row = ($this->css_row != "tblrow1")
            ? "tblrow1"
            : "tblrow2";
        $this->tpl->setVariable("CSS_ROW", $this->css_row);
        $this->tpl->setVariable("VAL_HIDDEN", "hidden");
        $this->tpl->setVariable("VAL_NO_GRADE", $lng->txt('rep_robj_xmum_frm_grade_overview_no_submission_made'));
        $this->tpl->setCurrentBlock("tbl_content");
        $this->tpl->parseCurrentBlock();
    }

    private function setTableRow($parentObj, $xapi_grade)
    {
        $this->tpl->setCurrentBlock("tbl_content");
        $this->css_row = ($this->css_row != "tblrow1")
            ? "tblrow1"
            : "tblrow2";
        $this->tpl->setVariable("CSS_ROW", $this->css_row);
        $this->tpl->setVariable("VAL_GRADE", round($xapi_grade->result->score->raw * 100));
        $this->tpl->setVariable("VAL_HIDDEN", "");
        $this->tpl->setVariable("VAL_NO_GRADE", "");
        $this->ctrl->setParameterByClass('ilObjMumieTaskGUI', 'user_id', $this->user_id);
        $this->ctrl->setParameterByClass('ilObjMumieTaskGUI', 'score', $xapi_grade->result->score->raw);
        $this->ctrl->setParameterByClass('ilObjMumieTaskGUI', 'timestamp', strtotime($xapi_grade->timestamp));
        $this->tpl->setVariable("LINK", $this->ctrl->getLinkTarget($parentObj, 'gradeOverride'));
        $dateTime = date('d.m.Y - H:i', strtotime($xapi_grade->timestamp));
        $this->tpl->setVariable("VAL_DATE", $dateTime);
        $this->tpl->setCurrentBlock("tbl_content");
        $this->tpl->parseCurrentBlock();
    }

    //necessary functions for list to be added to form
    public function insert($a_tpl)
    {
        $a_tpl->setCurrentBlock("prop_custom");
        $a_tpl->setVariable("CUSTOM_CONTENT", $this->render());
        $a_tpl->parseCurrentBlock();
    }


    public function getHiddenTitle()
    {
        return "";
    }

    public function getTitle()
    {
        return "";
    }


    public function getFormLabelFor()
    {
        return "";
    }

    public function getType()
    {
        return "";
    }

    public function getSubForm()
    {
        return "";
    }

    public function hideSubForm()
    {
        return true;
    }

    public function getAlert()
    {
        return "";
    }

    /**
    * Get Post Variable.
    *
    * @return	string	Post Variable
    */
    public function getFieldId()
    {
    }

    public function setParentForm($a_parentform)
    {
        $this->setParent($a_parentform);
    }

    public function setParent($a_val)
    {
    }

    public function getInfo()
    {
        return "";
    }

    public function getRequired()
    {
        return "";
    }
}
