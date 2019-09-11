<?php
require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/models/class.ilMumieTaskTaskStructure.php');

class ilMumieTaskCourseStructure {
    private $name, $tasks, $pathToCourseFile;

    public function loadStructure() {
    }

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
        $this->name = $courseAndTasks->name;
        $this->pathToCourseFile = $courseAndTasks->pathToCourseFile;
        foreach ($tasks as $task) {
            array_push($this->tasks, new ilMumieTaskTaskStructure($task));
        }
    }
}
?>