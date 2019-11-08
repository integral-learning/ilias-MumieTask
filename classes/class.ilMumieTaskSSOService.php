<?php
require_once ('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskSSOToken.php');
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
        $logger->info("---------------------------------------------____________________-------------verifyToken is called");

        global $ilDB;
        $token = $_POST['token'];
        $userid = $_POST['userId'];

        $mumietoken = new ilMumieTaskSSOToken($userid);
        $mumietoken->read();
        $logger->info("---------------------------------------------Saved token is: " . $mumietoken->getToken());
        $logger->info("---------------------------------------------request token is: " . $token);
        $logger->info("---------------------------------------------tokens are equal " . json_encode($mumietoken->getToken() == $token));

        $userQuery = $ilDB->query('SELECT * FROM usr_data WHERE usr_id = ' . $ilDB->quote($userid, "integer"));
        $user_rec = $ilDB->fetchAssoc($userQuery);
        $response = new stdClass();
        require_once (__DIR__ . "/class.ilMumieTaskAdminSettings.php");
        $configSettings = ilMumieTaskAdminSettings::getInstance();

        if ($mumietoken->getToken() != null && $mumietoken->getToken() == $token && $user_rec != null) {
            $current = time();
            if (($current - $mumietoken->timecreated) >= 60) {
                $response->status = "invalid";
            } else {
                $response->status = "valid";
                $response->userid = $user_rec['usr_id'];

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
        $ssotoken = new ilMumieTaskSSOToken($ilUser->getId());
        $ssotoken->insertOrRefreshToken();

        return $this->getHTMLCode($loginurl, $launchcontainer, $ssotoken, $problemurl);
    }

    private function getHTMLCode($loginurl, $launchcontainer, $ssotoken, $problemurl, $width = 800, $height = 600) {
        require_once ("./Services/UICore/classes/class.ilTemplate.php");
        require_once ("./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskAdminSettings.php");
        global $ilUser;
        $tpl = new ilTemplate("./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/templates/launch_form.html", true, true, true, "DEFAULT", true);
        // explanation for the various "true" arguments above: the last one is important because it signifies this is a plugin,
        // the other "true"s should always be set that way according to the ilias documentation
        $tpl->setVariable("TASKURL", $loginurl);
        $tpl->setVariable("TARGET", $launchcontainer == 1 ? 'MumieTaskLaunchFrame' : '_blank');
        $tpl->setVariable("USER_ID", $ilUser->getId());
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
