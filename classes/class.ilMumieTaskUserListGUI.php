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
  * 
  */
class ilMumieTaskUserListGUI extends ilTable2GUI
{

    private $participants;
    private $parent_gui;
    private $postvar;

    public function __construct($parentObj, $form = null)
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

        if(!is_null($form)) {
            $members = $this->getSearchedIds($form, $members);
        }

        $this->tpl->addBlockFile(
            "TBL_CONTENT",
            "tbl_content",
            "tpl.mumie_user_list.html",
            "Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask"
        );

        foreach ($members as $user_id) {   
                $this->tpl->setCurrentBlock("tbl_content");
                $this->css_row = ($this->css_row != "tblrow1")
                ? "tblrow1"
                : "tblrow2";
                $this->tpl->setVariable("CSS_ROW", $this->css_row);
                $this->ctrl->setParameterByClass('ilObjMumieTaskGUI', 'member_id', $user_id);
                $this->tpl->setVariable('LINK_NAME', $this->ctrl->getLinkTarget($parentObj, 'displayGradeList'));
                $this->tpl->setVariable('LINK_TXT', $lng->txt('rep_robj_xmum_frm_list_change_grade'));

                $result = $ilDB->query("SELECT mark 
                FROM ut_lp_marks 
                WHERE usr_id = " . $ilDB->quote($user_id, "integer") .
                " AND " .
                "obj_id = " . $ilDB->quote($parentObj->object->getId() , "integer")
                );

                $grade = $ilDB->fetchAssoc($result);
                $this->tpl->setVariable('VAL_GRADE', $grade['mark']);
            
                $result = $ilDB->query("SELECT firstname, lastname FROM usr_data WHERE usr_id = ". $ilDB->quote($user_id, "integer"));
                $names = $ilDB->fetchAssoc($result);

                $this->tpl->setVariable('VAL_NAME', $names['lastname'] . ", " . $names['lastname']);
                $this->tpl->setCurrentBlock("tbl_content");
                $this->tpl->parseCurrentBlock();
        }
        $this->enable('header');
        $this->enable('sort');
        $this->setEnableHeader(true);
    }

    private function getSearchedIds($form, $members)
    {
        global $ilDB;
        $search = $form->getInput("searchfield");
        $name = explode(" ", $search);
        $searchedMembers = array();
        foreach($members as $user_id) {
            $result = $ilDB->query("SELECT usr_id FROM usr_data 
            WHERE usr_id = ". $ilDB->quote($user_id, "integer") .
            " AND " .
            "(firstname LIKE " . $ilDB->quote($name[0] . "%", "text") .
            " OR " .
            "lastname LIKE " . $ilDB->quote($name[1] . "%", "text") . ")"
            );
            $id = $ilDB->fetchAssoc($result);

            if(!empty($id)) {
                array_push($searchedMembers, $id);
            }
        }
        
        return $searchedMembers();
    }

    private function checkSearchIds($user_id, $searchIds)
    {
        foreach($searchIds as $id) {
            if($id == $user_id) {
                return true;
            }
        }
        return false;
    }

    //All functions are necessary for the list to be implemented into a form
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
