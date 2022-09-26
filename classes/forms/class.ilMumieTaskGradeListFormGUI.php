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
class ilMumieTaskGradeListFormGUI extends ilPropertyFormGUI
{
    private $parentObj;
    private $textField;
    public function __construct()
    {
        parent::__construct();
    }

    public function setFields($parentObj)
    {
        global $lng, $ilDB;
        $this->parentObj = $parentObj;

        $result = $ilDB->query("SELECT firstname, lastname FROM usr_data WHERE usr_id = ". $ilDB->quote($_GET['member_id'], "integer"));
        $names = $ilDB->fetchAssoc($result);
        $this->setTitle($names["firstname"] . " " . $names["lastname"]);
        $textField = new ilTextInputGUI($lng->txt('rep_robj_xmum_frm_list_used_grade'));
        $textField->setDisabled(true);
        $this->textField = $textField;
        $this->addItem($this->textField);
        $this->updateTextField();

        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskGradeListGUI.php');
        $userList = new ilMumieTaskGradeListGUI($parentObj, $this);
        $this->addItem($userList);
    }

    public function updateTextField()
    {
        global $ilDB;
        $result = $ilDB->query("SELECT mark 
            FROM ut_lp_marks 
            WHERE usr_id = " . $ilDB->quote($_GET['member_id'], "integer") .
            " AND " .
            "obj_id = " . $ilDB->quote($this->parentObj->object->getId() , "integer")
            );
        $grade = $ilDB->fetchAssoc($result);
        $this->textField->setValue($grade["mark"]);
    }
   
}                                                                           
