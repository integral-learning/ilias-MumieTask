<?php
/**
 * MumieTask plugin
 *
 * @copyright   2022 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Vasilije Nedeljkovic(vasilije.nedeljkovic@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * This form is used to grant due date extensions for a given MumieTask
 */
class ilMumieTaskDueDateExtensionForm extends ilPropertyFormGUI
{
    private $date_input;
    private $mumie_task;
    /**
     * @var string
     */
    private $user_id;

    public function __construct($mumie_task, $user_id)
    {
        parent::__construct();
        $this->mumie_task = $mumie_task;
        $this->user_id = $user_id;
    }

    public function setFields()
    {
        global $lng;
        $this->ctrl->setParameterByClass('ilObjMumieTaskGUI', 'user_id', $this->user_id);
        $this->date_input = new ilDateTimeInputGUI($lng->txt('rep_robj_xmum_frm_deadline_extension_new_deadline'), 'dateTime');
        $this->date_input->setShowTime(true);
        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/DeadlineExtension/class.ilMumieDeadlineExtensionService.php');
        if(ilMumieDeadlineExtensionService::hasDeadlineExtension($this->user_id, $this->mumie_task)){
            $date_time = ilMumieDeadlineExtensionService::getDeadlineExtensionDate($this->user_id, $this->mumie_task);
        } else {
            $date_time = new ilDateTime($this->mumie_task->getActivationEndingTime(), IL_CAL_UNIX);
        }
        $this->date_input->setDate($date_time);
        $this->addItem($this->date_input);
    }

    public function checkInput() : bool
    {
        return parent::checkInput();
    }
}