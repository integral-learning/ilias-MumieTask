<?php 

/**
 * Class for SSO Token services : 
 * Generate SSO Tokens 
 * Insert them in database andhidden html form for SSO post requests to a mumie server
 * Verify Tokens
 */

class ilMumieTaskSSOService {

    const MUMIETOKENS_TABLE_NAME = "xmum_sso_tokens";
    
    /**
     * Verifies MUMIE tokens for SSO 
     * 
     * @return json object $response containing the field status: valid or invalid
     * and any user data that the admin has selected for sharing (user_id,firstname,lastname,email)
     */

    public static function verifyToken(){ 

	global $ilDB;
        $token = $_POST['token'];
        $userid = $_POST['userId'];

        $table = self::MUMIETOKENS_TABLE_NAME;

        $mumietoken = new \stdClass();

        $tokenQuery = $ilDB->query('SELECT * FROM ' . $table . ' WHERE user = ' . $ilDB->quote($userid, "integer"));
        $rec = $ilDB->fetchAssoc($tokenQuery);
        $mumietoken->token = $rec['token'];
        $mumietoken->timecreated = $rec['timecreated'];
        $userQuery = $ilDB->query('SELECT * FROM usr_data WHERE usr_id = ' . $ilDB->quote($userid, "integer"));
        $user_rec = $ilDB->fetchAssoc($userQuery);
        $response = new stdClass();
        require_once (__DIR__ . "/class.ilMumieTaskAdminSettings.php");
        $configSettings = ilMumieTaskAdminSettings::getInstance();


        if ($mumietoken != null && $user_rec != null) {
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
     * containing the login and logout urls, sso token and other infos
     */
    
    public function setUpTokenAndLaunchForm($loginurl, $launchcontainer, $problemurl){
        global $ilUser, $ilDB, $DIC;
        $ssotoken = new \stdClass();
        $ssotoken->token = $this->getToken(30);
        $ssotoken->user = $ilUser->getId();
        $ssotoken->timecreated = time();
        $tokentable =  self::MUMIETOKENS_TABLE_NAME;
        
        $query = 'SELECT * FROM ' . $tokentable . ' WHERE user = ' . $ilDB->quote($ilUser->getId(), "integer");
        $result = $ilDB->query($query);
        $rec = $ilDB->fetchAssoc($result);
        if(!is_null($rec)){
            $this->updateToken($tokentable, $rec, $ssotoken);
        } else {
            $this->insertToken($tokentable, $rec, $ssotoken);
        }
	
	$cookie_domain = $_SERVER['SERVER_NAME'];
        $cookie_path = dirname( $_SERVER['PHP_SELF'] );

        /* if ilias is called directly within the docroot $cookie_path
        is set to '/' expecting on servers running under windows..
        here it is set to '\'.
        in both cases a further '/' won't be appended due to the following regex
        */
        $cookie_path .= (!preg_match("/[\/|\\\\]$/", $cookie_path)) ? "/" : "";

        if($cookie_path == "\\") $cookie_path = '/';

        $cookie_domain = ''; // Temporary Fix
        
        setcookie('ilMumieTaskSSO',$_COOKIE['ilClientId'], $cookie_path, $cookie_domain);

        return $this->getHTMLCode($loginurl, $launchcontainer, $ssotoken, $problemurl);
    }
    
    private function updateToken($tokentable, $rec, $ssotoken){
        global $DIC, $ilUser;
        $ssotoken->id = $rec['user'];
            $DIC->database()->update($tokentable, 
            array(
                'token' => array('text', $ssotoken->token),
                'timecreated' => array('integer', $ssotoken->timecreated),
            ),
            array(
                'user' => array('integer', $ilUser->getId()),
            ));
    }

    private function insertToken($tokentable, $rec, $ssotoken) {
        global $DIC, $ilUser;
        $DIC->database()->insert($tokentable, 
            array(
                'id' => array('integer', $DIC->database()->nextID($tokentable)),
                'token' => array('text', $ssotoken->token), 
                'timecreated' => array('integer', $ssotoken->timecreated) , 
                'user' => array('integer', $ilUser->getId()))); //(array) $ssotoken );
    }


    private function getHTMLCode($loginurl, $launchcontainer, $ssotoken, $problemurl , $width = 800, $height = 600){
        require_once ("./Services/UICore/classes/class.ilTemplate.php");
        require_once ("./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskAdminSettings.php");
        global $ilUser;
        $tpl = new ilTemplate("./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/templates/launch_form.html", true, true, true, "DEFAULT" , true );
        // explanation for the various "true" arguments above: the last one is important because it signifies this is a plugin,
        // the other "true"s should always be "true" according to the ilias documentation 
        $tpl->setVariable("TASKURL", $loginurl);
        $tpl->setVariable("TARGET", $launchcontainer == 1 ? 'MumieTaskLaunchFrame' :'_blank');
        $tpl->setVariable("USER_ID", $ilUser->getId());
        $tpl->setVariable("TOKEN", $ssotoken->token);
        $tpl->setVariable("ORG", ilMumieTaskAdminSettings::getInstance()->getOrg());
        $tpl->setVariable("PROBLEMURL",$problemurl);
        $tpl->setVariable("WIDTH", $width);
        $tpl->setVariable("HEIGHT", $height);

        if($launchcontainer == 1){
            $tpl->setVariable("BUTTONTYPE", "hidden"); //embed the ifram and launch it immediately via $script
            $script = "<script>
            document.forms['mumie_sso_form'].submit();
            </script>";
            $tpl->setVariable("EMBED",$script);
        } else $tpl->setVariable("BUTTONTYPE", "submit"); // otherwise leave a button to launch in a new tab
    
        $html = $tpl->get();
        return $html;
    }
}
