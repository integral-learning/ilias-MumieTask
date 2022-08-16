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

        
        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskGradeSync.php');
        $gradesync  = new  ilMumieTaskGradeSync($parentObj->object, false);
        $result = $gradesync->getXapiGradesByUser();
        ilLoggerFactory::getLogger('xmum')->info($result['names']);

        $this->tpl->addBlockFile(
            "TBL_CONTENT",
            "tbl_content",
            "tpl.mumie_user_list.html",
            "Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask"
        );

        foreach ($result as $set) {
            $this->tpl->setCurrentBlock("tbl_content");
            $this->css_row = ($this->css_row != "tblrow1")
                ? "tblrow1"
                : "tblrow2";
            $this->tpl->setVariable("CSS_ROW", $this->css_row);
            $this->ctrl->setParameterByClass('ilObjMumieTaskGUI', 'member_id', $set['user_id']);
            $this->tpl->setVariable('LINK_NAME', $this->ctrl->getLinkTarget($this->parent_obj, 'displayGradeList'));
            $this->tpl->setVariable('LINK_TXT', "Noten Ändern(tmp)");
            $this->fillRow($set); 
            $this->tpl->setCurrentBlock("tbl_content");
            $this->tpl->parseCurrentBlock();
        }

        $this->enable('header');
        $this->enable('sort');
        $this->setEnableHeader(true);
    }

    public function fillTable()
    {
        
    }
}
