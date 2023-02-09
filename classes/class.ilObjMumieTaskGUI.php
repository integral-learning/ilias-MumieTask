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
 */

include_once('./Services/Repository/classes/class.ilObjectPluginGUI.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskServer.php');
require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/grades/class.ilMumieTaskGrade.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/deadlines/extension/class.ilMumieTaskDeadlineExtensionService.php');
require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskUserService.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskGradeOverrideService.php');



class ilObjMumieTaskGUI extends ilObjectPluginGUI
{
    /**
     * Get type.
     */
    final public function getType()
    {
        return ilMumieTaskPlugin::ID;
    }

    /**
     * Handles all commands of this class, centralizes permission checks
     */
    public function performCommand($cmd)
    {
        switch ($cmd) {
            case "editProperties":
                $this->setSubTabs("properties");
                $cmd .= 'Object';
                $this->$cmd();
                // no break
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
    public function setTabs()
    {
        global $ilCtrl, $ilAccess, $ilTabs, $lng, $DIC;
        $this->tabs->clearTargets();
        $this->object->read();
        if ($this->object->isDummy()) {
            return;
        }
        $this->tabs->addTab("viewContent", $this->lng->txt("content"), $ilCtrl->getLinkTarget($this, "viewContent"));
        if ($ilAccess->checkAccess("write", "", $this->object->getRefId())) {
            $this->tabs->addTab("properties", $this->txt("properties"), $ilCtrl->getLinkTarget($this, "editProperties"));
            $this->tabs->addTab("userList", $lng->txt('rep_robj_xmum_tab_userlist'), $ilCtrl->getLinkTarget($this, "displayGradeOverviewPage"));
        }

        include_once("Services/Tracking/classes/class.ilObjUserTracking.php");
        if ($this->object->getLpModus() && ilObjUserTracking::_enabledLearningProgress()) {
            $ilTabs->addTab("learning_progress", $lng->txt('learning_progress'), $ilCtrl->getLinkTarget($this, 'displayLearningProgress'));
        }

        $ilTabs->addTab("infoScreen", $this->lng->txt("info_short"), $ilCtrl->getLinkTarget($this, "infoScreen"));

        $this->addPermissionTab();
    }

    public function setSubTabs($a_tab)
    {
        global $ilTabs, $ilCtrl, $lng;
        if ($this->object->isDummy()) {
            return;
        }
        $ilTabs->clearSubTabs();
        switch ($a_tab) {
            case 'properties':
                $ilTabs->addSubTab("edit_task", $lng->txt('rep_robj_xmum_tab_gen_settings'), $ilCtrl->getLinkTarget($this, "editProperties"));
                $ilTabs->addSubTab("lp_settings", $lng->txt('rep_robj_xmum_tab_lp_settings'), $ilCtrl->getLinkTargetByClass(array('ilObjMumieTaskGUI'), 'editLPSettings'));
                $this->lng->loadLanguageModule('rep');
                $ilTabs->addSubTab("availability_settings", $this->lng->txt('rep_activation_availability'), $ilCtrl->getLinkTargetByClass(array('ilObjMumieTaskGUI'), 'editAvailabilitySettings'));
                break;
        }
    }

    /**
     * Create a dummy MumieTask without any meaningful properties. The values must be set, before it can be used
     *
     * We decided to do implement creation this way, because we need the option to add MUMIE servers during the creation process, but generating any kind of output during repObj creation caused ILIAS errors
     */
    public function create()
    {
        global $lng;
        $this->setCreationMode(true);
        $refId = $_GET["ref_id"];

        require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilObjMumieTask.php');
        require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskLPStatus.php');
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
        ilUtil::sendSuccess($lng->txt('rep_robj_xmum_msg_suc_saved'), true);
    }

    /**
     * Display the general settings of a MumieTask
     */
    public function editPropertiesObject()
    {
        global $tpl, $ilTabs, $lng;
        if (empty(ilMumieTaskServer::getAllServers())) {
            $this->addServer();
            ilUtil::sendInfo($lng->txt("rep_robj_xmum_msg_no_server_found"), true);
            return;
        }
        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskServer.php');
        $ilTabs->activateTab('properties');
        $ilTabs->activateSubTab("edit_task");
        $this->object->doRead();
        $this->initPropertiesForm();
        if (!$this->object->isDummy() && !ilMumieTaskServer::serverConfigExistsForUrl($this->object->getServer())) {
            $this->form->disable();
            ilUtil::sendFailure($lng->txt('rep_robj_xmum_msg_server_missing') . $this->object->getServer());
        } elseif (!$this->object->isDummy() && !ilMumieTaskServer::fromUrl($this->object->getServer())->isValidMumieServer()) {
            $this->form->disable();
            ilUtil::sendFailure($lng->txt('rep_robj_xmum_msg_no_connection_to_server') . $this->object->getServer());
        }
        $this->setPropertyValues();
        $tpl->addJavaScript('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/js/ilMumieTaskForm.js');
        $tpl->setContent($this->form->getHTML());
        $tpl->addCss("./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/templates/mumie.css");
    }

    /**
     * Set values for the general settings form
     */
    public function setPropertyValues()
    {
        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskServer.php');
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
     */
    public function initPropertiesForm()
    {
        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/forms/class.ilMumieTaskFormGUI.php');
        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilObjMumieTask.php');
        global $ilCtrl, $lng;

        $form = new ilMumieTaskFormGUI();
        $form->setFields();
        $form->setTitle($lng->txt('rep_robj_xmum_obj_xmum'));
        $form->addCommandButton("submitMumieTask", $lng->txt('save'));
        $form->addCommandButton($this->object->isDummy() ? 'cancelDummy' : 'viewContent', $lng->txt('cancel'));

        $form->setFormAction($ilCtrl->getFormAction($this));
        $this->form = $form;
    }

    /**
     * submit changes made to the general settings of a MumieTask and trigger a forced grade update if necessary
     */
    public function submitMumieTask()
    {
        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilObjMumieTask.php');
        global $tpl, $ilCtrl, $lng;
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
            $this->plugin->includeClass('class.ilMumieTaskLPStatus.php');
            require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskGradeOverrideService.php');
            ilMumieTaskLPStatus::updateGrades($this->object, $force_grade_update);
            ilMumieTaskGradeOverrideService::deleteGradeOverridesForTask($this->object);
        }
        ilUtil::sendSuccess($lng->txt('rep_robj_xmum_msg_suc_saved'), true);
        $cmd = 'editProperties';

        $ilCtrl->redirect($this, 'editProperties');
    }

    /**
     * Update the MumieTask object with the given form values
     */
    public function saveFormValues()
    {
        $mumieTask = $this->object;

        $mumieTask->setTitle($this->form->getInput('title'));
        $mumieTask->setServer($this->form->getInput('xmum_server'));
        $mumieTask->setMumieCourse($this->form->getInput('xmum_course'));
        $mumieTask->setTaskurl($this->form->getInput('xmum_task'));
        $mumieTask->setLanguage($this->form->getInput('xmum_language'));
        $mumieTask->setLaunchcontainer($this->form->getInput('xmum_launchcontainer'));
        $mumieTask->setMumieCoursefile($this->form->getInput('xmum_coursefile'));

        $mumieTask->setDescription($this->form->getInput('description'));
        $mumieTask->update();

        require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/tasks/class.ilMumieTaskMultiUploadProcessor.php');
        $tasks_json = $this->form->getInput("xmum_multi_problems");
        if (!empty($tasks_json)) {
            ilMumieTaskMultiUploadProcessor::process($mumieTask, $tasks_json);
        }
    }

    /**
     * Display a from to add a new MUMIE server
     */
    public function addServer()
    {
        global $ilTabs, $lng;
        $this->setSubTabs('properties');
        $ilTabs->activateTab('properties');
        $this->initServerForm();
        $this->form->setTitle($lng->txt('rep_robj_xmum_frm_server_add_title'));
        $this->tpl->setContent($this->form->getHTML());
    }

    /**
     * initialize the MUMIE server form and add command buttons
     */
    private function initServerForm()
    {
        global $ilCtrl, $lng;
        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/forms/class.ilMumieTaskServerFormGUI.php');

        $form = new ilMumieTaskServerFormGUI();
        $form->setFields();
        $form->setFormAction($ilCtrl->getFormAction($this));
        $form->addCommandButton('submitServer', $lng->txt('save'));
        $form->addCommandButton('editProperties', $lng->txt('cancel'));

        $this->form = $form;
    }

    /**
     * Submit and save a new mumie server
     */
    public function submitServer()
    {
        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskServer.php');
        global $tpl, $lng;
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
            ilUtil::sendSuccess($lng->txt('rep_robj_xmum_msg_suc_server_add'), true);

            $cmd = 'editProperties';
            $this->performCommand($cmd);
        }
    }

    /**
     * Display the learning progress tab, if enabled
     *
     * This function als also used as a hook to trigger a grade synchronization with the MUMIE server
     */
    public function displayLearningProgress()
    {
        global $ilCtrl;
        $this->plugin->includeClass('class.ilMumieTaskLPStatus.php');
        require_once('Services/User/classes/class.ilObjUser.php');
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
     */
    public function setProgressInfo()
    {
        global $ilUser, $lng;
        $status = ilMumieTaskLPStatus::getLPStatusForUser($this->object, $ilUser->getId());
        $status_path = ilLearningProgressBaseGUI::_getImagePathForStatus($status);
        $lng->loadLanguageModule('trac');

        switch ($status) {
            case ilLPStatus::LP_STATUS_COMPLETED_NUM:
                $status_text = $lng->txt(ilLPStatus::LP_STATUS_COMPLETED);
                ilUtil::sendSuccess($this->getLPMessageString($status_text, $status_path), true);
                break;
            case ilLPStatus::LP_STATUS_FAILED_NUM:
                $status_text = $lng->txt(ilLPStatus::LP_STATUS_FAILED);
                ilUtil::sendFailure($this->getLPMessageString($status_text, $status_path), true);
                break;
            case ilLPStatus::LP_STATUS_IN_PROGRESS_NUM:
                $status_text = $lng->txt(ilLPStatus::LP_STATUS_IN_PROGRESS);
                ilUtil::sendQuestion($this->getLPMessageString($status_text, $status_path), true);
                break;
            case ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM:
                $status_text = $lng->txt(ilLPStatus::LP_STATUS_NOT_ATTEMPTED);
                ilUtil::sendQuestion($this->getLPMessageString($status_text, $status_path), true);
                break;
            default:
                ilUtil::sendQuestion($this->getLPMessageString($status_text, $status_path), true);
        }
    }

    /**
     * Return an html string containing information about a users learning progress
     */
    public function getLPMessageString($status_text, $status_path)
    {
        global $lng;
        $lng->loadLanguageModule('trac');

        $html_string =

        '<table style= "padding:15px">'
        . ' <tr style="line-height:30px">
            <td><i>'
        . $lng->txt('rep_robj_xmum_frm_passing_grade')
        . ':</i></td>'
        . '<td style="padding-left:50px">'
        . $this->object->getPassingGrade()
        . '</td>'
        . '</tr>'
        . '<tr style="line-height:30px">'
        . '<td><i>'
        . $lng->txt('status')
            . ':</i></td>'
            . '<td style="padding-left:50px">'
            . "<img style='margin-right: 10px' src='" . $status_path . "'>"
            . $status_text
            . '</td></tr>'

            . '</table>';
        return $html_string;
    }
    /**
     * After object has been created -> jump to this command
     */
    public function getAfterCreationCmd()
    {
        return "editProperties";
    }

    /**
     * Get standard command
     */
    public function getStandardCmd()
    {
        return "viewContent";
    }

    public function cancelDummy()
    {
        $this->ctrl->returnToParent($this);
    }

    /**
     * Display either the embedded MUMIE Problem or a button that opens it in a new tab
     */
    protected function viewContent()
    {
        global $ilTabs, $ilCtrl, $lng, $ilUser;
        if ($this->object->isDummy()) {
            $ilCtrl->redirect($this, 'editProperties');
        }
        $ilTabs->activateTab('viewContent');
        $this->object->updateAccess();
        require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/deadlines/class.ilMumieTaskDeadlineService.php');
        if (ilMumieTaskDeadlineService::hasDeadlinePassedForUser($ilUser->getId(), $this->object)) {
            ilUtil::sendInfo($lng->txt('rep_robj_xmum_frm_list_grade_overview_after_deadline'));
        }
        $this->tpl->setContent($this->object->getContent());
    }

    /**
     * Display the LP settings form
     */
    public function editLPSettings()
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
     */
    public function initLPSettingsForm()
    {
        global $ilCtrl;
        require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/forms/class.ilMumieTaskLPSettingsFormGUI.php');
        $form = new ilMumieTaskLPSettingsFormGUI($this->object->isGradepoolSet());
        $form->setFields();
        $form->setTitle($this->lng->txt('rep_robj_xmum_tab_lp_settings'));

        require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/forms/class.ilMumieTaskFormButtonGUI.php");
        $force_sync_button = new ilMumieTaskFormButtonGUI($this->lng->txt('rep_robj_xmum_frm_force_update'));
        $force_sync_button->setButtonLabel($this->lng->txt('rep_robj_xmum_frm_force_update_btn'));
        $force_sync_button->setLink($ilCtrl->getLinkTargetByClass(array('ilObjMumieTaskGUI'), 'forceGradeUpdate'));
        $force_sync_button->setInfo($this->lng->txt('rep_robj_xmum_frm_force_update_desc'));
        $form->addItem($force_sync_button);

        $form->addCommandButton('submitLPSettings', $this->lng->txt('save'));
        $form->addCommandButton('editProperties', $this->lng->txt('cancel'));
        $form->setFormAction($ilCtrl->getFormAction($this));
        $this->form = $form;
    }

    /**
     * Submit changes to the learning progress settings
     */
    public function submitLPSettings()
    {
        $this->plugin->includeClass('class.ilMumieTaskLPStatus.php');
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
            $this->object->setPrivateGradepool((int)$this->form->getInput('privategradepool'));
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

        ilUtil::sendSuccess($this->lng->txt('rep_robj_xmum_msg_suc_saved'), false);

        $cmd = 'editLPSettings';
        $this->performCommand($cmd);
    }

    /**
     * Display availability settings form
     */
    public function editAvailabilitySettings()
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

        $this->form->setTitle($this->lng->txt('rep_activation_availability'));
        $this->tpl->setContent($this->form->getHTML());
    }

    /**
     * Initialize availability form and add command buttons
     */
    public function initAvailabilitySettingsForm()
    {
        global $ilCtrl;
        require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/forms/class.ilMumieTaskFormAvailabilityGUI.php');
        $form = new ilMumieTaskFormAvailabilityGUI();
        $form->setFields(!$this->object->isGradepoolSet());
        $form->addCommandButton('submitAvailabilitySettings', $this->lng->txt('save'));
        $form->addCommandButton('editProperties', $this->lng->txt('cancel'));
        $form->setFormAction($ilCtrl->getFormAction($this));

        $this->form = $form;
    }

    /**
     * Submit changes made to availability settings and trigger a forced grade update if necessary
     */
    public function submitAvailabilitySettings()
    {
        global $ilDB;
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
            $this->plugin->includeClass('class.ilMumieTaskLPStatus.php');
            ilMumieTaskLPStatus::updateGrades($this->object, $force_grade_update);
        }

        ilUtil::sendSuccess($this->lng->txt('rep_robj_xmum_msg_suc_saved'), false);

        $cmd = 'editProperties';
        $this->performCommand($cmd);
    }

    /**
     *
     */
    public function displayGradeOverviewPage()
    {
        global $ilTabs;
        $ilTabs->activateTab('userList');
        $this->initGradeOverviewPage();
        $this->tpl->setContent($this->form->getHTML());
    }

    private function initGradeOverviewPage()
    {
        global $ilCtrl, $lng;
        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/forms/class.ilMumieTaskGradeOverviewFormGUI.php');
        $form =  new ilMumieTaskGradeOverviewFormGUI();
        $form->setTitle($lng->txt('rep_robj_xmum_frm_user_overview_list_search_title'));
        $form->addCommandButton('displayGradeOverviewPage', $lng->txt('rep_robj_xmum_frm_user_overview_list_search'));
        $form->setFormAction($ilCtrl->getFormAction($this));
        $this->form = $form;
        if (!$this->form->checkInput()) {
            $this->tpl->setContent($this->form->getHTML());
            return;
        }
        $this->tpl->addCss("./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/templates/mumie.css");
        $this->form->setFields($this, $this->form);
    }

    public function displayGradeList()
    {
        global $ilTabs, $ilCtrl, $lng;
        $ilTabs->activateTab('userList');
        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/forms/class.ilMumieTaskGradeListFormGUI.php');
        $form =  new ilMumieTaskGradeListFormGUI($this, $_GET['user_id']);
        $form->setFields();
        $form->setFormAction($ilCtrl->getFormAction($this));
        $form->addCommandButton('displayGradeOverviewPage', $lng->txt('rep_robj_xmum_frm_grade_overview_list_back'));
        $this->form = $form;
        $this->tpl->setContent($this->form->getHTML());
    }

    public function gradeOverride()
    {
        global $lng;
        $user_id = $_GET["user_id"];
        $score = $_GET['score'];
        $timestamp = $_GET['timestamp'];
        $areParametersValid = !is_null($user_id) && !is_null($score) && !is_null($timestamp);

        $grade = new ilMumieTaskGrade($user_id, $score, $this->object, $timestamp);
        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskGradeSync.php');

        if ($areParametersValid && ilMumieTaskGradeSync::isValidGrade($grade)) {
            require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskGradeOverrideService.php');
            ilMumieTaskGradeOverrideService::overrideGrade($grade);
            ilUtil::sendSuccess(
                sprintf(
                    $lng->txt('rep_robj_xmum_frm_grade_overview_list_successfull_update'),
                    ilMumieTaskUserService::getFullName($user_id)
                )
            );
            $cmd = 'displayGradeOverviewPage';
            $this->performCommand($cmd);
        } else {
            ilUtil::sendFailure($lng->txt('rep_robj_xmum_frm_grade_overview_override_error'));
            $cmd = 'displayGradeOverviewPage';
            $this->performCommand($cmd);
        }
    }

    public function displayDeadlineExtension()
    {
        global $ilTabs;
        $ilTabs->activateTab('userList');
        $this->initDeadlineExtension();
        $this->tpl->setContent($this->form->getHTML());
    }

    private function initDeadlineExtension()
    {
        global $ilCtrl, $lng;
        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/forms/class.ilMumieTaskDeadlineExtensionForm.php');
        $form = new ilMumieTaskDeadlineExtensionForm($this->object, $_GET["user_id"]);
        $form->setTitle($lng->txt('rep_robj_xmum_frm_user_overview_list_extended_deadline'));
        $form->setFields();
        $form->addCommandButton('submitDeadlineExtension', $lng->txt('rep_robj_xmum_frm_save'));
        $form->addCommandButton('displayGradeOverviewPage', $lng->txt('rep_robj_xmum_frm_cancel'));
        $form->setFormAction($ilCtrl->getFormAction($this));
        $form->setInfoBox();

        $this->form = $form;
    }

    public function submitDeadlineExtension()
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

    private function deleteDeadlineExtension()
    {
        global $lng;
        $user_id = $_GET["user_id"];
        ilMumieTaskDeadlineExtensionService::deleteDeadlineExtension($this->object, $user_id);
        ilUtil::sendSuccess(
            sprintf(
                $lng->txt('rep_robj_xmum_frm_deadline_extension_successfull_delete'),
                ilMumieTaskUserService::getFullName($user_id)
            )
        );
        $cmd = 'displayGradeOverviewPage';
        $this->performCommand($cmd);
    }

    private function deleteGradeOverride()
    {
        global $lng;
        $user_id = $_GET["user_id"];
        ilMumieTaskGradeOverrideService::deleteGradeOverride($this->object, $user_id);
        $this->plugin->includeClass('class.ilMumieTaskLPStatus.php');
        ilMumieTaskLPStatus::updateGradeForUser($this->object, $user_id, true);
        ilUtil::sendSuccess(
            sprintf(
                $lng->txt('rep_robj_xmum_grade_override_removed'),
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
     */
    public function forceGradeUpdate()
    {
        $this->plugin->includeClass('class.ilMumieTaskLPStatus.php');
        ilMumieTaskGradeOverrideService::deleteGradeOverridesForTask($this->object);
        ilMumieTaskLPStatus::updateGrades($this->object, true);
        ilUtil::sendSuccess($this->lng->txt('rep_robj_xmum_msg_suc_saved'), false);
        $cmd = 'editLPSettings';
        $this->performCommand($cmd);
    }
}
