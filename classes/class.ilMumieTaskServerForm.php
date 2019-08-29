<?php
class ilMumieTaskServerForm extends ilPropertyFormGUI {

    function __construct() {
        parent::__construct();
    }

    private $urlItem, $nameItem;

    public function setFields() {
        global $lng;
        $this->nameItem = new ilTextInputGUI("name", 'name');
        $this->nameItem->setRequired(true);
        parent::addItem($this->nameItem);
        $this->urlItem = new ilTextInputGUI("url_prefix", 'url_prefix');
        $this->urlItem->setRequired(true);
        parent::addItem($this->urlItem);

        $this->addCommandButton('submitServer', $lng->txt('save'));
        $this->addCommandButton('listServers', $lng->txt('cancel'));
    }

    function checkInput() {
        global $DIC;
        $id = $_GET['server_id'];
        $DIC->ctrl()->setParameter($this, "server_id", $id);

        $ok = parent::checkInput();
        if ($ok) {
            require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskServer.php');

            $server = new ilMumieTaskServer();
            $server->setName($this->getInput("name"));
            $server->setUrlPrefix($this->getInput("url_prefix"));

            $nameExists = $server->nameExistsInDb();
            $urlPrefixExists = $server->urlPrefixExistsInDb();

            if (!$server->isValidMumieServer()) {
                $ok = false;
                $this->urlItem->setAlert("There is no MUMIE server for this URL");
            }

            if (!isset($id)) {
                if ($nameExists) {
                    $ok = false;
                    $this->nameItem->setAlert("There is already a MumieServer configuration for this name!");
                }
                if ($urlPrefixExists) {
                    $ok = false;
                    $this->urlItem->setAlert("There is already a MumieServer configuration for this URL!");
                }
            } else {
                $oldServer = new ilMumieTaskServer($id);
                $oldServer->load();

                if ($nameExists && $oldServer->getName() != $server->getName()) {
                    $ok = false;
                    $this->nameItem->setAlert("There is already a MumieServer configuration for this name!");
                }
                if ($urlPrefixExists && $oldServer->getUrlPrefix() != $server->getUrlPrefix()) {
                    $ok = false;
                    $this->urlItem->setAlert("There is already a MumieServer configuration for this URL!");
                }
            }
        }
        return $ok;
    }
}

?>