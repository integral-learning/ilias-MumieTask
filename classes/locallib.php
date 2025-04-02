<?php

require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/deadlines/extension/class.ilMumieTaskDeadlineExtensionService.php');

class locallib
{
    /**
        * A prefix used in task_urls indicating that the task is a worksheet
        */
    public const WORKSHEET_PREFIX = "worksheet_";

    /**
      * Get the unique identifier for a MUMIE task
      *
      * @param ilObjMumieTask $mumietask
      * @return string id for MUMIE task on MUMIE/LEMON server
      */
    public static function getMumieId($mumietask): string
    {
        return self::transformMumieId($mumietask->getTaskurl());
    }

    public static function transformMumieId($mumietask_taskurl): string
    {
        $id = $mumietask_taskurl;
        $prefix = "link/";
        if (strpos($id, $prefix) > 0) {
            $id = substr($mumietask_taskurl, strlen($prefix));
            ilLoggerFactory::getLogger('xmum')->info("test ");
        }
        return $id;

    }

    /**
     * Get the effective duedate for a student.
     *
     * Individual due date extensions always overrule general due date settings.
     *
     * @param  int $userid
     * @param  ilObjMumieTask $mumietask
     * @return int
     */
    public static function mumie_get_effective_duedate(int $userid, ilObjMumieTask $mumietask): ?int
    {
        return self::get_effective_duedate($userid, $mumietask);
    }

    /**
     * Transforms the deadline(Unix Timestamp) from seconds to milliseconds.
     * @param int $deadline timestamp in s
     * @return int timestamp in ms
     */
    public static function auth_mumie_get_deadline_in_ms($deadline)
    {
        return $deadline * 1000;
    }

    /**
    * Get worksheet id from problem path
    * @return string
    */
    public static function get_worksheet_id($mumietask): string
    {
        $problempath = $mumietask->auth_mumie_get_problem_path();
        return str_replace(self::WORKSHEET_PREFIX, "", $problempath);
    }

    /**
     * Get the effective duedate for a student.
     *
     * Individual due date extensions always overrule general due date settings.
     *
     * @param  int $userid
     * @param  ilObjMumieTask $mumietask
     * @return int
     */
    private static function get_effective_duedate($userid, $mumietask): ?int
    {
        $extension = new ilMumieTaskDeadlineExtensionService();
        $duedate = $extension->getDeadlineExtensionDate($userid, $mumietask);
        if (isset($duedate) && $duedate->getUnixTime()) {
            return $duedate->getUnixTime();
        }
        return $mumietask->getDeadline();
    }

}
