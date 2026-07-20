<?php

/**
 * MumieTask plugin.
 *
 * @copyright   2026 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Sabine Greiser (sabine.greiser@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/*
 * Exposes the public half of this installation's RSA keypair, used to verify
 * signed worksheet deadlines.
 */

header('Content-Type: text/plain');
$method = $_SERVER['REQUEST_METHOD'];
if ('GET' != $method) {
    http_response_code(405);
    echo $method . ' is not allowed';
    exit(0);
}

chdir(__DIR__ . '/../../../../../../../..');
require_once 'vendor/composer/vendor/autoload.php';

$_GET['client_id'] = 'default';
ilContext::init(ilContext::CONTEXT_REST);
require_once 'artifacts/bootstrap_default.php';
ilMumieTaskInitialisation::init('default');

echo ilMumieTaskCryptographyService::getPublicKeyPem();

exit(0);
