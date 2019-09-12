<?php
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
            case "submitMumieTask":
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

    function editProperties() {
        global $tpl;
        $this->initPropertiesForm();
        $this->setPropertyValues();
        $tpl->setContent($this->form->getHTML());
    }

    function setPropertyValues() {
        $this->object->doRead();
        $values["name"] = $this->object->getName();
        $values["task"] = $this->object->getTaskurl();
        $values["launchcontainer"] = $this->object->getLaunchcontainer();
        $values["course"] = $this->object->getMumie_course();
        $values["language"] = $this->object->getLanguage();
        $values["server"] = $this->object->getServer();
        $values["mumie_coursefile"] = $this->object->getMumie_coursefile();
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
        $mumieTask->setServer($this->form->getInput('server'));
        $mumieTask->setMumie_course($this->form->getInput('course'));
        $mumieTask->setTaskurl($this->form->getInput('task'));
        $mumieTask->setLanguage($this->form->getInput('language'));
        $mumieTask->setLaunchcontainer($this->form->getInput('launchcontainer'));
        $mumieTask->setMumie_coursefile("asdwwqeweq");
        $mumieTask->doUpdate();
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
/*
function debug_to_console($data) {
$output = $data;
if (is_array($output)) {
$output = implode(',', $output);
}

echo "<script>console.log('Debug Objects: " . $output . "' );</script>";
}
 */

?>