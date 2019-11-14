<?php
require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskServer.php');
require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/debugToConsole.php');

class ilMumieTaskFormGUI extends ilPropertyFormGUI {
    function __construct() {
        parent::__construct();
    }
    private $titleItem, $serverItem, $courseItem, $taskItem, $launchcontainerItem, $languageItem, $serverDataItem, $courseFileItem, $filterItem, $selectTaskHeader;

    private $serverOptions = array();
    private $courseOptions = array();
    private $taskOptions = array();
    private $langOptions = array();

    function setFields() {
        global $lng;

        $this->titleItem = new ilTextInputGUI($lng->txt('title'), 'title');
        $this->titleItem->setRequired(true);
        $this->addItem($this->titleItem);

        $this->serverItem = new ilSelectInputGui($lng->txt('rep_robj_xmum_mumie_server'), 'xmum_server');
        $this->serverItem->setRequired(true);
        $this->addItem($this->serverItem);

        $this->languageItem = new ilSelectInputGUI($lng->txt('rep_robj_xmum_language'), 'xmum_language');
        $this->languageItem->setRequired(true);
        $this->addItem($this->languageItem);

        $this->courseItem = new ilSelectInputGUI($lng->txt('rep_robj_xmum_mumie_course'), 'xmum_course');
        $this->courseItem->setRequired(true);
        $this->addItem($this->courseItem);

        $filterTitle = new ilFormSectionHeaderGUI();
        $filterTitle->setTitle($lng->txt('rep_robj_xmum_mumie_filter'));
        $this->addItem($filterTitle);
        
        $this->valuePairs = new ilMultiSelectInputGUI("Values", "xmum_values");
        $this->addItem($this->valuePairs);
        
        $selectTaskHeader = new ilFormSectionHeaderGUI();
        $selectTaskHeader->setTitle($lng->txt("rep_robj_xmum_mumie_select_task"));
        $this->addItem($selectTaskHeader);

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
        $this->courseFileItem->setRequired(true);
        $this->addItem($this->courseFileItem);

        $this->populateOptions($servers);
    }

    function checkInput() {
        global $lng;
        $ok = parent::checkInput();
        $server = new ilMumieTaskServer();
        $server->setUrlPrefix($this->getInput('xmum_server'));
        $server->buildStructure();

        if (!$server->isValidMumieServer()) {
            $ok = false;
            $this->serverItem->setAlert($lng->txt('rep_robj_xmum_server_not_valid'));
        }
        $course = $server->getCoursebyName($this->getInput('xmum_course'));
        if ($course == null) {
            $ok = false;
            $this->courseItem->setAlert($lng->txt('rep_robj_xmum_frm_tsk_course_not_found'));
        }

        $task = $course->getTaskByLink($this->getInput('xmum_task'));
        if ($task == null) {
            $ok = false;
            $this->taskItem->setAlert($lng->txt('rep_robj_xmum_frm_tsk_task_not_found'));
        }
        if (!in_array($this->getInput("xmum_language"), $task->getLanguages())) {
            $ok = false;
            $this->languageItem->setAlert($lng->txt('rep_robj_xmum_frm_tsk_lang_not_found'));
        }

        return $ok;
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
        $this->setDefault();
    }

    function setDefault() {
        if ($this->launchcontainerItem->getValue() == null) {
            $this->launchcontainerItem->setValue("0");
        }
    }

    function disableDropdowns() {
        $this->serverItem->setDisabled(true);
        $this->courseItem->setDisabled(true);
        $this->taskItem->setDisabled(true);
        $this->languageItem->setDisabled(true);
    }
}