<?php
/**
 * MumieTask plugin
 *
 * @copyright   2022 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Vasilije Nedeljkovic(vasilije.nedeljkovic@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/deadlines/class.ilMumieTaskDeadlineService.php');

/**
 * This form is used to grant due date extensions for a given MumieTask
 */
class ilMumieTaskDeadlineExtensionForm extends ilPropertyFormGUI
{
    public const DEADLINE_PARAM = 'deadline_extension';
    /**
     * @var ilDateTimeInputGUI
     */
    private $deadline_input;
    /**
     * @var ilObjMumieTask
     */
    private $mumie_task;
    /**
     * @var string
     */
    private $user_id;

    public function __construct($mumie_task, $user_id)    {
        parent::__construct();
        $this->mumie_task = $mumie_task;
        $this->user_id = $user_id;
    }

    public function setFields()
    {
        global $lng;
        $this->ctrl->setParameterByClass('ilObjMumieTaskGUI', 'user_id', $this->user_id);
        $this->deadline_input = new ilDateTimeInputGUI(
            $lng->txt('rep_robj_xmum_frm_user_overview_list_extended_deadline'),
            self::DEADLINE_PARAM
        );
        $this->deadline_input->setShowTime(true);
        $deadline_date = ilMumieTaskDeadlineService::getDeadlineDateForUser($this->user_id, $this->mumie_task);
        $this->deadline_input->setDate($deadline_date);
        $this->addItem($this->deadline_input);
    }

    public function setInfoBox()
    {
        global $lng;
        require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/templates/class.ilMumieTaskTemplateEngine.php');
        $description = $lng->txt('rep_robj_xmum_deadline_extension_desc');
        $template = ilMumieTaskTemplateEngine::getStudentGradingInfoboxTemplate($this->mumie_task, $this->user_id, $description);
        ilUtil::sendInfo($template->get());
    }

    public function checkInput(): bool
    {
        global $lng;
        $ok = parent::checkInput();
        if ($this->mumie_task->getDeadline() > strtotime($this->getInput(self::DEADLINE_PARAM))) {
            $ok = false;
            $this->deadline_input->setAlert($lng->txt("rep_robj_xmum_frm_deadline_extension_before_general_deadline_error"));
        }
        return $ok;
    }
}
