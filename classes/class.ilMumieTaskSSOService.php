<?php

/**
 * MumieTask plugin
 *
 * @copyright   2019 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskSSOToken.php');
require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskIdHashingService.php');
require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilObjMumieTask.php');
require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/locallib.php');
require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/sso/cryptographic/mumie_cryptography_service.php');
require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskUserService.php');
require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/sso/token/token_service.php');
require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/sso/launch_form_builder.php');

/**
 * This class provides functions for SSO between MUMIE servers and ILIAS
 */

class ilMumieTaskSSOService
{
    /**
         * Perform sso attempt for a given user and mumie task
         * @param ilObjMumieTask $mumietask
         * @return void
         * @throws \dml_exception
         */
    public static function sso(ilObjMumieTask $mumietask): string
    {
        //             debug_print_backtrace();
        global $ilUser;
        $mumieuser = ilMumieTaskUserService::get_user($ilUser->getId(), $mumietask);
        $ssotoken = token_service::generate_sso_token($mumieuser);
        $deadline = locallib::mumie_get_effective_duedate($ilUser->getId(), $mumietask);
        return self::get_launch_form($ssotoken, $mumietask, $mumieuser, $deadline);
    }

    /**
     * Get html code for launch form used to send POST request
     * @param sso_token  $token
     * @param ilObjMumieTask  $mumietask
     * @param int        $deadline
     * @param ilMumieTaskUser $user
     * @return string
     * @throws \dml_exception
     */
    private static function get_launch_form(
        sso_token $token,
        ilObjMumieTask $mumietask,
        ilMumieTaskUser $user,
        ?int     $deadline
    ): string {
        $launchformbuilder = new launch_form_builder($token, $mumietask, $user);

        $problempath = $mumietask->auth_mumie_get_problem_path();
        if ($deadline && self::include_signed_deadline($problempath, $deadline)) {
            $launchformbuilder->with_deadline($deadline);
        }
        return $launchformbuilder->build();
    }

    /**
     * Check whether we need to include signed deadline data in the request
     * @param string $problempath
     * @param int    $deadline
     * @return bool
     */
    private static function include_signed_deadline(string $problempath, int $deadline): bool
    {
        return str_starts_with($problempath, locallib::WORKSHEET_PREFIX)
            && $deadline > 0;
    }

    /**
     * Verifies MUMIE tokens for SSO
     *
     * @return json object $response containing the field status: valid or invalid
     * and any user data that the admin has selected for sharing (user_id, firstname, lastname,email)
     */

    public static function verifyToken()
    {
        global $ilDB;
        $token = $_POST['token'];
        $hashed_id = $_POST['userId'];

        $il_user_id = ilMumieTaskIdHashingService::getUserFromHash($hashed_id);

        $mumietoken = new ilMumieTaskSSOToken($hashed_id);
        $mumietoken->read();

        $user_query = $ilDB->query('SELECT * FROM usr_data WHERE usr_id = ' . $ilDB->quote($il_user_id, "integer"));
        $user_rec = $ilDB->fetchAssoc($user_query);
        $response = new stdClass();
        require_once(__DIR__ . "/class.ilMumieTaskAdminSettings.php");
        $admin_settings = ilMumieTaskAdminSettings::getInstance();

        if (!is_null($mumietoken->getToken()) && $mumietoken->getToken() == $token && $user_rec != null) {
            $current = time();
            if (($current - $mumietoken->getTimecreated()) >= 1000) {
                $response->status = "invalid";
            } else {
                $response->status = "valid";
                $response->userid = $hashed_id;

                if ($admin_settings->getShareFirstName()) {
                    $response->firstname = $user_rec['firstname'];
                }
                if ($admin_settings->getShareLastName()) {
                    $response->lastname = $user_rec['lastname'];
                }
                if ($admin_settings->getShareEmail()) {
                    $response->email = $user_rec['email'];
                }
            }
        } else {
            $response->status = "invalid";
        }
        return $response;
    }

}
