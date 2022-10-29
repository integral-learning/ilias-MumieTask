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
    public function __construct()
    {
        parent::__construct();
    }

    public function setFields($parentObj)
    {
        global $lng, $ilDB;
        $this->parentObj = $parentObj;

        $result = $ilDB->query("SELECT firstname, lastname FROM usr_data WHERE usr_id = ". $ilDB->quote($_GET['user_id'], "integer"));
        $names = $ilDB->fetchAssoc($result);
        $this->setTitle($names["firstname"] . " " . $names["lastname"]);
        $this->setCurentGradeInfo();

        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskGradeListGUI.php');
        $userList = new ilMumieTaskGradeListGUI($parentObj);
        $this->addItem($userList);
    }

    public function setCurentGradeInfo()
    {
        global $ilDB, $lng;
        $result = $ilDB->query(
            "SELECT mark 
            FROM ut_lp_marks 
            WHERE usr_id = " . $ilDB->quote($_GET['user_id'], "integer") .
            " AND " .
            "obj_id = " . $ilDB->quote($this->parentObj->object->getId(), "integer")
        );
        $grade = $ilDB->fetchAssoc($result);

        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskGradeSync.php');
        if (ilMumieTaskGradeSync::wasDueDateOverriden($_GET["user_id"], $this->parentObj->object)) {
            $abgabefrist = date('Y-m-d H:i', ilMumieTaskGradeSync::getOverridenDate($_GET["user_id"], $this->parentObj->object));
            ilUtil::sendInfo(
                "<b>" . $lng->txt('rep_robj_xmum_frm_list_used_grade') . "</b> " . $grade["mark"]. " <br> " .
                "<b>" . $lng->txt('rep_robj_xmum_frm_list_deadline') . ":</b> " . substr($abgabefrist, 8, 2) . "." . substr($abgabefrist, 5, 2) . "." . substr($abgabefrist, 0, 4) . " - " . substr($abgabefrist, 11, 5)
            );
        } else {
            ilUtil::sendInfo("<b>" . $lng->txt('rep_robj_xmum_frm_list_used_grade') . "</b> " . $grade["mark"]);
        }
    }
}
