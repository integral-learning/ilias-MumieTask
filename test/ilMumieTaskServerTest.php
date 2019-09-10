<?php
use PHPUnit\Framework\TestCase;

class ilMumieTaskServerTest extends TestCase {
    protected function setUp(): void {
        /*include_once ("./Services/PHPUnit/classes/class.ilUnitUtil.php");
        ilUnitUtil::performInitialisation();*/
        require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskServer.php');
    }

    public function testUrlStreamlinging() {
        $server = new ilMumieTaskServer();
        $server->setUrlPrefix("https://test.mumie.net/gwt");
        $this->assertEquals($server->getUrlPrefix(), "https://test.mumie.net/gwt/");
    }

    public function testValidServer() {
        $server = new ilMumieTaskServer();
        $server->setUrlPrefix("https://test.mumie.net/gwt");
        $this->assertTrue($server->isValidMumieServer());
    }

    public function testInvalidServer() {
        $server = new ilMumieTaskServer();
        $server->setUrlPrefix("https://www.google.com");
        $this->assertFalse($server->isValidMumieServer());
    }
}

?>