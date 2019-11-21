<?php
include_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/models/class.ilMumieTaskCourseStructure.php');
require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/debugToConsole.php');

class ilMumieTaskServerStructure implements \JsonSerializable {

    private $courses;
    private $languages = array();
    // private $names = array(); // these dont seem necessary anymore, 
    // private $values = array(); // if you do need them comment them in here but also comment them in in collectTags in loadStrucutre (~line 37)

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
        if($coursesAndTasks){
            foreach ($coursesAndTasks->courses as $course) {
                array_push($this->courses, new ilMumieTaskCourseStructure($course));
            }
        }
        $this->collectLanguages();
        // $this->collectTags();
    }

    private function collectLanguages() {
        $langs = [];
        foreach ($this->courses as $course) {
            array_push($langs, ...$course->getLanguages());
        }
        $this->languages = array_values(array_unique($langs));
    }

    private function collectTags() {
        $nmaes = [];
        $values = [];
        foreach ($this->courses as $course) {
            array_push($names, ...$course->getNames());
            array_push($values, ...$course->getValues());
            
        }
        $this->names = array_values(array_unique($names));
        $this->values = array_values(array_unique($values));
        
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
     * Get the names of tags
     */
    public function getNames() {
        return $this->names;
    }
    
    /**
     * Get the values of the tags
     */
    public function getValues() {
        return $this->values;
    }

    public function getCoursebyName($name) {
        foreach ($this->courses as $course) {
            if ($course->getName() == $name) {
                return $course;
            }
        }
    }
}

?>