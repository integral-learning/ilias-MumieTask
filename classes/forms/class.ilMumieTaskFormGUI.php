<?php
/**
 * MumieTask plugin
 *
 * @copyright   2019 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskServer.php');
require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/i18n/class.ilMumieTaskI18N.php');

/**
 * This form is used to edit and validate the general settings of MumieTasks
 */
class ilMumieTaskFormGUI extends ilPropertyFormGUI
{
    private ilMumieTaskI18N $i18N;
    public function __construct()
    {
        parent::__construct();
        $this->i18N = new ilMumieTaskI18N();
    }

    private $title_item;
    private $description_item;
    private $server_item;
    private $course_item;
    private $course_display_item;
    private $problem_display_item;
    private $problem_item;
    private $launchcontainer_item;
    private $language_item;
    private $server_data_item;
    private $course_file_item;
    private $org_item;
    private $worksheet_item;
    private $dropzone_item;

    private $server_options = array();

    public function setFields($is_creation_mode = false)
    {
        global $ilCtrl;

        $this->title_item = new ilTextInputGUI($this->i18N->globalTxt('title'), 'title');
        $this->title_item->setRequired(true);
        $this->addItem($this->title_item);

        $this->description_item = new ilTextInputGUI($this->i18N->globalTxt('description'), 'description');
        $this->addItem($this->description_item);

        require_once('Services/Form/classes/class.ilSelectInputGUI.php');
        $this->server_item = new ilSelectInputGui($this->i18N->txt('mumie_server'), 'xmum_server');
        $this->server_item->setRequired(true);
        $this->addItem($this->server_item);

        if (!$is_creation_mode) {
            require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/forms/class.ilMumieTaskFormButtonGUI.php");
            $add_server_button = new ilMumieTaskFormButtonGUI("", "xmum_add_server_btn");
            $add_server_button->setButtonLabel($this->i18N->txt('add_server'));
            $add_server_button->setLink($ilCtrl->getLinkTargetByClass(array('ilObjMumieTaskGUI'), 'addServer'));
            $add_server_button->setInfo($this->i18N->txt('add_server_desc'));
            $this->addItem($add_server_button);
        }

        $this->language_item = new ilHiddenInputGUI('xmum_language');
        $this->addItem($this->language_item);

        $this->course_item = new ilHiddenInputGUI('xmum_course');
        $this->addItem($this->course_item);

        $this->course_display_item = new ilTextInputGUI($this->i18N->txt('mumie_course'), 'xmum_course_display');
        $this->course_display_item->setDisabled(true);
        $this->addItem($this->course_display_item);

        $this->launchcontainer_item = new ilRadioGroupInputGUI($this->i18N->txt('launchcontainer'), 'xmum_launchcontainer');
        $opt_window = new ilRadioOption($this->i18N->txt('window'), '0');
        $opt_embedded = new ilRadioOption($this->i18N->txt('embedded'), '1');
        $this->launchcontainer_item->setRequired(true);
        $this->launchcontainer_item->addOption($opt_window);
        $this->launchcontainer_item->addOption($opt_embedded);
        $this->launchcontainer_item->setInfo($this->i18N->txt('launchcontainer_desc'));
        $this->addItem($this->launchcontainer_item);

        $select_task_header_item = new ilFormSectionHeaderGUI();
        $select_task_header_item->setTitle($this->i18N->txt("mumie_select_problem"));
        $this->addItem($select_task_header_item);

        $this->problem_display_item = new ilTextInputGUI($this->i18N->txt('mumie_problem'), 'xmum_display_task');
        $this->problem_display_item->setInfo($this->i18N->txt('mumie_problem_desc'));
        $this->problem_display_item->setDisabled(true);
        $this->addItem($this->problem_display_item);

        $this->problem_item = new ilHiddenInputGUI('xmum_task');
        $this->addItem($this->problem_item);

        $problem_selector_button = new ilMumieTaskFormButtonGUI("", "xmum_prb_sel");
        $problem_selector_button->setButtonLabel($this->i18N->txt('open_prb_selector'));
        $problem_selector_button->setInfo($this->i18N->txt('open_prb_selector_desc'));
        $this->addItem($problem_selector_button);

        $servers = ilMumieTaskServer::getAllServers();

        $this->server_data_item = new ilHiddenInputGUI('server_data');
        $this->addItem($this->server_data_item);

        $this->course_file_item = new ilHiddenInputGUI('xmum_coursefile');
        $this->course_file_item->setRequired(true);
        $this->addItem($this->course_file_item);

        $this->org_item = new ilHiddenInputGUI('mumie_org');
        $this->addItem($this->org_item);

        $this->worksheet_item = new ilHiddenInputGUI('xmum_worksheet');
        $this->addItem($this->worksheet_item);

        $this->populateServerOptions($servers);

        $select_task_header_item = new ilFormSectionHeaderGUI();
        $select_task_header_item->setTitle($this->i18N->txt('frm_multi_problem_header'));
        $this->addItem($select_task_header_item);
        $multi_problem_selector_btn = new ilMumieTaskFormButtonGUI($this->i18N->txt('mumie_problems'), "xmum_multi_prb_sel");
        $multi_problem_selector_btn->setButtonLabel($this->i18N->txt('open_dnd_prb_selector'));
        $multi_problem_selector_btn->setInfo($this->i18N->txt('dnd_prb_selector_desc'));
        $this->addItem($multi_problem_selector_btn);

        require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/forms/class.ilMumieTaskDropZoneGUI.php');
        $this->dropzone_item = new ilMumieTaskDropZoneGUI("", "xmum_multi_problems");
        $this->addItem($this->dropzone_item);
    }

    public function checkInput(): bool
    {
        $ok = parent::checkInput();
        $is_dummy = $this->getInput('title') == ilObjMumieTask::DUMMY_TITLE;
        $server = new ilMumieTaskServer();
        $server->setUrlPrefix($this->getInput('xmum_server'));
        $server->buildStructure();
        $task = $this->getInput('xmum_task');

        if ($is_dummy && $task != null) {
            $ok = false;
            $this->title_item->setAlert($this->i18N->txt('title_not_valid'));
        }
        if (!$server->isValidMumieServer()) {
            $ok = false;
            $this->server_item->setAlert($this->i18N->txt('server_not_valid'));
            return $ok;
        }

        if ($task == null && $is_dummy) {
            $ok = false;
            $this->problem_display_item->setAlert($this->i18N->globalTxt('required_field'));
            return $ok;
        } elseif ($task == null) {
            $ok = false;
            $this->problem_display_item->setAlert($this->i18N->txt('frm_tsk_problem_not_found'));
            return $ok;
        }

        require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/tasks/class.ilMumieTaskMultiUploadProcessor.php');
        $multi_problems_input = $this->getInput("xmum_multi_problems");
        if (!empty($multi_problems_input) && !ilMumieTaskMultiUploadProcessor::isValid($multi_problems_input)) {
            $ok = false;
            $this->dropzone_item->setAlert($this->i18N->txt('frm_tsk_problems_not_found'));
        }

        return $ok;
    }

    /**
     * Populate the drop down menus from the server structure with all possible options.
     *
     * js/ilMumieTaskForm.js removes incorrect options for any given selection
     */
    private function populateServerOptions($servers)
    {
        foreach ($servers as $server) {
            $this->server_options[$server->getUrlprefix()] = $server->getName();
        }
        $this->server_item->setOptions($this->server_options);
    }

    /**
     * Save all Mumie Servers as a hidden input field. The JS file needs to know about them and their structure
     */
    public function setValuesByArray($a_values, $a_restrict_to_value_keys = false): void
    {
        parent::setValuesByArray($a_values);
        $servers = ilMumieTaskServer::getAllServers();
        $this->server_data_item->setValue(json_encode($servers));
        $this->course_display_item->setValue($a_values['xmum_course']);
        $this->setDefault();
    }

    public function setDefault()
    {
        global $ilUser;
        if ($this->launchcontainer_item->getValue() == null) {
            $this->launchcontainer_item->setValue("0");
        }

        include_once("Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskAdminSettings.php");
        $this->org_item->setValue(ilMumieTaskAdminSettings::getInstance()->getOrg());
        if ($this->language_item->getValue() == null) {
            $this->language_item->setValue($ilUser->getLanguage());
        }
    }

    /**
     * Disable all drop down menus and command buttons for this form
     */
    public function disable()
    {
        $this->server_item->setDisabled(true);
        $this->course_item->setDisabled(true);
        $this->clearCommandButtons();
    }
}
