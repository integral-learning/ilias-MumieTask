<?php
/**
 * MumieTask plugin
 *
 * @copyright   2022 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class ilMumieTaskTaskDTO
{
    /**
     * @var string
     */
    private $server;
    /**
     * @var string
     */
    private $course;
    /**
     * @var string
     */
    /**
     * @var string
     */
    private $name;
    /**
     * @var string;
     */
    /**
     * @var string
     */
    private $language;
    /**
     * @var string
     */
    private $path_to_coursefile;
    /**
     * @var string
     */
    private $link;
    public function __construct(string $task_json)
    {
        $task = json_decode($task_json);
        $this->name = $task->name;
        $this->server = $task->server;
        $this->course = $task->course;
        $this->path_to_coursefile = $task->path_to_coursefile;
        $this->language = $task->language;
        $this->link = $task->link;
    }

    /**
     * @return string
     */
    public function getServer(): string
    {
        return $this->server;
    }

    /**
     * @return string
     */
    public function getCourse(): string
    {
        return $this->course;
    }

    /**
     * @return string
     */
    public function getPathToCoursefile(): string
    {
        return $this->path_to_coursefile;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getLanguage(): string
    {
        return $this->language;
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }
}
