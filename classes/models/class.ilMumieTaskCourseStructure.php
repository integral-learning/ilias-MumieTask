<?php
/**
 * MumieTask plugin
 *
 * @copyright   2019 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
include_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/models/class.ilMumieTaskTaskStructure.php');
include_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskAdminSettings.php');

class ilMumieTaskCourseStructure implements \JsonSerializable
{
    private $name;
    private $tasks;
    private $path_to_course_file;
    private $languages = array();
    private $tags = array();
    private $link;

    /**
     * Get the value of path_to_course_file
     */
    public function getPathToCourseFile()
    {
        return $this->path_to_course_file;
    }

    /**
     * Set the value of path_to_course_file
     *
     * @return  self
     */
    public function setPathToCourseFile($path_to_course_file)
    {
        $this->path_to_course_file = $path_to_course_file;

        return $this;
    }

    /**
     * Get the value of tasks
     */
    public function getTasks()
    {
        return $this->tasks;
    }

    /**
     * Set the value of tasks
     *
     * @return  self
     */
    public function setTasks($tasks)
    {
        $this->tasks = $tasks;

        return $this;
    }

    /**
     * Get the value of name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value of name
     *
     * @return  self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }
    /**
     * Set the value of link
     * @param  string $link
     * @return self
     */
    public function setLink($link)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * Get the value of link
     * @return void
     */
    public function getLink()
    {
        return $this->link;
    }

    public function __construct($course_and_tasks)
    {
        $this->name = $course_and_tasks->name;
        $this->path_to_course_file = $course_and_tasks->pathToCourseFile;
        $this->tasks = [];

        if(isset($course_and_tasks->link)) {
            $this->link = $course_and_tasks->link;
        }
        if ($course_and_tasks->tasks) {
            foreach ($course_and_tasks->tasks as $task) {
                $task_obj = new ilMumieTaskTaskStructure($task);
                array_push($this->tasks, $task_obj);
            }
        }
        $this->collectLanguages();
        $this->collectTags();
    }

    /**
     * Get all languages available in this course.
     *
     * @return string[]
     */
    public function collectLanguages()
    {
        $langs = [];
        foreach ($this->name as $translation) {
            array_push($langs, $translation->language);
        }
        $this->languages = array_values(array_unique($langs));
    }

    /**
     * Get all tags used in this course.
     *
     * @return ilMumieTaskTagStructure[] tags
     */
    public function collectTags()
    {
        $tags = array();
        foreach ($this->tasks as $task) {
            foreach ($task->getTags() as $tag) {
                if (!isset($tags[$tag->getName()])) {
                    $tags[$tag->getName()] = array();
                }
                $tags[$tag->getName()] = $tag->merge($tags[$tag->getName()]);
            }
        }

        $this->tags = array_values($tags);
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

    /**
     * Get the values of the tags
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
    * Get the tags
    */
    public function getTags()
    {
        return $this->tags;
    }

    public function getTaskByLink($link)
    {
        foreach ($this->tasks as $task) {
            if ($task->getLink() == $link) {
                return $task;
            }
        }
    }
}
