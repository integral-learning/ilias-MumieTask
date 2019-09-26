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
            case 'cancelServer':
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

        $firstNameItem = new ilCheckboxInputGUI($lng->txt("rep_robj_xmum_frm_share_first_name"), "shareFirstName");
        $firstNameItem->setInfo($lng->txt("rep_robj_xmum_frm_share_first_name_desc"));
        if ($adminSettings->getShareFirstName() && $loadSavedValues) {
            $firstNameItem->setValue('1');
            $firstNameItem->setChecked(true);
        }
        $lastNameItem = new ilCheckboxInputGUI($lng->txt("rep_robj_xmum_frm_share_last_name"), "shareLastName");
        $lastNameItem->setInfo($lng->txt("rep_robj_xmum_frm_share_last_name_desc"));
        if ($adminSettings->getShareLastName() && $loadSavedValues) {
            $lastNameItem->setValue('1');
            $lastNameItem->setChecked(true);
        }

        $emailItem = new ilCheckboxInputGUI($lng->txt("rep_robj_xmum_frm_share_email"), "shareEmail");
        $emailItem->setInfo($lng->txt("rep_robj_xmum_frm_share_email_desc"));
        if ($adminSettings->getShareEmail() && $loadSavedValues) {
            $emailItem->setValue('1');
            $emailItem->setChecked(true);
        }

        $form->addItem($firstNameItem);
        $form->addItem($lastNameItem);
        $form->addItem($emailItem);
        $form->addCommandButton('submitSharedData', $lng->txt('save'));
        $form->addCommandButton('config', $lng->txt('cancel'));
        $this->form = $form;
    }

    private function submitSharedData() {
        require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskAdminSettings.php');
        global $tpl, $ilCtrl, $lng;
        $this->initShareDataForm(false);
        if (!$this->form->checkInput()) {
            $this->form->setValuesByPost();
            $tpl->setContent($this->form->getHTML());
            return;
        }

        $adminSettings = ilMumieTaskAdminSettings::getInstance();
        $adminSettings->setShareFirstName($this->form->getInput('shareFirstName'));
        $adminSettings->setShareLastName($this->form->getInput('shareLastName'));
        $adminSettings->setShareEmail($this->form->getInput('shareEmail'));
        $adminSettings->update();
        $cmd = "sharedData";
        ilUtil::sendSuccess($lng->txt('rep_robj_xmum_msg_suc_saved'), true);
        $this->$cmd();
    }

    function authentication() {
        global $lng, $tpl, $ilTabs;
        $ilTabs->activateTab("tab_authentication");
        $this->initAuthForm();
        $tpl->setContent($this->form->getHTML());
    }

    function initAuthForm($loadSavedValues = true) {
        global $lng, $tpl, $ilTabs, $ilCtrl;
        require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskAdminSettings.php');
        $adminSettings = ilMumieTaskAdminSettings::getInstance();
        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this));
        $form->setTitle($lng->txt("rep_robj_xmum_tab_authentication"));
        $apiItem = new ilTextInputGUI($lng->txt('rep_robj_xmum_frm_auth_api'), 'api_key');
        $apiItem->setInfo($lng->txt('rep_robj_xmum_frm_auth_api_desc'));
        $orgItem = new ilTextInputGUI($lng->txt('rep_robj_xmum_frm_auth_org'), 'org');
        $orgItem->setInfo($lng->txt('rep_robj_xmum_frm_auth_org_desc'));
        if ($loadSavedValues) {
            $orgItem->setValue($adminSettings->getOrg());
            $apiItem->setValue($adminSettings->getApiKey());
        }
        $form->addCommandButton('submitAuthForm', $lng->txt('save'));
        $form->addCommandButton('authentication', $lng->txt('cancel'));
        $form->addItem($orgItem);
        $form->addItem($apiItem);

        $this->form = $form;
    }

    function submitAuthForm() {
        require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskAdminSettings.php');
        global $tpl, $ilCtrl, $lng;
        $this->initAuthForm(false);
        if (!$this->form->checkInput()) {
            $this->form->setValuesByPost();
            $tpl->setContent($this->form->getHTML());
            return;
        }

        $adminSettings = ilMumieTaskAdminSettings::getInstance();
        $adminSettings->setApiKey($this->form->getInput("api_key"));
        $adminSettings->setOrg($this->form->getInput("org"));
        $adminSettings->update();
        $cmd = "authentication";
        ilUtil::sendSuccess($lng->txt('rep_robj_xmum_msg_suc_saved'), true);
        $this->$cmd();
    }

    function addServer() {
        global $tpl, $lng;
        $this->initServerForm();
        $this->form->setTitle($lng->txt('rep_robj_xmum_frm_server_add_title'));
        $tpl->setContent($this->form->getHTML());
    }

    private function initServerForm() {
        global $ilCtrl, $lng;
        require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/forms/class.ilMumieTaskServerFormGUI.php');
        $form = new ilMumieTaskServerFormGUI();
        $form->setFields();
        $form->addCommandButton('submitServer', $lng->txt('save'));
        $form->addCommandButton('cancelServer', $lng->txt('cancel'));
        $this->form = $form;
    }

    function submitServer() {
        require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskServer.php');
        global $tpl, $lng;
        $this->initServerForm();
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
        ilUtil::sendSuccess($lng->txt('rep_robj_xmum_msg_suc_server_add'), true);
        $this->listServers();
    }

    function deleteServer() {
        global $lng;
        require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskServer.php');
        $server = new ilMumieTaskServer($_GET['server_id']);
        $server->delete();
        $cmd = "configure";
        ilUtil::sendSuccess($lng->txt('rep_robj_xmum_msg_suc_deleted'), true);
        $this->$cmd();
    }

    function editServer() {
        global $tpl, $DIC, $lng, $ilCtrl;
        $id = $_GET['server_id'];
        $DIC->ctrl()->setParameter($this, "server_id", $id);
        $this->initServerForm();
        $this->form->setValuesByArray($this->loadServerSettings($id));
        $this->form->setTitle($lng->txt('rep_robj_xmum_frm_server_edit_title'));
        $this->form->setFormAction($ilCtrl->getFormAction($this));
        $tpl->setContent($this->form->getHTML());
    }

    protected function loadServerSettings($id) {
        require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskServer.php');
        $values = array();
        $server = new ilMumieTaskServer($id);
        $server->load();
        $values["name"] = $server->getName();
        $values["url_prefix"] = $server->getUrlPrefix();
        return $values;
    }

    function cancelServer() {
        $this->listServers();
    }
}
?>