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
    private $user_id;
    private $parent_gui;
    public function __construct($parent_gui, $user_id)
    {
        parent::__construct();
        $this->parent_gui = $parent_gui;
        $this->user_id = $user_id;
    }

    public function setFields()
    {
        require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskUserService.php');
        $this->setTitle(ilMumieTaskUserService::getFirstName($this->user_id) . " " . ilMumieTaskUserService::getLastName($this->user_id));
        $this->setCurentGradeInfo();

        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskGradeListGUI.php');
        $gradelist = new ilMumieTaskGradeListGUI($this->parent_gui);
        $gradelist->init();
        $this->addItem($gradelist);
    }

    public function setCurentGradeInfo()
    {
        global $ilDB, $lng;
        $mumie_task = $this->parent_gui->object;
        $result = $ilDB->query(
            "SELECT mark 
            FROM ut_lp_marks 
            WHERE usr_id = " . $ilDB->quote($this->user_id, "integer") .
            " AND " .
            "obj_id = " . $ilDB->quote($mumie_task->getId(), "integer")
        );
        $grade = $ilDB->fetchAssoc($result);
        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/DeadlineExtension/class.ilMumieDeadlineExtensionService.php');
        if (ilMumieDeadlineExtensionService::hasDeadlineExtension($this->user_id, $mumie_task) && $mumie_task->getActivationLimited()) {
            $deadline = ilMumieDeadlineExtensionService::getDeadlineExtensionDate($this->user_id, $mumie_task)->get();
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
