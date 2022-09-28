<?php
/**
 * MumieTask plugin
 *
 * @copyright   2019 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Nicolas Zunker (nicolas.zunker@integral-learning.de)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 /**
  * This script is used by MUMIE Servers to verify a user's identity during SSO
  */

header('Content-Type:application/json');
$method = $_SERVER['REQUEST_METHOD'];
if ($method != 'POST') {
    echo($method . " is not allowed");
    exit(0);
}

chdir("../../../../../../../");

$_GET['client_id'] = $_REQUEST['clientId'];
// Initialise Ilias and the $ilDB global
require_once "Services/Context/classes/class.ilContext.php";
ilContext::init(ilContext::CONTEXT_REST);

require_once(__DIR__ . "/classes/class.ilMumieTaskInitialisation.php");
ilMumieTaskInitialisation::init($_REQUEST['clientId']);

//once the global exists we can verify the token

require_once(__DIR__ . "/classes/class.ilMumieTaskSSOService.php");
$response = ilMumieTaskSSOService::verifyToken();

echo json_encode($response);

exit(0);
