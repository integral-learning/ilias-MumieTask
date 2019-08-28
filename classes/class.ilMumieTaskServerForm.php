<?php
class ilMumieTaskServerForm extends ilPropertyFormGUI {

    function __construct() {
        parent::__construct();
    }

    private $urlItem, $nameItem;

    public function setFields() {
        global $lng;
        $this->nameItem = new ilTextInputGUI("name", 'name');
        parent::addItem($this->nameItem);
        $this->urlItem = new ilTextInputGUI("url_prefix", 'url_prefix');
        parent::addItem($this->urlItem);

        $this->addCommandButton('submitServer', $lng->txt('save'));
        $this->addCommandButton('listServers', $lng->txt('cancel'));
    }

    function checkInput() {
        global $DIC;
        $id = $_GET['server_id'];
        $DIC->ctrl()->setParameter($this, "server_id", $id);

        $ok = parent::checkInput();
        require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskServer.php');

        $server = new ilMumieTaskServer();
        $server->setName($this->nameItem->getValue());
        $server->setUrlPrefix($this->urlItem->getValue());

        $nameExists = $server->nameExists();
        $urlPrefixExists = $server->urlPrefixExists();

        if (!$id) {
            if ($nameExists) {
                $ok = false;
            }
            if ($urlPrefixExists) {
                $ok = false;
            }
        }

        return $ok;
    }
}