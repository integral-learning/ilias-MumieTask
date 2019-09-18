<?php
class ilMumieTaskTaskStructure implements \JsonSerializable {
    private $link, $headline;
    private $languages = array();

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
        //debug_to_console("TASK STRUCTURE: " . json_encode($task));
        $this->link = $task->link;
        $this->headline = $task->headline;
        $this->collectLanguages();
    }

    function collectLanguages() {
        foreach ($this->headline as $langItem) {
            array_push($this->languages, $langItem->language);
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
}
?>