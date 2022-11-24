<?php

/**
 * MumieTask plugin
 *
 * @copyright   2022 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Vasilije Nedeljkovic(vasilije.nedeljkovic@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * This class provides services for overriding grades
 */

class ilMumieTaskGradeOverrideService
{
    public static function wasGradeOverridden($user_id, $task)
    {
        $grade = self::returnOverriddenGrade($user_id, $task);
        return !is_null($grade["new_grade"]);
    }

    public static function getOverriddenGrade($user_id, $xapi_grades, $task)
    {
        $grade = self::returnOverriddenGrade($user_id, $task);
        foreach ($xapi_grades as $xGrade) {
            if (round($xGrade->result->score->raw * 100) == $grade["new_grade"]) {
                return $xGrade;
            }
        }
    }

    private static function returnOverriddenGrade($user_id, $task)
    {
        global $ilDB;
        $hashed_user = ilMumieTaskIdHashingService::getHashForUser($user_id, $task);
        $query = "SELECT new_grade
        FROM xmum_grade_override
        WHERE " .
        "usr_id = " . $ilDB->quote($hashed_user, "text") .
        " AND " .
        "task_id = " . $ilDB->quote($task->getId(), "integer");
        $result = $ilDB->query($query);
        return $ilDB->fetchAssoc($result);
    }
}
