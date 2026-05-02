<?php

/**
 * MumieTask plugin
 *
 * @copyright   2019 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @ilCtrl_isCalledBy ilObjMumieTaskGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
 * @ilCtrl_Calls ilObjMumieTaskGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI, ilExportGUI, ilLearningProgressGUI, ilLPListOfObjectsGUI,ilObjPluginDispatchGUI, ilLPListOfSettingsGui, ilMumieTaskLPGUI
 * @ilCtrl_Calls ilObjMumieTaskGUI: ilMumieTaskLPTableGUI
 *
 * @property ilObjMumieTask $object
 */

class ilObjMumieTaskGUI extends ilObjectPluginGUI
{
    private ilMumieTaskI18N $i18N;

    public function __construct(int $a_ref_id = 0, int $a_id_type = self::REPOSITORY_NODE_ID, int $a_parent_node_id = 0)
    {
        parent::__construct($a_ref_id, $a_id_type, $a_parent_node_id);
        $this->i18N = new ilMumieTaskI18N();
    }

    /**
     * Get type.
     */
    final public function getType(): string
    {
        return ilMumieTaskPlugin::ID;
    }

    /**
     * Handles all commands of this class, centralizes permission checks
     * @throws ilCtrlException
     */
    public function performCommand($cmd): void
    {
        switch ($cmd) {
            case "editProperties":
                $this->setSubTabs("properties");
                $cmd .= 'Object';
                $this->$cmd();
                // no break
            case 'create':
            case 'createObject':
            case "submitMumieTask":
            case 'cancelDummy':
            case 'addServer':
            case 'submitServer':
            case 'editLPSettings':
            case 'submitLPSettings':
            case 'editAvailabilitySettings':
            case 'submitAvailabilitySettings':
            case "viewContent":
            case "displayLearningProgress":
            case "submitDeadlineExtension":
            case "displayGradeList":
            case "displayDeadlineExtension":
            case "displayGradeOverviewPage":
            case "gradeOverride":
            case "deleteGradeOverride":
            case 'forceGradeUpdate':
            case 'deleteDeadlineExtension':
            case "setStatusToNotAttempted":
                $this->checkPermission("read");
                $this->$cmd();
                break;
        }
    }

    /**
     * @throws ilCtrlException
     * @throws ilObjectNotFoundException
     * @throws ilObjectTypeMismatchException
     */
    public function setTabs(): void
    {
        global $ilCtrl, $ilAccess, $ilTabs;
        $this->tabs->clearTargets();
        $this->object->read();
        if ($this->object->isDummy()) {
            return;
        }
        $this->tabs->addTab("viewContent", $this->i18N->globalTxt("content"), $ilCtrl->getLinkTarget($this, "viewContent"));
        if ($ilAccess->checkAccess("write", "", $this->object->getRefId())) {
            $this->tabs->addTab("properties", $this->i18N->txt("properties"), $ilCtrl->getLinkTarget($this, "editProperties"));
            $this->tabs->addTab("userList", $this->i18N->txt('tab_userlist'), $ilCtrl->getLinkTarget($this, "displayGradeOverviewPage"));
        }

        if ($this->object->getLpModus() && ilObjUserTracking::_enabledLearningProgress()) {
            $ilTabs->addTab("learning_progress", $this->i18N->globalTxt('learning_progress'), $ilCtrl->getLinkTarget($this, 'displayLearningProgress'));
        }

        $ilTabs->addTab("infoScreen", $this->i18N->globalTxt("info_short"), $ilCtrl->getLinkTarget($this, "infoScreen"));

        $this->addPermissionTab();
    }

    /**
     * @throws ilCtrlException
     */
    public function setSubTabs($a_tab): void
    {
        global $ilTabs, $ilCtrl;
        if ($this->object->isDummy()) {
            return;
        }
        $ilTabs->clearSubTabs();
        if ($a_tab == 'properties') {
            $ilTabs->addSubTab("edit_task", $this->i18N->txt('tab_gen_settings'), $ilCtrl->getLinkTarget($this, "editProperties"));
            $ilTabs->addSubTab("lp_settings", $this->i18N->txt('tab_lp_settings'), $ilCtrl->getLinkTargetByClass(array('ilObjMumieTaskGUI'), 'editLPSettings'));
            $this->lng->loadLanguageModule('rep');
            $ilTabs->addSubTab("availability_settings", $this->i18N->globalTxt('rep_activation_availability'), $ilCtrl->getLinkTargetByClass(array('ilObjMumieTaskGUI'), 'editAvailabilitySettings'));
        }
    }

    /**
     * Create a dummy MumieTask without any meaningful properties. The values must be set, before it can be used
     *
     * We decided to do implement creation this way, because we need the option to add MUMIE servers during the creation process, but generating any kind of output during repObj creation caused ILIAS errors
     * @throws ilCtrlException
     */
    public function create(): void
    {
        global $DIC;
        $this->setCreationMode(true);
        $refId = $_GET["ref_id"];

        $task = ilObjMumieTask::constructDummy();
        $task->setType($this->type);
        $task->create();
        $task->createReference();
        $task->putInTree($refId);
        $this->object = $task;
        $task->setParentRolePermissions($task->getParentRef());
        $task->setPrivateGradepool(ilMumieTaskLPStatus::deriveGradepoolSetting($refId));

        $task->update();

        $this->ctrl->setParameter($this, "ref_id", $this->object->getRefId());
        $this->afterSave($this->object);
        $this->setCreationMode(false);
        $DIC->ui()->mainTemplate()->setOnScreenMessage('success', $this->i18N->txt('msg_suc_saved'), true);
    }

    /**
     * Display the general settings of a MumieTask
     * @throws ilCurlConnectionException|ilCtrlException|ilDateTimeException
     */
    public function editPropertiesObject(): void
    {
        global $tpl, $ilTabs, $DIC;
        if (empty(ilMumieTaskServer::getAllServers())) {
            $this->addServer();
            $DIC->ui()->mainTemplate()->setOnScreenMessage('info', $this->i18N->txt("msg_no_server_found"), true);
            return;
        }
        $ilTabs->activateTab('properties');
        $ilTabs->activateSubTab("edit_task");
        $this->object->doRead();
        $this->initPropertiesForm();
        if (!$this->object->isDummy() && !ilMumieTaskServer::serverConfigExistsForUrl($this->object->getServer())) {
            $this->form->disable();
            $DIC->ui()->mainTemplate()->setOnScreenMessage('failure', $this->i18N->txt('msg_server_missing') . $this->object->getServer());
        } elseif (!$this->object->isDummy() && !ilMumieTaskServer::fromUrl($this->object->getServer())->isValidMumieServer()) {
            $this->form->disable();
            $DIC->ui()->mainTemplate()->setOnScreenMessage('failure', $this->i18N->txt('msg_no_connection_to_server') . $this->object->getServer());
        }
        $this->setPropertyValues();
        $tpl->addJavaScript('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/js/ilMumieTaskForm.js');
        $tpl->setContent($this->form->getHTML());
        $tpl->addCss("./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/templates/mumie.css");
    }

    /**
     * Set values for the general settings form
     * @throws ilCurlConnectionException|ilDateTimeException
     */
    public function setPropertyValues(): void
    {
        $values["title"] = $this->object->getTitle();
        $values["description"] = $this->object->getDescription();
        $values["xmum_task"] = $this->object->getTaskurl();
        $values["xmum_launchcontainer"] = $this->object->getLaunchcontainer();
        $values["xmum_course"] = $this->object->getMumieCourse();
        $values["xmum_language"] = $this->object->getLanguage();
        $values["xmum_server"] = $this->object->getServer();
        $values["xmum_coursefile"] = $this->object->getMumieCoursefile();
        $this->form->setValuesByArray($values);
    }

    /**
     * initialize the general settings form and add command buttons
     * @throws ilCtrlException
     * @throws ilCurlConnectionException
     */
    public function initPropertiesForm(): void
    {
        global $ilCtrl;

        $form = new ilMumieTaskFormGUI();
        $form->setFields();
        $form->setTitle($this->i18N->txt('obj_xmum'));
        $form->addCommandButton("submitMumieTask", $this->i18N->globalTxt('save'));
        $form->addCommandButton($this->object->isDummy() ? 'cancelDummy' : 'viewContent', $this->i18N->globalTxt('cancel'));

        $form->setFormAction($ilCtrl->getFormAction($this));
        $this->form = $form;
    }

    /**
     * submit changes made to the general settings of a MumieTask and trigger a forced grade update if necessary
     * @throws ilCurlConnectionException|ilCtrlException
     */
    public function submitMumieTask(): void
    {
        global $tpl, $ilCtrl, $DIC;
        $this->initPropertiesForm();

        if (!$this->form->checkInput()) {
            $this->form->setValuesByPost();
            $tpl->setContent($this->form->getHTML());
            $tpl->addJavaScript('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/js/ilMumieTaskForm.js');
            return;
        }
        $mumieTask = $this->object;
        $force_grade_update = $this->form->getInput('xmum_task') != $mumieTask->getTaskurl()
        || $this->form->getInput('xmum_course') != $mumieTask->getMumieCourse()
        || $this->form->getInput('xmum_server') != $mumieTask->getServer();

        $this->saveFormValues();

        if ($force_grade_update) {
            ilMumieTaskLPStatus::updateGrades($this->object, $force_grade_update);
            ilMumieTaskGradeOverrideService::deleteGradeOverridesForTask($this->object);
        }
        $DIC->ui()->mainTemplate()->setOnScreenMessage('success', $this->i18N->txt('msg_suc_saved'), true);

        $ilCtrl->redirect($this, 'editProperties');
    }

    /**
     * Update the MumieTask object with the given form values
     */
    public function saveFormValues(): void
    {
        $mumieTask = $this->object;

        $mumieTask->setTitle($this->form->getInput('title'));
        $mumieTask->setServer($this->form->getInput('xmum_server'));
        $mumieTask->setMumieCourse($this->form->getInput('xmum_course'));
        $mumieTask->setTaskurl($this->form->getInput('xmum_task'));
        $mumieTask->setLanguage($this->form->getInput('xmum_language'));
        $mumieTask->setLaunchcontainer($this->form->getInput('xmum_launchcontainer'));
        $mumieTask->setMumieCoursefile($this->form->getInput('xmum_coursefile'));
        $mumieTask->setWorksheet($this->form->getInput('xmum_worksheet'));

        $mumieTask->setDescription($this->form->getInput('description'));
        $mumieTask->update();

        $tasks_json = $this->form->getInput("xmum_multi_problems");
        if (!empty($tasks_json)) {
            ilMumieTaskMultiUploadProcessor::process($mumieTask, $tasks_json);
        }
    }

    /**
     * Display a from to add a new MUMIE server
     * @throws ilCtrlException
     */
    public function addServer(): void
    {
        global $ilTabs;
        $this->setSubTabs('properties');
        $ilTabs->activateTab('properties');
        $this->initServerForm();
        $this->form->setTitle($this->i18N->txt('frm_server_add_title'));
        $this->tpl->setContent($this->form->getHTML());
    }

    /**
     * initialize the MUMIE server form and add command buttons
     * @throws ilCtrlException
     */
    private function initServerForm(): void
    {
        global $ilCtrl;

        $form = new ilMumieTaskServerFormGUI();
        $form->setFields();
        $form->setFormAction($ilCtrl->getFormAction($this));
        $form->addCommandButton('submitServer', $this->i18N->globalTxt('save'));
        $form->addCommandButton('editProperties', $this->i18N->globalTxt('cancel'));

        $this->form = $form;
    }

    /**
     * Submit and save a new mumie server
     * @throws ilCtrlException
     * @throws ilCurlConnectionException
     */
    public function submitServer(): void
    {
        global $tpl, $DIC;
        $this->initServerForm();
        if (!$this->form->checkInput()) {
            $this->form->setValuesByPost();
            $tpl->setContent($this->form->getHTML());
        } else {
            $input_name = $this->form->getInput('name');
            $input_url_prefix = $this->form->getInput("url_prefix");

            $mumie_server = new ilMumieTaskServer();
            $mumie_server->setName($input_name);
            $mumie_server->setUrlPrefix($input_url_prefix);
            $mumie_server->upsert();
            $DIC->ui()->mainTemplate()->setOnScreenMessage('success', $this->i18N->txt('msg_suc_server_add'), true);

            $cmd = 'editProperties';
            $this->performCommand($cmd);
        }
    }

    /**
     * Display the learning progress tab, if enabled
     *
     * This function als also used as a hook to trigger a grade synchronization with the MUMIE server
     * @throws ilCtrlException
     * @throws ilCurlConnectionException
     * @throws ilLPException
     */
    public function displayLearningProgress(): void
    {
        global $ilCtrl;
        ilMumieTaskLPStatus::updateGrades($this->object);
        if ($this->checkPermissionBool('read_learning_progress')) {
            $ilCtrl->redirectByClass(array('ilObjMumieTaskGUI', 'ilLearningProgressGUI', 'ilLPListOfObjectsGUI'), 'showObjectSummary');
        } else {
            $this->setProgressInfo();
            $ilCtrl->redirectByClass(array('ilObjMumieTaskGUI', 'ilLearningProgressGUI'));
        }
    }

    /**
     * Display a users grade in a message box
     *
     * We couldn't find (and don't believe there is) a built-in functionality to display all necessary information about learning progress in the learning progress gui.
     * We need to use this workaround until ilias fixes this.
     * @throws ilLPException
     */
    public function setProgressInfo(): void
    {
        global $ilUser, $lng, $DIC;
        $status = ilMumieTaskLPStatus::getLPStatusForUser($this->object, $ilUser->getId());
        $status_path = $this->getStatusImagePath($status);
        $lng->loadLanguageModule('trac');

        switch ($status) {
            case ilLPStatus::LP_STATUS_COMPLETED_NUM:
                $status_text = $this->i18N->globalTxt(ilLPStatus::LP_STATUS_COMPLETED);
                $DIC->ui()->mainTemplate()->setOnScreenMessage('success', $this->getLPMessageString($status_text, $status_path), true);
                break;
            case ilLPStatus::LP_STATUS_FAILED_NUM:
                $status_text = $this->i18N->globalTxt(ilLPStatus::LP_STATUS_FAILED);
                $DIC->ui()->mainTemplate()->setOnScreenMessage('failure', $this->getLPMessageString($status_text, $status_path), true);
                break;
            case ilLPStatus::LP_STATUS_IN_PROGRESS_NUM:
                $status_text = $this->i18N->globalTxt(ilLPStatus::LP_STATUS_IN_PROGRESS);
                $DIC->ui()->mainTemplate()->setOnScreenMessage('question', $this->getLPMessageString($status_text, $status_path), true);
                break;
            case ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM:
                $status_text = $this->i18N->globalTxt(ilLPStatus::LP_STATUS_NOT_ATTEMPTED);
                $DIC->ui()->mainTemplate()->setOnScreenMessage('question', $this->getLPMessageString($status_text, $status_path), true);
                break;
            default:
                $DIC->ui()->mainTemplate()->setOnScreenMessage('question', $this->getLPMessageString($status_text, $status_path), true);
        }
    }

    /**
     * @throws ilLPException
     */
    private function getStatusImagePath($status): string
    {
        $icons = ilLPStatusIcons::getInstance(ilLPStatusIcons::ICON_VARIANT_SHORT);
        return $icons->getImagePathForStatus($status);
    }

    /**
     * Return an html string containing information about a users learning progress
     */
    public function getLPMessageString($status_text, $status_path): string
    {
        global $lng;
        $lng->loadLanguageModule('trac');

        return '<table style= "padding:15px">'
        . ' <tr style="line-height:30px">
            <td><i>'
        . $this->i18N->txt('frm_passing_grade')
        . ':</i></td>'
        . '<td style="padding-left:50px">'
        . $this->object->getPassingGrade()
        . '</td>'
        . '</tr>'
        . '<tr style="line-height:30px">'
        . '<td><i>'
        . $this->i18N->globalTxt('status')
            . ':</i></td>'
            . '<td style="padding-left:50px">'
            . "<img style='margin-right: 10px; width: 1em' src='" . $status_path . "'>"
            . $status_text
            . '</td></tr>'

            . '</table>';
    }
    /**
     * After object has been created -> jump to this command
     */
    public function getAfterCreationCmd(): string
    {
        return "editProperties";
    }

    /**
     * Get standard command
     */
    public function getStandardCmd(): string
    {
        return "viewContent";
    }

    /**
     * @throws ilCtrlException
     */
    public function cancelDummy(): void
    {
        $this->ctrl->returnToParent($this);
    }

    /**
     * Display either the embedded MUMIE Problem or a button that opens it in a new tab
     * @throws ilCtrlException
     * @throws ilDateTimeException
     */
    protected function viewContent(): void
    {
        global $ilTabs, $ilCtrl, $ilUser, $DIC;
        if ($this->object->isDummy()) {
            $ilCtrl->redirect($this, 'editProperties');
        }
        $ilTabs->activateTab('viewContent');
        $this->object->updateAccess();
        if (ilMumieTaskDeadlineService::hasDeadlinePassedForUser($ilUser->getId(), $this->object)) {
            $DIC->ui()->mainTemplate()->setOnScreenMessage('info', $this->i18N->txt('frm_list_grade_overview_after_deadline'));
        }
        $this->tpl->setContent($this->object->getContent());
    }

    /**
     * Display the LP settings form
     * @throws ilCtrlException
     * @throws ilDateTimeException
     * @throws ilCurlConnectionException
     */
    public function editLPSettings(): void
    {
        if ($this->object->isDummy()) {
            return;
        }
        global $ilTabs;
        $ilTabs->activateTab('properties');
        $this->setSubTabs("properties");
        $ilTabs->activateSubTab('lp_settings');
        $this->initLPSettingsForm();
        $values = array();
        $values['lp_modus'] = $this->object->getLpModus();
        $values['passing_grade'] = $this->object->getPassingGrade();
        $values['privategradepool'] = $this->object->getPrivateGradepool();
        $values['deadline'] = $this->object->getDeadlineDateTime();
        $this->form->setValuesByArray($values);
        $this->tpl->setContent($this->form->getHTML());
    }

    /**
     * Initialize the LP settings form and add force sync button and command buttons
     * @throws ilCtrlException
     */
    public function initLPSettingsForm(): void
    {
        global $ilCtrl;
        $form = new ilMumieTaskLPSettingsFormGUI($this->object->isGradepoolSet());
        $form->setFields();
        $form->setTitle($this->i18N->txt('tab_lp_settings'));

        $force_sync_button = new ilMumieTaskFormButtonGUI($this->i18N->txt('frm_force_update'));
        $force_sync_button->setButtonLabel($this->i18N->txt('frm_force_update_btn'));
        $force_sync_button->setLink($ilCtrl->getLinkTargetByClass(array('ilObjMumieTaskGUI'), 'forceGradeUpdate'));
        $force_sync_button->setInfo($this->i18N->txt('frm_force_update_desc'));
        $form->addItem($force_sync_button);

        $form->addCommandButton('submitLPSettings', $this->i18N->globalTxt('save'));
        $form->addCommandButton('editProperties', $this->i18N->globalTxt('cancel'));
        $form->setFormAction($ilCtrl->getFormAction($this));
        $this->form = $form;
    }

    /**
     * Submit changes to the learning progress settings
     * @throws ilCtrlException
     * @throws ilCurlConnectionException
     */
    public function submitLPSettings(): void
    {
        global $DIC;
        $this->initLPSettingsForm();
        if (!$this->form->checkInput()) {
            $this->form->setValuesByPost();
            $this->tpl->setContent($this->form->getHTML());
            return;
        }
        $force_grade_update = $this->object->getPassingGrade() !== $this->form->getInput('passing_grade');
        $is_gradepool_setting_update = $this->object->getPrivateGradepool() !== $this->form->getInput('privategradepool');
        $this->object->setLpModus($this->form->getInput('lp_modus'));
        if (!$this->object->isGradepoolSet()) {
            $this->object->setPrivateGradepool((int) $this->form->getInput('privategradepool'));
        }
        $this->object->setPassingGrade($this->form->getInput('passing_grade'));
        $this->object->setDeadline(strtotime($this->form->getInput('deadline')));
        $this->object->doUpdate();
        if ($is_gradepool_setting_update) {
            ilMumieTaskLPStatus::updateGradepoolSettingsForAllMumieTaskInRepository(
                $this->object->getParentRef(),
                $this->object->getPrivateGradepool()
            );
        }

        if ($force_grade_update) {
            ilMumieTaskLPStatus::updateGrades($this->object, $force_grade_update);
        }

        $DIC->ui()->mainTemplate()->setOnScreenMessage('success', $this->i18N->txt('msg_suc_saved'), false);

        $cmd = 'editLPSettings';
        $this->performCommand($cmd);
    }

    /**
     * Display availability settings form
     * @throws ilCtrlException
     * @throws ilCurlConnectionException
     * @throws ilDateTimeException
     */
    public function editAvailabilitySettings(): void
    {
        if ($this->object->isDummy()) {
            return;
        }
        global $ilTabs;
        $ilTabs->activateTab('properties');
        $this->setSubTabs("properties");

        $ilTabs->activateSubTab('availability_settings');
        $this->lng->loadLanguageModule('rep');
        $this->initAvailabilitySettingsForm();
        $values = array();
        $values['activation_type'] = $this->object->getActivationLimited();
        $values['online'] = $this->object->getOnline();
        $period = new stdClass();
        $period->startingTime = $this->object->getActivationStartingTime();
        $period->endingTime = $this->object->getActivationEndingTime();
        $values['period'] = $period;

        $this->form->setValuesByArray($values);

        $this->form->setTitle($this->i18N->globalTxt('rep_activation_availability'));
        $this->tpl->setContent($this->form->getHTML());
    }

    /**
     * Initialize availability form and add command buttons
     * @throws ilDateTimeException
     * @throws ilCtrlException
     */
    public function initAvailabilitySettingsForm(): void
    {
        global $ilCtrl;
        $form = new ilMumieTaskFormAvailabilityGUI();
        $form->setFields(!$this->object->isGradepoolSet());
        $form->addCommandButton('submitAvailabilitySettings', $this->i18N->globalTxt('save'));
        $form->addCommandButton('editProperties', $this->i18N->globalTxt('cancel'));
        $form->setFormAction($ilCtrl->getFormAction($this));

        $this->form = $form;
    }

    /**
     * Submit changes made to availability settings and trigger a forced grade update if necessary
     * @throws ilCtrlException
     * @throws ilCurlConnectionException
     * @throws ilDateTimeException
     */
    public function submitAvailabilitySettings(): void
    {
        global $DIC;
        $this->initAvailabilitySettingsForm();
        if (!$this->form->checkInput()) {
            $this->form->setValuesByPost();
            $this->tpl->setContent($this->form->getHTML());
            return;
        }
        $mumieTask = $this->object;
        $mumieTask->setOnline($this->form->getInput('online'));

        $force_grade_update = false;

        if ($this->form->getInput('activation_type') != $mumieTask->getActivationLimited()) {
            $force_grade_update = true;
        }
        if ($this->form->getInput('activation_type')) {
            $mumieTask->setActivationLimited(true);
            $period = $this->form->getItemByPostVar("access_period");

            if ($mumieTask->getActivationEndingTime() != $period->getEnd()->get(IL_CAL_UNIX)) {
                $force_grade_update = true;
            }

            $mumieTask->setActivationStartingTime($period->getStart()->get(IL_CAL_UNIX));
            $mumieTask->setActivationEndingTime($period->getEnd()->get(IL_CAL_UNIX));
        } else {
            $mumieTask->setActivationLimited(false);
        }

        $mumieTask->doUpdate();

        if ($force_grade_update) {
            ilMumieTaskLPStatus::updateGrades($this->object, $force_grade_update);
        }

        $DIC->ui()->mainTemplate()->setOnScreenMessage('success', $this->i18N->txt('msg_suc_saved'), false);

        $cmd = 'editProperties';
        $this->performCommand($cmd);
    }

    /**
     * @throws ilCtrlException
     * @throws ilCurlConnectionException
     * @throws ilDateTimeException
     * @throws ilException
     * @throws ilTemplateException
     */
    public function displayGradeOverviewPage(): void
    {
        global $ilTabs;
        $ilTabs->activateTab('userList');
        $this->initGradeOverviewPage();
        $this->tpl->setContent($this->form->getHTML());
    }

    /**
     * @throws ilCtrlException
     * @throws ilCurlConnectionException
     * @throws ilDateTimeException
     * @throws ilException
     * @throws ilTemplateException
     */
    private function initGradeOverviewPage(): void
    {
        global $ilCtrl;
        $form = new ilMumieTaskGradeOverviewFormGUI($this->object);
        $form->setTitle($this->i18N->txt('frm_user_overview_list_search_title'));
        $form->addCommandButton('displayGradeOverviewPage', $this->i18N->txt('frm_user_overview_list_search'));
        $form->setFormAction($ilCtrl->getFormAction($this));
        $this->form = $form;
        if (!$this->form->checkInput()) {
            $this->tpl->setContent($this->form->getHTML());
            return;
        }
        $this->tpl->addCss("./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/templates/mumie.css");
        $this->form->setFields($this, $this->form);
    }

    /**
     * @throws ilCtrlException
     * @throws ilCurlConnectionException
     * @throws ilException
     * @throws ilTemplateException
     */
    public function displayGradeList(): void
    {
        global $ilTabs, $ilCtrl;
        $ilTabs->activateTab('userList');
        $form = new ilMumieTaskGradeListFormGUI($this, $_GET['user_id'], $this->object);
        $form->setFields();
        $form->setFormAction($ilCtrl->getFormAction($this));
        $form->addCommandButton('displayGradeOverviewPage', $this->i18N->txt('frm_grade_overview_list_back'));
        $this->form = $form;
        $this->tpl->setContent($this->form->getHTML());
    }

    /**
     * @throws ilCtrlException
     */
    public function gradeOverride(): void
    {
        global $DIC;
        $user_id = $_GET["user_id"];
        $score = $_GET['score'];
        $timestamp = $_GET['timestamp'];
        $areParametersValid = !is_null($user_id) && !is_null($score) && !is_null($timestamp);

        $grade = new ilMumieTaskGrade($user_id, $score, $this->object, $timestamp);

        if ($areParametersValid && ilMumieTaskGradeSync::isValidGrade($grade)) {
            ilMumieTaskGradeOverrideService::overrideGrade($grade);
            $DIC->ui()->mainTemplate()->setOnScreenMessage(
                'success',
                sprintf(
                    $this->i18N->txt('frm_grade_overview_list_successfull_update'),
                    ilMumieTaskUserService::getFullName($user_id)
                )
            );
            $cmd = 'displayGradeOverviewPage';
            $this->performCommand($cmd);
        } else {
            $DIC->ui()->mainTemplate()->setOnScreenMessage('failure', $this->i18N->txt('frm_grade_overview_override_error'));
            $cmd = 'displayGradeOverviewPage';
            $this->performCommand($cmd);
        }
    }

    /**
     * @throws ilCtrlException
     * @throws ilTemplateException
     */
    public function displayDeadlineExtension(): void
    {
        global $ilTabs;
        $ilTabs->activateTab('userList');
        $this->initDeadlineExtension();
        $this->tpl->setContent($this->form->getHTML());
    }

    /**
     * @throws ilCtrlException
     * @throws ilTemplateException
     */
    private function initDeadlineExtension(): void
    {
        global $ilCtrl;
        $form = new ilMumieTaskDeadlineExtensionForm($this->object, $_GET["user_id"]);
        $form->setTitle($this->i18N->txt('frm_user_overview_list_extended_deadline'));
        $form->setFields();
        $form->addCommandButton('submitDeadlineExtension', $this->i18N->txt('frm_save'));
        $form->addCommandButton('displayGradeOverviewPage', $this->i18N->txt('frm_cancel'));
        $form->setFormAction($ilCtrl->getFormAction($this));
        $form->setInfoBox();

        $this->form = $form;
    }

    /**
     * @throws ilCtrlException
     * @throws ilCurlConnectionException
     * @throws ilDateTimeException
     * @throws ilTemplateException
     */
    public function submitDeadlineExtension(): void
    {
        $this->initDeadlineExtension();
        if (!$this->form->checkInput()) {
            $this->form->setValuesByPost();
            $this->tpl->setContent($this->form->getHTML());
            return;
        }
        ilMumieTaskDeadlineExtensionService::upsertDeadlineExtension($this->object, $this->form->getInput("deadline_extension"), $_GET["user_id"]);
        $cmd = 'displayGradeOverviewPage';
        $this->performCommand($cmd);
    }

    /**
     * @throws ilCtrlException
     */
    private function deleteDeadlineExtension(): void
    {
        global $DIC;
        $user_id = $_GET["user_id"];
        ilMumieTaskDeadlineExtensionService::deleteDeadlineExtension($this->object, $user_id);
        $DIC->ui()->mainTemplate()->setOnScreenMessage(
            'success',
            sprintf(
                $this->i18N->txt('frm_deadline_extension_successfull_delete'),
                ilMumieTaskUserService::getFullName($user_id)
            )
        );
        $cmd = 'displayGradeOverviewPage';
        $this->performCommand($cmd);
    }

    /**
     * @throws ilCurlConnectionException
     * @throws ilCtrlException
     */
    private function deleteGradeOverride(): void
    {
        global $DIC;
        $user_id = $_GET["user_id"];
        ilMumieTaskGradeOverrideService::deleteGradeOverride($this->object, $user_id);
        ilMumieTaskLPStatus::updateGradeForUser($this->object, $user_id, true);
        $DIC->ui()->mainTemplate()->setOnScreenMessage(
            'success',
            sprintf(
                $this->i18N->txt('grade_override_removed'),
                ilMumieTaskUserService::getFullName($user_id)
            )
        );
        $cmd = 'displayGradeOverviewPage';
        $this->performCommand($cmd);
    }

    /**
     * Some settings require invalidation of formerly synchronized grades and learning progress status (e.g. due date modified, passing threshold was changed etc).
     * After that a new synchronization is triggered.
     *
     * @throws ilCtrlException
     * @throws ilCurlConnectionException
     */
    public function forceGradeUpdate(): void
    {
        global $DIC;
        ilMumieTaskGradeOverrideService::deleteGradeOverridesForTask($this->object);
        ilMumieTaskLPStatus::updateGrades($this->object, true);
        $DIC->ui()->mainTemplate()->setOnScreenMessage('success', $this->i18N->txt('msg_suc_saved'), false);
        $cmd = 'editLPSettings';
        $this->performCommand($cmd);
    }
}
