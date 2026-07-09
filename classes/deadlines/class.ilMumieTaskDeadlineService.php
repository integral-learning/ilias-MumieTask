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
     * Returns null if the task has neither a fixed deadline nor a timelimit that has
     * already started (e.g. a timelimit-based worksheet the user has not opened yet).
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
     * Starts a timelimit-based worksheet's individual deadline the first time a
     * given user opens it, by storing it as a deadline extension. A no-op if the
     * task has no timelimit or the user's deadline has already been started.
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
