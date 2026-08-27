<?php

/**
 * MumieTask plugin.
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
    /**
     * @var object|null
     */
    private $worksheet;

    public function __construct(string $task_json)
    {
        $task = json_decode($task_json);
        if (!is_object($task)) {
            throw new InvalidArgumentException('Multi-select task payload is not a JSON object.');
        }
        foreach (['name', 'server', 'course', 'path_to_coursefile', 'language', 'link'] as $required_field) {
            if (!isset($task->{$required_field})) {
                throw new InvalidArgumentException(sprintf('Multi-select task payload is missing required field "%s".', $required_field));
            }
        }
        $this->name = $task->name;
        $this->server = $task->server;
        $this->course = $task->course;
        $this->path_to_coursefile = $task->path_to_coursefile;
        $this->language = $task->language;
        $this->link = $task->link;
        $this->worksheet = $task->worksheet ?? null;
    }

    public function getServer(): string
    {
        return $this->server;
    }

    public function getCourse(): string
    {
        return $this->course;
    }

    public function getPathToCoursefile(): string
    {
        return $this->path_to_coursefile;
    }

    public function getName(): string
    {
        return $this->name;
    }

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

    public function getWorksheet(): string
    {
        return null !== $this->worksheet ? json_encode($this->worksheet) : '';
    }
}
