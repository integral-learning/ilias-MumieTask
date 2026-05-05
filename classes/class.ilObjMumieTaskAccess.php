<?php

/**
 * MumieTask plugin
 *
 * @copyright   2022 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

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
    public function _checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id = ""): bool
    {
        global $DIC;
        $access = $DIC->access();

        if (!isset($a_cmd) || trim($a_cmd) === '') {
            $a_cmd = $DIC->ctrl()->getCmd();
        }

        if ($a_user_id == "") {
            $a_user_id = $DIC->user()->getId();
        }

        switch ($a_cmd) {
            case "editProperties":
                return $access->checkAccess("write", "", $a_ref_id);
            case 'createObject':
                return $access->checkAccess("write", "", $a_ref_id);
            case "submitMumieTask":
                return $access->checkAccess("write", "", $a_ref_id);
            case 'cancelServer':
                return $access->checkAccess("write", "", $a_ref_id);
            case 'cancelCreate':
                return $access->checkAccess("write", "", $a_ref_id);
            case 'addServer':
                return $access->checkAccess("write", "", $a_ref_id);
            case 'submitServer':
                return $access->checkAccess("write", "", $a_ref_id);
            case 'editLPSettings':
                return $access->checkAccess("write", "", $a_ref_id);
            case 'submitLPSettings':
                return $access->checkAccess("write", "", $a_ref_id);
            case "forceGradeUpdate":
                return $access->checkAccess("write", "", $a_ref_id);
            case "viewContent":
                return $access->checkAccess("read", "", $a_ref_id);
            case "displayLearningProgress":
                return $access->checkAccess("read", "", $a_ref_id);
            case 'editAvailabilitySettings':
                return $access->checkAccess("write", "", $a_ref_id);
            case 'submitAvailabilitySettings':
                return $access->checkAccess("write", "", $a_ref_id);
            case "displayGradeOverviewPage":
                return $access->checkAccess("write", "", $a_ref_id);
            case "displayGradeList":
                return $access->checkAccess("write", "", $a_ref_id);
            case "gradeOverride":
                return $access->checkAccess("write", "", $a_ref_id);
        }

        $rbacsystem = $DIC['rbacsystem'];
        $ilAccess = $DIC['ilAccess'];

        switch ($a_permission) {
            case "read":
            case "visible":
                if (!$rbacsystem->checkAccessOfUser($a_user_id, 'write', $a_ref_id)) {
                    if (!self::_lookupOnline($a_obj_id)) {
                        $ilAccess->addInfoItem(ilAccessInfo::IL_NO_OBJECT_ACCESS, $DIC->language()->txt("offline"));
                        return false;
                    }
                }
                break;
        }
        return true;
    }


    /**
     * Check whether a MUMIE task has been set to be visible and usable by users with reading permissions
     */
    public static function _lookupOnline($objId)
    {
        global $DIC;
        $db = $DIC->database();

        $query = "SELECT online FROM xmum_mumie_task where id = " . $db->quote($objId, 'integer');

        if ($row = $db->fetchAssoc($db->query($query))) {
            return $row["online"] == 1;
        }
        return false;
    }
}
