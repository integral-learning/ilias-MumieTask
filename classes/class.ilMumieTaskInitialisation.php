<?php

require_once ("Services/Init/classes/class.ilInitialisation.php");

/**
 * ILIAS initialisation for verifyToken script
 * This is needed to initialise the $ilDB and $ilUser objects
 */
class ilMumieTaskInitialisation extends ilInitialisation {
    public static function initILIAS() {
        /*
        require_once("./Services/Database/classes/class.ilDBWrapperFactory.php");
        require_once("./Services/Database/classes/class.ilDBConstants.php");
        $ilDB = ilDBWrapperFactory::getWrapper(ilDBConstants::TYPE_INNODB);
        //$ilDB->initFromIniFile();
        $ilDB->connect();
        parent::initGlobal("ilDB", $ilDB);

        parent::initGlobal(
        "ilUser",
        "ilObjUser",
        "./Services/User/classes/class.ilObjUser.php"
        );

         */
        define('CLIENT_ID', 'default');
        parent::initILIAS();
        //parent::initDatabase();
        //parent::initUser();

        //parent::initUser();

        /* this chunk is copied from xco ilExternalContentInitialisation.php

    // needed to get $rbarcreview initialized
    $main_version = substr(ILIAS_VERSION_NUMERIC, 0,3);

    if ($main_version == '5.1' || $main_version == '5.2')
    {
    // needed to get $rbarcreview initialized
    //parent::initAccessHandling();
    }
    else
    {
    // fill $DIC['ilUser'] needed by the course event handler
    // this also initializes the access handling
    parent::initUser();
    }
     */
    }
}