<?php

/**
 * MumieTask plugin
 *
 * @copyright   2019 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 /**
  * This form is used to add, edit and validate MUMIE Server configurations
  * @ilCtrl_isCalledBy ilBarGUI: ilFooGUI (multiple classes can be separated by comma)
  */
class ilMumieTaskUserListGUI extends ilTable2GUI
{

    private $participants;

    public function __construct($parentObj)
    {
        global $ilDB;
        $this->setId("user" . $_GET["ref_id"]);
        parent::__construct($parentObj, 'displayUserList');

        $this->setFormName('participants');
        $this->addColumn("", "", "1", true);
        $this->addColumn("Name(Tmp)", 'name');
        $this->addColumn("Deadline", 'date');
        $this->addColumn("Noten", 'note');
    
        include_once './Services/Membership/classes/class.ilParticipants.php';
        $this->participants = ilParticipants::getInstance($parentObj->object->getParentRef());
        $members = $this->participants->getMembers(); // get user ids of every memeber(no tutor/admin, for all use get participants)
        
        
       

        $this->tpl->addBlockFile(
            "TBL_CONTENT",
            "tbl_content",
            "tpl.mumie_user_list.html",
            "Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask"
        );

        foreach ($members as $set) {
            $this->tpl->setCurrentBlock("tbl_content");
            $this->css_row = ($this->css_row != "tblrow1")
                ? "tblrow1"
                : "tblrow2";
            $this->tpl->setVariable("CSS_ROW", $this->css_row);
            $this->ctrl->setParameterByClass('ilObjMumieTaskGUI', 'member_id', $set);
            $this->tpl->setVariable('LINK_NAME', $this->ctrl->getLinkTarget($this->parent_obj, 'displayGradeList'));
            $this->tpl->setVariable('LINK_TXT', "Noten Ändern(tmp)");
            $result = $ilDB->query("SELECT firstname, lastname FROM usr_data WHERE usr_id = ". $ilDB->quote($set, "integer"));
            $names = $ilDB->fetchAssoc($result);

            $this->tpl->setVariable('VAL_NAME', $names['firstname'] . ", " . $names['lastname']);
            //$this->fillRow($set); 
            $this->tpl->setCurrentBlock("tbl_content");
            $this->tpl->parseCurrentBlock();
        }

        $this->enable('header');
        $this->enable('sort');
        $this->setEnableHeader(true);
    }

}
