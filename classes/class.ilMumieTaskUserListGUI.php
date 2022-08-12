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
  */
class ilMumieTaskUserListGUI extends ilTable2GUI
{
    const MODE_USER_FOLDER = 1;
    const MODE_LOCAL_USER = 2;

    public function __construct($parentObj)
    {
        $this->setId("user" . $_GET["ref_id"]);
        parent::__construct($parentObj, 'displayUserList');

        $this->setFormName('participants');
        $this->addColumn("", "", "1", true);
        $this->addColumn("Name(Tmp)", 'named');
        $this->addColumn("Deadline Verlängern", 'deadline');
        $this->addColumn("Noten", 'note');
        $this->setRowTemplate("tpl.show_participants_row.html", "Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask");
        $tmpData = array(
            array(
                'named' => 'peter',
                'deadline' =>  'aaasd',
                'note' => '50'
            ),
            array(
                'named' => 'fasd',
                'deadline' =>  'dsaa',
                'note' => '05'
            )
            );
        $this->setData($tmpData);
        $asd = $this->getData();
        ilUtil::sendSuccess("tmpData is: ". array_keys($tmpData[1])[1] . " asd is: " . array_keys($asd[1])[1], false);

        $this->tpl->addBlockFile(
            "TBL_CONTENT",
            "tbl_content",
            $this->row_template,
            $this->row_template_dir
        );

        foreach ($asd as $set) {
            $this->tpl->setCurrentBlock("tbl_content");
            $this->css_row = ($this->css_row != "tblrow1")
                ? "tblrow1"
                : "tblrow2";
            $this->tpl->setVariable("CSS_ROW", $this->css_row);

            $this->fillRow($asd[0]);
            //$this->tpl->setVariable("VAL_deadline", "ass");
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
