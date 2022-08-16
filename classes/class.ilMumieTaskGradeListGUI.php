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
        
        $tmpData = array(
            array(
                'user_id' => '0',
                'grades' => array('34', '51', '52'),
                'dates' =>  array('2018-07-22', '2018-07-22', '2018-07-23')           
            ),
            array(
                'user_id' => '1',
                'grades' => array('44', '50', '32'),
                'dates' =>  array('2018-07-22', '2018-07-24', '2018-07-27')
            )
            );
        $asd = $tmpData;
        
        $this->tpl->addBlockFile(
            "TBL_CONTENT",
            "tbl_content",
            "tpl.mumie_user_list.html",
            "Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask"
        );

        foreach ($asd as $set) {
            if ($set['user_id'] === $user_id) {
                $grades = $set['grades'];
                $dates = $set['dates'];
                for($i = 0; $i < count($grades); $i++) {
                    $this->tpl->setCurrentBlock("tbl_content");
                $this->css_row = ($this->css_row != "tblrow1")
                    ? "tblrow1"
                    : "tblrow2";
                $this->tpl->setVariable("CSS_ROW", $this->css_row);
                $this->tpl->setVariable("VAL_GRADE", $grades[$i]);
                $this->tpl->setVariable("VAL_DATE", $dates[$i]);
                $this->tpl->setCurrentBlock("tbl_content");
                $this->tpl->parseCurrentBlock();
                }
                
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
