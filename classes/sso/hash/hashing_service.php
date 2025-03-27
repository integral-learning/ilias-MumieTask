<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/sso/hash/mumie_id_hash.php');
require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskAdminSettings.php');

/**
 * This class is a service providing functionalities regarding hashing/masking of moodle ids. Hashes are used as user id on MUMIE servers.
 *
 * @package auth_mumie
 * @copyright  2017-2023 integral-learning GmbH (https://www.integral-learning.de/)
 * @author Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hashing_service
{
    /**
     * Generate a hash of the userid for a given MUMIE Task
     * @param string    $useriliasid
     * @param ilObjMumieTask $mumietask
     * @return mumie_id_hash
     */
    public static function generate_hash(string $useriliasid, ilObjMumieTask $mumietask): mumie_id_hash
    {
        $mumieidhash = new mumie_id_hash($useriliasid, self::get_hash_with_suffix($useriliasid, $mumietask));
        $mumieidhash->save();
        return $mumieidhash;
    }

    /**
     * Generate a hash of the user id with the lecturer postfix
     *
     * @param string $userid The user id for which to generate the hash
     * @return mumie_id_hash The generated mumie_id_hash
     */
    public static function generate_hash_with_lecturer_postfix(string $userid): mumie_id_hash
    {
        $hash = self::auth_mumie_get_hashed_id($userid);
        $hash .= '@lecturer@';
        $mumieidhash = new mumie_id_hash($userid, $hash);
        $mumieidhash->save();
        return $mumieidhash;
    }

    /**
     * MUMIE user names consist out of the hashed user ID and a suffix depending on the MUMIE Task.
     *
     * This function returns the entire user name.
     * @param string    $userid
     * @param ilObjMumieTask $mumietask
     * @return string
     */
    private static function get_hash_with_suffix(string $userid, ilObjMumieTask $mumietask): string
    {
        $hash = self::auth_mumie_get_hashed_id($userid);
        if ($mumietask->getPrivateGradepool()) {
            $hash .= '@gradepool' . $mumietask->course . '@';
        }
        return $hash;
    }

    /**
     * Get a hashed string from the moodle user id.
     *
     * Some institutions use personal data (like matriculation numbers) as user id in moodle.
     * We need to pseudonymize the id to improve data protection.
     * We use the first 10 characters of the xapi-key as salt to further increase security.
     *
     * @param string $id userId that should be hashed
     * @return string Hashed string with 128 characters
     */
    private static function auth_mumie_get_hashed_id($id)
    {
        // xmum_admin_settings
        $admin_settings = ilMumieTaskAdminSettings::getInstance();
        return hash("sha512", $id . substr($admin_settings->getApiKey(), 0, 10));
    }

}
