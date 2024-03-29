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
require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/deadlines/extension/class.ilMumieTaskDeadlineExtensionService.php');
require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/deadlines/class.ilMumieTaskDeadlineService.php');
require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/grades/class.ilMumieTaskGrade.php');
require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/users/class.ilMumieTaskParticipantService.php');

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
        $this->user_ids = ilMumieTaskParticipantService::getAllMemberIds($task);
    }

    public function getSyncIdForUser($user_id)
    {
        $hashed_user = ilMumieTaskIdHashingService::getHashForUser($user_id, $this->task);
        return "GSSO_" . $this->admin_settings->getOrg() . "_" . $hashed_user;
    }

    /**
     * SyncIds are composed of a hashed ILIAS user id and a shorthand for the organization the operates the ilias platform.
     *
     * They must be a unique identifier for users on both ILIAS and MUMIE servers
     */
    public function getSyncIds($user_ids)
    {
        return array_map(array($this, "getSyncIdForUser"), $user_ids);
    }

    /**
     * Get the ilias id from a xapi grade
     */
    private function getIliasId($xapi_grade)
    {
        $hashed_user = substr(strrchr($xapi_grade->actor->account->name, "_"), 1);
        return ilMumieTaskIdHashingService::getUserFromHash($hashed_user);
    }

    private function getNewXapiGrades()
    {
        return $this->getXapiGrades($this->getXapiRequestBody(true));
    }

    private function getAllXapiGradesByUser()
    {
        return $this->getXapiGrades($this->getXapiRequestBody(false));
    }

    private function getXapiGrades($request_body)
    {
        $payload = json_encode($request_body);
        require_once './Services/Http/classes/class.ilProxySettings.php';
        $proxy_settings = ilProxySettings::_getInstance();
        $curl = new ilCurlConnection($this->task->getGradeSyncURL());
        $curl->init();
        if ($proxy_settings->isActive()) {
            $curl->setOpt(CURLOPT_HTTPPROXYTUNNEL, true);
            $curl->setOpt(CURLOPT_PROXY, $proxy_settings->getHost());
            $curl->setOpt(CURLOPT_PROXYPORT, $proxy_settings->getPort());
        }
        $curl->setOpt(CURLOPT_CUSTOMREQUEST, 'POST');
        $curl->setOpt(CURLOPT_USERAGENT, 'MUMIE Task for Ilias');
        $curl->setOpt(CURLOPT_POSTFIELDS, $payload);
        $curl->setOpt(CURLOPT_RETURNTRANSFER, 1);
        $curl->setOpt(
            CURLOPT_HTTPHEADER,
            $this->getXapiRequestHeaders($payload)
        );
        $response = json_decode($curl->exec());
        $curl->close();
        return($response);
    }

    private function getXapiRequestBody($getOnlyChangedGrades)
    {
        $params = array(
            "users" => $this->getSyncIds($this->user_ids),
            "course" => $this->task->getMumieCoursefile(),
            "objectIds" => array(self::getMumieId($this->task)),
            'lastSync' => $getOnlyChangedGrades ? $this->getLastSync() : 1,
            'includeAll' => true
        );
        return $params;
    }

    private function getXapiRequestHeaders($payload)
    {
        return array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($payload),
            "X-API-Key: " . $this->admin_settings->getApiKey(),
        );
    }

    /**
     * get a map of xapi grades by user
     */
    public function getValidAndNewXapiGradesByUser()
    {
        return $this->getValidGradeByUser($this->getNewXapiGrades());
    }

    public function getValidAndNewXapiGradesForUser($user_id)
    {
        $grades_by_user = $this->getValidAndNewXapiGradesByUser();
        return $grades_by_user[$user_id];
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
        return $oldest_timestamp*1000;
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
                $ilias_id = $this->getIliasId($xapi_grade);
                if (!isset($grades_by_user->$ilias_id)) {
                    $grades_by_user->{$ilias_id} = array();
                }
                array_push($grades_by_user->{$ilias_id}, $xapi_grade);
            }
        }

        $valid_grade_by_user = array();
        foreach ($grades_by_user as $user_id => $xapi_grades) {
            require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskGradeOverrideService.php');
            if (!ilMumieTaskGradeOverrideService::wasGradeOverridden($user_id, $this->task)) {
                $xapi_grades = array_filter($xapi_grades, array($this, "isGradeBeforeDueDate"));
                $valid_grade_by_user[$user_id] = $this->getLatestGrade($xapi_grades);
            } else {
                $valid_grade_by_user[$user_id] = ilMumieTaskGradeOverrideService::getOverriddenGrade($user_id, $xapi_grades, $this->task);
            }
        }
        return array_filter($valid_grade_by_user);
    }

    private function isGradeBeforeDueDate($grade)
    {
        if (!$this->task->hasDeadline()) {
            return true;
        }
        if (ilMumieTaskDeadlineExtensionService::hasDeadlineExtension($this->getIliasId($grade), $this->task)) {
            return strtotime($grade->timestamp) <= ilMumieTaskDeadlineExtensionService::getDeadlineExtensionDate($this->getIliasId($grade), $this->task)->getUnixTime();
        }
        return strtotime($grade->timestamp) <= $this->task->getDeadline();
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

    public static function isValidGrade(ilMumieTaskGrade $grade)
    {
        $user_grades = self::getGradesForUser($grade->getUserId(), $grade->getMumieTask());
        foreach ($user_grades as $xapi_grade) {
            if ($grade->getScore() == $xapi_grade->result->score->raw) {
                return true;
            }
        }
        return false;
    }

    public static function getGradesForUser($user_id, $mumie_task)
    {
        $gradesync  = new  ilMumieTaskGradeSync($mumie_task, false);
        $xapi_grades = $gradesync->getAllXapiGradesByUser();
        $syncId = $gradesync->getSyncIdForUser($user_id);
        $userGrades = array();
        if (empty($xapi_grades)) {
            return;
        }
        foreach ($xapi_grades as $xapi_grade) {
            if ($xapi_grade->actor->account->name == $syncId) {
                array_push($userGrades, $xapi_grade);
            }
        }
        return $userGrades;
    }
}
