<?php

/**
 * MumieTask plugin.
 *
 * @copyright   2022 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ilMumieTaskDeadlineService
{
    /**
     * Null means no deadline applies yet, e.g. a timelimit-based worksheet the user has not opened.
     */
    public static function getDeadlineDateForUser(string $user_id, ilObjMumieTask $task): ?ilMumieTaskDateTime
    {
        if (ilMumieTaskDeadlineExtensionService::hasDeadlineExtension($user_id, $task)) {
            return ilMumieTaskDeadlineExtensionService::getDeadlineExtensionDate($user_id, $task);
        }
        if ($task->hasDeadline()) {
            return $task->getDeadlineDateTime();
        }

        return null;
    }

    public static function hasDeadlinePassedForUser(string $user_id, ilObjMumieTask $task): bool
    {
        $deadline = self::getDeadlineDateForUser($user_id, $task);

        return null !== $deadline && $deadline->hasPassed();
    }

    /**
     * No-op if the task has no timelimit or this user's countdown has already started.
     */
    public static function ensureTimelimitStarted(string $user_id, ilObjMumieTask $task): void
    {
        if (!$task->hasTimelimit() || ilMumieTaskDeadlineExtensionService::hasDeadlineExtension($user_id, $task)) {
            return;
        }

        ilMumieTaskDeadlineExtensionService::upsertDeadlineExtensionFromUnixTime(
            $task,
            time() + $task->getTimelimit(),
            $user_id,
        );
    }
}
