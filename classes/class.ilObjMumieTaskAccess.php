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
}

?>