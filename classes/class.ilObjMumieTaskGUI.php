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
    }

    protected function initPropertiesForm() {
        require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/forms/class.ilMumieTaskFormGUI.php');
        global $tpl;

        $form = new ilMumieTaskFormGUI();
        $form->setFields();
        $this->form = $form;
        $tpl->setContent($this->form->getHTML());
    }

    function submitMumieTask() {
        global $tpl;
        $this->initPropertiesForm();
        if (!$this->form->checkInput()) {
            $this->form->setValuesByPost();
            $tpl->setContent($this->form->getHTML());
            return;
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