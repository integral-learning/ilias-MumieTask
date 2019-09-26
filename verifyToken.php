<?php

//require_once ("../../config.php");

global $ilDB;

header('Content-Type:application/json');
$method = $_SERVER['REQUEST_METHOD'];
if ($method != 'POST') {
    print_error($method . " is not allowed");
    exit(0);
}

$token = $_POST['token'];
$userid = $_POST['userId'];

$table = "xmum_sso_tokens"; // could be imported from sso service


$mumietoken = new \stdClass();
$query = 'SELECT * FROM ' . $tokentable . ' WHERE user = ' . $ilDB->quote($userid, "integer");
$result = $ilDB->query($query);
$rec = $ilDB->fetchAssoc($result);
$mumietoken->token= $rec['token'];
$mumietoken->timecreated = $rec['timecreated'];

$user_query_result = $ilDB->query('SELECT * FROM usr_data WHERE usr_id = ' . $ilDB->quote($userid, "integer"));
$user = $user_query_result // this is obviously not finished

$response = new stdClass();


if ($mumietoken != null && $user != null) {
    $current = time();
    if (($current - $mumietoken->timecreated) >= 60) {
        $response->status = "invalid";
    } else {
        $response->status = "valid";
        $response->userid = $user->id;

        if (get_config('auth_mumie', 'userdata_firstname')) {
            $response->firstname = $user->firstname;
        }
        if (get_config('auth_mumie', 'userdata_lastname')) {
            $response->lastname = $user->lastname;
        }
        if (get_config('auth_mumie', 'userdata_mail')) {
            $response->email = $user->email;
        }
    }
} else {
    $response->status = "invalid";
}

echo json_encode($response); // echo??