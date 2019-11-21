<?
require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskAdminSettings.php');
include_once ('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilObjMumieTask.php');
include_once ('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/debugToConsole.php');
require_once ('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskIdHashingService.php');

class ilMumieTaskGradeSync {
    private $userIds, $task, $adminSettings, $forceUpdate;

    public function __construct($task, $forceUpdate){
        $this->adminSettings = ilMumieTaskAdminSettings::getInstance();
        $this->task = $task;
        $this->forceUpdate = $forceUpdate;
        $this->userIds = $this->getAllUsers($task);
    }

    private function getSyncIds($userIds) {
        return array_map(function($userId) {
            $hashedUser = ilMumieTaskIdHashingService::getHashForUser($userId);
            ilLoggerFactory::getLogger('xmum')->info('________________________________________________ hashed id: ' . $hashedUser);
            return "GSSO_" . $this->adminSettings->getOrg() . "_" . $hashedUser;
        }, $userIds);
    }

    private function getIliasId($xapiGrade) {
        $hashedUser = substr(strrchr($xapiGrade->actor->account->name, "_"), 1);
        return ilMumieTaskIdHashingService::getUserFromHash($hashedUser);
    }

    public function getXapiGradesByUser() {
        $params = array(
            "users" => $this->getSyncIds($this->userIds),
            "course" => $this->task->getMumie_coursefile(),
            "objectIds" => array(self::getMumieId($this->task)),
            'lastSync' => $this->getLastSync(),
            'includeAll' => true
        );

        if($this->task->getActivationLimited() == 1) {
            $params["dueDate"] = $this->task->getActivationEndingTime() * 1000;
        }

        $payload = json_encode($params);
        $ch = curl_init($this->task->getGradeSyncURL());
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_USERAGENT, "My User Agent Name");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($payload),
            "X-API-Key: " . $this->adminSettings->getApiKey(),
        )
        );
        $response = json_decode(curl_exec($ch));
        curl_close($ch);

        return $this->getValidGradeByUser($response);
    }
    
    /**
     * Get the unique identifier for a MUMIE task
     *
     * @param stdClass $mumietask
     * @return string id for MUMIE task on MUMIE server
     */
    private function getMumieId($mumietask) {
        $id = substr($mumietask->getTaskurl(), strlen("link/"));
        return $id;
    }


    private function getLastSync() {
        global $ilDB;
        if($this->forceUpdate) {
            return 1;
        }

        $oldestTimestamp = PHP_INT_MAX;
        $result = $ilDB->query("SELECT usr_id, obj_id, status_changed".
            " FROM ut_lp_marks".
            " WHERE obj_id = ".$ilDB->quote($this->task->getId(), "integer"));
        while ($record = $ilDB->fetchAssoc($result)) {
            if(in_array($record['usr_id'], $this->userIds) && strtotime($record['status_changed'])<$oldestTimestamp) {
                $oldestTimestamp = strtotime($record['status_changed']);
            }
        }
        return $oldestTimestamp * 1000;
    }

    private function getAllUsers($task) {
        global $ilDB;
        $users = array();
        $result = $ilDB->query("SELECT usr_id" .
            " FROM ut_lp_marks" .
            " WHERE obj_id = " . $ilDB->quote($task->getId(), "integer"));

        while ($record = $ilDB->fetchAssoc($result)) {
            array_push($users, $record['usr_id']);
        }
        return $users;
    }

    private function getValidGradeByUser($response) {
        $gradesByUser = new stdClass();
        if($response) {
            foreach($response as $xapiGrade) {                
                if(!is_array($gradesByUser->{$this->getIliasId($xapiGrade)})){
                    $gradesByUser->{$this->getIliasId($xapiGrade)} = array();
                }
                array_push($gradesByUser->{$this->getIliasId($xapiGrade)}, $xapiGrade);
            }
        }
        
        $validGradeByUser = array();
        foreach($gradesByUser as $userId => $xapiGrades) {
            $latestGrade;
            $xapiGrades = array_filter($xapiGrades, array($this, "isGradeBeforeDueDate"));
            $validGradeByUser[$userId] = $this->getLatestGrade($xapiGrades);
        }

        return array_filter($validGradeByUser);
    }
    
    private function isGradeBeforeDueDate($grade) {
        if(!$this->task->getActivationLimited()){
            return true;
        }

        return strtotime($grade->timestamp) <= $this->task->getActivationEndingTime();
    }

    private function getLatestGrade($xapiGrades) {
        if(empty($xapiGrades)) {
            return null;
        }
        $latestGrade = $xapiGrades[0];

        foreach($xapiGrades as $grade) {
            if(strtotime($grade->timestamp)> strtotime($latestGrade->timestamp)){
                $latestGrade = $grade;
            }
        }
        return $latestGrade;
    }
}
?>