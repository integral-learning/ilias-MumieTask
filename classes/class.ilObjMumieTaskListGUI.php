<?php
/**
 * MumieTask plugin
 *
 * @copyright   2019 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use ILIAS\BackgroundTasks\Task;

include_once "./Services/Repository/classes/class.ilObjectPluginListGUI.php";

class ilObjMumieTaskListGUI extends ilObjectPluginListGUI
{
    public function initType()
    {
        $this->setType('xmum');
    }

    public function getGuiClass()
    {
        return 'ilObjMumieTaskGUI';
    }

    public function initCommands()
    {
        global $lng, $ctrl;
        include_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskLPStatus.php');
        //Very hacky solution to update all grades for MumieTasks that are direct children of an ilContainer (e.g. Course)
        try {
            ilMumieTaskLPStatus::updateGradesForIlContainer($_GET["ref_id"]);
        } catch (Exception $e) {
            ilLoggerFactory::getLogger('xmum')->info("Error when updating MUMIE grades:");
            ilLoggerFactory::getLogger('xmum')->info($e);
        }
        return array(
            array(
                "permission" => "read",
                "cmd" => "viewContent",
                "default" => true),
            array(
                "permission" => "write",
                "cmd" => "editProperties",
                "txt" => $lng->txt('rep_robj_xmum_edit_task'),
                "default" => false),
        );
    }

    public function insertDescription()
    {
        global $ilUser, $tpl, $lng;
        if ($this->getSubstitutionStatus()) {
            $this->insertSubstitutions();
            if (!$this->substitutions->isDescriptionEnabled()) {
                return true;
            }
        }
        include_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskGradeSync.php');
        try {
            $deadline = date('d.m.Y - H:i', ilMumieTaskGradeSync::getDueDateForUser($ilUser->getId(), $this->obj_id));
            $task = ilMumieTaskGradeSync::getMumieTaskFromId($this->obj_id);
            $tpl->addCss("./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/templates/mumie.css");
            if (!empty($task->getDescription())) {
                $description_text = '<span class = "mumie-deadline-text">' . $lng->txt('rep_robj_xmum_frm_grade_overview_list_deadline'). ": " . $deadline . "</span><br>" .
                $task->getDescription();
            } else {
                $description_text =  '<span class = "mumie-deadline-text">' . $lng->txt('rep_robj_xmum_frm_grade_overview_list_deadline'). ": " . $deadline . "</span>";
            }
            $this->tpl->setVariable("TXT_DESC", $description_text);
        } catch (Exception $e) {
            ilLoggerFactory::getLogger('xmum')->info("Error when updating MUMIE grades:");
            ilLoggerFactory::getLogger('xmum')->info($e);
            $this->tpl->setVariable("TXT_DESC", "");
        }

        $this->tpl->setCurrentBlock("item_description");
        $this->tpl->parseCurrentBlock();
    }

    /**
     * Insert an offline warning into the description field in list view, if MumieTask is not set to online
     *
     * @access public
     * @param
     *
     */
    public function getProperties()
    {
        global $lng;

        $this->plugin->includeClass("class.ilObjMumieTaskAccess.php");
        if (!ilObjMumieTaskAccess::_lookupOnline($this->obj_id)) {
            $props[] = array("alert" => true, "property" => $lng->txt("status"),
                "value" => $lng->txt("offline"));
        }
        return $props ? $props : array();
    }
}
