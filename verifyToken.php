<?php

header('Content-Type:application/json');
//var_dump($_POST['userId'] . ' ' . $_POST['token']);
$method = $_SERVER['REQUEST_METHOD'];
if ($method != 'POST') {
    echo( htmlspecialchars($method) . " is not allowed");
exit(0);
}

chdir("../../../../../../../");

// Initialise Ilias and the $ilDB global
require_once "Services/Context/classes/class.ilContext.php";
ilContext::init(ilContext::CONTEXT_REST);

require_once (__DIR__ . "/classes/class.ilMumieTaskInitialisation.php");
ilMumieTaskInitialisation::initILIAS($_REQUEST['clientId']);

//initialise logger and parse post reqeust vars (for debugging)

/*$s = "FIELDS OF POST REQ: \n";
foreach ($_POST as $key => $value) {
   $s .= "Field " . $key . " is " .  $value . " \n";
}

require_once( "./Services/Logging/classes/public/class.ilLoggerFactory.php");
ilLoggerFactory::getRootLogger()->info($s);*/

//once the global exists we can verify the token

require_once (__DIR__ . "/classes/class.ilMumieTaskSSOService.php");
$response = ilMumieTaskSSOService::verifyToken();

echo json_encode($response);

exit(0);
