<?php
class ilMumieTaskTaskStructure implements \JsonSerializable {
    private $link, $headline;
    private $languages = array();
    private $tags = array();

    /**
     * Get the value of headline
     */
    public function getHeadline() {
        return $this->headline;
    }

    /**
     * Set the value of headline
     *
     * @return  self
     */
    public function setHeadline($headline) {
        $this->headline = $headline;

        return $this;
    }

    function __construct($task) {
        require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/debugToConsole.php');
        // debug_to_console(json_encode($task)); // hint: not using "json_encode" causes an error that lets us see the stack trace on 
        $this->link = $task->link;
        $this->headline = $task->headline;
        if (isset($task->tags)) {
            $this->tags = $task->tags;
        }
        //debug_to_console("next task");
        $this->collectLanguages();
    }

    function collectLanguages() {
        if($this->headline) {
            foreach ($this->headline as $langItem) {
                array_push($this->languages, $langItem->language);
            }
        }
    }
    /**
     * Get the value of link
     */
    public function getLink() {
        return $this->link;
    }

    /**
     * Set the value of link
     *
     * @return  self
     */
    public function setLink($link) {
        $this->link = $link;

        return $this;
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