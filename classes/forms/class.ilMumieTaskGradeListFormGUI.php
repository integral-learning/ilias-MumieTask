<?php
/**
 * MumieTask plugin
 *
 * @copyright   2022 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Vasilije Nedeljkovic(vasilije.nedeljkovic@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * This form is used to display all submissions a user has gotten for a given MumieTask
 */
class ilMumieTaskGradeListFormGUI extends ilPropertyFormGUI
{
    private $user_id;
    private $parent_gui;
    public function __construct($parent_gui, $user_id)
    {
        parent::__construct();
        $this->parent_gui = $parent_gui;
        $this->user_id = $user_id;
    }

    public function setFields()
    {
        require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskUserService.php');
        $this->setTitle(ilMumieTaskUserService::getFullName($this->user_id));
        $this->setCurrentGradeInfo();

        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskGradeListGUI.php');
        $gradelist = new ilMumieTaskGradeListGUI($this->parent_gui);
        $gradelist->init();
        $this->addItem($gradelist);
    }

    public function setCurrentGradeInfo()
    {
        require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskLPStatus.php');
        $mumie_task = $this->parent_gui->object;
        $grade = ilMumieTaskLPStatus::getCurrentGradeForUser($this->user_id, $mumie_task->getId());
        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/deadlines/extension/class.ilMumieTaskDeadlineExtensionService.php');
        if (ilMumieTaskDeadlineExtensionService::hasDeadlineExtension($this->user_id, $mumie_task) && $mumie_task->getActivationLimited()) {
            $deadline = ilMumieTaskDeadlineExtensionService::getDeadlineExtensionDate($this->user_id, $mumie_task);
            ilUtil::sendInfo(
                $this->getCurrentGradeInformation($grade) .
                $this->getDeadlineExtensionInformation($deadline)
            );
        } else {
            ilUtil::sendInfo($this->getCurrentGradeInformation($grade));
        }
    }

    private function getDeadlineExtensionInformation(ilMumieTaskDateTime $deadline_extension_date): string
    {
        global $lng;
        return "<b>" . $lng->txt('rep_robj_xmum_frm_user_overview_list_extended_deadline') . ":</b> " . $deadline_extension_date;
    }

    private function getCurrentGradeInformation($grade): string
    {
        global $lng;
        $grade_info = is_null($grade) ? $lng->txt('rep_robj_xmum_frm_grade_overview_no_current_grade') : $grade;
        return "<b>" . $lng->txt('rep_robj_xmum_frm_grade_overview_list_used_grade') . "</b> " . $grade_info . "<br>";
    }

    public function getHTML()
    {
        $html = parent::getHTML();
        return str_replace("ilTableOuter", "mumie-user-table", $html);
    }
}
