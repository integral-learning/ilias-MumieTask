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
require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/i18n/class.ilMumieTaskI18N.php');
class ilMumieTaskMultiUploadProcessor
{
    public static function process(ilObjMumieTask $base_task, string $tasks_json)
    {
        global $DIC;
        $i18N = new ilMumieTaskI18N();
        $task_dtos = self::parseTaskDTOs($tasks_json);
        foreach ($task_dtos as $taskDTO) {
            self::generateMumieTask($taskDTO, $base_task);
        }
        $DIC->ui()->mainTemplate()->setOnScreenMessage('info', sprintf($i18N->txt("multi_create_success"), count($task_dtos)), true);
    }

    public static function isValid(string $tasks_json): bool
    {
        try {
            $task_dtos = self::parseTaskDTOs($tasks_json);
            return !in_array(
                false,
                array_map(function ($task_dto) {
                    return self::isValidProblem($task_dto);
                }, $task_dtos),
                true
            );
        } catch (Exception $exception) {
            return false;
        }
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
        $new_task = self::generateEmptyMumieTask($base_task->getParentRef(), $base_task->getType());

        $new_task->setTitle($task_dto->getName());
        $new_task->setServer($task_dto->getServer());
        $new_task->setMumieCourse($task_dto->getCourse());
        $new_task->setTaskurl($task_dto->getLink());
        $new_task->setLanguage($task_dto->getLanguage());
        $new_task->setLaunchcontainer($base_task->getLaunchcontainer());
        $new_task->setMumieCoursefile($task_dto->getPathToCoursefile());
        $new_task->setDeadline($base_task->getDeadline());
        $new_task->setOnline($base_task->getOnline());
        $new_task->setPrivateGradepool($base_task->getPrivateGradepool());
        $new_task->update();
    }

    private static function generateEmptyMumieTask($parent_ref, $type): ilObjMumieTask
    {
        $new_task = new ilObjMumieTask();
        $new_task->setType($type);
        $new_task->create();
        $new_task->createReference();
        $new_task->putInTree($parent_ref);
        $new_task->setParentRolePermissions($parent_ref);
        return $new_task;
    }

    private static function isValidProblem(ilMumieTaskTaskDTO $task_dto): bool
    {
        require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskServer.php');
        $server = ilMumieTaskServer::fromUrl($task_dto->getServer());
        $server->buildStructure();
        $course = $server->getCoursebyName($task_dto->getCourse());
        $task = $course->getTaskByLink($task_dto->getLink());
        return !is_null($task);
    }
}
