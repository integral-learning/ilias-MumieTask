<?php
use PHPUnit\Framework\TestSuite;

class ilMumieTaskSuite extends TestSuite {
    public static function suite() {
        $suite = new ilMumieTaskSuite();
        include_once ("./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/test/ilMumieTaskServerTest.php");
        $suite->addTestSuite("ilMumieTaskServerTest");
        return $suite;
    }
}

?>