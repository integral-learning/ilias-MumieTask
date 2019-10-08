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
//currently the context and the initialisation are not really working. i have no clue what the problem could be,
//this is modelled on the ilExternalContent result.php file
chdir("../../../../../../../");
//require_once ("./sso/index.php");
//global $ilDB;

//var_dump($ilDB == NULL ? "db still null..." : "works");




    //$cookie_domain = $_SERVER['SERVER_NAME'];
    //$cookie_path = dirname( $_SERVER['PHP_SELF'] );

    /* if ilias is called directly within the docroot $cookie_path
    is set to '/' expecting on servers running under windows..
    here it is set to '\'.
    in both cases a further '/' won't be appended due to the following regex
    */
    $cookie_path .= (!preg_match("/[\/|\\\\]$/", $cookie_path)) ? "/" : "";

    if($cookie_path == "\\") $cookie_path = '/';

    $cookie_domain = ''; // Temporary Fix

    setcookie("ilClientId", $_GET["client_id"], 0, $cookie_path, $cookie_domain);

    $_COOKIE["ilClientId"] = $_GET["client_id"];
    //var_dump($_GET['client_id']);


// REST context has http and client but no user, templates, html or redirects
require_once "Services/Context/classes/class.ilContext.php";
ilContext::init(ilContext::CONTEXT_REST);

/*
require_once "Services/Context/classes/class.ilContext.php";
ilContext::init(ilContext::CONTEXT_WAC); // no idea what the right context choice is

*/
require_once(__DIR__ . "/classes/class.ilMumieTaskInitialisation.php");

ilMumieTaskInitialisation::initILIAS(); // all the errors come from the method calls inside this class
global $ilDB, $ilUser;
if($ilDB != NULL){
    //var_dump("hallelujah!");
}
$testvar = $ilUser->getId();
echo("works (?) " . $testvar);
exit(0);


//---------------------------------once globals exist-------------------------------------------------------------



// this is where the moodle script starts, i "translated" it into ILIAS variables and functions but the globals are all initialised to null
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
// we may not need to query the db for the usr_data, its all in the $ilUser global - the only question is is that global initialised?
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
        $response->userid = $user->id; //$ilUser->getId();

        if ($configSettings->getShareFirstName()) {
            $response->firstname = $user_rec['firstname']; //$ilUser->getFirstname();
        }
        if ($configSettings->getShareLastName()) {
            $response->lastname = $user_rec['lastname']; //$ilUser->getLastname();
        }
        if ($configSettings->getShareEmail()) {
            $response->email = $user_rec['email']; //$ilUser->getEmail();
        }
    }
} else {
    $response->status = "invalid";
}

echo json_encode($response); 
?>