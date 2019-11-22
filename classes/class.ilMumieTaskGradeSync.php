<?php
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskAdminSettings.php');
include_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilObjMumieTask.php');
require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskIdHashingService.php');

class ilMumieTaskGradeSync
{
    private $user_ids;
    private $task;
    private $admin_settings;
    private $force_update;

    public function __construct($task, $force_update)
    {
        $this->admin_settings = ilMumieTaskAdminSettings::getInstance();
        $this->task = $task;
        $this->force_update = $force_update;
        $this->user_ids = $this->getAllUsers($task);
    }

    private function getSyncIds($user_ids)
    {
        return array_map(function ($user_id) {
            $hashed_user = ilMumieTaskIdHashingService::getHashForUser($user_id);
            return "GSSO_" . $this->admin_settings->getOrg() . "_" . $hashed_user;
        }, $user_ids);
    }

    private function getIliasId($xapi_grade)
    {
        $hashed_user = substr(strrchr($xapi_grade->actor->account->name, "_"), 1);
        return ilMumieTaskIdHashingService::getUserFromHash($hashed_user);
    }

    public function getXapiGradesByUser()
    {
        $params = array(
            "users" => $this->getSyncIds($this->user_ids),
            "course" => $this->task->getMumieCoursefile(),
            "objectIds" => array(self::getMumieId($this->task)),
            'lastSync' => $this->getLastSync(),
            'includeAll' => true
        );

        if ($this->task->getActivationLimited() == 1) {
            $params["dueDate"] = $this->task->getActivationEndingTime() * 1000;
        }

        $payload = json_encode($params);
        $ch = curl_init($this->task->getGradeSyncURL());
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_USERAGENT, "My User Agent Name");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($payload),
            "X-API-Key: " . $this->admin_settings->getApiKey(),
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
    private function getMumieId($mumietask)
    {
        $id = substr($mumietask->getTaskurl(), strlen("link/"));
        return $id;
    }


    private function getLastSync()
    {
        global $ilDB;
        if ($this->force_update) {
            return 1;
        }

        $oldest_timestamp = PHP_INT_MAX;
        $result = $ilDB->query("SELECT usr_id, obj_id, status_changed" .
            " FROM ut_lp_marks" .
            " WHERE obj_id = " . $ilDB->quote($this->task->getId(), "integer") .
            " AND mark IS NOT NULL");
        while ($record = $ilDB->fetchAssoc($result)) {
            if (in_array($record['usr_id'], $this->user_ids) && strtotime($record['status_changed'])<$oldest_timestamp) {
                $oldest_timestamp = strtotime($record['status_changed']);
            }
        }
        if ($oldest_timestamp == PHP_INT_MAX) {
            $oldest_timestamp = 1;
        }
        return $oldest_timestamp * 1000;
    }

    private function getAllUsers($task)
    {
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

    private function getValidGradeByUser($response)
    {
        $grades_by_user = new stdClass();
        if ($response) {
            foreach ($response as $xapi_grade) {
                if (!is_array($grades_by_user->{$this->getIliasId($xapi_grade)})) {
                    $grades_by_user->{$this->getIliasId($xapi_grade)} = array();
                }
                array_push($grades_by_user->{$this->getIliasId($xapi_grade)}, $xapi_grade);
            }
        }
        
        $valid_grade_by_user = array();
        foreach ($grades_by_user as $user_id => $xapi_grades) {
            $xapi_grades = array_filter($xapi_grades, array($this, "isGradeBeforeDueDate"));
            $valid_grade_by_user[$user_id] = $this->getLatestGrade($xapi_grades);
        }

        return array_filter($valid_grade_by_user);
    }
    
    private function isGradeBeforeDueDate($grade)
    {
        if (!$this->task->getActivationLimited()) {
            return true;
        }

        return strtotime($grade->timestamp) <= $this->task->getActivationEndingTime();
    }

    private function getLatestGrade($xapi_grades)
    {
        if (empty($xapi_grades)) {
            return null;
        }
        $latest_grade = $xapi_grades[0];

        foreach ($xapi_grades as $grade) {
            if (strtotime($grade->timestamp)> strtotime($latest_grade->timestamp)) {
                $latest_grade = $grade;
            }
        }
        return $latest_grade;
    }
}
