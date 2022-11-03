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

    public function __construct($parentObj, $form)
    {
        global $ilDB, $lng;
        $this->setId("user" . $_GET["ref_id"]);
        parent::__construct($parentObj, 'displayUserList');

        $this->setFormName('participants');
        $this->addColumn($lng->txt('rep_robj_xmum_frm_list_name'), 'name');
        $this->addColumn($lng->txt('rep_robj_xmum_frm_list_grade'), 'note');
        $this->addColumn($lng->txt('rep_robj_xmum_frm_list_submissions'), 'submission');
        $this->setDefaultFilterVisiblity(true);

        $members = $this->getMembers($parentObj, $form);

        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskLPStatus.php');
        ilMumieTaskLPStatus::updateGrades($parentObj->object);

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
            $this->ctrl->setParameterByClass('ilObjMumieTaskGUI', 'user_id', $user_id);
            $this->tpl->setVariable('LINK_NAME', $this->ctrl->getLinkTarget($parentObj, 'displayGradeList'));
            $this->tpl->setVariable('LINK_TXT', $lng->txt('rep_robj_xmum_frm_list_change_grade'));

            $result = $ilDB->query(
                "SELECT mark
                FROM ut_lp_marks 
                WHERE usr_id = " . $ilDB->quote($user_id, "integer") .
                " AND " .
                "obj_id = " . $ilDB->quote($parentObj->object->getId(), "integer")
            );

            $grade = $ilDB->fetchAssoc($result);
            $this->tpl->setVariable('VAL_GRADE', $grade['mark']);

            $result = $ilDB->query("SELECT firstname, lastname FROM usr_data WHERE usr_id = ". $ilDB->quote($user_id, "integer"));
            $names = $ilDB->fetchAssoc($result);
            $this->tpl->setVariable('VAL_NAME', $names['firstname'] . ", " . $names['lastname']);
            $this->tpl->setCurrentBlock("tbl_content");
            $this->tpl->parseCurrentBlock();
        }
        $this->setEnableHeader(true);
    }

    private function getSearchedIds($form)
    {
        $members = $this->participants->getMembers();
        if (empty($form) || (empty($form->getInput("firstnamefield")) && empty($form->getInput("lastnamefield")))) {
            return $members;
        }
        $searchedMembers = array();
        foreach ($members as $user_id) {
            $id = $this->checkIfFirstNameInList($user_id, $form->getInput("firstnamefield"));
            if (!empty($id) && !empty($form->getInput("firstnamefield"))) {
                array_push($searchedMembers, $id["usr_id"]);
            }
            $id = $this->checkIfLastNameInList($user_id, $form->getInput("lastnamefield"));
            if (!empty($id) && !in_array($id["usr_id"], $searchedMembers) && !empty($form->getInput("lastnamefield"))) {
                array_push($searchedMembers, $id["usr_id"]);
            }
        }
        return $searchedMembers;
    }

    private function checkIfFirstNameInList($user_id, $name)
    {
        global $ilDB;
        $result = $ilDB->query(
            "SELECT usr_id FROM usr_data 
        WHERE usr_id = ". $ilDB->quote($user_id, "integer") .
        " AND " .
        $ilDB->like("firstname", "text", trim($name) . "%", false)
        );
        return $ilDB->fetchAssoc($result);
    }

    private function checkIfLastNameInList($user_id, $name)
    {
        global $ilDB;
        $result = $ilDB->query(
            "SELECT usr_id FROM usr_data 
        WHERE usr_id = ". $ilDB->quote($user_id, "integer") .
        " AND " .
        $ilDB->like("lastname", "text", trim($name) . "%", false)
        );
        return $ilDB->fetchAssoc($result);
    }



    private function getMembers($parentObj, $form)
    {
        if ($parentObj->object->getParentRef() != 1) {
            include_once './Services/Membership/classes/class.ilParticipants.php';
            $this->participants = ilParticipants::getInstance($parentObj->object->getParentRef());
            return $this->getSearchedIds($form);
        } else {
            require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskGradeSync.php');
            return ilMumieTaskGradeSync::getAllUserIds();
        }
    }

    //All functions are necessary for the list to be implemented into a form
    public function checkInput()
    {
        return true;
    }
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
