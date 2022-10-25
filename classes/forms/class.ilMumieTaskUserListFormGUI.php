<?php
/**
 * MumieTask plugin
 *
 * @copyright   2019 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * This form is used to edit the Learning Progress settings of MumieTasks
 */
class ilMumieTaskUserListFormGUI extends ilPropertyFormGUI
{
    private $text_item_first;
    private $text_item_last;
    
    public function __construct()
    {
        parent::__construct();
        $this->setDisableStandardMessage(true);
    }

    public function setFields($parentObj, $form)
    {
        $this->setSearch($parentObj, $form);
        $this->setTable($parentObj, $form);
    }

    private function setSearch($parentObj, $form)
    {
        global $lng;
        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskUserListGUI.php');
        $this->text_item_first = new ilTextInputGUI($lng->txt('rep_robj_xmum_frm_list_firstname_search'), 'firstnamefield');
        if (!empty($this->getInput("firstnamefield"))) {
            $this->text_item_first->setValue($form->getInput("firstnamefield"));
        }
        $this->addItem($this->text_item_first);
        $this->text_item_last = new ilTextInputGUI($lng->txt('rep_robj_xmum_frm_list_lastname_search'), 'lastnamefield');
        if (!empty($this->getInput("lastnamefield"))) {
            $this->text_item_last->setValue($this->getInput("lastnamefield"));
        }
        $this->addItem($this->text_item_last);

        if ($parentObj->object->getActivationLimited()) {
            $dateTime = new ilDateTime($parentObj->object->getActivationEndingTime() ?? time(), IL_CAL_UNIX);
            ilUtil::sendInfo('<span>
            <b>' . $lng->txt('rep_robj_xmum_frm_list_general_dealine') . '</b>
            <span style="margin-left:50px"> ' . substr($dateTime->get(IL_CAL_DATETIME), 0, 10) . " - " . substr($dateTime->get(IL_CAL_DATETIME), 11, 8) . '</span>
            </span>');
        }
    }

    private function setTable($parentObj, $form)
    {
        global $lng;
        $select_task_header_item = new ilFormSectionHeaderGUI();
        $select_task_header_item->setTitle($lng->txt('rep_robj_xmum_tab_userlist'));
        $this->addItem($select_task_header_item);
        $userList = new ilMumieTaskUserListGUI($parentObj, $form);
        $this->addItem($userList);
    }

    public function checkInput()
    {
        $ok = parent::checkInput();
        return $ok;
    }
}
