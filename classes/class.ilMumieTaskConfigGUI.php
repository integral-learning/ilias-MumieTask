<?php

include_once ("./Services/Component/classes/class.ilPluginConfigGUI.php");

class ilMumieTaskConfigGUI extends ilPluginConfigGUI {

    /**
     * Handles all commmands, default is "configure"
     */
    function performCommand($cmd) {
        global $tree, $rbacsystem, $ilErr, $lng, $ilCtrl, $tpl;

        $cmd = $ilCtrl->getCmd($this);

        switch ($cmd) {
            case 'addServer':
            case 'deleteServer':
            case 'editServer':
            default:
                if (!$cmd) {
                    $cmd = "configure";
                }
                $this->$cmd();
                break;
        }
    }

    function configure() {
        //global $tpl, $ilToolbar, $ilCtrl;

        //$tpl->setContent("<h1>asdsad</h1>");
        $this->listServers();
    }

    function listServers() {
        global $tpl;
        require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskServerTableGUI.php');
        $server_gui = new ilMumieTaskServerTableGUI($this, 'listServers');
        $server_gui->init($this);
        $tpl->setContent($server_gui->getHTML());
    }

    private function debug_to_console($data) {
        $output = $data;
        if (is_array($output)) {
            $output = implode(',', $output);
        }

        echo "<script>console.log('Debug Objects: " . $output . "' );</script>";
    }

    function addServer() {
        global $tpl;
        $this->initAddForm();
        $tpl->setContent($this->form->getHTML());
    }

    private function initAddForm() {
        global $ilCtrl, $lng;
        require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskServerForm.php');

        $form = new ilMumieTaskServerForm();
        $form->setFields();
        /*
        include_once ("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this));
        $form->setTitle("FORM TITLE");

        $item1 = new ilTextInputGUI("name", 'name');
        $form->addItem($item1);

        $item2 = new ilTextInputGUI("url_prefix", 'url_prefix');
        $form->addItem($item2);

        $form->addCommandButton('submitServer', $lng->txt('save'));
        $form->addCommandButton('listServers', $lng->txt('cancel'));
         */
        $this->form = $form;
    }

    function submitServer() {
        require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskServer.php');
        global $tpl;
        $this->initAddForm();
        if (!$this->form->checkInput()) {
            $this->form->setValuesByPost();
            $tpl->setContent($this->form->getHTML());
            return;
        }
        $inputName = $this->form->getInput('name');
        $inputUrlPrefix = $this->form->getInput("url_prefix");
        if ($_GET["server_id"]) {
            $mumieServer = new ilMumieTaskServer($_GET["server_id"]);
        } else {
            $mumieServer = new ilMumieTaskServer();
        }
        $mumieServer->setName($inputName);
        $mumieServer->setUrlPrefix($inputUrlPrefix);
        $mumieServer->upsert();
        $this->listServers();
    }

    function submitEditServer() {
        require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskServer.php');
    }

    function deleteServer() {
        require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskServer.php');

        $server = new ilMumieTaskServer($_GET['server_id']);
        $server->delete();
        $cmd = "configure";
        $this->$cmd();
    }

    function editServer() {
        global $tpl, $DIC;
        $id = $_GET['server_id'];
        $DIC->ctrl()->setParameter($this, "server_id", $id);
        require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskServer.php');
        $this->initEditForm($this->loadServerSettings($id));
        $tpl->setContent($this->form->getHTML());
    }

    protected function loadServerSettings($id) {
        $values = array();
        $server = new ilMumieTaskServer($id);

        $server->load();
        $values["name"] = $server->getName();
        $values["url_prefix"] = $server->getUrlPrefix();
        return $values;
    }

    private function initEditForm($values = array()) {
        global $ilCtrl, $lng;

        include_once ("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this));
        $form->setTitle("FORM TITLE");

        $item1 = new ilTextInputGUI("name", 'name');
        $item1->setValue($values["name"]);
        $form->addItem($item1);

        $item2 = new ilTextInputGUI("url_prefix", 'url_prefix');
        $item2->setValue($values["url_prefix"]);
        $form->addItem($item2);

        $form->addCommandButton('submitServer', $lng->txt('save'));
        $form->addCommandButton('listServers', $lng->txt('cancel'));
        $this->form = $form;
    }
}

?>