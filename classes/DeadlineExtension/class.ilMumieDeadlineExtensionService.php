<?php

/**
 * MumieTask plugin
 *
 * @copyright   2022 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Vasilije Nedeljkovic(vasilije.nedeljkovic@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/DeadlineExtension/class.ilMumieDeadlineExtension.php');
/**
 * This class provides services for overriding grades
 */

class ilMumieDeadlineExtensionService
{
    const DEADLINE_EXTENSION_TABLE = "xmum_deadline_ext";
    const TASK_ID = 'task_id';
    const USER_ID = 'usr_id';
    const DATE = 'date';

    public static function upsertDeadlineExtension($mumie_task, $date_time_input, $user_id)
    {
        global $ilDB, $lng;
        $deadline_extension = new ilMumieDeadlineExtension(strtotime($date_time_input), $user_id, $mumie_task->getId());
        if (!self::hasDeadlineExtension($user_id, $mumie_task)) {
            self::insertDeadlineExtension($deadline_extension);
        } else {
            self::updateDeadlineExtension($deadline_extension);
        }
        $result = $ilDB->query("SELECT firstname, lastname FROM usr_data WHERE usr_id = ". $ilDB->quote($user_id, "integer"));
        $names = $ilDB->fetchAssoc($result);
        ilUtil::sendSuccess($lng->txt('rep_robj_xmum_frm_deadline_extension_successfull_date_update') . " " . $names["firstname"] . ",  " . $names["lastname"] . " " .  $lng->txt('rep_robj_xmum_frm_grade_overview_list_to') . " " . substr($date_time_input, 0, 10) . " - " . substr($date_time_input, 11, 5));
    }

    private static function insertDeadlineExtension(ilMumieDeadlineExtension $deadline_extension)
    {
        global $ilDB;
        $ilDB->insert(
            self::DEADLINE_EXTENSION_TABLE,
            array(
                self::TASK_ID => array('integer', $deadline_extension->getTaskId()),
                self::USER_ID => array('text', $deadline_extension->getUserId()),
                self::DATE => array('integer', $deadline_extension->getDate())
            )
        );
    }

    private static function updateDeadlineExtension(ilMumieDeadlineExtension $deadline_extension)
    {
        global $ilDB;
        $ilDB->update(
            self::DEADLINE_EXTENSION_TABLE,
            array(
                self::DATE => array('integer', $deadline_extension->getDate())
            ),
            array(
                self::TASK_ID => array('integer', $deadline_extension->getTaskId()),
                self::USER_ID => array('text', $deadline_extension->getUserId()),
            )
        );
    }

    public static function hasDeadlineExtension($user_id, $task): boolean
    {
        return !is_null(self::getDeadlineExtension($user_id, $task)->getUserId());
    }

    public static function getDeadlineExtensionDate($user_id, $task)
    {
        return self::getDeadlineExtension($user_id, $task)->getDate();
    }

    private static function getDeadlineExtension($user_id, $task): ilMumieDeadlineExtension
    {
        global $ilDB;
        $query = "SELECT *
        FROM xmum_deadline_ext
        WHERE " .
            self::TASK_ID .
            " = "
            . $ilDB->quote($task->getId(), "integer") .
            " AND " .
            self::USER_ID .
            " = " .
            $ilDB->quote($user_id, "text");
        $result = $ilDB->fetchAssoc($ilDB->query($query));
        return new ilMumieDeadlineExtension($result[self::DATE], $result[self::USER_ID], $result[self::TASK_ID]);
    }

    public static function deleteDeadlineExtensions($task)
    {
        global $ilDB;
        $ilDB->manipulate("DELETE FROM xmum_deadline_ext WHERE task_id = " . $ilDB->quote($task->getId(), 'integer'));
    }
}
