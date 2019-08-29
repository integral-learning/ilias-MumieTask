<?php

include_once ("./Services/Component/classes/class.ilPluginConfigGUI.php");

class ilMumieTaskConfigGUI extends ilPluginConfigGUI {

    /**
     * Handles all commmands, default is "configure"
     */
    function performCommand($cmd) {
        global $tree, $rbacsystem, $ilErr, $lng, $ilCtrl, $tpl;

        $cmd = $ilCtrl->getCmd($this);

        $this->setTabs();
        switch ($cmd) {
            case 'addServer':
            case 'deleteServer':
            case 'editServer':
            case 'listServers':
            case 'sharedData':
            case 'authentication':
            default:
                if (!$cmd) {
                    $cmd = "configure";
                }
                $this->$cmd();
                break;
        }
    }

    function configure() {
        global $tpl, $ilToolbar, $ilCtrl, $ilTabs;

        $this->setTabs();
        $this->listServers();
        $ilTabs->activateTab("tab_servers");
    }

    function setTabs() {
        global $ilCtrl, $ilTabs, $lng;
        $ilTabs->clearTargets();

        $ilTabs->addTab("tab_servers",
            $lng->txt("rep_robj_xmum_tab_servers"),
            $ilCtrl->getLinkTarget($this, "listServers")
        );
        $ilTabs->addTab("tab_shared_data",
            $lng->txt("rep_robj_xmum_tab_shared_data"),
            $ilCtrl->getLinkTarget($this, "sharedData")
        );

        $ilTabs->addTab('tab_authentication',
            $lng->txt("rep_robj_xmum_tab_authentication"),
            $ilCtrl->getLinkTarget($this, "authentication")
        );
    }

    function listServers() {
        global $tpl, $ilTabs;
        $ilTabs->activateTab("tab_servers");
        require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskServerTableGUI.php');
        $server_gui = new ilMumieTaskServerTableGUI($this, 'listServers');
        $server_gui->init($this);
        $tpl->setContent($server_gui->getHTML());
    }

    function sharedData($setSavedValues = false) {
        require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskAdminSettings.php');

        global $tpl, $ilTabs;
        $ilTabs->activateTab("tab_shared_data");
        $this->initShareDataForm();

        $tpl->setContent($this->form->getHTML());
    }

    function initShareDataForm($loadSavedValues = true) {
        global $lng, $tpl, $ilTabs, $ilCtrl;
        require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskAdminSettings.php');
        $adminSettings = ilMumieTaskAdminSettings::getInstance();
        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this));
        $form->setTitle($lng->txt("rep_robj_xmum_tab_shared_data"));
        $form->setDescription($lng->txt("rep_robj_xmum_frm_shared_data_description"));

        $firstNameItem = new ilCheckboxInputGUI($lng->txt("rep_robj_xmum_share_first_name"), "shareFirstName");
        /*
        if ($adminSettings->getShare_first_name() && $loadSavedValues) {
        $firstNameItem->setValue('1');
        }*/
        $lastNameItem = new ilCheckboxInputGUI($lng->txt("rep_robj_xmum_share_last_name"), "shareLastName");
        /*
        if ($adminSettings->getShare_last_name() && $loadSavedValues) {
        $lastNameItem->setValue('1');
        }
         */
        $emailItem = new ilCheckboxInputGUI($lng->txt("rep_robj_xmum_share_email", "shareEmail"));
        /*
        if ($adminSettings->getShare_email() && $loadSavedValues) {
        $emailItem->setValue('1');
        }
         */
        $form->addItem($firstNameItem);
        $form->addItem($lastNameItem);
        $form->addItem($emailItem);
        $form->addCommandButton('submitSharedData', $lng->txt('save'));
        $form->addCommandButton('config', $lng->txt('cancel'));
        $this->form = $form;
    }

    private function submitSharedData() {
        require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskAdminSettings.php');
        global $tpl, $ilCtrl;
        //debug_to_console("shareFirstName is called");
        $this->initShareDataForm(false);
        if (!$this->form->checkInput()) {
            $this->form->setValuesByPost();
            $tpl->setContent($this->form->getHTML());
            return;
        }

        $adminSettings = ilMumieTaskAdminSettings::getInstance();
        $adminSettings->setShare_first_name($this->form->getInput('shareFirstName'));
        $adminSettings->SetShare_last_name($this->form->getInput('shareLastName'));
        //$adminSettings->SetShare_email($this->form->getInput('shareEmail'));
        //debug_to_console("shareFirstName = " . $this->form->getInput('shareFirstName') . "adminsettings share name: " . json_encode($this->form->getInputItemsRecursive()));
        $adminSettings->update();
        //$cmd = "configure";
        //$this->$cmd();
    }

    function authentication() {
        global $lng, $tpl, $ilTabs;
        $ilTabs->activateTab("tab_authentication");
        $form = new ilPropertyFormGUI();
        $apiItem = new ilTextInputGUI($lng->txt('rep_robj_xmum_frm_auth_api'), 'api_key');
        $apiItem->setInfo($lng->txt('rep_robj_xmum_frm_auth_api_desc'));
        $form->addItem($apiItem);
        $orgItem = new ilTextInputGUI($lng->txt('rep_robj_xmum_frm_auth_org'), 'org');
        $orgItem->setInfo($lng->txt('rep_robj_xmum_frm_auth_org_desc'));
        $form->addItem($orgItem);
        $form->addCommandButton('submitAuthentication', $lng->txt('save'));
        $form->addCommandButton('config', $lng->txt('cancel'));

        $this->form = $form;

        $tpl->setContent($form->getHTML());
    }

    function addServer() {
        global $tpl;
        $this->initAddForm();
        $tpl->setContent($this->form->getHTML());
    }

    private function initAddForm() {
        global $ilCtrl, $lng;
        require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/forms/class.ilMumieTaskServerFormGUI.php');

        $form = new ilMumieTaskServerFormGUI();
        $form->setFields();
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

function debug_to_console($data) {
    $output = $data;
    if (is_array($output)) {
        $output = implode(',', $output);
    }

    echo "<script>console.log('Debug Objects: " . $output . "' );</script>";
}

?>