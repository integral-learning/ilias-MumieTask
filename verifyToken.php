<?php

/**
 * MumieTask plugin.
 *
 * @copyright   2019 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Nicolas Zunker (nicolas.zunker@integral-learning.de)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/*
 * This script is used by MUMIE Servers to verify a user's identity during SSO
 */

header('Content-Type:application/json');
$method = $_SERVER['REQUEST_METHOD'];
if ('POST' != $method) {
    echo $method . ' is not allowed';
    exit(0);
}

chdir(__DIR__ . '/../../../../../../../..');
require_once 'vendor/composer/vendor/autoload.php';

$_GET['client_id'] = $_REQUEST['clientId'];
ilContext::init(ilContext::CONTEXT_REST);
ilMumieTaskInitialisation::init($_REQUEST['clientId']);

// once the global exists we can verify the token

$response = ilMumieTaskSSOService::verifyToken();

echo json_encode($response);

exit(0);
