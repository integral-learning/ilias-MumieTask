<?php
require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskServer.php');
require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/debugToConsole.php');

class ilMumieTaskFormGUI extends ilPropertyFormGUI {
    function __construct() {
        parent::__construct();
    }
    private $titleItem, $descriptionItem, $serverItem, $courseItem, $problemItem, $launchcontainerItem, $languageItem, $serverDataItem, $courseFileItem, $filterItem;

    private $serverOptions = array();
    private $courseOptions = array();
    private $taskOptions = array();
    private $langOptions = array();

    function setFields($isCreationMode = false) {
        global $lng, $ilCtrl;

        $this->titleItem = new ilTextInputGUI($lng->txt('title'), 'title');
        $this->titleItem->setRequired(true);
        $this->addItem($this->titleItem);

        $this->descriptionItem = new ilTextInputGUI($lng->txt('description'), 'description');
        $this->addItem($this->descriptionItem);

        $this->serverItem = new ilSelectInputGui($lng->txt('rep_robj_xmum_mumie_server'), 'xmum_server');
        $this->serverItem->setRequired(true);
        $this->addItem($this->serverItem);

        if (!$isCreationMode) {
            require_once ("Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/forms/class.ilMumieTaskFormButtonGUI.php");
            $addServerButton = new ilMumieTaskFormButtonGUI("");
            $addServerButton->setButtonLabel($this->lng->txt('rep_robj_xmum_add_server'));
            $addServerButton->setLink($ilCtrl->getLinkTargetByClass(array('ilObjMumieTaskGUI'), 'addServer'));
            $addServerButton->setInfo($this->lng->txt('rep_robj_xmum_add_server_desc'));
            $this->addItem($addServerButton);
        }

        $this->languageItem = new ilSelectInputGUI($lng->txt('rep_robj_xmum_language'), 'xmum_language');
        $this->languageItem->setInfo($lng->txt('rep_robj_xmum_language_desc'));
        $this->languageItem->setRequired(true);
        $this->addItem($this->languageItem);

        $this->courseItem = new ilSelectInputGUI($lng->txt('rep_robj_xmum_mumie_course'), 'xmum_course');
        $this->courseItem->setRequired(true);
        $this->addItem($this->courseItem);

        $filterTitle = new ilFormSectionHeaderGUI();
        $filterTitle->setTitle($lng->txt('rep_robj_xmum_mumie_filter'));
        $this->addItem($filterTitle);
        
        $valuePairs = new ilMultiSelectInputGUI("Values", "xmum_values"); // no need for $lng here this field will be hidden by the js
        $this->addItem($valuePairs);
        
        $selectTaskHeader = new ilFormSectionHeaderGUI();
        $selectTaskHeader->setTitle($lng->txt("rep_robj_xmum_mumie_select_task"));
        $this->addItem($selectTaskHeader);

        $this->problemItem = new ilSelectInputGUI($lng->txt('rep_robj_xmum_mumie_problem'), 'xmum_task');
        $this->problemItem->setInfo($lng->txt('rep_robj_xmum_mumie_problem_desc'));
        $this->problemItem->setRequired(true);

        $this->addItem($this->problemItem);

        $this->launchcontainerItem = new ilRadioGroupInputGUI($lng->txt('rep_robj_xmum_launchcontainer'), 'xmum_launchcontainer');
        $optWindow = new ilRadioOption($lng->txt('rep_robj_xmum_window'), '0');
        $optEmbedded = new ilRadioOption($lng->txt('rep_robj_xmum_embedded'), '1');
        $this->launchcontainerItem->setRequired(true);
        $this->launchcontainerItem->addOption($optWindow);
        $this->launchcontainerItem->addOption($optEmbedded);
        $this->launchcontainerItem->setInfo($lng->txt('rep_robj_xmum_launchcontainer_desc'));
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
        $isDummy = $this->getInput('title') == ilObjMumieTask::DUMMY_TITLE;
        $server = new ilMumieTaskServer();
        $server->setUrlPrefix($this->getInput('xmum_server'));
        $server->buildStructure();
        $course = $server->getCoursebyName($this->getInput('xmum_course'));
        $task = $course->getTaskByLink($this->getInput('xmum_task'));

        if ($isDummy && $task != null) {
            $ok = false;
            $this->titleItem->setAlert($lng->txt('rep_robj_xmum_title_not_valid'));
        }
        if (!$server->isValidMumieServer()) {
            $ok = false;
            $this->serverItem->setAlert($lng->txt('rep_robj_xmum_server_not_valid'));
            return $ok;
        }
        if ($course == null) {
            $ok = false;
            $this->courseItem->setAlert($lng->txt('rep_robj_xmum_frm_tsk_course_not_found'));
            return $ok;
        }

        if ($task == null && $isDummy) {
            $ok = false;
            $this->problemItem->setAlert($lng->txt('required_field'));
            return $ok;
        } else if ($task == null) {
            $ok = false;
            $this->problemItem->setAlert($lng->txt('rep_robj_xmum_frm_tsk_problem_not_found'));
            return $ok;
        }
        if (!$isDummy && !in_array($this->getInput("xmum_language"), $task->getLanguages())) {
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
        $this->problemItem->setOptions($this->taskOptions);
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
        $this->problemItem->setDisabled(true);
        $this->languageItem->setDisabled(true);
    }
}