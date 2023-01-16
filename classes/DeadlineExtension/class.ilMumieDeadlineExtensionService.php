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
 * This service is used to manage deadline extensions for students
 */
class ilMumieDeadlineExtensionService
{
    const DEADLINE_EXTENSION_TABLE = "xmum_deadline_ext";
    const TASK_ID = 'task_id';
    const USER_ID = 'usr_id';
    const DATE = 'date';

    public static function upsertDeadlineExtension($mumie_task, $date_time_input, $user_id)
    {
        $deadline_extension = new ilMumieDeadlineExtension(strtotime($date_time_input), $user_id, $mumie_task->getId());
        if (!self::hasDeadlineExtension($user_id, $mumie_task)) {
            self::insertDeadlineExtension($deadline_extension);
        } else {
            self::updateDeadlineExtension($deadline_extension);
        }
        self::sendUpdateSuccessMessage($deadline_extension);
    }

    private static function insertDeadlineExtension(ilMumieDeadlineExtension $deadline_extension)
    {
        global $ilDB;
        $ilDB->insert(
            self::DEADLINE_EXTENSION_TABLE,
            array(
                self::TASK_ID => array('integer', $deadline_extension->getTaskId()),
                self::USER_ID => array('text', $deadline_extension->getUserId()),
                self::DATE => array('integer', $deadline_extension->getDate()->getUnixTime())
            )
        );
    }

    private static function updateDeadlineExtension(ilMumieDeadlineExtension $deadline_extension)
    {
        global $ilDB;
        $ilDB->update(
            self::DEADLINE_EXTENSION_TABLE,
            array(
                self::DATE => array('integer', $deadline_extension->getDate()->getUnixTime())
            ),
            array(
                self::TASK_ID => array('integer', $deadline_extension->getTaskId()),
                self::USER_ID => array('text', $deadline_extension->getUserId()),
            )
        );
    }

    public static function hasDeadlineExtension($user_id, $task): bool
    {
        return !is_null(self::getDeadlineExtension($user_id, $task)->getUserId());
    }

    public static function getDeadlineExtensionDate($user_id, $task): ilMumieTaskDateTime
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
        //TODO: Delete when MT is deleted
        $ilDB->manipulate("DELETE FROM xmum_deadline_ext WHERE task_id = " . $ilDB->quote($task->getId(), 'integer'));
    }

    private static function sendUpdateSuccessMessage(ilMumieDeadlineExtension $deadline_extension)
    {
        global $lng;
        require_once ('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskUserService.php');
        ilUtil::sendSuccess(
            sprintf(
                $lng->txt('rep_robj_xmum_frm_deadline_extension_successfull_update'),
                ilMumieTaskUserService::getFullName($deadline_extension->getUserId())
            )
        );
    }
}
