<?php

header('Content-Type:application/json');

$method = $_SERVER['REQUEST_METHOD'];
if ($method != 'POST') {
    echo($method . " is not allowed");
exit(0);
}

chdir("../../../../../../../");

$cookie_path .= (!preg_match("/[\/|\\\\]$/", $cookie_path)) ? "/" : "";

if ($cookie_path == "\\") {
    $cookie_path = '/';
}

$cookie_domain = ''; // Temporary Fix

// Initialise Ilias and the $ilDB global
require_once "Services/Context/classes/class.ilContext.php";
ilContext::init(ilContext::CONTEXT_REST);

require_once (__DIR__ . "/classes/class.ilMumieTaskInitialisation.php");
ilMumieTaskInitialisation::initILIAS();

//once the global exists we can verify the token

require_once (__DIR__ . "/classes/class.ilMumieTaskSSOService.php");
$response = ilMumieTaskSSOService::verifyToken();

echo json_encode($response);

?>