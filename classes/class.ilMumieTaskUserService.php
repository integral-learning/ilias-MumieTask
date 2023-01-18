<?php

/**
 * MumieTask plugin
 *
 * @copyright   2022 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Vasilije Nedeljkovic(vasilije.nedeljkovic@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/users/class.ilMumieTaskUser.php');
/**
 * This class provides services for overriding grades
 */

class ilMumieTaskUserService
{
    public static function getFirstName($user_id)
    {
        return self::getUser($user_id)->getFirstName();
    }

    public static function getLastName($user_id)
    {
        return self::getUser($user_id)->getLastName();
    }

    public static function getFullName($user_id)
    {
        return self::getUser($user_id)->getFullname();
    }

    public static function getUser($user_id): ilMumieTaskUser
    {
        global $ilDB;
        $result = $ilDB->query("SELECT * FROM usr_data WHERE usr_id = ". $ilDB->quote($user_id, "integer"));
        $user = $ilDB->fetchAssoc($result);
        return new ilMumieTaskUser($user['usr_id'], $user["firstname"], $user['lastname']);
    }
}