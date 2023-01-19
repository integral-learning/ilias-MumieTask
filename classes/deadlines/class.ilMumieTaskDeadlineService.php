<?php
require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/deadlines/extension/class.ilMumieTaskDeadlineExtension.php');
require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilObjMumieTask.php');
require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskObjService.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/deadlines/extension/class.ilMumieTaskDeadlineExtensionService.php');

class ilMumieTaskDeadlineService
{
    public static function getDeadlineDateForUser(string $user_id, ilObjMumieTask $task) : ilMumieTaskDateTime
    {
        if(ilMumieTaskDeadlineExtensionService::hasDeadlineExtension($user_id, $task))
        {
            return ilMumieTaskDeadlineExtensionService::getDeadlineExtensionDate($user_id, $task);
        }
        return $task->getDeadlineDateTime();
    }

    public static function hasDeadlinePassedForUser(string $user_id, ilObjMumieTask $task) : bool
    {
        if(!$task->hasDeadline())
        {
            return false;
        }
        return self::getDeadlineDateForUser($user_id, $task)->hasPassed();
    }
}