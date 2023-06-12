<?php
/**
 * MumieTask plugin
 *
 * @copyright   2022 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Vasilije Nedeljkovic(vasilije.nedeljkovic@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/templates/class.ilMumieTaskTemplateEngine.php');
require_once ('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/i18n/class.ilMumieTaskI18N.php');
/**
 * This form is used to display all submissions a user has gotten for a given MumieTask
 */
class ilMumieTaskGradeListFormGUI extends ilPropertyFormGUI
{
    private $user_id;
    private $parent_gui;
    private ilObjMumieTask $mumie_task;
    private ilMumieTaskI18N $i18N;
    public function __construct($parent_gui, $user_id, ilObjMumieTask $mumie_task)
    {
        parent::__construct();
        $this->parent_gui = $parent_gui;
        $this->user_id = $user_id;
        $this->mumie_task = $mumie_task;
        $this->i18N = new ilMumieTaskI18N();
    }

    public function setFields()
    {
        require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskUserService.php');
        $this->setTitle(ilMumieTaskUserService::getFullName($this->user_id));
        $this->setInfoBox();

        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskGradeListGUI.php');
        $grade_list = new ilMumieTaskGradeListGUI($this->parent_gui, $this->mumie_task);
        $grade_list->init();
        $this->addItem($grade_list);

        require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/forms/class.ilMumieTaskFormButtonGUI.php");
        $remove_grade_override_button = new ilMumieTaskFormButtonGUI("", "xmum_btn_remove_grade_override");
        $remove_grade_override_button->setButtonLabel($this->i18N->txt('btn_remove_grade_override'));
        $this->ctrl->setParameterByClass('ilObjMumieTaskGUI', 'user_id', $this->user_id);
        $remove_grade_override_button->setLink($this->ctrl->getLinkTargetByClass(array('ilObjMumieTaskGUI'), 'deleteGradeOverride'));
        $this->addItem($remove_grade_override_button);
    }

    private function setInfoBox()
    {
        global $DIC;
        $description = $this->i18N->txt('grade_override_desc');
        $template = ilMumieTaskTemplateEngine::getStudentGradingInfoboxTemplate($this->mumie_task, $this->user_id, $description);
        $DIC->ui()->mainTemplate()->setOnScreenMessage('info', $template->get());
    }

    public function getHTML(): string
    {
        $html = parent::getHTML();
        return str_replace("ilTableOuter", "mumie-user-table", $html);
    }
}
