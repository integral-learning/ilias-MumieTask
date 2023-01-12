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

class ilMumieTaskDateOverrideService
{
    const DATE_OVERRIDE_TABLE = "xmum_deadline_ext";
    const TASK_ID = 'task_id';
    const USER_ID = 'usr_id';
    const DATE = 'date';

    public static function upsertOverriddenDate($mumie_task, $date_time_input, $user_id)
    {
        global $ilDB, $lng;
        $hashed_user = ilMumieTaskIdHashingService::getHashForUser($user_id, $mumie_task);
        if (!self::wasDueDateOverriden($user_id, $mumie_task)) {
            self::insertOverriddenDate($mumie_task,$hashed_user, $date_time_input);
        } else {
            self::updateOverriddenDate($mumie_task, $hashed_user, $date_time_input);
        }
        $result = $ilDB->query("SELECT firstname, lastname FROM usr_data WHERE usr_id = ". $ilDB->quote($user_id, "integer"));
        $names = $ilDB->fetchAssoc($result);
        ilUtil::sendSuccess($lng->txt('rep_robj_xmum_frm_deadline_extension_successfull_date_update') . " " . $names["firstname"] . ",  " . $names["lastname"] . " " .  $lng->txt('rep_robj_xmum_frm_grade_overview_list_to') . " " . substr($date_time_input, 0, 10) . " - " . substr($date_time_input, 11, 5));
    }

    private static function insertOverriddenDate($mumie_task, $user_id, $date) {
        global $ilDB;
        $ilDB->insert(
            self::DATE_OVERRIDE_TABLE,
            array(
                self::TASK_ID => array('integer', $mumie_task->getId()),
                self::USER_ID => array('text', $user_id),
                self::DATE => array('integer', strtotime($date))
            )
        );
    }

    private static function updateOverriddenDate($mumie_task, $user_id, $date) {
        global $ilDB;
        $ilDB->update(
            self::DATE_OVERRIDE_TABLE,
            array(
                self::DATE => array('integer', strtotime($date))
            ),
            array(
                self::TASK_ID => array('integer', $mumie_task->getId()),
                self::USER_ID => array('text', $user_id),
            )
        );
    }

    public static function wasDueDateOverriden($user_id, $task)
    {
        global $ilDB;
        $hashed_user = ilMumieTaskIdHashingService::getHashForUser($user_id, $task);
        $query = "SELECT date
        FROM xmum_deadline_ext
        WHERE " .
        "usr_id = " . $ilDB->quote($hashed_user, "text") .
        " AND " .
        "task_id = " . $ilDB->quote($task->getId(), "integer");
        $result = $ilDB->query($query);
        $grade = $ilDB->fetchAssoc($result);
        return !is_null($grade[self::DATE]);
        return false;
    }

    public static function getOverridenDueDate($user_id, $task)
    {
        global $ilDB;
        $hashed_user = ilMumieTaskIdHashingService::getHashForUser($user_id, $task);
        $query = "SELECT date
        FROM xmum_deadline_ext
        WHERE " .
        "task_id = " . $ilDB->quote($task->getId(), "integer") .
        " AND " .
        "usr_id = " . $ilDB->quote($hashed_user, "text");
        $result = $ilDB->query($query);
        return $ilDB->fetchAssoc($result)[self::DATE];
    }

    public static function deleteOverridenGradesForTask($task)
    {
        global $ilDB;
        $ilDB->manipulate("DELETE FROM xmum_deadline_ext WHERE task_id = " . $ilDB->quote($task->getId(), 'integer'));
    }
}
