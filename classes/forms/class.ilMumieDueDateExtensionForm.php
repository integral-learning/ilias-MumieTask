<?php
/**
 * MumieTask plugin
 *
 * @copyright   2019 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * This form is used to edit the Learning Progress settings of MumieTasks
 */
class ilMumieDueDateExtensionForm extends ilPropertyFormGUI
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
        $this->date_input = new ilDateTimeInputGUI($lng->txt('rep_robj_xmum_frm_list_new_deadline'), 'dateTime');
        $this->date_input->setShowTime(true);
        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskGradeSync.php');
        if(ilMumieTaskGradeSync::wasDueDateOverriden($_GET["user_id"], $parentObj->object)){
            $date_time = new ilDateTime(ilMumieTaskGradeSync::getOverridenDate($_GET["user_id"], $parentObj->object), IL_CAL_UNIX);
        } else {
            $date_time = new ilDateTime($parentObj->object->getActivationEndingTime(), IL_CAL_UNIX);
        }
        $this->date_input->setDate($date_time);
        $this->addItem($this->date_input);
    }

    public function updateGrade($parentObj)
    {
        global $ilDB, $lng;
        $hashed_user = ilMumieTaskIdHashingService::getHashForUser($_GET["user_id"], $parentObj->object);
        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskGradeSync.php');
        if (!ilMumieTaskGradeSync::wasDueDateOverriden($_GET["user_id"], $parentObj->object)) {
            $ilDB->insert(
                "xmum_date_override",
                array(
                    'task_id' => array('integer', $parentObj->object->getId()),
                    'usr_id' => array('text', $hashed_user),
                    'new_date' => array('text', $this->getInput("dateTime"))
                )
            );
        } else {
            $ilDB->update(
                "xmum_date_override",
                array(
                    'new_date' => array('text', $this->getInput("dateTime"))
                ),
                array(
                    'task_id' => array('integer', $parentObj->object->getId()),
                    'usr_id' => array('text', $hashed_user),
                )
            );
        }
        $result = $ilDB->query("SELECT firstname, lastname FROM usr_data WHERE usr_id = ". $ilDB->quote($_GET['user_id'], "integer"));
        $names = $ilDB->fetchAssoc($result);
        ilUtil::sendSuccess($lng->txt('rep_robj_xmum_frm_list_successfull_date_update') . " " . $names["firstname"] . ",  " . $names["lastname"] . " " .  $lng->txt('rep_robj_xmum_frm_list_to') . " " . substr($this->getInput("dateTime"), 0, 10) . " - " . substr($this->getInput("dateTime"), 11, 5) );
    }

    public function checkInput()
    {
        $ok = parent::checkInput();
        return $ok;
    }
}
