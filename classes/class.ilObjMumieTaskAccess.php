<?php

class ilObjMumieTaskAccess extends ilObjectPluginAccess {

    /**
     * Checks wether a user may invoke a command or not
     * (this method is called by ilAccessHandler::checkAccess)
     *
     * Please do not check any preconditions handled by
     * ilConditionHandler here. Also don't do usual RBAC checks.
     *
     * @param    string    $a_cmd        command (not permission!)
     * @param    string    $a_permission    permission
     * @param    int        $a_ref_id        reference id
     * @param    int        $a_obj_id        object id
     * @param    int        $a_user_id        user id (default is current user)
     *
     * @return    boolean        true, if everything is ok
     */
    function _checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id = "") {
        global $ilUser, $ilAccess;
        return true; /*

    if ($a_user_id == "") {
    $a_user_id = $ilUser->getId();
    }

    switch ($a_permission) {
    case "read":
    if (!ilObjExampleAccess::checkOnline($a_obj_id) &&
    !$ilAccess->checkAccessOfUser($a_user_id, "write", "", $a_ref_id)) {
    return false;
    }
    break;
    }

    return true;*/
    }

    /**
     * Check online status of example object
     */
    static function checkOnline($a_id) {
        global $ilDB;

        return true;
        /*
    $set = $ilDB->query("SELECT is_online FROM rep_robj_xexo_data " .
    " WHERE id = " . $ilDB->quote($a_id, "integer")
    );
    $rec = $ilDB->fetchAssoc($set);
    return (boolean) $rec["is_online"];
     */
    }
    static function _getCommands() {
        $commands = array
            (
            array("permission" => "write", "cmd" => "questionsTabGateway", "lang_var" => "tst_edit_questions"),
            array("permission" => "write", "cmd" => "ilObjTestSettingsGeneralGUI::showForm", "lang_var" => "settings"),
            array("permission" => "read", "cmd" => "infoScreen", "lang_var" => "tst_run",
                "default" => true),
            //array("permission" => "write", "cmd" => "", "lang_var" => "edit"),
            array("permission" => "tst_statistics", "cmd" => "outEvaluation", "lang_var" => "tst_statistical_evaluation"),
            array("permission" => "read", "cmd" => "userResultsGateway", "lang_var" => "tst_test_results"),
        );

        return $commands;
    }
}

?>