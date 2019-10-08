<?php

require_once ("Services/Init/classes/class.ilInitialisation.php");

/**
 * ILIAS initialisation for verifyToken script
 * This is needed to initialise the $ilDB object
 */
class ilMumieTaskInitialisation extends ilInitialisation {
    public static function initILIAS() {
        define('CLIENT_ID', $_COOKIE['ilClientId']);
        parent::initILIAS();

    }
}