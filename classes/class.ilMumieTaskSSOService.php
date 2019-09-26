<?php

/**
 * Class to generate SSO Tokens, insert them in database and generate the
 * form fields to launch a mumie task
 */

class ilMummieTaskSSOService {

    private $TOKEN_TABLE_NAME = "xmum_sso_tokens";
    /**
     * Generate a randomized token for single sign in to MUMIE servers
     *
     * @param int $length word length of the token
     * @return string token
     */
    public function getToken($length) {
        $token = "";
        $codealphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $codealphabet .= "abcdefghijklmnopqrstuvwxyz";
        $codealphabet .= "0123456789";
        $max = strlen($codealphabet) - 1;

        for ($i = 0; $i < $length; $i++) {
            $token .= $codealphabet[rand(0, $max)];
        }

        return $token;
    }

    /**
     * Generates an sso Token and the html for a form with hidden fields
     * containing the urls and sso tokens
     */

    public function setUpTokenAndLaunchForm($loginurl, $launchcontainer, $problemurl) {
        global $ilUser, $ilDB, $DIC;
        $ssotoken = new \stdClass();
        $ssotoken->token = $this->getToken(30);
        $ssotoken->user = $ilUser->getId();
        $ssotoken->timecreated = time();

        $query = 'SELECT * FROM ' . $this->TOKEN_TABLE_NAME . ' WHERE user = ' . $ilDB->quote($ilUser->getId(), "integer");
        $result = $ilDB->query($query);
        $rec = $ilDB->fetchAssoc($result);
        if (!is_null($rec)) {
            $this->updateToken($rec, $ssotoken);
        } else {
            $this->insertToken($rec, $ssotoken);
        }

        return $this->getHTMLCode($loginurl, $launchcontainer, $ssotoken, $problemurl);
    }

    private function updateToken($rec, $ssotoken) {
        global $DIC, $ilUser;
        $ssotoken->id = $rec['user'];
        $DIC->database()->update($this->TOKEN_TABLE_NAME,
            array(
                'token' => array('text', $ssotoken->token),
                'timecreated' => array('integer', $ssotoken->timecreated),
            ),
            array(
                'user' => array('integer', $ilUser->getId()),
            ));
    }

    private function insertToken($rec, $ssotoken) {
        global $DIC, $ilUser;
        $DIC->database()->insert($this->TOKEN_TABLE_NAME,
            array(
                'id' => array('integer', $DIC->database()->nextID($this->TOKEN_TABLE_NAME)),
                'token' => array('text', $ssotoken->token),
                'timecreated' => array('integer', $ssotoken->timecreated),
                'user' => array('integer', $ilUser->getId()))); //(array) $ssotoken);
    }

    private function getHTMLCode($loginurl, $launchcontainer, $ssotoken, $problemurl, $width = 800, $height = 600) {
        require_once ("./Services/UICore/classes/class.ilTemplate.php");
        require_once ("./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskAdminSettings.php");
        global $ilUser;
        $tpl = new ilTemplate("./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/templates/launch_form.html", true, true, true, "DEFAULT", true);

        $tpl->setVariable("TASKURL", $loginurl);
        $tpl->setVariable("TARGET", $launchcontainer == 1 ? 'MumieTaskLaunchFrame' : '_blank');
        $tpl->setVariable("USER_ID", $ilUser->getId());
        $tpl->setVariable("TOKEN", $ssotoken->token);
        $tpl->setVariable("ORG", ilMumieTaskAdminSettings::getInstance()->getOrg());
        $tpl->setVariable("PROBLEMURL", $problemurl);
        $tpl->setVariable("WIDTH", $width);
        $tpl->setVariable("HEIGHT", $height);

        if ($launchcontainer == 1) {
            $tpl->setVariable("BUTTONTYPE", "hidden");
            $script = "<script>
            document.forms['mumie_sso_form'].submit();
            </script>";
            $tpl->setVariable("EMBED", $script);
        } else {
            $tpl->setVariable("BUTTONTYPE", "submit");
        }

        $html = $tpl->get();
        return $html;
    }

    private function getTokenTableName() {
        return "xmum_sso_tokens";
    }
}
