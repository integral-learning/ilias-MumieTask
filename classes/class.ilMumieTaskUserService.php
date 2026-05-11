<?php

/**
 * MumieTask plugin.
 *
 * @copyright   2022 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Vasilije Nedeljkovic(vasilije.nedeljkovic@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * This class provides services for overriding grades.
 */
class ilMumieTaskUserService
{
    public static function getFullName($user_id)
    {
        return self::getUser($user_id)->getFullname();
    }

    public static function getUser($user_id): ilMumieTaskUser
    {
        global $DIC;
        $db = $DIC->database();
        $result = $db->query('SELECT * FROM usr_data WHERE usr_id = ' . $db->quote($user_id, 'integer'));
        $user = $db->fetchAssoc($result);

        return new ilMumieTaskUser($user['usr_id'], $user['firstname'], $user['lastname']);
    }
}
