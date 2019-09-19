<?php
use PHPUnit\Framework\TestCase;

class ilMumieTaskServerTest extends TestCase {
    protected function setUp(): void {
        /*include_once ("./Services/PHPUnit/classes/class.ilUnitUtil.php");
        ilUnitUtil::performInitialisation();*/
        require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskServer.php');

        //require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/models/class.ilMumieTaskCourseStructure.php');

    }

    public function testUrlStreamlining() {
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

    public function testAvailableLanguages() {
        $server = new ilMumieTaskServer();
        $server->setUrlPrefix("https://test.mumie.net/gwt");
        $server->buildStructure();

        $foundLang = $server->getLanguages();
        $this->assertTrue(in_array("de", $foundLang));
        $this->assertTrue(in_array("en", $foundLang));
        $this->assertTrue(in_array("zh", $foundLang));
        $this->assertFalse(in_array("fr", $foundLang));
    }

    public function testTags() {
        $server = new ilMumieTaskServer();
        $server->setUrlPrefix('test');
        $server->buildStructure();

        $this->assertTrue($server->isValidMumieServer());
        $this->assertTrue(count($server->getCourses()[0]->getTags()) > 0);
    }

}

?>