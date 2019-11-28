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

/**
 * This class provides functions for SSO between MUMIE servers and ILIAS
 */

class ilMumieTaskSSOService
{

    /**
     * Verifies MUMIE tokens for SSO
     *
     * @return json object $response containing the field status: valid or invalid
     * and any user data that the admin has selected for sharing (user_id,firstname,lastname,email)
     */

    public static function verifyToken()
    {
        $logger = ilLoggerFactory::getLogger('xmum');
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

    public function setUpTokenAndLaunchForm($loginurl, $launchcontainer, $problemurl)
    {
        global $ilUser, $ilDB, $DIC;
        $hashed_user = ilMumieTaskIdHashingService::getHashForUser($ilUser->getId());
        $ssotoken = new ilMumieTaskSSOToken($hashed_user);
        $ssotoken->insertOrRefreshToken();

        return $this->getHTMLCode($loginurl, $launchcontainer, $ssotoken, $problemurl, $hashed_user);
    }


    /**
     * Get html code for the MUMIE task launcher
     */
    private function getHTMLCode($loginurl, $launchcontainer, $ssotoken, $problemurl, $hashed_user, $width = 800, $height = 600)
    {
        require_once("./Services/UICore/classes/class.ilTemplate.php");
        require_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskAdminSettings.php");
        $tpl = new ilTemplate("./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/templates/launch_form.html", true, true, true, "DEFAULT", true);
        // explanation for the various "true" arguments above: the last one is important because it signifies this is a plugin,
        // the other "true"s should always be set that way according to the ilias documentation
        $tpl->setVariable("TASKURL", $loginurl);
        $tpl->setVariable("TARGET", $launchcontainer == 1 ? 'MumieTaskLaunchFrame' : '_blank');
        $tpl->setVariable("USER_ID", $hashed_user);
        $tpl->setVariable("TOKEN", $ssotoken->getToken());
        $tpl->setVariable("ORG", htmlspecialchars(ilMumieTaskAdminSettings::getInstance()->getOrg()));
        $tpl->setVariable("PROBLEMURL", $problemurl);
        $tpl->setVariable("WIDTH", '100%');
        $tpl->setVariable("HEIGHT", $height);

        if ($launchcontainer == 1) {
            $tpl->setVariable("BUTTONTYPE", "hidden"); //embed the iframe and launch it immediately via $script
            $script = "<script>
            var iframe = document.getElementById('basicMumieTaskLaunchFrame');
            var width = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;

            var height = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;
            var height = height * 0.8;
            var width = width * 0.6;

            console.log(window.screen.height, window.screen.width, window.innerHeight, window.innerHeight, height, width);

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
