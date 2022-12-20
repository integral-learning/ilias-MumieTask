<?php

/**
 * MumieTask plugin
 *
 * @copyright   2022 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Vasilije Nedeljkovic(vasilije.nedeljkovic@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * This GUI provides a way to list users in a MUMIE task
 */
class ilMumieTaskGradeOverviewGUI extends ilTable2GUI
{
    private $members;

    public function __construct($parentObj)
    {
        $this->setId("user" . $_GET["ref_id"]);
        parent::__construct($parentObj, 'displayUserList');
    }

    public function init($parentObj, $form)
    {
        $this->createList($parentObj, $form);
    }

    private function createList($parentObj, $form)
    {
        global $lng;
        $this->setFormName('participants');
        $this->addColumn($lng->txt('rep_robj_xmum_frm_user_overview_list_name'), 'name');
        if($parentObj->object->getActivationLimited()) {
            $this->addColumn($lng->txt('rep_robj_xmum_frm_user_overview_list_deadline_extension'), 'deadline_extension');
            $this->addColumn($lng->txt('rep_robj_xmum_frm_user_overview_list_extended_deadline'), 'extended_deadline');
        } else {
            $this->addColumn("", 'deadline_extension');
            $this->addColumn("", 'extended_deadline');
        }
        
        $this->addColumn($lng->txt('rep_robj_xmum_frm_list_grade'), 'note');
        $this->addColumn($lng->txt('rep_robj_xmum_frm_user_overview_list_submissions'), 'submission');
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
            $this->setTableRow($user_id, $parentObj);
        }
        $this->setEnableHeader(true);
    }

    private function setTableRow($user_id, $parentObj)
    {
        $this->tpl->setCurrentBlock("tbl_content");
        $this->css_row = ($this->css_row != "tblrow1")
        ? "tblrow1"
        : "tblrow2";
        $this->tpl->setVariable("CSS_ROW", $this->css_row);
        $this->ctrl->setParameterByClass('ilObjMumieTaskGUI', 'user_id', $user_id);
        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskDateOverrideService.php');
        if (ilMumieTaskDateOverrideService::wasDueDateOverriden($user_id, $parentObj->object) && $parentObj->object->getActivationLimited()) {
            $deadline = date('d.m.Y - H:i', ilMumieTaskDateOverrideService::getOverridenDueDate($user_id, $parentObj->object));
            $this->tpl->setVariable("VAL_EXTENDED_DEADLINE", $deadline);
        }
        if ($parentObj->object->getActivationLimited()) {
            $this->tpl->setVariable("VAL_HIDDEN", "");
            $this->tpl->setVariable('LINK_DEADLINE_EXTENSION', $this->ctrl->getLinkTarget($parentObj, 'dueDateExtension'));
        } else {
            $this->tpl->setVariable("VAL_HIDDEN", "hidden");
            $this->tpl->setVariable('LINK_DEADLINE_EXTENSION', "");
        }
        $grade = $this->getGradeForUser($user_id, $parentObj->object->getId());
        $this->tpl->setVariable('LINK_GRADE_OVERVIEW', $this->ctrl->getLinkTarget($parentObj, 'displayGradeList'));
        $this->tpl->setVariable('VAL_GRADE', $grade['mark']);
        require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskUserServer.php');
        $this->tpl->setVariable('VAL_NAME', ilMumieTaskUserServer::getFirstName($user_id) . ", " . ilMumieTaskUserServer::getLastName($user_id));
        $this->tpl->setCurrentBlock("tbl_content");
        $this->tpl->parseCurrentBlock();
    }

    private function getGradeForUser($user_id, $task_id)
    {
        global $ilDB;
        $result = $ilDB->query(
            "SELECT mark
            FROM ut_lp_marks 
            WHERE usr_id = " . $ilDB->quote($user_id, "integer") .
            " AND " .
            "obj_id = " . $ilDB->quote($task_id, "integer")
        );

        return $ilDB->fetchAssoc($result);
    }

    private function getSearchedIds($form)
    {
        $members = $this->members;
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
            $this->members = ilParticipants::getInstance($parentObj->object->getParentRef())->getMembers();
            return $this->getSearchedIds($form);
        } else {
            require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskGradeSync.php');
            $this->members =  ilMumieTaskGradeSync::getAllUserIds();
            return $this->getSearchedIds($form);
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

    /**
    * Get Post Variable.
    *
    * @return	string	Post Variable
    */
    public function getFieldId()
    {
        return null;
    }

    public function setParentForm($a_parentform)
    {
        $this->setParent($a_parentform);
    }

    public function setParent($a_val)
    {

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
