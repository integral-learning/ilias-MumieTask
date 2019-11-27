<?php
include_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/models/class.ilMumieTaskTaskStructure.php');

class ilMumieTaskCourseStructure implements \JsonSerializable
{
    private $name;
    private $tasks;
    private $path_to_course_file;
    private $languages = array();
    private $tag_names = array();
    private $tag_values = array();

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

    public function __construct($course_and_tasks)
    {
        $this->name = $course_and_tasks->name;
        $this->path_to_course_file = $course_and_tasks->pathToCourseFile;
        $this->tasks = [];
        if ($course_and_tasks->tasks) {
            foreach ($course_and_tasks->tasks as $task) {
                $task_obj = new ilMumieTaskTaskStructure($task);
                array_push($this->tasks, $task_obj);
            }
        }
        $this->collectLanguages();
        $this->collectTags();
    }

    public function collectLanguages()
    {
        $langs = [];
        foreach ($this->tasks as $task) {
            array_push($langs, ...$task->getLanguages());
        }
        $this->languages = array_values(array_unique($langs));
    }

    public function collectTags()
    {
        $tag_names = array();
        $tag_values = array();
        foreach ($this->tasks as $task) {
            foreach ($task->getTags() as $tag) {
                array_push($tag_names, $tag->name);
                array_push($tag_values, ...$tag->values);
            }
        }
        $this->tag_names = array_values(array_unique($tag_names));
        $this->values = array_values(array_unique($tag_values));
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
    * Get the tag_names of the tags
    */
    public function getTagNames()
    {
        return $this->tag_names;
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
