<?php


chdir("../../../../../../../../../");
$_GET['client_id'] = $_REQUEST['clientId'];

require_once "Services/Context/classes/class.ilContext.php";
ilContext::init(ilContext::CONTEXT_REST);

$pluginPath = strstr(__DIR__, '/auth', true);

require_once($pluginPath . "/classes/class.ilMumieTaskInitialisation.php");
ilMumieTaskInitialisation::init($_REQUEST['clientId']);

require_once($pluginPath . "/classes/sso/cryptographic/mumie_cryptography_service.php");
$publickey = mumie_cryptography_service::get_public_key();

if (!$publickey) {
    header("HTTP/1.1 404 Not Found");
    echo json_encode(["error" => "Kein Eintrag gefunden"]);
    exit;
}

echo $publickey->get_key();
exit;
