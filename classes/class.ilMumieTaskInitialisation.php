<?php

require_once ("Services/Init/classes/class.ilInitialisation.php");

/**
 * ILIAS initialisation for verifyToken script
 * This is needed to initialise the $ilDB object
 * @param string clientId from the post request params
 */
class ilMumieTaskInitialisation extends ilInitialisation {
    public static function initILIAS($clientId) {       
	    define('CLIENT_ID', $clientId);
        parent::initILIAS();

    }
}
