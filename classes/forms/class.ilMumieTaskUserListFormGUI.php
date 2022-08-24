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
    }

    public function setFields($parentObj)
    {
        $textField = new ilTextInputGUI("User Suche(tmp)");
        $this->addItem($textField);
        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskUserListGUI.php');
        $userList = new ilMumieTaskUserListGUI($parentObj);
        
        $this->addItem($userList);
    }

   
}                                                                           
