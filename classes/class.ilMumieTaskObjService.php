<?php

/**
 * MumieTask plugin.
 *
 * @copyright   2022 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ilMumieTaskObjService
{
    public static function getMumieTaskFromObjectReference($obj_id): ilObjMumieTask
    {
        global $DIC;
        $db = $DIC->database();
        $query = 'SELECT  * FROM object_reference WHERE obj_id = ' . $db->quote($obj_id, 'integer');
        $result = $db->query($query);
        $task_ref_id = $db->fetchAssoc($result);

        return new ilObjMumieTask($task_ref_id['ref_id']);
    }
}
