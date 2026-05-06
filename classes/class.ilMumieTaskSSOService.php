<?php

/**
 * MumieTask plugin
 *
 * @copyright   2019 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * This class provides functions for SSO between MUMIE servers and ILIAS
 */
class ilMumieTaskSSOService
{
    /**
     * Verifies MUMIE tokens for SSO
     *
     * @return json object $response containing the field status: valid or invalid
     * and any user data that the admin has selected for sharing (user_id, firstname, lastname,email)
     */

    public static function verifyToken()
    {
        global $DIC;
        $db = $DIC->database();
        $token = $_POST['token'];
        $hashed_id = $_POST['userId'];

        $il_user_id = ilMumieTaskIdHashingService::getUserFromHash($hashed_id);

        $mumietoken = new ilMumieTaskSSOToken($hashed_id);
        $mumietoken->read();

        $user_query = $db->query('SELECT * FROM usr_data WHERE usr_id = ' . $db->quote($il_user_id, "integer"));
        $user_rec = $db->fetchAssoc($user_query);
        $response = new stdClass();
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
        global $DIC;
        $hashed_user = ilMumieTaskIdHashingService::getHashForUser($DIC->user()->getId(), $task);
        $ssotoken = new ilMumieTaskSSOToken($hashed_user);
        $ssotoken->insertOrRefreshToken();

        return $this->getHTMLCode($task, $ssotoken, $hashed_user);
    }


    /**
     * Get html code for the MUMIE task launcher
     * @throws ilTemplateException
     */
    private function getHTMLCode($taskObj, $ssotoken, $hashed_user, $height = 600): string
    {
        $tpl = new ilTemplate("./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/templates/launch_form.html", true, true);
        $tpl->setVariable("TASKURL", $taskObj->getLoginUrl());
        $tpl->setVariable("TARGET", $taskObj->getLaunchcontainer() == 1 ? 'MumieTaskLaunchFrame' : '_blank');
        $tpl->setVariable("USER_ID", $hashed_user);
        $tpl->setVariable("TOKEN", $ssotoken->getToken());
        $tpl->setVariable("ORG", htmlspecialchars(ilMumieTaskAdminSettings::getInstance()->getOrg()));
        $tpl->setVariable("PROBLEMURL", $taskObj->getProblemUrl());
        $tpl->setVariable("LANGUAGE", $taskObj->getLanguage());
        $tpl->setVariable('PROBLEMPATH', $taskObj->getTaskurl());
        $tpl->setVariable("WIDTH", '100%');
        $tpl->setVariable("HEIGHT", $height);

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

        return $tpl->get();
    }
}
