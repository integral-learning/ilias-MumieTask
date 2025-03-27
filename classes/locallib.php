<?php

require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/deadlines/extension/class.ilMumieTaskDeadlineExtensionService.php');

class locallib
{
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
