<?php

/**
 * MumieTask plugin
 *
 * @copyright   2022 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Vasilije Nedeljkovic(vasilije.nedeljkovic@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/users/class.ilMumieTaskParticipantService.php');
/**
 * This GUI provides a way to list users in a MUMIE task
 */
class ilMumieTaskGradeOverviewGUI extends ilTable2GUI
{
    const EMPTY_CELL = "-";
    private $members;

    public function __construct($parentObj)
    {
        $this->setId("user" . $_GET["ref_id"]);
        parent::__construct($parentObj, 'displayUserList');
    }

    public function init($parentObj, $form)
    {
        global $lng;
        $this->setFormName('participants');
        $this->addColumn($lng->txt('rep_robj_xmum_frm_user_overview_list_name'), 'name');
        $this->addColumn($lng->txt('rep_robj_xmum_frm_user_overview_list_extended_deadline'));
        $this->addColumn($lng->txt('rep_robj_xmum_frm_list_grade'), 'note');
        $this->addColumn($lng->txt('rep_robj_xmum_frm_user_overview_list_submissions'), 'submission');
        $this->setDefaultFilterVisiblity(true);

        $members = ilMumieTaskParticipantService::filter($parentObj->object, $form->getInput("firstnamefield"),$form->getInput("lastnamefield"));

        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskLPStatus.php');
        ilMumieTaskLPStatus::updateGrades($parentObj->object);

        $this->tpl->addBlockFile(
            "TBL_CONTENT",
            "tbl_content",
            "tpl.mumie_user_list.html",
            "Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask"
        );

        foreach ($members as $user_id) {
            $this->addTableRow($user_id, $parentObj);
        }
        $this->setEnableHeader(true);
    }

    private function addTableRow($user_id, $parentObj)
    {
        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/deadlines/extension/class.ilMumieTaskDeadlineExtensionService.php');
        require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskLPStatus.php');
        require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskUserService.php');

        $this->tpl->setCurrentBlock("tbl_content");
        $this->css_row = ($this->css_row != "tblrow1")
        ? "tblrow1"
        : "tblrow2";
        $this->tpl->setVariable("CSS_ROW", $this->css_row);
        $this->ctrl->setParameterByClass('ilObjMumieTaskGUI', 'user_id', $user_id);
        $grade = ilMumieTaskLPStatus::getCurrentGradeForUser($user_id, $parentObj->object);
        $this->tpl->setVariable('DEADLINE_CELL_CONTENT', $this->getDeadlineCellContent($user_id, $parentObj->object));
        $this->tpl->setVariable('LINK_GRADE_OVERVIEW', $this->ctrl->getLinkTarget($parentObj, 'displayGradeList'));
        $this->tpl->setVariable('VAL_GRADE', $this->getGradeCellContent($grade));
        $this->tpl->setVariable('VAL_NAME', ilMumieTaskUserService::getFullName($user_id));
        $this->tpl->parseCurrentBlock();
    }

    private function getDeadlineCellContent($user_id, $mumie_task)
    {
        if (!$mumie_task->hasDeadline())
        {
            return self::EMPTY_CELL;
        }
        if (ilMumieTaskDeadlineExtensionService::hasDeadlineExtension($user_id, $mumie_task))
        {
            return $this->getDeadlineSetCellContent($user_id, $mumie_task);
        }
        return $this->getDeadlineUnsetCellContent();
    }

    private function getDeadlineUnsetCellContent()
    {
        $tpl = new ilTemplate("./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/templates/GradeOverview/tpl.deadline-cell-extension-unset.html", true, true, true, "DEFAULT", true);
        $tpl->setVariable('LINK_EDIT_DEADLINE_EXTENSION', $this->ctrl->getLinkTarget($this->parent_obj, 'displayDeadlineExtension'));
        return $tpl->get();
    }

    private function getDeadlineSetCellContent($user_id, $mumie_task)
    {
        $tpl = new ilTemplate("./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/templates/GradeOverview/tpl.deadline-cell-extension-set.html", true, true, true, "DEFAULT", true);
        $deadline = ilMumieTaskDeadlineExtensionService::getDeadlineExtensionDate($user_id, $mumie_task)->get();
        $tpl->setVariable("DEADLINE", $deadline);
        $tpl->setVariable('LINK_EDIT_DEADLINE_EXTENSION', $this->ctrl->getLinkTarget($this->parent_obj, 'displayDeadlineExtension'));
        $tpl->setVariable('LINK_DELETE_DEADLINE_EXTENSION', $this->ctrl->getLinkTarget($this->parent_obj, 'deleteDeadlineExtension'));
        return $tpl->get();
    }

    private function getGradeCellContent(?ilMumieTaskGrade $grade): string
    {
        global $lng;
        if (is_null($grade))
        {
            return self::EMPTY_CELL;
        }
        require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskGradeOverrideService.php');
        if (ilMumieTaskGradeOverrideService::wasGradeOverridden($grade->getUserId(), $grade->getMumieTask()))
        {
            $tpl = new ilTemplate("Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/templates/GradeOverview/tpl.overridden-grade-cell-html.html", true, true, true, "DEFAULT", true);
            $tpl->setVariable("VAL_GRADE", $grade->getPercentileScore());
            $tpl->setVariable("OVERRIDDEN_MOUSEOVER", $lng->txt('rep_robj_xmum_frm_user_gradeoverview_overridden_explanation'));
            return $tpl->get();
        }
        return $grade->getPercentileScore();
    }

    //All functions are necessary for the list to be implemented into a form
    public function checkInput()
    {
        return true;
    }
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
        return "";
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
