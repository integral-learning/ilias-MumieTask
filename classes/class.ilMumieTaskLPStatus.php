<?php

/**
 * MumieTask plugin
 *
 * @copyright   2019 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * This class provides information about LP progress and methods to synchronize it with MUMIE servers
 */
class ilMumieTaskLPStatus extends ilLPStatusPlugin
{
    public static function updateAccess($user_id, $objId, $refId, $old_status)
    {
        require_once('Services/Tracking/classes/class.ilChangeEvent.php');
        ilChangeEvent::_recordReadEvent('xmum', $refId, $objId, $user_id);

        $status = self::getLPDataForUser($objId, $user_id);
        if ($status == self::LP_STATUS_NOT_ATTEMPTED_NUM) {
            self::writeStatus($objId, $user_id, self::LP_STATUS_IN_PROGRESS_NUM);
            self::raiseEvent(
                $objId,
                $user_id,
                self::LP_STATUS_IN_PROGRESS_NUM,
                $old_status,
                self::getPercentageForUser($objId, $user_id)
            );
        }
    }

    public static function getLPInProgressForMumieTask($taskId)
    {
        return self::getLPStatusData($taskId, self::LP_STATUS_IN_PROGRESS_NUM);
    }

    public static function getLPFailedForMumieTask($taskId)
    {
        return self::getLPStatusData($taskId, self::LP_STATUS_FAILED_NUM);
    }

    public static function getLPCompletedForMumieTask($taskId)
    {
        return self::getLPStatusData($taskId, self::LP_STATUS_COMPLETED_NUM);
    }

    public static function getLPNotAttemptedForMumieTask($taskId)
    {
        return self::getLPStatusData($taskId, self::LP_STATUS_NOT_ATTEMPTED_NUM);
    }

    private static function updateResult($user_id, $taskId, $succeeded, $percentage)
    {
        $status = $succeeded ? self::LP_STATUS_COMPLETED_NUM : self::LP_STATUS_FAILED_NUM;
        self::writeStatus($taskId, $user_id, $status, $percentage, true);
        self::raiseEvent($taskId, $user_id, $status, self::getLPDataForUser($taskId, $user_id), $percentage);
    }

    /**
     * Synchronize grade for a given MumieTask
     *
     * @param stdClass $task the task we want to update grades for
     * @param boolean $force_update if true delete all saved learning progress data and then synchronize it again
     */
    public static function updateGrades($task, $force_update = false)
    {
        if ($task->getPrivateGradepool() == -1) {
            return;
        }
        include_once("Services/Tracking/classes/class.ilObjUserTracking.php");

        if (!$task->getServer() || !$task->getLpModus() && ilObjUserTracking::_enabledLearningProgress()) {
            return;
        }
        include_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskGradeSync.php');
        $grade_sync = new ilMumieTaskGradeSync($task, $force_update);

        if ($force_update) {
            ilLoggerFactory::getLogger('xmum')->info("MumieTask: Changes triggered forced grade update");
            self::deleteLPForTask($task);
        }
        include_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilObjMumieTask.php');
        $grades_by_user = $grade_sync->getValidXapiGradesByUser();
        foreach (array_keys($grades_by_user) as $user_id) {
            $xapi_grade = $grades_by_user[$user_id];
            $percentage = round($xapi_grade->result->score->scaled * 100);
            self::updateResult($user_id, (string) $task->getId(), $percentage >= $task->getPassingGrade(), $percentage);
            self::upsertMarks($user_id, $task, $xapi_grade);
        }
    }

    private static function upsertMarks($user_id, $task, $xapi_grade)
    {
        global $ilDB, $DIC;
        $query = "SELECT * FROM ut_lp_marks WHERE 
        obj_id = " . $ilDB->quote($task->getId(), "integer") .
        " AND " .
        "usr_id = " . $ilDB->quote($user_id, "integer");
        $existingGrade = $ilDB->fetchAssoc($ilDB->query($query));
        if (is_null($existingGrade)) {
            self::insertMark($user_id, $task->getId());
        }
        self::updateMark($user_id, $task->getId(), round($xapi_grade->result->score->scaled * 100), strtotime($xapi_grade->timestamp));
    }

    private static function insertMark($user_id, $task_id)
    {
        global $ilDB;
        $ilDB->insert(
            "ut_lp_marks",
            array(
                'obj_id' => array('integer', $task_id),
                'usr_id' => array('text', $user_id)
            )
        );
    }

    public static function updateMark($user_id, $task_id, $percentage, $timestamp)
    {
        global $DIC;
        $DIC->database()->update(
            'ut_lp_marks',
            array(
                "status_changed" => array('text', date("Y-m-d H:i:s", $timestamp)),
                "mark" => array('int', $percentage),
            ),
            array(
                'obj_id' => array('int', $task_id),
                'usr_id' => array('int', $user_id),
            )
        );
    }

    /**
     * Update grade for all MumieTasks that are found in a given ilContainer (e.g. Course)
     *
     * @param int $refId RefId of the ilContainer
     */
    public static function updateGradesForIlContainer($refId)
    {
        include_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilObjMumieTask.php');

        $mumieTasks = ilMumieTaskLPStatus::getMumieTasksInRepository($refId);
        foreach ($mumieTasks as $mumieTask) {
            try {
                $mumieTask->setDescription("ttttmmmppppp");
                self::updateGrades($mumieTask);
            } catch(Exception $e) {
                ilLoggerFactory::getLogger('xmum')->info('Error when updating grades for MUMIE Task: ' . $mumieTask->id);
                ilLoggerFactory::getLogger('xmum')->info($e);
            }
        }
    }

    /**
     *  @return ilObjMumieTask[]
     */
    private static function getMumieTasksInRepository($refId)
    {
        global $ilDB;

        $result = $ilDB->query(
            "SELECT o.ref_id, m.id
            FROM tree t
            JOIN object_reference o ON t.child = o.ref_id
            JOIN xmum_mumie_task m ON m.id = o.obj_id
            WHERE t.parent = " . $ilDB->quote($refId, "integer")
        );

        $mumieTasks = array();

        while ($record = $ilDB->fetchAssoc($result)) {
            $mumieTask = new ilObjMumieTask($record["ref_id"]);
            $mumieTask->read();
            array_push($mumieTasks, $mumieTask);
        }
        return $mumieTasks;
    }

    public static function updateGradepoolSettingsForAllMumieTaskInRepository($refId, $privategradepool)
    {
        $mumieTasks = ilMumieTaskLPStatus::getMumieTasksInRepository($refId);
        foreach ($mumieTasks as $mumieTask) {
            $mumieTask->setPrivateGradepool($privategradepool);
            $mumieTask->doUpdate();
        }
    }


    public static function deriveGradepoolSetting($refId)
    {
        $mumieTasks = ilMumieTaskLPStatus::getMumieTasksInRepository($refId);
        if (!empty($mumieTasks)) {
            return $mumieTasks[0]->getPrivateGradepool();
        }
    }

    public static function getLPStatusForUser($task, $user_id)
    {
        return self::getLPDataForUser($task->getId(), $user_id);
    }

    public static function getCurrentGradeForUser($user_id, $task_id)
    {
        global $ilDB;
        $result = $ilDB->query(
            "SELECT mark
            FROM ut_lp_marks 
            WHERE usr_id = " . $ilDB->quote($user_id, "integer") .
            " AND " .
            "obj_id = " . $ilDB->quote($task_id, "integer")
        );
        return $ilDB->fetchAssoc($result)["mark"];
    }

    private static function deleteLPForTask($task)
    {
        require_once('Services/Tracking/classes/class.ilChangeEvent.php');
        ilChangeEvent::_deleteReadEvents($task->getId());
        global $ilDB;
        $ilDB->manipulate("DELETE FROM ut_lp_marks WHERE obj_id = " . $ilDB->quote($task->getId(), 'integer'));
    }
}
