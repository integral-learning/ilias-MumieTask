<?php

/* fake correct response: theoretically this should then verify the token and complete the sso login

header('Content-Type:application/json');
$response = new stdClass();

$response->status = "valid";
$response->firstname = "root";
$response->lastname = "user";
$response->userid = "6";
$response->email = "ilias@yourserver.com";
echo json_encode($response);
exit(0);
*/


//set up the ilias globals
//currently the context and the initialisation are not really working. i actually have no clue what the problem could be,
//this is modelled on the ilExternalContent result.php file
chdir("../../../../../../../");

require_once "Services/Context/classes/class.ilContext.php";
ilContext::init(ilContext::CONTEXT_SAML); // no idea what the right context choice is or if its even needed

require_once(__DIR__ . "/classes/class.ilMumieTaskInitialisation.php");

ilMumieTaskInitialisation::initILIAS(); // all the errors are being thrown by the method calls inside this class

$testvar = $ilUser->getId();
var_dump("works",$testvar);
exit(0);

//this is where the moodle script starts, i "translated" it into ILIAS variables and functions but the globals are all initialised to null
// when this script gets called which is why i added the bit above that tries to initialise the ilDB and ilUser objects

header('Content-Type:application/json');
$method = $_SERVER['REQUEST_METHOD'];
if ($method != 'POST') {
    var_dump($method . " is not allowed"); // print_error is an undefined function...
    exit(0);
}

$token = $_POST['token'];
$userid = $_POST['userId'];

$table = "xmum_sso_tokens"; // could be imported from sso service

$mumietoken = new \stdClass();

$tokenQuery = $ilDB->query( 'SELECT * FROM ' . $tokentable . ' WHERE user = ' . $ilDB->quote($userid, "integer") );
$rec = $ilDB->fetchAssoc($tokenQuery);
$mumietoken->token= $rec['token'];
$mumietoken->timecreated = $rec['timecreated'];
//-------------------------for debugging if we ever get here-------------------------------
var_dump($mumietoken->token, $mumietoken->timecreated);
exit(0);
//-----------------------------------------------------------------------------------------
$userQuery= $ilDB->query('SELECT * FROM usr_data WHERE usr_id = ' . $ilDB->quote($userid, "integer"));
$user_rec = $ilDB->fetchAssoc($userQuery);
$response = new stdClass();

require_once(__DIR__ . "/classes/class.ilMumieTaskAdminSettings.php");
$configSettings = ilMumieTaskAdminSettings::getInstance();

if ($mumietoken != null && $user != null) {
    $current = time();
    if (($current - $mumietoken->timecreated) >= 60) {
        $response->status = "invalid";
    } else {
        $response->status = "valid";
        $response->userid = $user->id;

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

echo json_encode($response); 