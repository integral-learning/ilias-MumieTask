<?php
/**
 * MumieTask plugin
 *
 * @copyright   2022 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/tasks/class.ilMumieTaskTaskDTO.php');
require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilObjMumieTask.php');
class ilMumieTaskMultiUploadProcessor
{
    public static function process(ilObjMumieTask $base_task, string $tasks_json)
    {
        global $lng;
        $taskDTOs = self::parseTaskDTOs($tasks_json);
        foreach ($taskDTOs as $taskDTO) {
            self::generateMumieTask($taskDTO, $base_task);
        }
        ilUtil::sendInfo(sprintf($lng->txt("rep_robj_xmum_multi_create_success"), count($taskDTOs)), true);
    }

    private static function parseTaskDTOs(string $tasks_json): array
    {
        $tasks =  json_decode($tasks_json);
        return array_map(function (string $task) {
            return new ilMumieTaskTaskDTO($task);
        }, $tasks);
    }

    private static function generateMumieTask(ilMumieTaskTaskDTO $task_dto, ilObjMumieTask $base_task)
    {
        $new_task = self::generateEmtyMumieTask($base_task->getParentRef(), $base_task->getType());

        $new_task->setTitle($task_dto->getName());
        $new_task->setServer($task_dto->getServer());
        $new_task->setMumieCourse($task_dto->getCourse());
        $new_task->setTaskurl($task_dto->getLink());
        $new_task->setLanguage($task_dto->getLanguage());
        $new_task->setLaunchcontainer($base_task->getLaunchcontainer());
        $new_task->setMumieCoursefile($task_dto->getPathToCoursefile());
        $new_task->setDeadline($base_task->getDeadline());
        $new_task->setOnline($base_task->getOnline());
        $new_task->update();
    }

    private static function generateEmtyMumieTask($parent_ref, $type): ilObjMumieTask
    {
        $new_task = new ilObjMumieTask();
        $new_task->setType($type);
        $new_task->create();
        $new_task->createReference();
        $new_task->putInTree($parent_ref);
        $new_task->setParentRolePermissions($parent_ref);
        return $new_task;
    }
}