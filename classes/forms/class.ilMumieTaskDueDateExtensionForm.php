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

    public function __construct()
    {
        parent::__construct();
    }

    public function setFields($parentObj)
    {
        global $lng;
        $this->ctrl->setParameterByClass('ilObjMumieTaskGUI', 'user_id', $_GET["user_id"]);
        $this->date_input = new ilDateTimeInputGUI($lng->txt('rep_robj_xmum_frm_deadline_extension_new_deadline'), 'dateTime');
        $this->date_input->setShowTime(true);
        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskGradeSync.php');
        if(ilMumieTaskGradeSync::wasDueDateOverriden($_GET["user_id"], $parentObj->object)){
            $date_time = new ilDateTime(ilMumieTaskGradeSync::getOverridenDueDate($_GET["user_id"], $parentObj->object), IL_CAL_UNIX);
        } else {
            $date_time = new ilDateTime($parentObj->object->getActivationEndingTime(), IL_CAL_UNIX);
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