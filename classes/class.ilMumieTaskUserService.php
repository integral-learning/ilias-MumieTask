<?php

/**
 * MumieTask plugin
 *
 * @copyright   2022 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Vasilije Nedeljkovic(vasilije.nedeljkovic@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * This class provides services for overriding grades
 */

class ilMumieTaskUserService
{
    public static function getFirstName($user_id)
    {
        return self::getUsername($user_id)["firstname"];
    }

    public static function getLastName($user_id)
    {
        return self::getUsername($user_id)["lastname"];
    }

    public static function getFullName($user_id)
    {
        return self::getFirstName($user_id) . " " . self::getLastName($user_id);
    }

    private static function getUsername($user_id)
    {
        global $ilDB;
        $result = $ilDB->query("SELECT firstname, lastname FROM usr_data WHERE usr_id = ". $ilDB->quote($user_id, "integer"));
        return $ilDB->fetchAssoc($result);
    }
}