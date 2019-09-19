<?php
require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/debugToConsole.php');

/**
 * @ilCtrl_isCalledBy ilObjMumieTaskGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
 * @ilCtrl_Calls ilObjMumieTaskGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI, ilExportGUI
 */
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
            case "submitMumieTask":
            case 'cancelServer':
            case 'addServer':
            case 'submitServer';
            // list all commands that need read permission here
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
        global $ilCtrl, $ilAccess, $ilTabs;
        if ($ilAccess->checkAccess("write", "", $this->object->getRefId())) {
            $this->tabs->addTab("properties", $this->txt("properties"), $ilCtrl->getLinkTarget($this, "editProperties"));
        }
        $this->addPermissionTab();
    }

    function setSubTabs($a_tab) {
        global $ilTabs, $ilCtrl, $lng;
        switch ($a_tab) {
            case 'properties':
                $ilTabs->addSubTab("edit_task", $lng->txt('rep_robj_xmum_edit_task'), $ilCtrl->getLinkTarget($this, "editProperties"));
                $ilTabs->addSubTab("add_server", $lng->txt('rep_robj_xmum_add_server'), $ilCtrl->getLinkTarget($this, "addServer"));
        }
    }

    function editPropertiesObject() {
        global $tpl, $ilTabs;
        $ilTabs->activateSubTab("edit_task");
        $this->initPropertiesForm();
        $this->setPropertyValues();
        $tpl->setContent($this->form->getHTML());
        $tpl->addJavaScript('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/js/ilMumieTaskForm.js');
    }

    function setPropertyValues() {
        require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskServer.php');
        $this->object->doRead();
        $values["name"] = $this->object->getName();
        $values["xmum_task"] = $this->object->getTaskurl();
        $values["xmum_launchcontainer"] = $this->object->getLaunchcontainer();
        $values["xmum_course"] = $this->object->getMumie_course();
        $values["xmum_language"] = $this->object->getLanguage();
        $values["xmum_server"] = $this->object->getServer();
        $values["xmum_mumie_coursefile"] = $this->object->getMumie_coursefile();
        //debug_to_console("ID: " . $this->object->getId() . json_encode($values));
        $this->form->setValuesByArray($values);
    }

    public function initPropertiesForm() {
        require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/forms/class.ilMumieTaskFormGUI.php');
        global $ilCtrl;

        $form = new ilMumieTaskFormGUI();
        $form->setFields();
        $form->setFormAction($ilCtrl->getFormAction($this));
        $this->form = $form;
    }

    public function submitMumieTask() {
        require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilObjMumieTask.php');

        global $tpl, $ilCtrl;
        $this->initPropertiesForm();

        if (!$this->form->checkInput()) {
            $this->form->setValuesByPost();
            $tpl->setContent($this->form->getHTML());
            return;
        }
        $mumieTask = $this->object;
        $mumieTask->setName($this->form->getInput('name'));
        $mumieTask->setServer($this->form->getInput('xmum_server'));
        $mumieTask->setMumie_course($this->form->getInput('xmum_course'));
        $mumieTask->setTaskurl($this->form->getInput('xmum_task'));
        $mumieTask->setLanguage($this->form->getInput('xmum_language'));
        $mumieTask->setLaunchcontainer($this->form->getInput('xmum_launchcontainer'));
        $mumieTask->setMumie_coursefile($this->form->getInput('xmum_coursefile'));
        $mumieTask->doUpdate();
    }

    function addServer() {
        global $tpl, $ilTabs;
        $this->setSubTabs('properties');
        $ilTabs->activateSubTab('add_server');
        $this->initAddForm();
        $tpl->setContent($this->form->getHTML());
    }

    private function initAddForm() {
        global $ilCtrl, $lng;
        require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/forms/class.ilMumieTaskServerFormGUI.php');

        $form = new ilMumieTaskServerFormGUI();
        $form->setFields();
        $form->setFormAction($ilCtrl->getFormAction($this));

        $this->form = $form;
    }

    function submitServer() {
        require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskServer.php');
        //debug_to_console("SUBMIT SERVER IS ALLED");
        global $tpl;
        $this->initAddForm();
        if (!$this->form->checkInput()) {
            //debug_to_console("INVALID INPUT");
            $this->form->setValuesByPost();
            $tpl->setContent($this->form->getHTML());
            return;
        }
        $inputName = $this->form->getInput('name');
        $inputUrlPrefix = $this->form->getInput("url_prefix");

        $mumieServer = new ilMumieTaskServer();
        $mumieServer->setName($inputName);
        $mumieServer->setUrlPrefix($inputUrlPrefix);
        $mumieServer->upsert();

        $cmd = 'editProperties';
        $this->performCommand($cmd);
    }

    function cancelServer() {
        $cmd = 'editProperties';
        $this->performCommand($cmd);
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
        return "showContent";
    }

    private function setStatusToCompleted() {
        $this->setStatusAndRedirect(ilLPStatus::LP_STATUS_COMPLETED_NUM);
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
}

?>