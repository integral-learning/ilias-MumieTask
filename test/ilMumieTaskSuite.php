<?php

use PHPUnit\Framework\TestSuite;

class ilMumieTaskSuite extends TestSuite
{
    public static function suite()
    {
        $suite = new ilMumieTaskSuite();
        $suite->addTestSuite('ilMumieTaskServerTest');
        $suite->addTestSuite('ilObjMumieTaskTest');
        $suite->addTestSuite('ilMumieTaskCryptographyServiceTest');

        return $suite;
    }
}
