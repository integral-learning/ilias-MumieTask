<?php
require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/models/class.ilMumieTaskCourseStructure.php');

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
    public function loadStructure($coursesAndTasks) {
        foreach ($coursesAndTasks as $course) {
            array_push($courses, new ilMumieTaskCourseStructure($course));
        }
    }
}

?>