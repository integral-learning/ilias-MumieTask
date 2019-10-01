<?php
require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/debugToConsole.php');

/**
 * @ilCtrl_isCalledBy ilObjMumieTaskGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
 * @ilCtrl_Calls ilObjMumieTaskGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI, ilExportGUI, ilLearningProgressGUI, ilLPListOfObjectsGUI,ilObjPluginDispatchGUI
 */

include_once ('./Services/Repository/classes/class.ilObjectPluginGUI.php');
include_once ('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/debugToConsole.php');
require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskServer.php');

class ilObjMumieTaskGUI extends ilObjectPluginGUI {
    const LP_SESSION_ID = 'xmum_lp_session_state';

    /**
     * Get type.
     */
    final function getType() {
        return ilMumieTaskPlugin::ID;
    }

    /**
     * Handles all commmands of this class, centralizes permission checks
     */
    function performCommand($cmd) {
        switch ($cmd) {
            case "editProperties":
                $this->setSubTabs("properties");
                $cmd .= 'Object';
                $this->$cmd();
            case 'createObject':
            case "submitMumieTaskUpdate":
            case "submitMumieTaskCreate":
            case 'cancelServer':
            case 'cancelCreate':
            case 'addServer':
            case 'submitServer':
            case 'editLPSettings':
            case 'submitLPSettings':
            // list all commands that need read permission here
            case "viewContent":
            case "setStatusToCompleted":
            case "setStatusToFailed":
            case "setStatusToInProgress":
            case "setStatusToNotAttempted":
                $this->checkPermission("read");
                $this->$cmd();
                break;
        }
    }
    function setTabs() {
        global $ilCtrl, $ilAccess, $ilTabs, $lng;
        $this->tabs->clearTargets();
        $this->object->read();
        if ($this->isCreationMode()) {
        } else {
            $this->tabs->addTab("viewContent", $this->lng->txt("content"), $ilCtrl->getLinkTarget($this, "viewContent"));
            if ($ilAccess->checkAccess("write", "", $this->object->getRefId())) {
                $this->tabs->addTab("properties", $this->txt("properties"), $ilCtrl->getLinkTarget($this, "editProperties"));
            }
            if ($this->checkPermissionBool("read_learning_progress")) {
                $ilTabs->addTab("learning_progress", $lng->txt('learning_progress'), $ilCtrl->getLinkTargetByClass(array('ilObjMumieTaskGUI', 'ilLearningProgressGUI')));
            } else {
                $ilTabs->addTab("learning_progress", $lng->txt('learning_progress'), $ilCtrl->getLinkTargetByClass(array('ilObjExternalContentGUI', 'ilLearningProgressGUI', 'ilLPListOfObjectsGUI'), 'showObjectSummary'));
            }
            $this->addPermissionTab();
        }
    }

    function setSubTabs($a_tab) {
        global $ilTabs, $ilCtrl, $lng;
        $ilTabs->clearSubTabs();
        switch ($a_tab) {
            case 'properties':
                $ilTabs->addSubTab("edit_task", $lng->txt('rep_robj_xmum_tab_gen_settings'), $ilCtrl->getLinkTarget($this, "editProperties"));
                $ilTabs->addSubTab("lp_settings", $lng->txt('rep_robj_xmum_tab_lp_settings'), $ilCtrl->getLinkTargetByClass(array('ilObjMumieTaskGUI'), 'editLPSettings'));
                $ilTabs->addSubTab("add_server", $lng->txt('rep_robj_xmum_add_server'), $ilCtrl->getLinkTarget($this, "addServer"));
                break;
        }
    }

    function create() {

        $this->setCreationMode(true);
        global $$ilTabs, $ilCtrl, $lng;
        if (empty(ilMumieTaskServer::getAllServers())) {
            $this->addServer();
            ilUtil::sendInfo($lng->txt("rep_robj_xmum_msg_no_server_found"), true);
        } else {
            $this->createObject();
        }
    }

    function createObject() {
        global $tpl, $ilTabs;
        $ilTabs->activateSubTab('create_task');
        $this->initPropertiesForm(true);
        $this->form->setValuesByArray(array());

        $tpl->addJavaScript('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/js/ilMumieTaskForm.js');
        $tpl->setContent($this->form->getHTML());
        //$tpl->setVariable('ADM_CONTENT', $this->form->getHTML());*/
    }

    function editPropertiesObject() {
        global $tpl, $ilTabs, $lng;
        require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskServer.php');
        $ilTabs->activateTab('properties');
        $ilTabs->activateSubTab("edit_task");
        $this->object->doRead();
        $this->initPropertiesForm();
        $this->setPropertyValues();
        if (!ilMumieTaskServer::serverExistsForUrl($this->object->getServer())) {
            $this->form->disableDropdowns();
            ilUtil::sendFailure($lng->txt('rep_robj_xmum_msg_server_missing') . $this->object->getServer());
        }
        $tpl->addJavaScript('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/js/ilMumieTaskForm.js');
        $tpl->setContent($this->form->getHTML());
    }

    function setPropertyValues() {
        require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskServer.php');
        $values["title"] = $this->object->getTitle();
        $values["xmum_task"] = $this->object->getTaskurl();
        $values["xmum_launchcontainer"] = $this->object->getLaunchcontainer();
        $values["xmum_course"] = $this->object->getMumie_course();
        $values["xmum_language"] = $this->object->getLanguage();
        $values["xmum_server"] = $this->object->getServer();
        $values["xmum_mumie_coursefile"] = $this->object->getMumie_coursefile();
        $this->form->setValuesByArray($values);
    }

    public function initPropertiesForm() {
        require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/forms/class.ilMumieTaskFormGUI.php');
        require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilObjMumieTask.php');
        global $ilCtrl, $lng;

        $form = new ilMumieTaskFormGUI();
        $form->setFields();
        $form->setTitle($lng->txt('rep_robj_xmum_obj_xmum'));
        $form->addCommandButton($this->isCreationMode() ? 'submitMumieTaskCreate' : "submitMumieTaskUpdate", $lng->txt('save'));
        $form->addCommandButton($this->isCreationMode() ? 'cancelCreate' : 'viewContent', $lng->txt('cancel'));

        $form->setFormAction($ilCtrl->getFormAction($this));
        $this->form = $form;
    }

    public function submitMumieTaskUpdate() {
        require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilObjMumieTask.php');
        global $tpl, $ilCtrl, $lng;
        $this->initPropertiesForm();

        if (!$this->form->checkInput()) {
            $this->form->setValuesByPost();
            $tpl->setContent($this->form->getHTML());
            $tpl->addJavaScript('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/js/ilMumieTaskForm.js');
        } else {
            $this->saveFormValues();
            ilUtil::sendSuccess($lng->txt('rep_robj_xmum_msg_suc_saved'), true);
            $cmd = 'editProperties';
            $this->performCommand($cmd);
        }
    }

    public function submitMumieTaskCreate() {
        global $lng, $tpl;
        $this->initPropertiesForm(true);

        if (!$this->form->checkInput()) {
            $this->form->setValuesByPost();
            $tpl->setContent($this->form->getHTML());
            $tpl->addJavaScript('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/js/ilMumieTaskForm.js');
        } else {
            $this->object = new ilObjMumieTask;
            $this->object->setType($this->type);
            $this->object->create();
            $this->object->createReference();
            $this->object->putInTree($_GET["ref_id"]);
            $this->saveFormValues();

            $this->ctrl->setParameter($this, "ref_id", $this->object->getRefId());
            $this->afterSave($this->object);
            $this->setCreationMode(false);
            ilUtil::sendSuccess($lng->txt('rep_robj_xmum_msg_suc_saved'), true);
        }
    }

    public function saveFormValues() {
        $mumieTask = $this->object;
        $mumieTask->setTitle($this->form->getInput('title'));
        $mumieTask->setServer($this->form->getInput('xmum_server'));
        $mumieTask->setMumie_course($this->form->getInput('xmum_course'));
        $mumieTask->setTaskurl($this->form->getInput('xmum_task'));
        $mumieTask->setLanguage($this->form->getInput('xmum_language'));
        $mumieTask->setLaunchcontainer($this->form->getInput('xmum_launchcontainer'));
        $mumieTask->setMumie_coursefile($this->form->getInput('xmum_coursefile'));

        $mumieTask->update();
    }
    function addServer() {

        global $ilTabs, $lng;
        $this->setSubTabs($this->getCreationMode() ? 'create' : 'properties');
        $ilTabs->activateTab('properties');
        $ilTabs->activateSubTab('add_server');
        $this->initServerForm();
        $this->form->setTitle($lng->txt('rep_robj_xmum_frm_server_add_title'));
        $this->tpl->setContent($this->form->getHTML());
    }

    private function initServerForm() {
        global $ilCtrl, $lng;
        require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/forms/class.ilMumieTaskServerFormGUI.php');

        $form = new ilMumieTaskServerFormGUI();
        $form->setFields();
        $form->setFormAction($ilCtrl->getFormAction($this));
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
        } else {
            $inputName = $this->form->getInput('name');
            $inputUrlPrefix = $this->form->getInput("url_prefix");

            $mumieServer = new ilMumieTaskServer();
            $mumieServer->setName($inputName);
            $mumieServer->setUrlPrefix($inputUrlPrefix);
            $mumieServer->upsert();
            ilUtil::sendSuccess($lng->txt('rep_robj_xmum_msg_suc_server_add'), true);
            if ($this->isCreationMode()) {
                $this->create();
            } else {
                $cmd = 'editProperties';

                $this->performCommand($cmd);
            }
        }
    }

    function cancelServer() {
        if ($this->isCreationMode()) {
            $this->cancelCreate();
        } else {
            $cmd = 'editProperties';
            $this->performCommand($cmd);
        }
    }

    /**
     * After object has been created -> jump to this command
     */
    function getAfterCreationCmd() {
        return "editProperties";
    }

    /**
     * Get standard command
     */
    function getStandardCmd() {
        return "viewContent";
    }

    function cancelCreate() {
        $this->ctrl->returnToParent($this);
    }

    private function setStatusToCompleted() {
        $this->setStatusAndRedirect(ilLPStatus::LP_STATUS_COMPLETED_NUM);
    }

    protected function viewContent() {
        global $ilCtrl, $DIC, $ilTabs;
        $ilTabs->activateTab('viewContent');
        $this->tpl->setContent($this->object->getContent());
    }

    private function setStatusAndRedirect($status) {
        global $ilUser;
        $_SESSION[self::LP_SESSION_ID] = $status;
        ilLPStatusWrapper::_updateStatus($this->object->getId(), $ilUser->getId());
        $this->ctrl->redirect($this, $this->getStandardCmd());
    }

    protected function setStatusToFailed() {
        $this->setStatusAndRedirect(ilLPStatus::LP_STATUS_FAILED_NUM);
    }

    protected function setStatusToInProgress() {
        $this->setStatusAndRedirect(ilLPStatus::LP_STATUS_IN_PROGRESS_NUM);
    }

    protected function setStatusToNotAttempted() {
        $this->setStatusAndRedirect(ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM);
    }

    function isCreationMode() {

        return $this->getCreationMode() == true || !($this->object instanceof ilObjMumieTask);
    }

    function editLPSettings() {
        global $ilTabs;

        $ilTabs->activateTab('properties');
        $this->setSubTabs("properties");
        $ilTabs->activateSubTab('lp_settings');
        $this->initLPSettingsForm();
        $values = array();
        $values['lp_modus'] = $this->object->getLp_modus();
        $values['passing_grade'] = $this->object->getPassing_grade();
        $this->form->setValuesByArray($values);
        $this->tpl->setContent($this->form->getHTML());
    }

    function initLPSettingsForm() {
        global $ilCtrl;
        require_once ('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/forms/class.ilMumieTaskLPSettingsFormGUI.php');
        $form = new ilMumieTaskLPSettingsFormGUI();
        $form->setFields();
        $form->setTitle($this->lng->txt('rep_robj_xmum_tab_lp_settings'));
        $form->addCommandButton('submitLPSettings', $this->lng->txt('save'));
        $form->setFormAction($ilCtrl->getFormAction($this));
        $this->form = $form;
    }

    function submitLPSettings() {
        //debug_to_console("submitting lp settings");
        $this->initLPSettingsForm();
        if (!$this->form->checkInput()) {
            $this->form->setValuesByPost();
            $this->tpl->setContent($this->form->getHTML());
            return;
        }
        $this->object->setLp_modus($this->form->getInput('lp_modus'));
        $this->object->setPassing_grade($this->form->getInput('passing_grade'));
        $this->object->doUpdate();
        ilUtil::sendSuccess($this->lng->txt('rep_robj_xmum_msg_suc_saved'), false);

        $cmd = 'editLPSettings';
        $this->performCommand($cmd);
    }
}

?>