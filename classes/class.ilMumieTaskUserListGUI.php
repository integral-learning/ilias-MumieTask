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
    private $parent_gui;
    private $postvar;

    public function __construct($parentObj)
    {
        global $ilDB, $lng;
        $this->setId("user" . $_GET["ref_id"]);
        parent::__construct($parentObj, 'displayUserList');

        $this->setFormName('participants');
        $this->addColumn("", "", "1", true);
        $this->addColumn($lng->txt('rep_robj_xmum_frm_list_name'), 'name');
        $this->addColumn($lng->txt('rep_robj_xmum_frm_list_grade'), 'note');
        $this->setDefaultFilterVisiblity(true);

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
            $this->tpl->setVariable('LINK_NAME', $this->ctrl->getLinkTarget($parentObj, 'displayGradeList'));
            $this->tpl->setVariable('LINK_TXT', $lng->txt('rep_robj_xmum_frm_list_change_grade'));

            $result = $ilDB->query("SELECT mark 
            FROM ut_lp_marks 
            WHERE usr_id = " . $ilDB->quote($set, "integer") .
            " AND " .
            "obj_id = " . $ilDB->quote($parentObj->object->getId() , "integer")
            );

            $grade = $ilDB->fetchAssoc($result);
            $this->tpl->setVariable('VAL_GRADE', $grade['mark']);
            
            $result = $ilDB->query("SELECT firstname, lastname FROM usr_data WHERE usr_id = ". $ilDB->quote($set, "integer"));
            $names = $ilDB->fetchAssoc($result);

            $this->tpl->setVariable('VAL_NAME', $names['lastname'] . ", " . $names['lastname']);
            $this->tpl->setCurrentBlock("tbl_content");
            $this->tpl->parseCurrentBlock();
        }
        $this->enable('header');
        $this->enable('sort');
        $this->setEnableHeader(true);
    }

    //Alle funktionen notwendig damit die Liste in eine Form eingefügt werden kann
    public function insert($a_tpl)
    {
        $a_tpl->setCurrentBlock("prop_custom");
        $a_tpl->setVariable("CUSTOM_CONTENT", $this->render());
        $a_tpl->parseCurrentBlock();
    }

    public function getHiddenTitle()
    {
        return "";
    }

    public function getTitle()
    {
        return "";
    }


    public function getFormLabelFor()
    {
        return "";
    }

    public function getType()
    {
        return "";
    }

    public function getSubForm()
    {
        return "";
    }

    public function hideSubForm()
    {
        return true;
    }

    public function getAlert()
    {
        return "";
    }

    

    public function getPostVar()
    {
        return $this->postvar;
    }

    /**
    * Get Post Variable.
    *
    * @return	string	Post Variable
    */
    public function getFieldId()
    {
        $id = str_replace("[", "__", $this->getPostVar());
        $id = str_replace("]", "__", $id);
        
        return $id;
    }

    public function setParentForm($a_parentform)
    {
        $this->setParent($a_parentform);
    }

    public function setParent($a_val)
    {
        $this->parent_gui = $a_val;
    }

    public function getInfo()
    {
        return "";
    }

    public function getRequired()
    {
        return "";
    }



}
