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
class ilMumieTaskGradeListGUI extends ilTable2GUI
{

    public function __construct($parentObj, $user_id)
    {
        $this->setId("user" . $_GET["ref_id"]);
        parent::__construct($parentObj, 'displayUserList');

        $this->setFormName('participants');
        $this->addColumn("", "", "1", true);
        $this->addColumn("Name(Tmp)", 'name');
        $this->addColumn("Deadline", 'date');
        $this->addColumn("Noten", 'note');
        
        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskGradeSync.php');
        $gradesync  = new  ilMumieTaskGradeSync($parentObj->object, false);
        $grades = $gradesync->getXapiGradesByUser();
        foreach($grades[$user_id] as $grade) {
            $this->tpl->setCurrentBlock("tbl_content");
            $this->css_row = ($this->css_row != "tblrow1")
                ? "tblrow1"
                : "tblrow2";
            $this->tpl->setVariable("CSS_ROW", $this->css_row);
            $this->tpl->setVariable("VAL_GRADE", $grade->result->score * 100);
            $this->tpl->setVariable("VAL_DATE", $gra);
            $this->tpl->setCurrentBlock("tbl_content");
            $this->tpl->parseCurrentBlock();
        }
        $asd = $tmpData;
        
        $this->tpl->addBlockFile(
            "TBL_CONTENT",
            "tbl_content",
            "tpl.mumie_user_list.html",
            "Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask"
        );

        // foreach ($asd as $set) {
        //     if ($set['user_id'] === $user_id) {
        //         
        //         }
                
        //     }
        // }

        $this->enable('header');
        $this->enable('sort');
        $this->setEnableHeader(true);
    }

    public function fillTable()
    {
        
    }
}
