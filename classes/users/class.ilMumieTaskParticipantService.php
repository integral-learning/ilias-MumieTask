<?php

/**
 * MumieTask plugin
 *
 * @copyright   2022 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * This class provides functionality to retrieve and filter all members of a MUMIE Task
 */
class ilMumieTaskParticipantService
{
    public static function filter(ilObjMumieTask $mumie_task, $first_name = '', $last_name = ''): array
    {
        $members = self::getAllMemberIds($mumie_task);
        return array_filter($members, function ($user_id) use ($first_name, $last_name) {
            return self::matchesName($user_id, $first_name, $last_name);
        });
    }

    private static function matchesName($user_id, $first_name, $last_name): bool
    {
        $user = ilMumieTaskUserService::getUser($user_id);
        return self::matchesFirstName($user, $first_name) && self::matchesLastName($user, $last_name);
    }

    private static function matchesFirstName(ilMumieTaskUser $user, $first_name = ''): bool
    {
        return self::matchesCaseInsensitive($user->getFirstName(), $first_name);
    }

    private static function matchesLastName(ilMumieTaskUser $user, $last_name = ''): bool
    {
        return self::matchesCaseInsensitive($user->getLastName(), $last_name);
    }

    private static function matchesCaseInsensitive($haystack, $needle)
    {
        return preg_match(sprintf("#^%s#i", $needle), $haystack);
    }

    public static function getAllMemberIds(ilObjMumieTask $mumie_task): array
    {
        if (self::isInBaseRepository($mumie_task)) {
            return self::getAllUserIds();
        } else {
            return ilParticipants::getInstance($mumie_task->getParentRef())->getMembers();
        }
    }

    private static function isInBaseRepository(ilObjMumieTask $mumie_task): bool
    {
        return $mumie_task->getParentRef() == 1;
    }

    private static function getAllUserIds(): array
    {
        global $DIC;
        $db = $DIC->database();
        $result = $db->query(
            "SELECT usr_id FROM usr_data;",
        );
        $allIds = [];
        while ($user_id = $db->fetchAssoc($result)) {
            array_push($allIds, $user_id["usr_id"]);
        }
        return $allIds;
    }
}
