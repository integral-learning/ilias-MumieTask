<?php

/**
 * MumieTask plugin
 *
 * @copyright   2019 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


class ilMumieTaskServerStructure implements JsonSerializable
{
    private $courses;
    private array $languages = array();

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
     * @param $courses
     * @return  self
     */
    public function setCourses($courses): static
    {
        $this->courses = $courses;

        return $this;
    }
    protected function loadStructure($courses_and_tasks): void
    {
        $this->courses = [];
        if ($courses_and_tasks) {
            foreach ($courses_and_tasks->courses as $course) {
                array_push($this->courses, new ilMumieTaskCourseStructure($course));
            }
        }
        $this->collectLanguages();
    }

    private function collectLanguages(): void
    {
        $langs = [];
        foreach ($this->courses as $course) {
            array_push($langs, ...$course->getLanguages());
        }
        $this->languages = array_values(array_unique($langs));
    }


    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }

    /**
     * Get the value of languages
     */
    public function getLanguages(): array
    {
        return $this->languages;
    }

    /**
     * Set the value of languages
     *
     * @param $languages
     * @return  self
     */
    public function setLanguages($languages): static
    {
        $this->languages = $languages;

        return $this;
    }


    public function getCoursebyName($name)
    {
        foreach ($this->courses as $course) {
            foreach ($course->getName() as $translation) {
                if ($translation->value == $name) {
                    return $course;
                }
            }
        }
    }
}
