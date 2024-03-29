<?php
/**
 * MumieTask plugin
 *
 * @copyright   2022 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Vasilije Nedeljkovic(vasilije.nedeljkovic@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/i18n/class.ilMumieTaskI18N.php');

/**
 * This form is used to edit the Learning Progress settings of MumieTasks
 */
class ilMumieTaskGradeOverviewFormGUI extends ilPropertyFormGUI
{
    private $text_item_first;
    private $text_item_last;
    private ilMumieTaskI18N $i18N;
    private ilObjMumieTask $objMumieTask;

    public function __construct(ilObjMumieTask $objMumieTask)
    {
        parent::__construct();
        $this->setDisableStandardMessage(true);
        $this->i18N = new ilMumieTaskI18N();
        $this->objMumieTask = $objMumieTask;
    }

    public function setFields($parentObj, $form)
    {
        $this->setSearch($parentObj, $form);
        $this->setTable($parentObj, $form);
    }

    private function setSearch($parentObj, $form)
    {
        global $DIC;
        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskGradeOverviewGUI.php');
        $this->text_item_first = new ilTextInputGUI($this->i18N->txt('frm_user_overview_list_firstname_search'), 'firstnamefield');
        if (!empty($this->getInput("firstnamefield"))) {
            $this->text_item_first->setValue($form->getInput("firstnamefield"));
        }
        $this->addItem($this->text_item_first);
        $this->text_item_last = new ilTextInputGUI($this->i18N->txt('frm_user_overview_list_lastname_search'), 'lastnamefield');
        if (!empty($this->getInput("lastnamefield"))) {
            $this->text_item_last->setValue($this->getInput("lastnamefield"));
        }
        $this->addItem($this->text_item_last);

        if ($this->objMumieTask->hasDeadline()) {
            $DIC->ui()->mainTemplate()->setOnScreenMessage('info', '<span>
            <b>' . $this->i18N->txt('frm_user_overview_list_general_deadline') . '</b>
            <span style="margin-left:50px"> ' . $this->objMumieTask->getDeadlineDateTime() . '</span>
            </span>');
        }
    }

    private function setTable($parentObj, $form)
    {
        $select_task_header_item = new ilFormSectionHeaderGUI();
        $select_task_header_item->setTitle($this->i18N->txt('tab_userlist'));
        $this->addItem($select_task_header_item);
        $userList = new ilMumieTaskGradeOverviewGUI($parentObj, $this->objMumieTask);
        $userList->init($parentObj, $form);
        $this->addItem($userList);
    }

    public function getHTML(): string
    {
        $html = parent::getHTML();
        return str_replace("ilTableOuter", "mumie-user-table", $html);
    }

    public function checkInput(): bool
    {
        $ok = parent::checkInput();
        return $ok;
    }
}
