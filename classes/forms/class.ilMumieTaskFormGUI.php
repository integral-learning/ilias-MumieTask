<?php
require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskServer.php');
require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/debugToConsole.php');

class ilMumieTaskFormGUI extends ilPropertyFormGUI {
    function __construct() {
        parent::__construct();
    }
    private $nameItem, $serverItem, $courseItem, $taskItem, $launchcontainerItem, $languageItem, $serverDataItem, $courseFileItem, $filterItem;

    private $serverOptions = array();
    private $courseOptions = array();
    private $taskOptions = array();
    private $langOptions = array();

    function setFields() {
        global $lng;

        $this->nameItem = new ilTextInputGUI($lng->txt('name'), 'name');
        $this->nameItem->setRequired(true);
        $this->addItem($this->nameItem);

        $this->serverItem = new ilSelectInputGui($lng->txt('rep_robj_xmum_mumie_server'), 'xmum_server');
        $this->serverItem->setRequired(true);
        $this->addItem($this->serverItem);

        $this->languageItem = new ilSelectInputGUI($lng->txt('rep_robj_xmum_language'), 'xmum_language');
        $this->languageItem->setRequired(true);
        $this->addItem($this->languageItem);

        $this->courseItem = new ilSelectInputGUI($lng->txt('rep_robj_xmum_mumie_course'), 'xmum_course');
        $this->courseItem->setRequired(true);
        $this->addItem($this->courseItem);

        $this->filterItem = new ilMultiSelectInputGUI($lng->txt("rep_robj_xmum_filter"), "xmum_filter");
        $this->addItem($this->filterItem);

        $this->taskItem = new ilSelectInputGUI($lng->txt('rep_robj_xmum_mumie_task'), 'xmum_task');
        $this->taskItem->setInfo($lng->txt('rep_robj_xmum_mumie_task_desc'));
        $this->taskItem->setRequired(true);

        $this->addItem($this->taskItem);

        $this->launchcontainerItem = new ilRadioGroupInputGUI($lng->txt('rep_robj_xmum_launchcontainer'), 'xmum_launchcontainer');
        $optWindow = new ilRadioOption($lng->txt('rep_robj_xmum_window'), '0');
        $optEmbedded = new ilRadioOption($lng->txt('rep_robj_xmum_embedded'), '1');
        $this->launchcontainerItem->setRequired(true);
        $this->launchcontainerItem->addOption($optWindow);
        $this->launchcontainerItem->addOption($optEmbedded);
        $this->addItem($this->launchcontainerItem);

        $servers = ilMumieTaskServer::getAllServers();

        $this->serverDataItem = new ilHiddenInputGUI('server_data');
        $this->addItem($this->serverDataItem);

        $this->courseFileItem = new ilHiddenInputGUI('xmum_coursefile');
        $this->addItem($this->courseFileItem);

        $this->populateOptions($servers);
        $this->addCommandButton("submitMumieTask", $lng->txt('save'));
        $this->addCommandButton('editProperties', $lng->txt('cancel'));
    }

    function checkInput() {
        //TODO: implement validation
        return parent::checkInput();
    }

    private function populateOptions($servers) {
        forEach ($servers as $server) {
            $this->compileServerOption($server);
            $this->compileLangOptions($server);
        }
        $this->serverItem->setOptions($this->serverOptions);
        $this->courseItem->setOptions($this->courseOptions);
        $this->taskItem->setOptions($this->taskOptions);
        $this->languageItem->setOptions($this->langOptions);
    }

    private function compileLangOptions($server) {
        foreach ($server->getLanguages() as $lang) {
            $this->langOptions[$lang] = $lang;
        };
    }

    private function compileServerOption($server) {

        $this->serverOptions[$server->getUrlprefix()] = $server->getName();

        foreach ($server->getCourses() as $course) {
            $this->compileCourseOption($course);
        }
    }

    private function compileCourseOption($course) {
        $this->courseOptions[$course->getName()] = $course->getName();

        foreach ($course->getTasks() as $task) {
            $this->compileTaskOption($task);
        }
    }

    private function compileTaskOption($task) {
        $this->taskOptions[$task->getLink()] = $task->getLink();
    }

    function setValuesByArray($a_values, $a_restrict_to_value_keys = false) {
        parent::setValuesByArray($a_values);
        $servers = (array) ilMumieTaskServer::getAllServers();
        $this->serverDataItem->setValue(json_encode($servers));
    }
}