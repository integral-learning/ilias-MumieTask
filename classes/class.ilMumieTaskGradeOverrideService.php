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
        $grade = self::loadOverriddenGrade($user_id, $task);
        return !is_null($grade);
    }

    public static function getOverriddenGrade($user_id, $xapi_grades, $task)
    {
        $grade = self::loadOverriddenGrade($user_id, $task);
        foreach ($xapi_grades as $xGrade) {
            if (round($xGrade->result->score->raw * 100) == $grade) {
                return $xGrade;
            }
        }
    }

    private static function loadOverriddenGrade($user_id, $task)
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
        return $ilDB->fetchAssoc($result)["new_grade"];
    }

    public static function overrideGrade(ilMumieTaskGrade $grade)
    {
        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskLPStatus.php');
        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskGradeSync.php');
        require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskIdHashingService.php');

        ilMumieTaskLPStatus::updateMark($grade->getUserId(), $grade->getMumieTask()->getId(), $grade->getPercentileScore(), $grade->getTimestamp());
        $hashed_user = ilMumieTaskIdHashingService::getHashForUser($grade->getUserId(), $grade->getMumieTask());
        if (!self::wasGradeOverridden($grade->getUserId(), $grade->getMumieTask())) {
            self::insertOverridenGradenUserData($hashed_user, $grade->getMumieTask()->getId());
        }
        self::updateOverridenGrade($hashed_user, $grade->getMumieTask()->getId(), $grade->getPercentileScore());
    }

    private static function insertOverridenGradenUserData($hashed_user, $task_id)
    {
        global $ilDB;
        $ilDB->insert(
            "xmum_grade_override",
            array(
                'task_id' => array('integer', $task_id),
                'usr_id' => array('text', $hashed_user),
            )
        );
    }

    private static function updateOverridenGrade($hashed_user, $task_id, $percentage)
    {
        global $ilDB;
        $ilDB->update(
            "xmum_grade_override",
            array(
                'new_grade' => array('integer', $percentage)
            ),
            array(
                'task_id' => array('integer', $task_id),
                'usr_id' => array('text', $hashed_user),
            )
        );
    }

    public static function deleteOverridenGradesForTask($task)
    {
        global $ilDB;
        $ilDB->manipulate("DELETE FROM xmum_grade_override WHERE task_id = " . $ilDB->quote($task->getId(), 'integer'));
    }
}
