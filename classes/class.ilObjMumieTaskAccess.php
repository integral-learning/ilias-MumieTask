<?php

class ilObjMumieTaskAccess extends ilObjectPluginAccess
{

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
    public function _checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id = "")
    {
        global $ilUser, $ilAccess, $ilCtrl, $DIC, $lng;
        if (!isset($a_cmd) || trim($a_cmd) === '') {
            $a_cmd = $ilCtrl->getCmd();
        }

        if ($a_user_id == "") {
            $a_user_id = $ilUser->getId();
        }

        $rbacsystem = $DIC['rbacsystem'];
        switch ($a_cmd) {
            case "editProperties":
                return $ilAccess->checkAccess("write", "", $a_ref_id);
            case 'createObject':
                return $ilAccess->checkAccess("write", "", $a_ref_id);
            case "submitMumieTask":
                return $ilAccess->checkAccess("write", "", $a_ref_id);
            case 'cancelServer':
                return $ilAccess->checkAccess("write", "", $a_ref_id);
            case 'cancelCreate':
                return $ilAccess->checkAccess("write", "", $a_ref_id);
            case 'addServer':
                return $ilAccess->checkAccess("write", "", $a_ref_id);
            case 'submitServer':
                return $ilAccess->checkAccess("write", "", $a_ref_id);
            case 'editLPSettings':
                return $ilAccess->checkAccess("write", "", $a_ref_id);
            case 'submitLPSettings':
                return $ilAccess->checkAccess("write", "", $a_ref_id);
            case "forceGradeUpdate":
                return $ilAccess->checkAccess("write", "", $a_ref_id);
            case "viewContent":
                return $ilAccess->checkAccess("read", "", $a_ref_id);
            case "displayLearningProgress":
                return $ilAccess->checkAccess("read", "", $a_ref_id);
            case 'editAvailabilitySettings':
                return $ilAccess->checkAccess("write", "", $a_ref_id);
            case 'submitAvailabilitySettings':
                return $ilAccess->checkAccess("write", "", $a_ref_id);
        }

        $rbacsystem = $DIC['rbacsystem'];
        $ilAccess = $DIC['ilAccess'];

        switch ($a_permission) {
            case "read":
            case "visible":
                if (!self::_lookupOnline($a_obj_id)
                    && (!$rbacsystem->checkAccessOfUser($a_user_id, 'write', $a_ref_id))
                ) {
                    $ilAccess->addInfoItem(IL_NO_OBJECT_ACCESS, $lng->txt("offline"));

                    return false;
                }
                break;
        }
        return true;
    }

    public static function _lookupOnline($objId)
    {
        global $ilDB;

        $query = "SELECT online FROM xmum_mumie_task where id = " . $ilDB->quote($objId, 'integer');

        if ($row = $ilDB->fetchAssoc($ilDB->query($query))) {
            return $row["online"] == 1;
        }
        return false;
    }
}
