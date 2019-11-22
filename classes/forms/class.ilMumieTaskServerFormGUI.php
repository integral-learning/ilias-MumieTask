<?php
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/debugToConsole.php');

class ilMumieTaskServerFormGUI extends ilPropertyFormGUI
{
    public function __construct()
    {
        parent::__construct();
    }

    private $urlItem;
    private $nameItem;

    public function setFields()
    {
        global $lng;
        $this->nameItem = new ilTextInputGUI($lng->txt('name'), 'name');
        $this->nameItem->setRequired(true);
        parent::addItem($this->nameItem);
        $this->urlItem = new ilTextInputGUI($lng->txt('rep_robj_xmum_url_prefix'), 'url_prefix');
        $this->urlItem->setRequired(true);
        parent::addItem($this->urlItem);
    }

    public function checkInput()
    {
        global $DIC, $lng;
        $id = $_GET['server_id'];
        $DIC->ctrl()->setParameter($this, "server_id", $id);

        $ok = parent::checkInput();
        if ($ok) {
            require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskServer.php');

            $server = new ilMumieTaskServer();
            $server->setName($this->getInput("name"));
            $server->setUrlPrefix($this->getInput("url_prefix"));

            $nameExists = $server->nameExistsInDb();
            $urlPrefixExists = $server->urlPrefixExistsInDb();
            if (!$server->isValidMumieServer()) {
                $ok = false;
                $this->urlItem->setAlert($lng->txt("rep_robj_xmum_server_not_valid"));
            }

            if (!isset($id)) {
                if ($nameExists) {
                    $ok = false;
                    $this->nameItem->setAlert($lng->txt("rep_robj_xmum_name_exists"));
                }
                if ($urlPrefixExists) {
                    $ok = false;
                    $this->urlItem->setAlert($lng->txt("rep_robj_xmum_url_exists"));
                }
            } else {
                $oldServer = new ilMumieTaskServer($id);
                $oldServer->load();

                if ($nameExists && $oldServer->getName() != $server->getName()) {
                    $ok = false;
                    $this->nameItem->setAlert($lng->txt("rep_robj_xmum_name_exists"));
                }
                if ($urlPrefixExists && $oldServer->getUrlPrefix() != $server->getUrlPrefix()) {
                    $ok = false;
                    $this->urlItem->setAlert($lng->txt("rep_robj_xmum_url_exists"));
                }
            }
        }
        return $ok;
    }
}
