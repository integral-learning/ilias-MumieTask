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
    public function __construct()
    {
        parent::__construct();
        $this->setDisableStandardMessage(true);
    }

    public function setFields($parentObj, $form = null)
    {
        global $lng;
        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskUserListGUI.php');
        $textField = new ilTextInputGUI($lng->txt('rep_robj_xmum_frm_list_firstname_search'), "firstnamefield");
        $this->addItem($textField);
        $textField = new ilTextInputGUI($lng->txt('rep_robj_xmum_frm_list_lastname_search'), "lastnamefield");
        $this->addItem($textField);
        
        $userList = new ilMumieTaskUserListGUI($parentObj, $form);
        
        $this->addItem($userList);
    }

   
}                                                                           