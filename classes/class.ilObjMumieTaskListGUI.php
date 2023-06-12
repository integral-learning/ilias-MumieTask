<?php
/**
 * MumieTask plugin
 *
 * @copyright   2019 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class ilObjMumieTaskListGUI extends ilObjectPluginListGUI
{
    private ilMumieTaskI18N $i18n;

    public function __construct(int $a_context)
    {
        parent::__construct($a_context);
        $this->i18n = new ilMumieTaskI18N();
    }

    public function initType()
    {
        $this->setType('xmum');
    }

    public function getGuiClass(): string
    {
        return 'ilObjMumieTaskGUI';
    }

    public function initCommands(): array
    {
        global $lng, $ctrl;
        include_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskLPStatus.php');
        //Very hacky solution to update all grades for MumieTasks that are direct children of an ilContainer (e.g. Course)
        try {
            ilMumieTaskLPStatus::updateGradesForIlContainer($_GET["ref_id"]);
        } catch (Exception $e) {
            ilLoggerFactory::getLogger('xmum')->info("Error when updating MUMIE grades:");
            ilLoggerFactory::getLogger('xmum')->info($e);
        }
        return array(
            array(
                "permission" => "read",
                "cmd" => "viewContent",
                "default" => true),
            array(
                "permission" => "write",
                "cmd" => "editProperties",
                "txt" => $this->i18n->txt('edit_task'),
                "default" => false),
        );
    }

    /**
     * Insert description for MUMIE Task - including information about any deadline set by the teacher,
     *
     * We need to to override parent method or we won't be able to add any kind of styling to the deadline bade.
     * We closely follow the structure found in ilObjectListGUI::insertDescription
     *
     * @return true|void
     */
    public function insertDescription(): void
    {
        global $ilUser, $tpl;

        include_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/deadlines/class.ilMumieTaskDeadlineService.php');
        include_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskObjService.php');
        try {
            // This fragment replicates parent behaviour
            if ($this->getSubstitutionStatus()) {
                $this->insertSubstitutions();
                if (!$this->substitutions->isDescriptionEnabled()) {
//                    return true;
                }
            }

            $task = ilMumieTaskObjService::getMumieTaskFromObjectReference($this->obj_id);

            if (!$task->hasDeadline()) {
                parent::insertDescription();
            }

            $deadline = ilMumieTaskDeadlineService::getDeadlineDateForUser($ilUser->getId(), $task);

            $tpl->addCss("./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/templates/mumie.css");
            $this->tpl->setVariable("TXT_DESC", $this->getDescriptionWithDeadlineBadge($deadline, $task));
            $this->tpl->setCurrentBlock("item_description");
            $this->tpl->parseCurrentBlock();
        } catch (Exception $e) {
            ilLoggerFactory::getLogger('xmum')->error("Error when setting MUMIE Task description in list view:");
            ilLoggerFactory::getLogger('xmum')->error($e);
            parent::insertDescription();
        }
    }

    private function getDescriptionWithDeadlineBadge(ilMumieTaskDateTime $deadline, ilObjMumieTask $task): string
    {
        if (!empty($task->getDescription())) {
            return $this->getDeadlineBadge($deadline) . "<br>" .
                strip_tags($task->getDescription());
        } else {
            return  $this->getDeadlineBadge($deadline);
        }
    }

    private function getDeadlineBadge(ilMumieTaskDateTime $deadline_date): string
    {
        return '<span class = "mumie-deadline-badge">' . $this->i18n->txt('frm_grade_overview_list_deadline'). ": " . $deadline_date . "</span>";
    }

    /**
     * Insert an offline warning into the description field in list view, if MumieTask is not set to online
     *
     * @access public
     * @return array
     */
    public function getProperties(): array
    {
        global $lng;

        if (!ilObjMumieTaskAccess::_lookupOnline($this->obj_id)) {
            $props[] = array("alert" => true, "property" => $lng->txt("status"),
                "value" => $lng->txt("offline"));
        }
        return $props ?? array();
    }
}
