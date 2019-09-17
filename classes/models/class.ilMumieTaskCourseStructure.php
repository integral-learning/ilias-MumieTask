<?php
include_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/models/class.ilMumieTaskTaskStructure.php');

class ilMumieTaskCourseStructure {
    private $name, $tasks, $pathToCourseFile;

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
        //debug_to_console("COURSE STRUCTURE: " . json_encode($courseAndTasks));
        $this->name = $courseAndTasks->name;
        $this->pathToCourseFile = $courseAndTasks->pathToCourseFile;
        $this->tasks = [];
        foreach ($courseAndTasks->tasks as $task) {
            array_push($this->tasks, new ilMumieTaskTaskStructure($task));
        }
    }

    function getLanguages() {
        $langs = [];
        foreach ($courses as $course) {
            array_push($langs, $course->getLanguages());
        }
        return array_unique($langs);
    }
}
?>