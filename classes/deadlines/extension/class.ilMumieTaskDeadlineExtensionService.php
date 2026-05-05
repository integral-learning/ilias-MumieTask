<?php

/**
 * MumieTask plugin
 *
 * @copyright   2022 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Vasilije Nedeljkovic(vasilije.nedeljkovic@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/deadlines/extension/class.ilMumieTaskDeadlineExtension.php');
/**
 * This service is used to manage deadline extensions for students
 */
class ilMumieTaskDeadlineExtensionService
{
    public const DEADLINE_EXTENSION_TABLE = "xmum_deadline_ext";
    public const TASK_ID = 'task_id';
    public const USER_ID = 'usr_id';
    public const DATE = 'date';

    public static function hasDeadlineExtension($user_id, $task): bool
    {
        return !is_null(self::getDeadlineExtensionAssoc($user_id, $task));
    }

    public static function getDeadlineExtensionDate($user_id, $task): ilMumieTaskDateTime
    {
        return self::getDeadlineExtension($user_id, $task)->getDate();
    }

    public static function upsertDeadlineExtension($mumie_task, $date_time_input, $user_id)
    {
        $deadline_extension = new ilMumieTaskDeadlineExtension(strtotime($date_time_input), $user_id, $mumie_task->getId());
        if (!self::hasDeadlineExtension($user_id, $mumie_task)) {
            self::insertDeadlineExtension($deadline_extension);
        } else {
            self::updateDeadlineExtension($deadline_extension);
        }
        self::sendUpdateSuccessMessage($deadline_extension);
    }

    public static function deleteDeadlineExtension($mumie_task, $user_id)
    {
        global $ilDB;
        $ilDB->manipulate(
            "DELETE FROM xmum_deadline_ext WHERE task_id = " .
            $ilDB->quote($mumie_task->getId(), 'integer') .
            " AND usr_id = " .
            $ilDB->quote($user_id, 'integer')
        );
    }

    public static function deleteDeadlineExtensions($task)
    {
        global $ilDB;
        $ilDB->manipulate("DELETE FROM xmum_deadline_ext WHERE task_id = " . $ilDB->quote($task->getId(), 'integer'));
    }

    private static function insertDeadlineExtension(ilMumieTaskDeadlineExtension $deadline_extension)
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

    private static function updateDeadlineExtension(ilMumieTaskDeadlineExtension $deadline_extension)
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

    private static function getDeadlineExtension($user_id, $task): ilMumieTaskDeadlineExtension
    {
        $result = self::getDeadlineExtensionAssoc($user_id, $task);
        return new ilMumieTaskDeadlineExtension($result[self::DATE], $result[self::USER_ID], $result[self::TASK_ID]);
    }

    private static function getDeadlineExtensionAssoc($user_id, $task): ?array
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
        return $ilDB->fetchAssoc($ilDB->query($query));
    }

    private static function sendUpdateSuccessMessage(ilMumieTaskDeadlineExtension $deadline_extension)
    {
        global $DIC;
        require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskUserService.php');
        require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/i18n/class.ilMumieTaskI18N.php');
        $i18n = new ilMumieTaskI18N();
        $DIC->ui()->mainTemplate()->setOnScreenMessage(
            'success',
            sprintf(
                $i18n->txt('frm_deadline_extension_successfull_update'),
                ilMumieTaskUserService::getFullName($deadline_extension->getUserId())
            )
        );
    }
}
