<?php
require_once ('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilObjMumieTask.php');
class ilMumieTaskObjService
{
    public static function getMumieTaskFromObjectReference($obj_id): ilObjMumieTask
    {
        global $ilDB;
        $query = "SELECT  * FROM object_reference WHERE obj_id = " . $ilDB->quote($obj_id, "integer");
        $result = $ilDB->query($query);
        $task_ref_id = $ilDB->fetchAssoc($result);
        $task = new ilObjMumieTask($task_ref_id["ref_id"]);
        return $task;
    }
}