<?php
/**
 * MumieTask plugin
 *
 * @copyright   2019 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
include_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/models/class.ilMumieTaskCourseStructure.php');

class ilMumieTaskServerStructure implements \JsonSerializable
{
    private $courses;
    private $languages = array();

    /**
     * Get the value of courses
     */
    public function getCourses()
    {
        return $this->courses;
    }

    /**
     * Set the value of courses
     *
     * @return  self
     */
    public function setCourses($courses)
    {
        $this->courses = $courses;

        return $this;
    }
    protected function loadStructure($courses_and_tasks)
    {
        $this->courses = [];
        if ($courses_and_tasks) {
            foreach ($courses_and_tasks->courses as $course) {
                array_push($this->courses, new ilMumieTaskCourseStructure($course));
            }
        }
        $this->collectLanguages();
    }

    private function collectLanguages()
    {
        $langs = [];
        foreach ($this->courses as $course) {
            array_push($langs, ...$course->getLanguages());
        }
        $this->languages = array_values(array_unique($langs));
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


    public function getCoursebyName($name)
    {
        foreach ($this->courses as $course) {
            if ($course->getName() == $name) {
                return $course;
            }
        }
    }
}
