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
        $description = $lng->txt('rep_robj_xmum_grade_override_desc');
        $template = ilMumieTaskTemplateEngine::getStudentGradingInfoboxTemplate($this->mumie_task, $this->user_id, $description);
        ilUtil::sendInfo($template->get());
    }

    public function getHTML() : string
    {
        $html = parent::getHTML();
        return str_replace("ilTableOuter", "mumie-user-table", $html);
    }
}
