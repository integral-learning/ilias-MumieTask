<?php
class ilMumieTaskTaskStructure {
    private $link, $headline, $languages;

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
    }

    function getLanguages() {
        $langs = [];
        foreach ($this->headline as $langItem) {
            array_push($langs, $langItem->language);
        }

        return $langs;
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
}
?>