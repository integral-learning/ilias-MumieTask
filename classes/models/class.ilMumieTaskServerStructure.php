<?php
include_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/models/class.ilMumieTaskCourseStructure.php');
require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/debugToConsole.php');

class ilMumieTaskServerStructure {

    private $courses;

    /**
     * Get the value of courses
     */
    public function getCourses() {
        require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/debugToConsole.php');
        //debug_to_console("SERVER STRUCTURE: " . json_encode($coursesAndTasks));
        return $this->courses;
    }

    /**
     * Set the value of courses
     *
     * @return  self
     */
    public function setCourses($courses) {
        $this->courses = $courses;

        return $this;
    }
    protected function loadStructure($coursesAndTasks) {
        $this->courses = [];
        foreach ($coursesAndTasks->courses as $course) {
            array_push($this->courses, new ilMumieTaskCourseStructure($course));
        }
    }

    public function getLanguages() {
        $langs = [];
        foreach ($this->courses as $course) {
            array_push($langs, ...$course->getLanguages());
        }
        return array_values(array_unique($langs));
    }
}

?>