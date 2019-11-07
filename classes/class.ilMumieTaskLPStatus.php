<?php
include_once ('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/debugToConsole.php');

class ilMumieTaskLPStatus extends ilLPStatusPlugin {

    public static function updateAccess($userId, $objId, $refId) {
        require_once ('Services/Tracking/classes/class.ilChangeEvent.php');
        ilChangeEvent::_recordReadEvent('xmum', $refId, $objId, $userId);

        $status = self::getLPDataForUser($objId, $userId);
        if ($status == self::LP_STATUS_NOT_ATTEMPTED_NUM) {
            self::writeStatus($objId, $userId, self::LP_STATUS_IN_PROGRESS_NUM);
            self::raiseEvent($objId, $userId, self::LP_STATUS_IN_PROGRESS_NUM,
                self::getPercentageForUser($objId, $userId));
        }
    }

    public static function getLPInProgressForMumieTask($taskId) {
        return self::getLPStatusData($taskId, self::LP_STATUS_IN_PROGRESS_NUM);
    }

    public static function getLPFailedForMumieTask($taskId) {
        return self::getLPStatusData($taskId, self::LP_STATUS_FAILED_NUM);
    }

    public static function getLPCompletedForMumieTask($taskId) {
        return self::getLPStatusData($taskId, self::LP_STATUS_COMPLETED_NUM);
    }

    public static function getLPNotAttemptedForMumieTask($taskId) {
        return self::getLPStatusData($taskId, self::LP_STATUS_NOT_ATTEMPTED_NUM);
    }

    private static function updateResult($userId, $taskId, $succeded, $percentage) {
        $status = $succeded ? self::LP_STATUS_COMPLETED_NUM : self::LP_STATUS_FAILED_NUM;
        self::writeStatus($taskId, $userId, $status, $percentage, true);
        self::raiseEvent($taskId, $userId, $status, $percentage);
    }

    public static function updateGrades($task, $forceUpdate = false) {
        include_once ("Services/Tracking/classes/class.ilObjUserTracking.php");

        if (!$task->getLp_modus() && ilObjUserTracking::_enabledLearningProgress()) {
            return;
        }
        include_once ('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilObjMumieTask.php');
        include_once ('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskGradeSync.php');
        $gradeSync = new ilMumieTaskGradeSync($task, $forceUpdate);
        $gradesByUser = $gradeSync->getXapiGradesByUser();
        foreach (array_keys($gradesByUser) as $userId) {
            $xapiGrade = $gradesByUser[$userId];
            $percentage = round($xapiGrade->result->score->scaled * 100);
            self::updateResult($userId, (string) $task->getId(), $percentage >= $task->getPassing_grade(), $percentage);
            global $DIC;
            $DIC->database()->update('ut_lp_marks',
                array(
                    "status_changed" => array('text', date("Y-m-d H:i:s", strtotime($xapiGrade->timestamp))),
                    "mark" => array('int', $percentage),
                ),
                array(
                    'obj_id' => array('int', $task->getId()),
                    'usr_id' => array('int', $userId),
                ));
        }
    }

    public static function updateGradesForIlContainer($refId) {
        global $ilDB;
        include_once ('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilObjMumieTask.php');
        $result = $ilDB->query(
            "SELECT o.ref_id, m.id
            FROM tree t
            JOIN object_reference o ON t.child = o.ref_id
            JOIN xmum_mumie_task m ON m.id = o.obj_id
            WHERE t.parent = " . $ilDB->quote($refId, "integer")
        );

        while ($record = $ilDB->fetchAssoc($result)) {
            $mumieTask = new ilObjMumieTask($record["ref_id"]);
            $mumieTask->read();
            self::updateGrades($mumieTask);
        }
    }

    public static function getLPStatusForUser($task, $userId) {
        return self::getLPDataForUser($task->getId(), $userId);
    }
}

?>