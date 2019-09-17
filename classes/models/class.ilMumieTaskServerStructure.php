<?php
include_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/models/class.ilMumieTaskCourseStructure.php');

class ilMumieTaskServerStructure {

    private $courses;

    /**
     * Get the value of courses
     */
    public function getCourses() {
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
        require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/debugToConsole.php');
        //debug_to_console("SERVER STRUCTURE: " . json_encode($coursesAndTasks));
        foreach ($coursesAndTasks->courses as $course) {
            array_push($this->courses, new ilMumieTaskCourseStructure($course));
        }
    }
}

?>