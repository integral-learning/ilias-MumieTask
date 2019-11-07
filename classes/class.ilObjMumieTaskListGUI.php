<?php
include_once "./Services/Repository/classes/class.ilObjectPluginListGUI.php";

class ilObjMumieTaskListGUI extends ilObjectPluginListGUI {

    function initType() {
        $this->setType('xmum');
    }

    function getGuiClass() {
        return 'ilObjMumieTaskGUI';
    }

    function initCommands() {
        global $lng, $ctrl;
        include_once ('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskLPStatus.php');

        //Very hacky solution to update all grades for MumieTasks that are direct children of an ilContainer (e.g. Course)
        ilMumieTaskLPStatus::updateGradesForIlContainer($_GET["ref_id"]);
        return array
            (
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
}

?>