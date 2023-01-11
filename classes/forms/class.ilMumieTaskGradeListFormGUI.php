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
class ilMumieTaskGradeListFormGUI extends ilPropertyFormGUI
{
    private $parentObj;
    public function __construct()
    {
        parent::__construct();
    }

    public function setFields($parentObj)
    {
        $this->parentObj = $parentObj;

        require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskUserServer.php');
        $this->setTitle(ilMumieTaskUserServer::getFirstName($_GET['user_id']) . " " . ilMumieTaskUserServer::getLastName($_GET['user_id']));
        $this->setCurentGradeInfo();

        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskGradeListGUI.php');
        $gradelist = new ilMumieTaskGradeListGUI($parentObj);
        $gradelist->init();
        $this->addItem($gradelist);
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
        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskDateOverrideService.php');
        if (ilMumieTaskDateOverrideService::wasDueDateOverriden($_GET["user_id"], $this->parentObj->object) && $this->parentObj->object->getActivationLimited()) {
            $deadline = date('d.m.Y - H:i', ilMumieTaskDateOverrideService::getOverridenDueDate($_GET["user_id"], $this->parentObj->object));
            ilUtil::sendInfo(
                "<b>" . $lng->txt('rep_robj_xmum_frm_grade_overview_list_used_grade') . "</b> " . $grade["mark"]. " <br> " .
                "<b>" . $lng->txt('rep_robj_xmum_frm_user_overview_list_extended_deadline') . ":</b> " . $deadline
            );
            if (empty($grade["mark"])) {
                ilUtil::sendInfo(
                    "<b>" . $lng->txt('rep_robj_xmum_frm_grade_overview_list_used_grade') . "</b> " . $lng->txt('rep_robj_xmum_frm_grade_overview_no_current_grade') . " <br> " .
                    "<b>" . $lng->txt('rep_robj_xmum_frm_user_overview_list_extended_deadline') . ":</b> " . $deadline
                );
            }
        } else {
            ilUtil::sendInfo("<b>" . $lng->txt('rep_robj_xmum_frm_grade_overview_list_used_grade') . "</b> " . $grade["mark"]);
            if (empty($grade["mark"])) {
                ilUtil::sendInfo(
                    "<b>" . $lng->txt('rep_robj_xmum_frm_grade_overview_list_used_grade') . "</b> " . $lng->txt('rep_robj_xmum_frm_grade_overview_no_current_grade') . " <br> "
                );
            }
        }
    }

    public function getHTML()
    {
        $html = parent::getHTML();
        return str_replace("ilTableOuter", "mumie-user-table", $html);
    }
}
