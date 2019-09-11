<?php
class ilMumieTaskFormGUI extends ilPropertyFormGUI {
    function __construct() {
        parent::__construct();
    }
    private $nameItem, $serverItem, $courseItem, $taskItem, $launchcontainerItem, $languageItem;

    function setFields() {
        global $lng;

        $this->nameItem = new ilTextInputGUI($lng->txt('name'), 'name');
        $this->nameItem->setRequired(true);
        $this->addItem($this->nameItem);

        $this->serverItem = new ilTextInputGUI($lng->txt('rep_robj_xmum_mumie_server'), 'server');
        $this->serverItem->setRequired(true);
        $this->addItem($this->serverItem);

        $this->courseItem = new ilTextInputGUI($lng->txt('rep_robj_xmum_mumie_course'), 'course');
        $this->courseItem->setRequired(true);
        $this->addItem($this->courseItem);

        $this->taskItem = new ilTextInputGUI($lng->txt('rep_robj_xmum_mumie_task'), 'task');
        $this->taskItem->setRequired(true);
        $this->addItem($this->taskItem);

        $this->languageItem = new ilRadioGroupInputGUI($lng->txt('rep_robj_xmum_language'), 'language');
        $optLang1 = new ilRadioOption('en', 'en');
        $optLang2 = new ilRadioOption('de', 'de');
        $this->languageItem->addOption($optLang1);
        $this->languageItem->addOption($optLang2);
        $this->languageItem->setRequired(true);
        $this->addItem($this->languageItem);

        $this->launchcontainerItem = new ilRadioGroupInputGUI($lng->txt('rep_robj_xmum_launchcontainer'), 'launchcontainer');
        $optWindow = new ilRadioOption($lng->txt('rep_robj_xmum_window'), '0');
        $optEmbedded = new ilRadioOption($lng->txt('rep_robj_xmum_embedded'), '1');
        $this->launchcontainerItem->setRequired(true);
        $this->launchcontainerItem->addOption($optWindow);
        $this->launchcontainerItem->addOption($optEmbedded);
        $this->addItem($this->launchcontainerItem);

        $this->addCommandButton("submitMumieTask", $lng->txt('save'));
        $this->addCommandButton('editProperties', $lng->txt('cancel'));

    }

    function checkInput() {
        //TODO: implement validation
        return parent::checkInput();
    }
}