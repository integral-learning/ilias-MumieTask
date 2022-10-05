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
    private $form;

    public function __construct($parentObj, $form)
    {
        global $ilDB, $lng;
        $this->form = $form;
        $user_id = $_GET['user_id'];
        $this->parentObj = $parentObj;
        $this->admin_settings = ilMumieTaskAdminSettings::getInstance();

        $this->setId("user" . $_GET["ref_id"]);
        parent::__construct($parentObj, 'displayGradeList');

        $this->setFormName('participants');

        $this->addColumn($lng->txt('rep_robj_xmum_frm_list_submission_date'), 'date');
        $this->addColumn("", 'gradeswitch');
        $this->addColumn($lng->txt('rep_robj_xmum_frm_list_grade'), 'grade');

        $this->tpl->addBlockFile(
            "TBL_CONTENT",
            "tbl_content",
            "tpl.mumie_grade_list.html",
            "Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask"
        );
        if (is_null($_GET['newGrade']) == false && $_GET["user_id"] == $user_id) {
            $this->overrideGrade();
        }
        $gradesync  = new  ilMumieTaskGradeSync($parentObj->object, false);
        $xGrades = $gradesync->getAllXapiGradesByUser();
        $syncId = $gradesync->getSyncIds(array($user_id))[0];
        if (!empty($xGrades)) {
            foreach ($xGrades as $xGrade) {
                if ($xGrade->actor->account->name == $syncId) {
                    $this->tpl->setCurrentBlock("tbl_content");
                    $this->css_row = ($this->css_row != "tblrow1")
                        ? "tblrow1"
                        : "tblrow2";

                    $this->tpl->setVariable("CSS_ROW", $this->css_row);
                    $this->tpl->setVariable("VAL_GRADE", round($xGrade->result->score->raw * 100));
                    $this->ctrl->setParameterByClass('ilObjMumieTaskGUI', 'user_id', $user_id);
                    $this->ctrl->setParameterByClass('ilObjMumieTaskGUI', 'newGrade', round($xGrade->result->score->raw * 100));
                    $this->ctrl->setParameterByClass('ilObjMumieTaskGUI', 'timestamp', strtotime($xGrade->timestamp));

                    $this->tpl->setVariable("LINK_NAME", $this->ctrl->getLinkTarget($parentObj, 'displayGradeList'));

                    $this->tpl->setVariable("LINK_TXT", $lng->txt('rep_robj_xmum_frm_list_use_grade'));
                    $this->tpl->setVariable("VAL_DATE", substr($xGrade->timestamp, 0, 10));
                    $this->tpl->setCurrentBlock("tbl_content");
                    $this->tpl->parseCurrentBlock();
                }
            }
        }

        $this->enable('header');
        $this->enable('sort');
        $this->setEnableHeader(true);
    }

    private function overrideGrade()
    {
        global $ilDB, $DIC;
        ilLoggerFactory::getLogger('xmum')->info("user id: " . $_GET["user_id"] . " new grade: " . $_GET["newGrade"]);
        $percentage = $_GET['newGrade'];
        $DIC->database()->update(
            'ut_lp_marks',
            array(
                "status_changed" => array('text', date("Y-m-d H:i:s", $_GET['timestamp'])),
                "mark" => array('int', $percentage),
            ),
            array(
                'obj_id' => array('int', $this->parentObj->object->getId()),
                'usr_id' => array('int', $_GET['timestamp']),
            )
        );
        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskGradeSync.php');
        require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskIdHashingService.php');
        $hashed_user = ilMumieTaskIdHashingService::getHashForUser($_GET["user_id"], $this->parentObj->object);
        $gradesync  = new  ilMumieTaskGradeSync($this->parentObj->object, false);
        if (!$gradesync->wasGradeOverriden($_GET["user_id"])) {
            ilLoggerFactory::getLogger('xmum')->info("Grade was overriden");
            $ilDB->insert(
                "xmum_grade_override",
                array(
                    'task_id' => array('integer', $this->parentObj->object->getId()),
                    'usr_id' => array('text', $hashed_user),
                )
            );
        }
        $this->form->updateTextField();
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
