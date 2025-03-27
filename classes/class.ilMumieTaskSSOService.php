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
        * A prefix used in task_urls indicating that the task is a worksheet
        */
    public const WORKSHEET_PREFIX = "worksheet_";

    /**
         * Perform sso attempt for a given user and mumie task
         * @param ilObjMumieTask $mumietask
         * @return void
         * @throws \dml_exception
         */
    public static function sso(ilObjMumieTask $mumietask): void
    {
        global $ilUser;
        $mumieuser = ilMumieTaskUserService::get_user($ilUser->getId(), $mumietask);
        $ssotoken = token_service::generate_sso_token($mumieuser);
        $deadline = locallib::mumie_get_effective_duedate($ilUser->getId(), $mumietask);
        echo self::get_launch_form($ssotoken, $mumietask, $mumieuser, $deadline);
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
        return str_starts_with($problempath, self::WORKSHEET_PREFIX)
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

    /**
     * Generates an sso Token and the html for a form with hidden fields
     * containing the login and logout urls, sso token and other infos
     */

    public function setUpTokenAndLaunchForm($task)
    {
        global $ilUser, $ilDB, $DIC;
        $hashed_user = ilMumieTaskIdHashingService::getHashForUser($ilUser->getId(), $task);
        $ssotoken = new ilMumieTaskSSOToken($hashed_user);
        $ssotoken->insertOrRefreshToken();
        $deadline = locallib::mumie_get_effective_duedate($ilUser->getId(), $task);
        $syncidlowercase = strtolower($this->user->get_sync_id());
        $signeddata = mumie_cryptography_service::sign_data(
            $deadline,
            $syncidlowercase,
            $task->get_worksheet_id($task)
        );
        return $this->getHTMLCode($task, $ssotoken, $hashed_user, $deadline, $signeddata);
    }

    /**
      * Get worksheet id from problem path
      * @return string
      */
    private function get_worksheet_id($mumietask): string
    {
        $problempath = $mumietask->auth_mumie_get_problem_path();
        return str_replace('worksheet_', "", $problempath);
    }

    /**
     * Get html code for the MUMIE task launcher
     */
    private function getHTMLCode(
        $taskObj,
        $ssotoken,
        $hashed_user,
        $deadline,
        $signeddata,
        $width = 800,
        $height = 600
    ) {
        require_once("./Services/UICore/classes/class.ilTemplate.php");
        require_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskAdminSettings.php");
        require_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/grades/synchronization/context/class.ilMumieTaskContextProvider.php");
        require_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/locallib.php");

        $contextProvider = new ilMumieTaskContextProvider();
        $mumietasksids = array(locallib::getMumieId($taskObj));

        $tpl = new ilTemplate("./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/templates/launch_form.html", true, true, true, "DEFAULT", true);
        // explanation for the various "true" arguments above: the last one is important because it signifies this is a plugin,
        // the other "true"s should always be set that way according to the ilias documentation
        $tpl->setVariable("TASKURL", $taskObj->getLoginUrl());
        $tpl->setVariable("TARGET", $taskObj->getLaunchcontainer() === 1 ? 'MumieTaskLaunchFrame' : '_blank');
        $tpl->setVariable("USER_ID", $hashed_user);
        $tpl->setVariable("TOKEN", $ssotoken->getToken());
        $tpl->setVariable("ORG", htmlspecialchars(ilMumieTaskAdminSettings::getInstance()->getOrg()));
        $tpl->setVariable("PROBLEMURL", $taskObj->getProblemUrl());
        $tpl->setVariable("LANGUAGE", $taskObj->getLanguage());
        $tpl->setVariable('PROBLEMPATH', $taskObj->getTaskurl());
        $tpl->setVariable("WIDTH", '100%');
        $tpl->setVariable("HEIGHT", $height);
        $tpl->setVariable("DEADLINE", $deadline);
        $tpl->setVariable("DEADLINESIGNATURE", $signeddata);

        if ($taskObj->getLaunchcontainer() == 1) {
            $tpl->setVariable("BUTTONTYPE", "hidden"); //embed the iframe and launch it immediately via $script
            $script = "<script>
            const iframe = document.getElementById('basicMumieTaskLaunchFrame');
            let width = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;

            let height = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;
            height = height * 0.8;
            width = width * 0.6;

            //iframe.width = width;
            iframe.height = height;
            document.forms['mumie_sso_form'].submit();
            </script>";
            $tpl->setVariable("EMBED", $script);
        } else {
            $tpl->setVariable("BUTTONTYPE", "submit");
        }
        // otherwise leave a button to launch in a new tab

        $html = $tpl->get();
        return $html;
    }
}
