<?php

/**
 * MumieTask plugin.
 *
 * @copyright   2019 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Nicolas Zunker (nicolas.zunker@integral-learning.de)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
/**
 * ILIAS initialisation for verifyToken script.
 * Bootstraps the ILIAS service container (via the "ILIAS Legacy Initialisation
 * Adapter" entry point) so verifyToken.php can access $ilDB and other services.
 *
 * @param string clientId from the post request params
 */
class ilMumieTaskInitialisation
{
    public static function init($clientId)
    {
        define('CLIENT_ID', $clientId);
        entry_point('ILIAS Legacy Initialisation Adapter');
    }
}
