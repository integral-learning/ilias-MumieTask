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
    public const TASK_ID = 'task_id';
    public const USER_ID = 'usr_id';
    public const NEW_GRADE = 'new_grade';

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
        return null;
    }

    private static function loadOverriddenGrade($user_id, $task)
    {
        global $ilDB;
        $query = "SELECT new_grade
        FROM xmum_grade_override
        WHERE " .
        "usr_id = " . $ilDB->quote($user_id, "text") .
        " AND " .
        "task_id = " . $ilDB->quote($task->getId(), "integer");
        $result = $ilDB->query($query);
        return $ilDB->fetchAssoc($result)[self::NEW_GRADE];
    }

    public static function overrideGrade(ilMumieTaskGrade $grade)
    {
        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskLPStatus.php');
        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskGradeSync.php');

        ilMumieTaskLPStatus::updateMark($grade->getUserId(), $grade->getMumieTask()->getId(), $grade->getPercentileScore(), $grade->getTimestamp());
        if (!self::wasGradeOverridden($grade->getUserId(), $grade->getMumieTask())) {
            self::insertGradeOverride($grade);
        }
        self::updateGradeOverride($grade);
    }

    private static function insertGradeOverride(ilMumieTaskGrade $grade)
    {
        global $ilDB;
        $ilDB->insert(
            "xmum_grade_override",
            array(
                self::TASK_ID => array('integer', $grade->getMumieTask()->getId()),
                self::USER_ID => array('text', $grade->getUserId()),
                self::NEW_GRADE => array('integer', $grade->getPercentileScore())
            )
        );
    }

    private static function updateGradeOverride(ilMumieTaskGrade $grade)
    {
        global $ilDB;
        $ilDB->update(
            "xmum_grade_override",
            array(
                self::NEW_GRADE => array('integer', $grade->getPercentileScore())
            ),
            array(
                self::TASK_ID => array('integer', $grade->getMumieTask()->getId()),
                self::USER_ID => array('text', $grade->getUserId()),
            )
        );
    }

    public static function deleteGradeOverridesForTask(ilObjMumieTask $task)
    {
        global $ilDB;
        $ilDB->manipulate("DELETE FROM xmum_grade_override WHERE task_id = " . $ilDB->quote($task->getId(), 'integer'));
    }

    public static function deleteGradeOverride(ilObjMumieTask $task, $user_id)
    {
        global $ilDB;
        $query = "DELETE FROM xmum_grade_override WHERE task_id = " .
            $ilDB->quote($task->getId(), 'integer') .
            " AND usr_id = " . $ilDB->quote($user_id);
        $ilDB->manipulate($query);
    }
}
