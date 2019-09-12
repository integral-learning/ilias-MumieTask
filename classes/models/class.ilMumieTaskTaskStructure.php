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
        $this->link = $task->link;
        $this->headline = $task->headline;
    }

    function getLanguages() {
        $langs = [];
        foreach ($headline as $langItem) {
            array_push($langs, $langItem->language);
        }
    }
}
?>