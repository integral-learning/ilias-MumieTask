<?php

/**
 * MumieTask plugin
 *
 * @copyright   2019 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/grades/class.ilMumieTaskGrade.php');

/**
 * This class provides information about LP progress and methods to synchronize it with MUMIE servers
 */
class ilMumieTaskLPStatus extends ilLPStatusPlugin
{
    public static function updateAccess($user_id, ilObjMumieTask $mumie_task, $refId, $old_status)
    {
        require_once('Services/Tracking/classes/class.ilChangeEvent.php');
        ilChangeEvent::_recordReadEvent('xmum', $refId, $mumie_task->getId(), $user_id);

        $status = self::getLPStatusForUser($mumie_task, $user_id);
        if ($status == self::LP_STATUS_NOT_ATTEMPTED_NUM) {
            self::writeStatus($mumie_task->getId(), $user_id, self::LP_STATUS_IN_PROGRESS_NUM);
            self::raiseEvent(
                $mumie_task->getId(),
                $user_id,
                self::LP_STATUS_IN_PROGRESS_NUM,
                $old_status,
                self::getPercentageForUser($mumie_task->getId(), $user_id)
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
        if (!self::isGradable($task)) {
            return;
        }
        include_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskGradeSync.php');
        $grade_sync = new ilMumieTaskGradeSync($task, $force_update);

        if ($force_update) {
            ilLoggerFactory::getLogger('xmum')->info("MumieTask: Changes triggered forced grade update");
            self::deleteLPForTask($task);
        }
        include_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilObjMumieTask.php');
        $grades_by_user = $grade_sync->getValidAndNewXapiGradesByUser();
        foreach (array_keys($grades_by_user) as $user_id) {
            $xapi_grade = $grades_by_user[$user_id];
            self::upsertXapiGrade($xapi_grade, $task, $user_id);
        }
    }

    public static function updateGradeForUser($task, $user_id, $force_update = false)
    {
        if (!self::isGradable($task)) {
            return;
        }
        include_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskGradeSync.php');
        $grade_sync = new ilMumieTaskGradeSync($task, $force_update);

        if ($force_update) {
            self::deleteLPForTask($task, $user_id);
        }

        $xapi_grade = $grade_sync->getValidAndNewXapiGradesForUser($user_id);
        self::upsertXapiGrade($xapi_grade, $task, $user_id);
    }

    private static function upsertXapiGrade($xapi_grade, $task, $user_id)
    {
        $percentage = round($xapi_grade->result->score->scaled * 100);
        self::updateResult($user_id, (string) $task->getId(), $percentage >= $task->getPassingGrade(), $percentage);
        self::upsertMarks($user_id, $task, $xapi_grade);
    }

    private static function isGradable(ilObjMumieTask $task): bool
    {
        if ($task->getPrivateGradepool() == -1) {
            return false;
        }

        if (!$task->getServer()) {
            return false;
        }
        include_once("Services/Tracking/classes/class.ilObjUserTracking.php");
        if (!$task->getLpModus() && ilObjUserTracking::_enabledLearningProgress()) {
            return false;
        }
        return true;
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
                self::updateGrades($mumieTask);
            } catch (Exception $e) {
                ilLoggerFactory::getLogger('xmum')->info('Error when updating grades for MUMIE Task: ' . $mumieTask->getId());
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

    public static function getLPStatusForUser(ilObjMumieTask $task, $user_id): int
    {
        //This is necessary because of a null offset bug in ilLPStatusPlugin::getLPDataForUser under php8
        if (is_null(self::getLpMark($user_id, $task))) {
            return self::LP_STATUS_NOT_ATTEMPTED_NUM;
        }
        return self::getLPDataForUser($task->getId(), $user_id);
    }

    public static function getCurrentGradeForUser($user_id, ilObjMumieTask $mumie_task): ?ilMumieTaskGrade
    {
        $lp_mark = self::getLpMark($user_id, $mumie_task);
        if (is_null($lp_mark)) {
            return null;
        }
        $score = $lp_mark['mark'] / 100;
        $timestamp = strtotime($lp_mark['status_changed']);
        return new ilMumieTaskGrade($user_id, $score, $mumie_task, $timestamp);
    }

    private static function getLpMark($user_id, ilObjMumieTask $mumie_task): ?array
    {
        global $ilDB;

        return $ilDB->fetchAssoc($ilDB->query(
            "SELECT *
            FROM ut_lp_marks 
            WHERE usr_id = " . $ilDB->quote($user_id, "integer") .
            " AND " .
            "obj_id = " . $ilDB->quote($mumie_task->getId(), "integer")
        ));
    }

    private static function deleteLPForTask($task, $user_id = 0)
    {
        require_once('Services/Tracking/classes/class.ilChangeEvent.php');
        ilChangeEvent::_deleteReadEvents($task->getId());
        global $ilDB;
        $query = "DELETE FROM ut_lp_marks WHERE obj_id = " . $ilDB->quote($task->getId(), 'integer');
        if ($user_id > 0) {
            $query .= " AND usr_id = " . $ilDB->quote($task->getId(), 'integer');
        }
        $ilDB->manipulate($query);
    }
}
