<?php

/**
 * MumieTask plugin.
 *
 * @copyright   2019 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

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
     * Handles all commands, default is "configure".
     */
    public function performCommand(string $cmd): void
    {
        global $DIC;

        $cmd = $DIC->ctrl()->getCmd($cmd);

        $this->setTabs();
        switch ($cmd) {
            case 'addServer':
            case 'deleteServer':
            case 'editServer':
            case 'cancelServer':
            case 'listServers':
            case 'sharedData':
            case 'authentication':
            case 'problemSelector':
            default:
                if (!$cmd) {
                    $cmd = 'configure';
                }
                $this->$cmd();
                break;
        }
    }

    /**
     * Entry point for this gui.
     */
    public function configure()
    {
        global $DIC;

        $this->setTabs();
        $this->listServers();
        $DIC->tabs()->activateTab('tab_servers');
    }

    public function setTabs()
    {
        global $DIC;
        $ctrl = $DIC->ctrl();
        $tabs = $DIC->tabs();

        $i18N = $this->i18N;
        $tabs->clearTargets();

        $tabs->addTab(
            'tab_servers',
            $i18N->txt('tab_servers'),
            $ctrl->getLinkTarget($this, 'listServers'),
        );
        $tabs->addTab(
            'tab_shared_data',
            $i18N->txt('tab_shared_data'),
            $ctrl->getLinkTarget($this, 'sharedData'),
        );

        $tabs->addTab(
            'tab_authentication',
            $i18N->txt('tab_authentication'),
            $ctrl->getLinkTarget($this, 'authentication'),
        );
        $tabs->addTab(
            'tab_problem_selector',
            $i18N->txt('tab_problem_selector'),
            $ctrl->getLinkTarget($this, 'problemSelector'),
        );
    }

    /**
     * List all configured MUMIE servers with options to add, edit and delete.
     */
    public function listServers()
    {
        global $DIC;
        $DIC->tabs()->activateTab('tab_servers');
        $server_gui = new ilMumieTaskServerTableGUI($this, 'listServers');
        $server_gui->init($this);
        $DIC->ui()->mainTemplate()->setContent($server_gui->getHTML());
    }

    /**
     * Display options for sharing personal data.
     */
    public function sharedData()
    {
        global $DIC;
        $DIC->tabs()->activateTab('tab_shared_data');
        $this->initShareDataForm();

        $DIC->ui()->mainTemplate()->setContent($this->form->getHTML());
    }

    /**
     * Define and initialize the form for privacy options.
     */
    public function initShareDataForm($load_saved_values = true)
    {
        global $DIC;
        $admin_settings = ilMumieTaskAdminSettings::getInstance();
        $form = new ilPropertyFormGUI();
        $form->setFormAction($DIC->ctrl()->getFormAction($this));
        $form->setTitle($this->i18N->txt('tab_shared_data'));
        $form->setDescription($this->i18N->txt('frm_shared_data_description'));

        $first_name_item = new ilCheckboxInputGUI($this->i18N->txt('frm_share_first_name'), 'shareFirstName');
        $first_name_item->setInfo($this->i18N->txt('frm_share_first_name_desc'));
        if ($admin_settings->getShareFirstName() && $load_saved_values) {
            $first_name_item->setValue('1');
            $first_name_item->setChecked(true);
        }
        $last_name_item = new ilCheckboxInputGUI($this->i18N->txt('frm_share_last_name'), 'shareLastName');
        $last_name_item->setInfo($this->i18N->txt('frm_share_last_name_desc'));
        if ($admin_settings->getShareLastName() && $load_saved_values) {
            $last_name_item->setValue('1');
            $last_name_item->setChecked(true);
        }

        $email_item = new ilCheckboxInputGUI($this->i18N->txt('frm_share_email'), 'shareEmail');
        $email_item->setInfo($this->i18N->txt('frm_share_email_desc'));
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
     * Submit changes made in the shared data form.
     *
     * @SuppressWarnings("PHPMD.UnusedPrivateMethod")
     */
    private function submitSharedData()
    {
        global $DIC;
        $this->initShareDataForm(false);
        if (!$this->form->checkInput()) {
            $this->form->setValuesByPost();
            $DIC->ui()->mainTemplate()->setContent($this->form->getHTML());

            return;
        }

        $admin_settings = ilMumieTaskAdminSettings::getInstance();
        $admin_settings->setShareFirstName((int) $this->form->getInput('shareFirstName'));
        $admin_settings->setShareLastName((int) $this->form->getInput('shareLastName'));
        $admin_settings->setShareEmail((int) $this->form->getInput('shareEmail'));
        $admin_settings->update();
        $cmd = 'sharedData';
        $DIC->ui()->mainTemplate()->setOnScreenMessage('success', $this->i18N->txt('msg_suc_saved'), true);
        $this->$cmd();
    }

    /**
     * Display form for authentication.
     */
    public function authentication()
    {
        global $DIC;
        $DIC->tabs()->activateTab('tab_authentication');
        $this->initAuthForm();
        $DIC->ui()->mainTemplate()->setContent($this->form->getHTML());
    }

    /**
     * Define and initialize the form for authentication.
     */
    public function initAuthForm($load_saved_values = true)
    {
        global $DIC;
        $admin_settings = ilMumieTaskAdminSettings::getInstance();
        $form = new ilPropertyFormGUI();
        $form->setFormAction($DIC->ctrl()->getFormAction($this));
        $form->setTitle($this->i18N->txt('tab_authentication'));
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
     * Display form for problem selector.
     */
    public function problemSelector()
    {
        global $DIC;
        $DIC->tabs()->activateTab('tab_problem_selector');
        $this->initProblemSelectorUrl();
        $DIC->ui()->mainTemplate()->setContent($this->form->getHTML());
    }

    /**
     * Define and initialize the form for problem selector.
     */
    public function initProblemSelectorUrl($load_saved_values = true)
    {
        global $DIC;
        $admin_settings = ilMumieTaskAdminSettings::getInstance();

        $form = new ilPropertyFormGUI();
        $form->setFormAction($DIC->ctrl()->getFormAction($this));

        $form->setTitle($this->i18N->txt('tab_problem_selector'));
        $form->setDescription($this->i18N->txt('tab_problem_selector_no_change'));

        $url_input = new ilTextInputGUI(
            $this->i18N->txt('tab_problem_selector_label_text'),
            'problem_selector_url',
        );
        $url_input->setRequired(true);
        if ($load_saved_values) {
            $url_input->setValue($admin_settings->getProblemSelectorUrl());
        }
        $url_input->setInfo($this->i18N->txt('tab_problem_selector_info_text'));

        $url_input->setValidationRegexp('/^http?[s]+:\/\/[^\s$.?#].[^\s]*$/i');
        $url_input->setValidationFailureMessage($this->i18N->txt('tab_problem_selector_info_text'));
        $form->addItem($url_input);

        $form->addCommandButton('saveProblemSelectorUrl', $this->i18N->txt('frm_save'));
        $form->addCommandButton('problemSelector', $this->i18N->txt('frm_cancel'));

        $this->form = $form;
    }

    /**
     * Submit changes made in the authentication form.
     */
    public function submitAuthForm()
    {
        global $DIC;
        $this->initAuthForm(false);
        if (!$this->form->checkInput()) {
            $this->form->setValuesByPost();
            $DIC->ui()->mainTemplate()->setContent($this->form->getHTML());

            return;
        }

        $admin_settings = ilMumieTaskAdminSettings::getInstance();
        $admin_settings->setApiKey($this->form->getInput('api_key'));
        $admin_settings->setOrg($this->form->getInput('org'));
        $admin_settings->update();
        $cmd = 'authentication';
        $DIC->ui()->mainTemplate()->setOnScreenMessage('success', $this->i18N->txt('msg_suc_saved'), true);
        $this->$cmd();
    }

    /**
     * Display the MUMIE server form for creation or modification.
     */
    public function addServer()
    {
        global $DIC;
        $this->initServerForm();
        $this->form->setTitle($this->i18N->txt('frm_server_add_title'));
        $DIC->ui()->mainTemplate()->setContent($this->form->getHTML());
    }

    /**
     * Submit changes made in the problem selector form.
     */
    public function saveProblemSelectorUrl(): void
    {
        global $DIC;
        $this->initProblemSelectorUrl(false);
        if (!$this->form->checkInput()) {
            $this->form->setValuesByPost();
            $DIC->ui()->mainTemplate()->setContent($this->form->getHTML());

            return;
        }

        $admin_settings = ilMumieTaskAdminSettings::getInstance();
        $admin_settings->setProblemSelectorUrl($this->form->getInput('problem_selector_url'));
        $admin_settings->update();
        $cmd = 'problemSelector';
        $DIC->ui()->mainTemplate()->setOnScreenMessage('success', $this->i18N->txt('msg_suc_saved'), true);
        $this->$cmd();
    }

    /**
     * Initialize and set command buttons for the MUMIE server form.
     */
    private function initServerForm()
    {
        $form = new ilMumieTaskServerFormGUI();
        $form->setFields();
        $form->addCommandButton('submitServer', $this->i18N->globalTxt('save'));
        $form->addCommandButton('cancelServer', $this->i18N->globalTxt('cancel'));
        $this->form = $form;
    }

    /**
     * Create a new or edit an existing MUMIE server.
     *
     * Params in query
     */
    public function submitServer()
    {
        global $DIC;
        $this->initServerForm();
        if (!$this->form->checkInput()) {
            $this->form->setValuesByPost();
            $DIC->ui()->mainTemplate()->setContent($this->form->getHTML());

            return;
        }
        $input_name = $this->form->getInput('name');
        $input_url_prefix = $this->form->getInput('url_prefix');
        if ($_GET['server_id']) {
            $mumie_server = new ilMumieTaskServer($_GET['server_id']);
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
     * Delete an existing MUMIE server.
     *
     * Values in query
     */
    public function deleteServer()
    {
        global $DIC;
        $server = new ilMumieTaskServer($_GET['server_id']);
        $server->delete();
        $cmd = 'configure';
        $DIC->ui()->mainTemplate()->setOnScreenMessage('success', $this->i18N->txt('msg_suc_deleted'), true);
        $this->$cmd();
    }

    /**
     * Display form to edit an existing MUMIE server.
     */
    public function editServer()
    {
        global $DIC;
        $id = $_GET['server_id'];
        $DIC->ctrl()->setParameter($this, 'server_id', $id);
        $this->initServerForm();
        $this->form->setValuesByArray($this->loadServerSettings($id));
        $this->form->setTitle($this->i18N->txt('frm_server_edit_title'));
        $this->form->setFormAction($DIC->ctrl()->getFormAction($this));
        $DIC->ui()->mainTemplate()->setContent($this->form->getHTML());
    }

    /**
     * Return settings of a given server as array.
     */
    protected function loadServerSettings($id)
    {
        $values = [];
        $server = new ilMumieTaskServer($id);
        $server->load();
        $values['name'] = $server->getName();
        $values['url_prefix'] = $server->getUrlPrefix();

        return $values;
    }

    /**
     * Execute this function if cancel is pressed in the MUMIE server form.
     */
    public function cancelServer()
    {
        $this->listServers();
    }
}
