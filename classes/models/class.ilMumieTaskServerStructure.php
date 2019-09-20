<?php
include_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/models/class.ilMumieTaskCourseStructure.php');
require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/debugToConsole.php');

class ilMumieTaskServerStructure implements \JsonSerializable {

    private $courses;
    private $languages = array();
    private $tags = array();

    /**
     * Get the value of courses
     */
    public function getCourses() {
        require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/debugToConsole.php');
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
        $this->collectLanguages();
        $this->collectTags();
    }

    private function collectLanguages() {
        $langs = [];
        foreach ($this->courses as $course) {
            array_push($langs, ...$course->getLanguages());
        }
        $this->languages = array_values(array_unique($langs));
    }

    private function collectTags() {
        $tags = [];
        foreach ($this->courses as $course) {
            array_push($tags, ...$course->getTags());
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
}

?>