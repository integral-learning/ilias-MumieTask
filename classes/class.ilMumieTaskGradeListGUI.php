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

require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskGradeSync.php');
class ilMumieTaskGradeListGUI extends ilTable2GUI
{
    private $parentObj;
    private $parent_gui;
    private $postvar;

    public function __construct($parentObj, $user_id, $updateGradeId)
    {
        global $ilDB;
        $this->parentObj = $parentObj;
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
        
        
        $gradesync  = new  ilMumieTaskGradeSync($parentObj->object, false);
        $xGrades = $gradesync->getAllXapiGradesByUser();
        $syncId = $gradesync->getSyncIds(array($user_id))[0];
        
        
        foreach($xGrades as $xGrade) {
            if($xGrade->actor->account->name == $syncId)
            {
                if(is_null($updateGradeId) == false && $xGrade->id == $updateGradeId) {
                    $this->overrideGrade($xGrade, $user_id);
                }
        
                $this->tpl->setCurrentBlock("tbl_content");
                $this->css_row = ($this->css_row != "tblrow1")
                    ? "tblrow1"
                    : "tblrow2";
                
                $this->tpl->setVariable("CSS_ROW", $this->css_row);
                $this->ctrl->setParameterByClass('ilObjMumieTaskGUI', 'member_id', $user_id);
                $this->ctrl->setParameterByClass('ilObjMumieTaskGUI', 'updateGrade', $xGrade->id);
                $this->tpl->setVariable("VAL_GRADE", $xGrade->result->score->raw * 100);
                $this->tpl->setVariable("VAL_LINK" , $this->ctrl->getLinkTarget($parentObj, 'displayGradeList'));
                $this->tpl->setVariable("VAL_DATE", substr($xGrade->timestamp, 0, 10));
                $this->tpl->setCurrentBlock("tbl_content");
                $this->tpl->parseCurrentBlock();
                
            }
        }

        $this->enable('header');
        $this->enable('sort');
        $this->setEnableHeader(true);
    }

    private function overrideGrade($xapi_grade, $user_id)
    {
        global $ilDB, $DIC;
        $percentage = round($xapi_grade->result->score->scaled * 100);
        $DIC->database()->update(
            'ut_lp_marks',
            array(
                "status_changed" => array('text', date("Y-m-d H:i:s", strtotime($xapi_grade->timestamp))),
                "mark" => array('int', $percentage),
            ),
            array(
                'obj_id' => array('int', $this->parentObj->object->getId()),
                'usr_id' => array('int', $user_id),
            )
        );
        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskGradeSync.php');
        require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskIdHashingService.php');
        $hashed_user = ilMumieTaskIdHashingService::getHashForUser($user_id, $this->parentObj->object);
        $gradesync  = new  ilMumieTaskGradeSync($this->parentObj->object, false);
        if(!$gradesync->wasGradeOverriden($user_id)) {
            $ilDB->insert(
                "xmum_grade_override",
                array(
                    'task_id' => array('integer', $this->parentObj->object->getId()),
                    'usr_id' => array('text', $hashed_user),
                )
            );
        }
        ilLoggerFactory::getLogger('xmum')->info("end override Grade");
        $this->parentObj->performCommand('displayUserList');
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