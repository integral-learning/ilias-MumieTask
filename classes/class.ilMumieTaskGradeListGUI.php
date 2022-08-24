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
    private $admin_settings;

    public function __construct($parentObj, $user_id)
    {
        global $ilDB;
        $this->admin_settings = ilMumieTaskAdminSettings::getInstance();

        $this->setId("user" . $_GET["ref_id"]);
        parent::__construct($parentObj, 'displayUserList');

        $result = $ilDB->query("SELECT firstname, lastname FROM usr_data WHERE usr_id = ". $ilDB->quote($user_id, "integer"));
        $names = $ilDB->fetchAssoc($result);

        $this->setFormName('participants');

        $this->addColumn("Submission Date(tmp)", 'date');
        $this->addColumn("Noten(tmp)", 'grade');

        $this->tpl->addBlockFile(
            "TBL_CONTENT",
            "tbl_content",
            "tpl.mumie_grade_list.html",
            "Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask"
        );
        
        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskGradeSync.php');
        $gradesync  = new  ilMumieTaskGradeSync($parentObj->object, false);
        $xGrades = $gradesync->getAllXapiGradesByUser();
        $syncId = $gradesync->getSyncIds(array($user_id))[0];
        
        foreach($xGrades as $xGrade) {
            if($xGrade->actor->account->name == $syncId)
            {
                $this->tpl->setCurrentBlock("tbl_content");
                $this->css_row = ($this->css_row != "tblrow1")
                    ? "tblrow1"
                    : "tblrow2";
                $this->tpl->setVariable("CSS_ROW", $this->css_row);
                $this->tpl->setVariable("VAL_GRADE", $xGrade->result->score->raw * 100);
                $this->tpl->setVariable("VAL_DATE", substr($xGrade->timestamp, 0, 10));
                $this->tpl->setCurrentBlock("tbl_content");
                $this->tpl->parseCurrentBlock();
                
            }
        }

        $this->enable('header');
        $this->enable('sort');
        $this->setEnableHeader(true);
    }

    public function fillTable()
    {
        
    }
}