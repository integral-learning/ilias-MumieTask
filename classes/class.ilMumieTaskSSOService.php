<?php
require_once ('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskSSOToken.php');
require_once ('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskIdHashingService.php');
/**
 * Class for SSO Token services :
 * Generate SSO Tokens
 * Insert them in database andhidden html form for SSO post requests to a mumie server
 * Verify Tokens
 */

class ilMumieTaskSSOService {

    /**
     * Verifies MUMIE tokens for SSO
     *
     * @return json object $response containing the field status: valid or invalid
     * and any user data that the admin has selected for sharing (user_id,firstname,lastname,email)
     */

    public static function verifyToken() {
        $logger = ilLoggerFactory::getLogger('xmum');
        global $ilDB;
        $token = $_POST['token'];
        $hashedId = $_POST['userId'];

        $ilUserId = ilMumieTaskIdHashingService::getUserFromHash($hashedId);

        $mumietoken = new ilMumieTaskSSOToken($hashedId);
        $mumietoken->read();

        $userQuery = $ilDB->query('SELECT * FROM usr_data WHERE usr_id = ' . $ilDB->quote($ilUserId, "integer"));
        $user_rec = $ilDB->fetchAssoc($userQuery);
        $response = new stdClass();
        require_once (__DIR__ . "/class.ilMumieTaskAdminSettings.php");
        $configSettings = ilMumieTaskAdminSettings::getInstance();

        if (!is_null($mumietoken->getToken()) && $mumietoken->getToken() == $token && $user_rec != null) {
            $current = time();
            if (($current - $mumietoken->getTimecreated()) >= 1000) {
                $response->status = "invalid";
            } else {
                $response->status = "valid";
                $response->userid = $hashedId;

                if ($configSettings->getShareFirstName()) {
                    $response->firstname = $user_rec['firstname'];
                }
                if ($configSettings->getShareLastName()) {
                    $response->lastname = $user_rec['lastname'];
                }
                if ($configSettings->getShareEmail()) {
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

    public function setUpTokenAndLaunchForm($loginurl, $launchcontainer, $problemurl) {
        global $ilUser, $ilDB, $DIC;
        $hashedUser = ilMumieTaskIdHashingService::getHashForUser($ilUser->getId());
        $ssotoken = new ilMumieTaskSSOToken($hashedUser);
        $ssotoken->insertOrRefreshToken();

        return $this->getHTMLCode($loginurl, $launchcontainer, $ssotoken, $problemurl, $hashedUser);
    }

    private function getHTMLCode($loginurl, $launchcontainer, $ssotoken, $problemurl, $hashedUser, $width = 800, $height = 600) {
        require_once ("./Services/UICore/classes/class.ilTemplate.php");
        require_once ("./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskAdminSettings.php");
        $tpl = new ilTemplate("./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/templates/launch_form.html", true, true, true, "DEFAULT", true);
        // explanation for the various "true" arguments above: the last one is important because it signifies this is a plugin,
        // the other "true"s should always be set that way according to the ilias documentation
        $tpl->setVariable("TASKURL", $loginurl);
        $tpl->setVariable("TARGET", $launchcontainer == 1 ? 'MumieTaskLaunchFrame' : '_blank');
        $tpl->setVariable("USER_ID", $hashedUser);
        $tpl->setVariable("TOKEN", $ssotoken->getToken());
        $tpl->setVariable("ORG", ilMumieTaskAdminSettings::getInstance()->getOrg());
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
