<?php

/**
 * MumieTask plugin
 *
 * @copyright   2019 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ilMumieTaskTaskStructure implements JsonSerializable
{
    private $link;
    private $headline;
    private array $languages = [];
    private array $tags = [];

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
     * @param $headline
     * @return  self
     */
    public function setHeadline($headline): static
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
     * Get all languages used in this task
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
     * @param $link
     * @return  self
     */
    public function setLink($link): static
    {
        $this->link = $link;

        return $this;
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

    /**
     * Get the value of tags
     */
    public function getTags(): array
    {
        return $this->tags;
    }
}
