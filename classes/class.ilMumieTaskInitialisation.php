<?php
/**
 * MumieTask plugin
 *
 * @copyright   2019 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Nicolas Zunker (nicolas.zunker@integral-learning.de)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once("Services/Init/classes/class.ilInitialisation.php");

/**
 * ILIAS initialisation for verifyToken script
 * This is needed to initialise the $ilDB object
 * @param string clientId from the post request params
 */
class ilMumieTaskInitialisation extends ilInitialisation
{
    public static function initILIAS($clientId)
    {
        define('CLIENT_ID', $clientId);
        parent::initILIAS();
    }
}
