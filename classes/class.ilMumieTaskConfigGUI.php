<?php
/**
 * MumieTask plugin
 *
 * @copyright   2019 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

include_once("./Services/Component/classes/class.ilPluginConfigGUI.php");
require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/i18n/class.ilMumieTaskI18N.php');
/**
 * @ilCtrl_IsCalledBy ilMumieTaskConfigGUI: ilObjComponentSettingsGUI
 */
class ilMumieTaskConfigGUI extends ilPluginConfigGUI
{
    private ilMumieTaskI18N $i18N;

    public function __construct()
    {
        $this->i18N = new ilMumieTaskI18N();
    }

    /**
     * Handles all commands, default is "configure"
     */
    public function performCommand(string $cmd): void
    {
        global $ilCtrl;

        $cmd = $ilCtrl->getCmd($cmd);

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

    /**
     * Entry point for this gui
     */
    public function configure()
    {
        global $ilTabs;

        $this->setTabs();
        $this->listServers();
        $ilTabs->activateTab("tab_servers");
    }

    public function setTabs()
    {
        global $ilCtrl, $ilTabs;
        $i18N = $this->i18N;
        $ilTabs->clearTargets();

        $ilTabs->addTab(
            "tab_servers",
            $i18N->txt("tab_servers"),
            $ilCtrl->getLinkTarget($this, "listServers")
        );
        $ilTabs->addTab(
            "tab_shared_data",
            $i18N->txt("tab_shared_data"),
            $ilCtrl->getLinkTarget($this, "sharedData")
        );

        $ilTabs->addTab(
            'tab_authentication',
            $i18N->txt("tab_authentication"),
            $ilCtrl->getLinkTarget($this, "authentication")
        );
    }

    /**
     * List all configured MUMIE servers with options to add, edit and delete
     */
    public function listServers()
    {
        global $tpl, $ilTabs;
        $ilTabs->activateTab("tab_servers");
        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskServerTableGUI.php');
        $server_gui = new ilMumieTaskServerTableGUI($this, 'listServers');
        $server_gui->init($this);
        $tpl->setContent($server_gui->getHTML());
    }

    /**
     * Display options for sharing personal data
     */
    public function sharedData($setSavedValues = false)
    {
        global $tpl, $ilTabs;
        $ilTabs->activateTab("tab_shared_data");
        $this->initShareDataForm();

        $tpl->setContent($this->form->getHTML());
    }

    /**
     * Define and initialize the form for privacy options
     */
    public function initShareDataForm($load_saved_values = true)
    {
        global $lng, $ilCtrl;
        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskAdminSettings.php');
        $admin_settings = ilMumieTaskAdminSettings::getInstance();
        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this));
        $form->setTitle($this->i18N->txt("tab_shared_data"));
        $form->setDescription($this->i18N->txt("frm_shared_data_description"));

        $first_name_item = new ilCheckboxInputGUI($this->i18N->txt("frm_share_first_name"), "shareFirstName");
        $first_name_item->setInfo($this->i18N->txt("frm_share_first_name_desc"));
        if ($admin_settings->getShareFirstName() && $load_saved_values) {
            $first_name_item->setValue('1');
            $first_name_item->setChecked(true);
        }
        $last_name_item = new ilCheckboxInputGUI($this->i18N->txt("frm_share_last_name"), "shareLastName");
        $last_name_item->setInfo($this->i18N->txt("frm_share_last_name_desc"));
        if ($admin_settings->getShareLastName() && $load_saved_values) {
            $last_name_item->setValue('1');
            $last_name_item->setChecked(true);
        }

        $email_item = new ilCheckboxInputGUI($this->i18N->txt("frm_share_email"), "shareEmail");
        $email_item->setInfo($this->i18N->txt("frm_share_email_desc"));
        if ($admin_settings->getShareEmail() && $load_saved_values) {
            $email_item->setValue('1');
            $email_item->setChecked(true);
        }

        $form->addItem($first_name_item);
        $form->addItem($last_name_item);
        $form->addItem($email_item);
        $form->addCommandButton('submitSharedData', $this->i18N->globalTxt('save'));
        $form->addCommandButton('config', $this->i18N->globalTxt('cancel'));
        $this->form = $form;
    }

    /**
     * Submit changes made in the shared data form
     */
    private function submitSharedData()
    {
        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskAdminSettings.php');
        global $tpl, $DIC;
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
        $DIC->ui()->mainTemplate()->setOnScreenMessage('success', $this->i18N->txt('msg_suc_saved'), true);
        $this->$cmd();
    }

    /**
     * Display form for authentication
     */
    public function authentication()
    {
        global $tpl, $ilTabs;
        $ilTabs->activateTab("tab_authentication");
        $this->initAuthForm();
        $tpl->setContent($this->form->getHTML());
    }

    /**
     * Define and initialize the form for authentication
     */
    public function initAuthForm($load_saved_values = true)
    {
        global $lng, $ilCtrl;
        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskAdminSettings.php');
        $admin_settings = ilMumieTaskAdminSettings::getInstance();
        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this));
        $form->setTitle($this->i18N->txt("tab_authentication"));
        $api_item = new ilTextInputGUI($this->i18N->txt('frm_auth_api'), 'api_key');
        $api_item->setInfo($this->i18N->txt('frm_auth_api_desc'));
        $org_item = new ilTextInputGUI($this->i18N->txt('frm_auth_org'), 'org');
        $org_item->setInfo($this->i18N->txt('frm_auth_org_desc'));
        if ($load_saved_values) {
            $org_item->setValue($admin_settings->getOrg());
            $api_item->setValue($admin_settings->getApiKey());
        }
        $form->addCommandButton('submitAuthForm', $this->i18N->globalTxt('save'));
        $form->addCommandButton('authentication', $this->i18N->globalTxt('cancel'));
        $form->addItem($org_item);
        $form->addItem($api_item);

        $this->form = $form;
    }

    /**
     * Submit changes made in the authentication form
     */
    public function submitAuthForm()
    {
        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskAdminSettings.php');
        global $tpl, $DIC;
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
        $DIC->ui()->mainTemplate()->setOnScreenMessage('success', $this->i18N->txt('msg_suc_saved'), true);
        $this->$cmd();
    }

    /**
     * Display the MUMIE server form for creation or modification
     */
    public function addServer()
    {
        global $tpl;
        $this->initServerForm();
        $this->form->setTitle($this->i18N->txt('frm_server_add_title'));
        $tpl->setContent($this->form->getHTML());
    }


    /**
     * Initialize and set command buttons for the MUMIE server form
     */
    private function initServerForm()
    {
        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/forms/class.ilMumieTaskServerFormGUI.php');
        $form = new ilMumieTaskServerFormGUI();
        $form->setFields();
        $form->addCommandButton('submitServer', $this->i18N->globalTxt('save'));
        $form->addCommandButton('cancelServer', $this->i18N->globalTxt('cancel'));
        $this->form = $form;
    }

    /**
     * Create a new or edit an existing MUMIE server
     *
     * Params in query
     */
    public function submitServer()
    {
        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskServer.php');
        global $tpl, $DIC;
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
        $DIC->ui()->mainTemplate()->setOnScreenMessage('success', $this->i18N->txt('msg_suc_server_add'), true);
        $this->listServers();
    }

    /**
     * Delete an existing MUMIE server
     *
     * Values in query
     */
    public function deleteServer()
    {
        global $DIC;
        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskServer.php');
        $server = new ilMumieTaskServer($_GET['server_id']);
        $server->delete();
        $cmd = "configure";
        $DIC->ui()->mainTemplate()->setOnScreenMessage('success', $this->i18N->txt('msg_suc_deleted'), true);
        $this->$cmd();
    }

    /**
     * Display form to edit an existing MUMIE server
     */
    public function editServer()
    {
        global $tpl, $DIC, $ilCtrl;
        $id = $_GET['server_id'];
        $DIC->ctrl()->setParameter($this, "server_id", $id);
        $this->initServerForm();
        $this->form->setValuesByArray($this->loadServerSettings($id));
        $this->form->setTitle($this->i18N->txt('frm_server_edit_title'));
        $this->form->setFormAction($ilCtrl->getFormAction($this));
        $tpl->setContent($this->form->getHTML());
    }

    /**
     * Return settings of a given server as array
     */
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

    /**
     * Execute this function if cancel is pressed in the MUMIE server form
     */
    public function cancelServer()
    {
        $this->listServers();
    }
}
