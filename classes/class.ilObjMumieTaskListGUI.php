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
        global $lng;
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