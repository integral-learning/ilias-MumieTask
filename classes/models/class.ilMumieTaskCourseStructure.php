<?php
include_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/models/class.ilMumieTaskTaskStructure.php');

class ilMumieTaskCourseStructure implements \JsonSerializable {
    private $name, $tasks, $pathToCourseFile;
    private $languages = array();
    private $tags = array();

    /**
     * Get the value of pathToCourseFile
     */
    public function getPathToCourseFile() {
        return $this->pathToCourseFile;
    }

    /**
     * Set the value of pathToCourseFile
     *
     * @return  self
     */
    public function setPathToCourseFile($pathToCourseFile) {
        $this->pathToCourseFile = $pathToCourseFile;

        return $this;
    }

    /**
     * Get the value of tasks
     */
    public function getTasks() {
        return $this->tasks;
    }

    /**
     * Set the value of tasks
     *
     * @return  self
     */
    public function setTasks($tasks) {
        $this->tasks = $tasks;

        return $this;
    }

    /**
     * Get the value of name
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Set the value of name
     *
     * @return  self
     */
    public function setName($name) {
        $this->name = $name;

        return $this;
    }

    function __construct($courseAndTasks) {
        require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/debugToConsole.php');
        $this->name = $courseAndTasks->name;
        $this->pathToCourseFile = $courseAndTasks->pathToCourseFile;
        $this->tasks = [];
        foreach ($courseAndTasks->tasks as $task) {
            $taskObj = new ilMumieTaskTaskStructure($task);
            array_push($this->tasks, $taskObj);
        }
        $this->collectLanguages();
        $this->collectTags();
    }

    function collectLanguages() {
        $langs = [];
        foreach ($this->tasks as $task) {
            array_push($langs, ...$task->getLanguages());
        }
        $this->languages = array_values(array_unique($langs));
    }

    function collectTags() {
        $tags = [];
        foreach ($this->tasks as $task) {
            array_push($tags, ...$task->getTags());
        }
        $this->tags = array_values(array_unique($tags));
    }

    public function jsonSerialize() {
        $vars = get_object_vars($this);

        return $vars;
    }

    /**
     * Get the value of languages
     */
    public function getLanguages() {
        return $this->languages;
    }

    /**
     * Set the value of languages
     *
     * @return  self
     */
    public function setLanguages($languages) {
        $this->languages = $languages;

        return $this;
    }

    /**
     * Get the value of tags
     */
    public function getTags() {
        return $this->tags;
    }

    public function getTaskByLink($link) {
        foreach ($this->tasks as $task) {
            if ($task->getLink() == $link) {
                return $task;
            }
        }
    }
}
?>