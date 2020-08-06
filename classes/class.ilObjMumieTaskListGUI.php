<?php
/**
 * MumieTask plugin
 *
 * @copyright   2019 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
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
