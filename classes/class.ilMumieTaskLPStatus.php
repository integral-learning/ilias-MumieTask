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

    public static function updateGrades($userId, $task) {
        include_once ('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilObjMumieTask.php');
        //TEMP result!!!!
        $response = self::getXapiGradeForUser($task, $userId);
        $rawGrade = $response->result->score->scaled * 100;
        self::updateResult($userId, (string) $task->getId(), $rawGrade >= $task->getPassing_grade(), $rawGrade);

        global $DIC;
        $DIC->database()->update('ut_lp_marks',
            array(
                "status_changed" => array('text', date("Y-m-d H:i:s", strtotime($response->timestamp))),
            ),
            array(
                'obj_id' => array('int', $task->getId()),
                'usr_id' => array('int', $userId),
            ));
    }

    private static function getXapiGradeForUser($task, $userId) {
        /*
        $curl = curl_init($this->getCoursesAndTasksURL());
        curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_USERAGENT => 'Codular Sample cURL Request',
        ]);
        $response = curl_exec($curl);
        curl_close($curl);
         */
        //TEMP
        require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskAdminSettings.php');
        $adminSettings = ilMumieTaskAdminSettings::getInstance();
        $payload = json_encode(array(
            "users" => "GSSO_" . $adminSettings->getOrg() . "_" . $userId,
            "course" => $task->getMumie_coursefile(),
            "objectIds" => self::get_mumie_id($task),
            'lastSync' => 0,
        ));

        //debug_to_console($payload);
        $curl = curl_init($task->getGradeSyncURL());

        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_USERAGENT, "My User Agent Name");
        curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($payload),
            "X-API-Key: " . $adminSettings->getApiKey(),
        )
        );

        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response);
        /*
    $response = json_decode('[
    {
    "id": "3e78f83a-3d29-4332-b204-e34fd9c881d0",
    "actor": {
    "account": {
    "homePage": "https://test.mumie.net/gwt",
    "name": "GSSO_mi2_12"
    },
    "objectType": "Agent"
    },
    "verb": {
    "id": "https://www.mumie.net/xapi/verbs/submitted",
    "display": {
    "de": "abgegeben",
    "en": "submitted"
    }
    },
    "object": {
    "id": "OnlineMathemBrueckPlus/ElemenRechne/Schlus"
    },
    "result": {
    "success": false,
    "score": {
    "scaled": 0.69,
    "raw": 0.99,
    "min": 0.0,
    "max": 1.0
    }
    },
    "timestamp": "2019-05-21T14:32:17+02"
    }
    ]');
    //END TEMP
    return $response[0];
     */
    }

    /**
     * Get the unique identifier for a MUMIE task
     *
     * @param stdClass $mumietask
     * @return string id for MUMIE task on MUMIE server
     */
    private static function get_mumie_id($mumietask) {
        $id = substr($mumietask->getTaskurl(), strlen("link/"));
        return $id;
    }
}

?>