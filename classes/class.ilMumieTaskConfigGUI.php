<?php

include_once("./Services/Component/classes/class.ilPluginConfigGUI.php");

class ilMumieTaskConfigGUI extends ilPluginConfigGUI
{

    /**
     * Handles all commmands, default is "configure"
     */
    public function performCommand($cmd)
    {
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

    public function configure()
    {
        global $tpl, $ilToolbar, $ilCtrl, $ilTabs;

        $this->setTabs();
        $this->listServers();
        $ilTabs->activateTab("tab_servers");
    }

    public function setTabs()
    {
        global $ilCtrl, $ilTabs, $lng;
        $ilTabs->clearTargets();

        $ilTabs->addTab(
            "tab_servers",
            $lng->txt("rep_robj_xmum_tab_servers"),
            $ilCtrl->getLinkTarget($this, "listServers")
        );
        $ilTabs->addTab(
            "tab_shared_data",
            $lng->txt("rep_robj_xmum_tab_shared_data"),
            $ilCtrl->getLinkTarget($this, "sharedData")
        );

        $ilTabs->addTab(
            'tab_authentication',
            $lng->txt("rep_robj_xmum_tab_authentication"),
            $ilCtrl->getLinkTarget($this, "authentication")
        );
    }

    public function listServers()
    {
        global $tpl, $ilTabs;
        $ilTabs->activateTab("tab_servers");
        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskServerTableGUI.php');
        $server_gui = new ilMumieTaskServerTableGUI($this, 'listServers');
        $server_gui->init($this);
        $tpl->setContent($server_gui->getHTML());
    }

    public function sharedData($setSavedValues = false)
    {
        global $tpl, $ilTabs;
        $ilTabs->activateTab("tab_shared_data");
        $this->initShareDataForm();

        $tpl->setContent($this->form->getHTML());
    }

    public function initShareDataForm($load_saved_values = true)
    {
        global $lng, $tpl, $ilTabs, $ilCtrl;
        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskAdminSettings.php');
        $admin_settings = ilMumieTaskAdminSettings::getInstance();
        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this));
        $form->setTitle($lng->txt("rep_robj_xmum_tab_shared_data"));
        $form->setDescription($lng->txt("rep_robj_xmum_frm_shared_data_description"));

        $first_name_item = new ilCheckboxInputGUI($lng->txt("rep_robj_xmum_frm_share_first_name"), "shareFirstName");
        $first_name_item->setInfo($lng->txt("rep_robj_xmum_frm_share_first_name_desc"));
        if ($admin_settings->getShareFirstName() && $load_saved_values) {
            $first_name_item->setValue('1');
            $first_name_item->setChecked(true);
        }
        $last_name_item = new ilCheckboxInputGUI($lng->txt("rep_robj_xmum_frm_share_last_name"), "shareLastName");
        $last_name_item->setInfo($lng->txt("rep_robj_xmum_frm_share_last_name_desc"));
        if ($admin_settings->getShareLastName() && $load_saved_values) {
            $last_name_item->setValue('1');
            $last_name_item->setChecked(true);
        }

        $email_item = new ilCheckboxInputGUI($lng->txt("rep_robj_xmum_frm_share_email"), "shareEmail");
        $email_item->setInfo($lng->txt("rep_robj_xmum_frm_share_email_desc"));
        if ($admin_settings->getShareEmail() && $load_saved_values) {
            $email_item->setValue('1');
            $email_item->setChecked(true);
        }

        $form->addItem($first_name_item);
        $form->addItem($last_name_item);
        $form->addItem($email_item);
        $form->addCommandButton('submitSharedData', $lng->txt('save'));
        $form->addCommandButton('config', $lng->txt('cancel'));
        $this->form = $form;
    }

    private function submitSharedData()
    {
        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskAdminSettings.php');
        global $tpl, $ilCtrl, $lng;
        $this->initShareDataForm(false);
        if (!$this->form->checkInput()) {
            $this->form->setValuesByPost();
            $tpl->setContent($this->form->getHTML());
            return;
        }

        $admin_settings = ilMumieTaskAdminSettings::getInstance();
        $admin_settings->setShareFirstName($this->form->getInput('shareFirstName'));
        $admin_settings->setShareLastName($this->form->getInput('shareLastName'));
        $admin_settings->setShareEmail($this->form->getInput('shareEmail'));
        $admin_settings->update();
        $cmd = "sharedData";
        ilUtil::sendSuccess($lng->txt('rep_robj_xmum_msg_suc_saved'), true);
        $this->$cmd();
    }

    public function authentication()
    {
        global $lng, $tpl, $ilTabs;
        $ilTabs->activateTab("tab_authentication");
        $this->initAuthForm();
        $tpl->setContent($this->form->getHTML());
    }

    public function initAuthForm($load_saved_values = true)
    {
        global $lng, $tpl, $ilTabs, $ilCtrl;
        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskAdminSettings.php');
        $admin_settings = ilMumieTaskAdminSettings::getInstance();
        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this));
        $form->setTitle($lng->txt("rep_robj_xmum_tab_authentication"));
        $api_item = new ilTextInputGUI($lng->txt('rep_robj_xmum_frm_auth_api'), 'api_key');
        $api_item->setInfo($lng->txt('rep_robj_xmum_frm_auth_api_desc'));
        $org_item = new ilTextInputGUI($lng->txt('rep_robj_xmum_frm_auth_org'), 'org');
        $org_item->setInfo($lng->txt('rep_robj_xmum_frm_auth_org_desc'));
        if ($load_saved_values) {
            $org_item->setValue($admin_settings->getOrg());
            $api_item->setValue($admin_settings->getApiKey());
        }
        $form->addCommandButton('submitAuthForm', $lng->txt('save'));
        $form->addCommandButton('authentication', $lng->txt('cancel'));
        $form->addItem($org_item);
        $form->addItem($api_item);

        $this->form = $form;
    }

    public function submitAuthForm()
    {
        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskAdminSettings.php');
        global $tpl, $ilCtrl, $lng;
        $this->initAuthForm(false);
        if (!$this->form->checkInput()) {
            $this->form->setValuesByPost();
            $tpl->setContent($this->form->getHTML());
            return;
        }

        $admin_settings = ilMumieTaskAdminSettings::getInstance();
        $admin_settings->setApiKey($this->form->getInput("api_key"));
        $admin_settings->setOrg($this->form->getInput("org"));
        $admin_settings->update();
        $cmd = "authentication";
        ilUtil::sendSuccess($lng->txt('rep_robj_xmum_msg_suc_saved'), true);
        $this->$cmd();
    }

    public function addServer()
    {
        global $tpl, $lng;
        $this->initServerForm();
        $this->form->setTitle($lng->txt('rep_robj_xmum_frm_server_add_title'));
        $tpl->setContent($this->form->getHTML());
    }

    private function initServerForm()
    {
        global $ilCtrl, $lng;
        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/forms/class.ilMumieTaskServerFormGUI.php');
        $form = new ilMumieTaskServerFormGUI();
        $form->setFields();
        $form->addCommandButton('submitServer', $lng->txt('save'));
        $form->addCommandButton('cancelServer', $lng->txt('cancel'));
        $this->form = $form;
    }

    public function submitServer()
    {
        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskServer.php');
        global $tpl, $lng;
        $this->initServerForm();
        if (!$this->form->checkInput()) {
            $this->form->setValuesByPost();
            $tpl->setContent($this->form->getHTML());
            return;
        }
        $input_name = $this->form->getInput('name');
        $input_url_prefix = $this->form->getInput("url_prefix");
        if ($_GET["server_id"]) {
            $mumie_server = new ilMumieTaskServer($_GET["server_id"]);
        } else {
            $mumie_server = new ilMumieTaskServer();
        }
        $mumie_server->setName($input_name);
        $mumie_server->setUrlPrefix($input_url_prefix);
        $mumie_server->upsert();
        ilUtil::sendSuccess($lng->txt('rep_robj_xmum_msg_suc_server_add'), true);
        $this->listServers();
    }

    public function deleteServer()
    {
        global $lng;
        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskServer.php');
        $server = new ilMumieTaskServer($_GET['server_id']);
        $server->delete();
        $cmd = "configure";
        ilUtil::sendSuccess($lng->txt('rep_robj_xmum_msg_suc_deleted'), true);
        $this->$cmd();
    }

    public function editServer()
    {
        global $tpl, $DIC, $lng, $ilCtrl;
        $id = $_GET['server_id'];
        $DIC->ctrl()->setParameter($this, "server_id", $id);
        $this->initServerForm();
        $this->form->setValuesByArray($this->loadServerSettings($id));
        $this->form->setTitle($lng->txt('rep_robj_xmum_frm_server_edit_title'));
        $this->form->setFormAction($ilCtrl->getFormAction($this));
        $tpl->setContent($this->form->getHTML());
    }

    protected function loadServerSettings($id)
    {
        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskServer.php');
        $values = array();
        $server = new ilMumieTaskServer($id);
        $server->load();
        $values["name"] = $server->getName();
        $values["url_prefix"] = $server->getUrlPrefix();
        return $values;
    }

    public function cancelServer()
    {
        $this->listServers();
    }
}
