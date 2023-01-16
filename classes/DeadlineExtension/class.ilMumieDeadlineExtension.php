<?php

/**
 * MumieTask plugin
 *
 * @copyright   2022 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Vasilije Nedeljkovic(vasilije.nedeljkovic@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/DeadlineExtension/class.ilMumieTaskDateTime.php');

/**
 * This class represents a due date extension granted to a student
 */
class ilMumieDeadlineExtension {
    private $date;
    private $user_id;
    private $task_id;

    /**
     * @param $unix_time
     * @param $user_id
     * @param $task_id
     */
    public function __construct($unix_time, $user_id, $task_id)
    {
        $this->date = new ilMumieTaskDateTime($unix_time, IL_CAL_UNIX);
        $this->user_id = $user_id;
        $this->task_id = $task_id;
    }

    /**
     * @return ilMumieTaskDateTime
     */
    public function getDate(): ilMumieTaskDateTime
    {
        return $this->date;
    }

    /**
     * @param mixed $date
     */
    public function setDate($date) : void
    {
        $this->date = $date;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * @param mixed $user_id
     */
    public function setUserId($user_id) : void
    {
        $this->user_id = $user_id;
    }

    /**
     * @return mixed
     */
    public function getTaskId()
    {
        return $this->task_id;
    }

    /**
     * @param mixed $task_id
     */
    public function setTaskId($task_id) : void
    {
        $this->task_id = $task_id;
    }
}