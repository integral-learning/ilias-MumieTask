<?php
/**
 * MumieTask plugin
 *
 * @copyright   2019 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskAdminSettings.php');
include_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilObjMumieTask.php');
require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskIdHashingService.php');

/**
 * This class pulls grades for a given task from its MUMIE server
 */
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

    /**
     * SyncIds are composed of a hashed ILIAS user id and a shorthand for the organization the operates the ilias platform.
     *
     * They must be a unique identifier for users on both ILIAS and MUMIE servers
     */
    public function getSyncIds($user_ids)
    {
        return array_map(function ($user_id) {
            $hashed_user = ilMumieTaskIdHashingService::getHashForUser($user_id, $this->task);
            return "GSSO_" . $this->admin_settings->getOrg() . "_" . $hashed_user;
        }, $user_ids);
    }

    /**
     * Get the ilias id from a xapi grade
     */
    private function getIliasId($xapi_grade)
    {
        $hashed_user = substr(strrchr($xapi_grade->actor->account->name, "_"), 1);
        return ilMumieTaskIdHashingService::getUserFromHash($hashed_user);
    }

    public function getAllXapiGradesByUser()
    {
        $params = array(
            "users" => $this->getSyncIds($this->user_ids),
            "course" => $this->task->getMumieCoursefile(),
            "objectIds" => array(self::getMumieId($this->task)),
            'lastSync' => $this->getLastSync(),
            'includeAll' => true
        );
        //ilLoggerFactory::getLogger('xmum')->info(print_r($params, true));
        if ($this->task->getActivationLimited() == 1) {
            $params["dueDate"] = $this->task->getActivationEndingTime() * 1000;
        }
        $payload = json_encode($params);

        require_once './Services/Http/classes/class.ilProxySettings.php';
        $proxy_settings = ilProxySettings::_getInstance();
        $curl = new ilCurlConnection($this->task->getGradeSyncURL());
        $curl->init();
        if (ilProxySettings::_getInstance()->isActive()) {
            $curl->setOpt(CURLOPT_HTTPPROXYTUNNEL, true);
            $curl->setOpt(CURLOPT_PROXY, ilProxySettings::_getInstance()->getHost());
            $curl->setOpt(CURLOPT_PROXYPORT, ilProxySettings::_getInstance()->getPort());
        }
        $curl->setOpt(CURLOPT_CUSTOMREQUEST, 'POST');
        $curl->setOpt(CURLOPT_USERAGENT, 'MUMIE Task for Ilias');
        $curl->setOpt(CURLOPT_POSTFIELDS, $payload);
        $curl->setOpt(CURLOPT_RETURNTRANSFER, 1);
        $curl->setOpt(
            CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($payload),
                "X-API-Key: " . $this->admin_settings->getApiKey(),
            )
        );
        $response = json_decode($curl->exec());
        //ilLoggerFactory::getLogger('xmum')->info(print_r($response, true));
        $curl->close();
        return($response);
    }

    /**
     * get a map of xapi grades by user
     */
    public function getValidXapiGradesByUser()
    {
        return $this->getValidGradeByUser($this->getAllXapiGradesByUser());
    }

    /**
     * Get the unique identifier for a MUMIE task
     *
     * @param stdClass $mumietask
     * @return string id for MUMIE task on MUMIE server
     */
    private function getMumieId($mumietask)
    {
        $id = $mumietask->getTaskurl();
        $prefix = "link/";
        if (strpos($id, $prefix) === 0) {
            $id = substr($id, strlen($prefix));
        }
        return $id;
    }


    /**
     * LastSync is used to improve performance. We don't need to check grades that were awarded before the last time we synced
     */
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
        return 1000;
    }

    /**
     * Get all users that can get marks for this MUMIE task
     */
    private function getAllUsers($task)
    {
        return ilParticipants::getInstance($task->getParentRef())->getMembers();
    }

    /**
     * A user can submit multiple solutions to MUMIE Tasks.
     *
     * Filter out grades that were earned after the due date. Other than that, select always the latest grade
     */
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
            if (!$this->wasGradeOverriden($user_id)) {
                $xapi_grades = array_filter($xapi_grades, array($this, "isGradeBeforeDueDate"));
                $valid_grade_by_user[$user_id] = $this->getLatestGrade($xapi_grades);
            } else {
                $valid_grade_by_user[$user_id] = $this->getOverridenGrade($user_id, $xapi_grades);
            }
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
    
    public function wasGradeOverriden($user_id)
    {
        global $ilDB;
        $hashed_user = ilMumieTaskIdHashingService::getHashForUser($user_id, $this->task);
        ilLoggerFactory::getLogger('xmum')->info("hashed user: " . $hashed_user);
        ilLoggerFactory::getLogger('xmum')->info("task: " . $this->task->getId());
        $query = "SELECT new_grade
        FROM xmum_grade_override
        WHERE " .
        "task_id = " . $ilDB->quote($this->task->getId(), "integer") .
        " AND " .
        "usr_id = " . $ilDB->quote($hashed_user, "text");
        $result = $ilDB->query($query);
        $grade = $ilDB->fetchAssoc($result);
        return !empty((int)$grade["new_grade"]);
    }

    private function getOverridenGrade($user_id, $xapi_grades)
    {
        global $ilDB;
        $hashed_user = ilMumieTaskIdHashingService::getHashForUser($user_id, $this->task);
        $query = "SELECT new_grade
        FROM xmum_grade_override
        WHERE " .
        "task_id = " . $ilDB->quote($this->task->getId(), "integer") .
        " AND " .
        "usr_id = " . $ilDB->quote($hashed_user, "text");
        $result = $ilDB->query($query);
        $grade = $ilDB->fetchAssoc($result);
        foreach($xapi_grades as $xGrade) {
            if(round($xGrade->result->score->raw * 100) == $grade["new_grade"]){
                return $xGrade;
            }
        }
    }

}
