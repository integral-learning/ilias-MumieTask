<?php
/**
 * MumieTask plugin
 *
 * @copyright   2022 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Vasilije Nedeljkovic(vasilije.nedeljkovic@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ilMumieTaskDueDateExtension extends ilPropertyFormGUI
{
    public static function upsertOverridenDate($parentObj, $date_time_input)
    {
        global $ilDB, $lng;
        $hashed_user = ilMumieTaskIdHashingService::getHashForUser($_GET["user_id"], $parentObj->object);
        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskGradeSync.php');
        if (!ilMumieTaskGradeSync::wasDueDateOverriden($_GET["user_id"], $parentObj->object)) {
            $ilDB->insert(
                "xmum_date_override",
                array(
                    'task_id' => array('integer', $parentObj->object->getId()),
                    'usr_id' => array('text', $hashed_user),
                    'new_date' => array('integer', strtotime($date_time_input))
                )
            );
        } else {
            $ilDB->update(
                "xmum_date_override",
                array(
                    'new_date' => array('integer', strtotime($date_time_input))
                ),
                array(
                    'task_id' => array('integer', $parentObj->object->getId()),
                    'usr_id' => array('text', $hashed_user),
                )
            );
        }
        $result = $ilDB->query("SELECT firstname, lastname FROM usr_data WHERE usr_id = ". $ilDB->quote($_GET['user_id'], "integer"));
        $names = $ilDB->fetchAssoc($result);
        ilUtil::sendSuccess($lng->txt('rep_robj_xmum_frm_deadline_extension_successfull_date_update') . " " . $names["firstname"] . ",  " . $names["lastname"] . " " .  $lng->txt('rep_robj_xmum_frm_grade_overview_list_to') . " " . substr($date_time_input, 0, 10) . " - " . substr($date_time_input, 11, 5));
    }
}