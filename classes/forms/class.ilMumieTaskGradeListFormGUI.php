<?php
/**
 * MumieTask plugin
 *
 * @copyright   2022 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Vasilije Nedeljkovic(vasilije.nedeljkovic@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/templates/class.ilMumieTaskTemplateEngine.php');
/**
 * This form is used to display all submissions a user has gotten for a given MumieTask
 */
class ilMumieTaskGradeListFormGUI extends ilPropertyFormGUI
{
    private $user_id;
    private $parent_gui;
    private $mumie_task;
    public function __construct($parent_gui, $user_id)
    {
        parent::__construct();
        $this->parent_gui = $parent_gui;
        $this->user_id = $user_id;
        $this->mumie_task = $parent_gui->object;
    }

    public function setFields()
    {
        require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskUserService.php');
        $this->setTitle(ilMumieTaskUserService::getFullName($this->user_id));
        $this->setInfoBox();

        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskGradeListGUI.php');
        $grade_list = new ilMumieTaskGradeListGUI($this->parent_gui);
        $grade_list->init();
        $this->addItem($grade_list);

        require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/forms/class.ilMumieTaskFormButtonGUI.php");
        $remove_grade_override_button = new ilMumieTaskFormButtonGUI("", "xmum_btn_remove_grade_override");
        $remove_grade_override_button->setButtonLabel($this->lng->txt('rep_robj_xmum_btn_remove_grade_override'));
        $this->ctrl->setParameterByClass('ilObjMumieTaskGUI', 'user_id', $this->user_id);
        $remove_grade_override_button->setLink($this->ctrl->getLinkTargetByClass(array('ilObjMumieTaskGUI'), 'deleteGradeOverride'));
        $this->addItem($remove_grade_override_button);
    }

    private function setInfoBox()
    {
        global $lng;
        $template = ilMumieTaskTemplateEngine::getTemplate("Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/templates/GradeList/tpl.grade-list-info-box.html");
        $template->setVariable('STUDENT_NAME', $lng->txt('rep_robj_xmum_student_name'));
        $template->setVariable('STUDENT_NAME_VALUE', ilMumieTaskUserService::getFullName($this->user_id));
        $template->setVariable('GENERAL_DEADLINE', $lng->txt('rep_robj_xmum_frm_user_overview_list_general_deadline'));
        $template->setVariable('GENERAL_DEADLINE_VALUE', $this->getDeadlineInformation());
        $template->setVariable('DEADLINE_EXTENSION', $lng->txt('rep_robj_xmum_frm_user_overview_list_extended_deadline'));
        $template->setVariable('DEADLINE_EXTENSION_VALUE', $this->getDeadlineExtensionInformation());
        $template->setVariable('CURRENT_GRADE', $lng->txt('rep_robj_xmum_frm_grade_overview_list_used_grade'));
        $template->setVariable('CURRENT_GRADE_VALUE', $this->getCurrentGradeInformation());
        $template->setVariable('GRADE_OVERVIEW_DESC', $lng->txt('rep_robj_xmum_grade_override_desc'));
        ilUtil::sendInfo($template->get());
    }

    private function getDeadlineInformation()
    {
        if($this->mumie_task->hasDeadline())
        {
            return $this->mumie_task->getDeadlineDateTime();
        }
        return ilMumieTaskTemplateEngine::EMPTY_CELL;

    }
    private function getDeadlineExtensionInformation(): string
    {
        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/deadlines/extension/class.ilMumieTaskDeadlineExtensionService.php');
        if (ilMumieTaskDeadlineExtensionService::hasDeadlineExtension($this->user_id, $this->mumie_task) && $this->mumie_task->hasDeadline())
        {
            return ilMumieTaskDeadlineExtensionService::getDeadlineExtensionDate($this->user_id,
                $this->parent_gui->object);
        }
        return ilMumieTaskTemplateEngine::EMPTY_CELL;
    }

    private function getCurrentGradeInformation(): string
    {
        require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskLPStatus.php');
        $mumie_task = $this->parent_gui->object;
        $grade = ilMumieTaskLPStatus::getCurrentGradeForUser($this->user_id, $mumie_task);
        if (is_null($grade))
        {
            return ilMumieTaskTemplateEngine::EMPTY_CELL;
        }
        if (ilMumieTaskGradeOverrideService::wasGradeOverridden($this->user_id, $mumie_task))
        {
            $template = ilMumieTaskTemplateEngine::getTemplate("Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/templates/GradeOverview/tpl.overridden-grade-cell-html.html");
            $template->setVariable("VAL_GRADE", $grade->getPercentileScore());
            return $template->get();
        }
        return $grade->getPercentileScore();
    }

    public function getHTML()
    {
        $html = parent::getHTML();
        return str_replace("ilTableOuter", "mumie-user-table", $html);
    }
}
