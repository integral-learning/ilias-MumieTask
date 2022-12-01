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
    public function __construct()
    {
        parent::__construct();
    }

    public function setFields($parentObj)
    {
        global $lng;
        require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskGradeSync.php');
        $this->setTitle(ilMumieTaskGradeSync::getFirstName($_GET["user_id"]) . " " . ilMumieTaskGradeSync::getLastName($_GET["user_id"]));

        require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskLPStatus.php');
        $grade = ilMumieTaskLPStatus::getCurrentGradeForUser($_GET["user_id"], $parentObj->object->getId());
        ilUtil::sendInfo($lng->txt('rep_robj_xmum_frm_grade_overview_list_used_grade') . " " . $grade);

        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskGradeListGUI.php');
        $gradeList = new ilMumieTaskGradeListGUI($parentObj);
        $gradeList->init($parentObj);
        $this->addItem($gradeList);
    }

    public function getHTML()
    {
        $html = parent::getHTML();
        return str_replace("ilTableOuter", "mumie-user-table", $html);
    }
}
