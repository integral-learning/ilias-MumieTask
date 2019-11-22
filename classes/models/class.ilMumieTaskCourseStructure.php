<?php
include_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/models/class.ilMumieTaskTaskStructure.php');

class ilMumieTaskCourseStructure implements \JsonSerializable
{
    private $name;
    private $tasks;
    private $pathToCourseFile;
    private $languages = array();
    private $names = array();
    private $values = array();

    /**
     * Get the value of pathToCourseFile
     */
    public function getPathToCourseFile()
    {
        return $this->pathToCourseFile;
    }

    /**
     * Set the value of pathToCourseFile
     *
     * @return  self
     */
    public function setPathToCourseFile($pathToCourseFile)
    {
        $this->pathToCourseFile = $pathToCourseFile;

        return $this;
    }

    /**
     * Get the value of tasks
     */
    public function getTasks()
    {
        return $this->tasks;
    }

    /**
     * Set the value of tasks
     *
     * @return  self
     */
    public function setTasks($tasks)
    {
        $this->tasks = $tasks;

        return $this;
    }

    /**
     * Get the value of name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value of name
     *
     * @return  self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function __construct($courseAndTasks)
    {
        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/debugToConsole.php');
        $this->name = $courseAndTasks->name;
        $this->pathToCourseFile = $courseAndTasks->pathToCourseFile;
        $this->tasks = [];
        if ($courseAndTasks->tasks) {
            foreach ($courseAndTasks->tasks as $task) {
                $taskObj = new ilMumieTaskTaskStructure($task);
                array_push($this->tasks, $taskObj);
            }
        }
        $this->collectLanguages();
        $this->collectTags();
    }

    public function collectLanguages()
    {
        $langs = [];
        foreach ($this->tasks as $task) {
            array_push($langs, ...$task->getLanguages());
        }
        $this->languages = array_values(array_unique($langs));
    }

    public function collectTags()
    {
        $names = array();
        $values = array();
        foreach ($this->tasks as $task) {
            foreach ($task->getTags() as $tag) {
                array_push($names, $tag->name);
                array_push($values, ...$tag->values);
            }
        }
        $this->names = array_values(array_unique($names));
        $this->values = array_values(array_unique($values));
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);

        return $vars;
    }

    /**
     * Get the value of languages
     */
    public function getLanguages()
    {
        return $this->languages;
    }

    /**
     * Set the value of languages
     *
     * @return  self
     */
    public function setLanguages($languages)
    {
        $this->languages = $languages;

        return $this;
    }

    /**
     * Get the values of the tags
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
    * Get the names of the tags
    */
    public function getNames()
    {
        return $this->names;
    }

    public function getTaskByLink($link)
    {
        foreach ($this->tasks as $task) {
            if ($task->getLink() == $link) {
                return $task;
            }
        }
    }
}
