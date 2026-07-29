<?php

/**
 * MumieTask plugin.
 *
 * @copyright   2022 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * This class provides functionality to retrieve and filter all members of a MUMIE Task.
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
        return preg_match(sprintf('#^%s#i', $needle), $haystack);
    }

    public static function getAllMemberIds(ilObjMumieTask $mumie_task): array
    {
        $container_ref_id = self::findEnclosingCourseOrGroupRefId($mumie_task->getRefId());
        if (null === $container_ref_id) {
            return self::getMemberIdsWithoutCourseContext($mumie_task);
        }

        return ilParticipants::getInstance($container_ref_id)->getMembers();
    }

    /**
     * @return null if the task isn't inside a Course or Group (e.g. base repository)
     */
    private static function findEnclosingCourseOrGroupRefId(int $ref_id): ?int
    {
        global $DIC;
        $tree = $DIC->repositoryTree();

        foreach (array_reverse($tree->getPathFull($ref_id)) as $node) {
            if (in_array($node['type'], ['crs', 'grp'], true)) {
                return (int) $node['child'];
            }
        }

        return null;
    }

    /**
     * Without a Course/Group roster to scope by, fall back to users who already have
     * LP data for this task (i.e. have opened or submitted it at least once), instead
     * of syncing every user on the platform.
     */
    private static function getMemberIdsWithoutCourseContext(ilObjMumieTask $mumie_task): array
    {
        global $DIC;
        $db = $DIC->database();
        $result = $db->query(
            'SELECT DISTINCT usr_id FROM ut_lp_marks WHERE obj_id = ' . $db->quote($mumie_task->getId(), 'integer'),
        );
        $ids = [];
        while ($row = $db->fetchAssoc($result)) {
            $ids[] = (int) $row['usr_id'];
        }

        return $ids;
    }
}
