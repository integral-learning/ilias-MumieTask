<?php

require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/models/class.ilMumieTaskTagStructure.php');
/**
 * MumieTask plugin
 *
 * @copyright   2019 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ilMumieTaskTaskStructure implements \JsonSerializable
{
    private $link;
    private $headline;
    private $languages = array();
    private $tags = array();

    /**
     * Get the value of headline
     */
    public function getHeadline()
    {
        return $this->headline;
    }

    /**
     * Set the value of headline
     *
     * @return  self
     */
    public function setHeadline($headline)
    {
        $this->headline = $headline;

        return $this;
    }

    public function __construct($task)
    {
        $this->link = $task->link;
        $this->headline = $task->headline;
        if (isset($task->tags)) {
            foreach ($task->tags as $tag) {
                array_push($this->tags, new ilMumieTaskTagStructure($tag->name, $tag->values));
            }
        }
        $this->collectLanguages();
    }

    /**
     * Get all langauges used in this task
     *
     * @return string[]
     */
    public function collectLanguages()
    {
        if ($this->headline) {
            foreach ($this->headline as $langItem) {
                array_push($this->languages, $langItem->language);
            }
        }
    }
    /**
     * Get the value of link
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Set the value of link
     *
     * @return  self
     */
    public function setLink($link)
    {
        $this->link = $link;

        return $this;
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
     * Get the value of tags
     */
    public function getTags()
    {
        return $this->tags;
    }
}
