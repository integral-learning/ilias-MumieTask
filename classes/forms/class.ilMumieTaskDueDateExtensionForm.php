<?php
/**
 * MumieTask plugin
 *
 * @copyright   2022 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Vasilije Nedeljkovic(vasilije.nedeljkovic@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * This form is used to edit the Learning Progress settings of MumieTasks
 */
class ilMumieTaskDueDateExtensionForm extends ilPropertyFormGUI
{
    private $date_input;
    private $mumie_task;

    public function __construct($mumie_task)
    {
        parent::__construct();
        $this->mumie_task = $mumie_task;
    }

    public function setFields()
    {
        global $lng;
        $this->ctrl->setParameterByClass('ilObjMumieTaskGUI', 'user_id', $_GET["user_id"]);
        $this->date_input = new ilDateTimeInputGUI($lng->txt('rep_robj_xmum_frm_deadline_extension_new_deadline'), 'dateTime');
        $this->date_input->setShowTime(true);
        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskDateOverrideService.php');
        if(ilMumieTaskDateOverrideService::wasDueDateOverriden($_GET["user_id"], $this->mumie_task)){
            $date_time = new ilDateTime(ilMumieTaskDateOverrideService::getOverridenDueDate($_GET["user_id"], $this->mumie_task), IL_CAL_UNIX);
        } else {
            $date_time = new ilDateTime($this->mumie_task->getActivationEndingTime(), IL_CAL_UNIX);
        }
        $this->date_input->setDate($date_time);
        $this->addItem($this->date_input);
    }

    public function checkInput()
    {
        $ok = parent::checkInput();
        return $ok;
    }
}