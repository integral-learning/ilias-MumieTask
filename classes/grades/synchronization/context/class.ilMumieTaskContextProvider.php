<?php

/**
 * This class is used to create the context that is required for some XAPI requests.
 *
 * @package mod_mumie
 * @copyright  2017-2023 integral-learning GmbH (https://www.integral-learning.de/)
 * @author Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ilMumieTaskContextProvider {
    /**
     * Get context for a given list of MUMIE Tasks and users.
     *
     * @param array $mumietasks
     * @param array $users
     * @return ilMumieTaskContext
     */
    public static function get_context(array $mumietasks, array $mumietasksids, array $user_ids): ilMumieTaskContext {
        $context = new ilMumieTaskContext();
        $index = 0;
        foreach ($mumietasks as $mumietask) {
            if (self::requires_context($mumietask)) {
                $context->add_object_context(
                    $mumietasksids[$index],
                    self::create_object_context($mumietask, $user_ids)
                );
            }
            $index++;
        }
        return $context;
    }

    /**
     * Check whether a MUMIE Task requires context for XAPI requests.
     * @param ilObjMumieTask $mumie
     * @return bool
     */
    public static function requires_context(ilObjMumieTask $mumie_task): bool {
        return (substr( $mumie_task->getTaskurl(), 0, 10 ) === "worksheet_")
            && ($mumie_task->getDeadline() > 0); //?? TODO duedate or deadline ??
            // ||            $mumie->timelimit > 0
    }

    /**
     * Create a new ilMumieTaskObjectContext instance for a given list of users.
     *
     * @param ilObjMumieTask $mumie
     * @param array    user_ids
     * @return ilMumieTaskObjectContext
     */
    private static function create_object_context(ilObjMumieTask $mumie_task, array $user_ids): ilMumieTaskObjectContext {
        $context = new ilMumieTaskObjectContext($mumie_task->getLanguage());
        foreach ($user_ids as $user_id) {
            $context->add_user_context($user_id, self::create_user_context($mumie_task, $user_id));
        }
        return $context;
    }

    /**
     * Create a new ilMumieTaskUserContext instance for a given user and MUMIE Task
     * @param ilObjMumieTask   $mumie
     * @param ilMumieTaskUser $user
     * @return ilMumieTaskUserContext
     */
    private static function create_user_context(ilObjMumieTask $mumie_task, string $user_id): ilMumieTaskUserContext {
        $deadline = ilMumieTaskDeadlineService::getDeadlineDateForUser($user_id, $mumie_task);
        return new ilMumieTaskUserContext($deadline->getUnixTime());
        /* ?? moodle_id !== sync_id ?? */
    }
}
