<?php

/**
 * MumieTask plugin
 *
 * @copyright   2022 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Vasilije Nedeljkovic(vasilije.nedeljkovic@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/users/class.ilMumieTaskUser.php');
require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/sso/hash/hashing_service.php');
require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/sso/hash/mumie_id_hash.php');
/**
 * This class provides services for overriding grades
 */

class ilMumieTaskUserService
{
    public static function getFullName($user_id)
    {
        return self::get_user_from_moodle_id($user_id)->get_fullname();
    }

    //     public static function getUser($user_id): ilMumieTaskUser
    //     {
    //         global $ilDB;
    //         $result = $ilDB->query("SELECT * FROM usr_data WHERE usr_id = ". $ilDB->quote($user_id, "integer"));
    //         $user = $ilDB->fetchAssoc($result);
    //         return new ilMumieTaskUser($user['usr_id'], $user["firstname"], $user['lastname']);
    //     }

    /**
         * Get a ilMumieTaskUser instance for a given moodle user and MUMIE Task
         * @param string    $moodleid
         * @param ilObjMumieTask $mumietask
         * @return ilMumieTaskUser
         */
    public static function get_user(string $moodleid, ilObjMumieTask $mumietask): ilMumieTaskUser
    {
        $mumieid = self::use_id_masking($mumietask)
            ? hashing_service::generate_hash($moodleid, $mumietask)->get_hash()
            : $moodleid;
        return new ilMumieTaskUser($moodleid, $mumieid);
    }

    /**
     * Get the Problem selector user from a Moodle user id.
     *
     * @param string $moodleid The Moodle user id
     * @return ilMumieTaskUser The Problem selector user
     */
    public static function get_problem_selector_user(string $moodleid): ilMumieTaskUser
    {
        $mumieid = hashing_service::generate_hash_with_lecturer_postfix($moodleid)->get_hash();
        return new ilMumieTaskUser($moodleid, $mumieid);
    }

    public static function get_user_from_moodle_id($moodleid): ilMumieTaskUser
    {
        $user = new ilMumieTaskUser($moodleid, '');
        $exists = $user->load();
        if (!$exists) {
            return null;
        };
        return $user;
    }

    /**
     * Get ilMumieTaskUser from a mumie user id.
     * @param string $mumieid
     * @return ilMumieTaskUser
     * @throws \dml_exception
     */
    public static function get_user_from_mumie_id(string $mumieid): ?ilMumieTaskUser
    {
        if (self::is_mumie_id_masked($mumieid)) {
            $moodleid = mumie_id_hash::find_by_hash($mumieid)->get_user();
        } else {
            $moodleid = (int) $mumieid;
        }
        $user = new ilMumieTaskUser($moodleid, $mumieid);
        $exists = $user->load();
        if (!$exists) {
            return null;
        };
        return $user;
    }

    /**
     * Check whether we should mask the moodle user id.
     *
     * Only very old MUMIE Tasks dont use id masking.
     * @param mixed $mumietask
     * @return bool
     */
    private static function use_id_masking(mixed $mumietask): bool
    {
        return isset($mumietask->use_hashed_id) && $mumietask->use_hashed_id == 1;
    }

    /**
     * Check whether this mumie id was created by masking a moodle id.
     * @param string $mumieid
     * @return bool
     */
    private static function is_mumie_id_masked(string $mumieid): bool
    {
        return strlen($mumieid) >= 128;
    }
}
