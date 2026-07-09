<?php

/**
 * MumieTask plugin.
 *
 * @copyright   2019 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * This form is used to edit the Learning Progress settings of MumieTasks.
 */
class ilMumieTaskLPSettingsFormGUI extends ilPropertyFormGUI
{
    public const DEADLINE_MODE_NONE = 'none';
    public const DEADLINE_MODE_FIXED = 'fixed';
    public const DEADLINE_MODE_TIMELIMIT = 'timelimit';

    private ilMumieTaskI18N $i18N;

    public function __construct($disable_grade_pool_selection, $is_worksheet = false)
    {
        parent::__construct();
        $this->disable_grade_pool_selection = $disable_grade_pool_selection;
        $this->is_worksheet = $is_worksheet;
        $this->i18N = new ilMumieTaskI18N();
    }

    private $modus_item;
    private $gradepool_item;
    private $passing_threshold_item;
    private $deadline_mode_item;
    private $deadline_item;
    private $timelimit_item;
    private $disable_grade_pool_selection;
    private $is_worksheet;

    public function setFields()
    {
        $this->modus_item = new ilRadioGroupInputGUI($this->i18N->txt('frm_sync_lp'), 'lp_modus');
        $this->modus_item->setInfo($this->i18N->txt('frm_sync_lp_desc'));
        $modus_option_true = new ilRadioOption($this->i18N->txt('frm_enable'), 1);
        $modus_option_false = new ilRadioOption($this->i18N->txt('frm_disable'), 0);
        $this->modus_item->addOption($modus_option_true);
        $this->modus_item->addOption($modus_option_false);
        $this->addItem($this->modus_item);

        $this->gradepool_item = new ilRadioGroupInputGUI($this->i18N->txt('frm_privategradepool'), 'privategradepool');

        $this->gradepool_item->setInfo($this->getGradepoolInfo());
        $gradepool_option_true = new ilRadioOption($this->i18N->txt('frm_enable'), 0);
        $gradepool_option_false = new ilRadioOption($this->i18N->txt('frm_disable'), 1);
        $gradepool_option_pending = new ilRadioOption($this->i18N->txt('frm_gradepool_pending'), -1);
        $gradepool_option_true->setDisabled($this->disable_grade_pool_selection);
        $gradepool_option_false->setDisabled($this->disable_grade_pool_selection);
        $this->gradepool_item->addOption($gradepool_option_true);
        $this->gradepool_item->addOption($gradepool_option_false);
        if (!$this->disable_grade_pool_selection) {
            $this->gradepool_item->addOption($gradepool_option_pending);
        }
        $this->addItem($this->gradepool_item);

        $this->passing_threshold_item = new ilNumberInputGUI($this->i18N->txt('frm_passing_grade'), 'passing_grade');
        $this->passing_threshold_item->setRequired(true);
        $this->passing_threshold_item->setMinValue(0);
        $this->passing_threshold_item->setMaxValue(100);
        $this->passing_threshold_item->setDecimals(0);
        $this->addItem($this->passing_threshold_item);
        $this->passing_threshold_item->setInfo($this->i18N->txt('frm_passing_grade_desc'));

        $this->deadline_mode_item = new ilRadioGroupInputGUI($this->i18N->txt('frm_deadline_mode'), 'deadline_mode');
        $this->deadline_mode_item->setInfo($this->i18N->txt('frm_deadline_mode_desc'));

        $deadline_mode_option_none = new ilRadioOption($this->i18N->txt('frm_deadline_mode_none'), self::DEADLINE_MODE_NONE);
        $this->deadline_mode_item->addOption($deadline_mode_option_none);

        $this->deadline_item = new ilDateTimeInputGUI($this->i18N->txt('frm_grade_overview_list_deadline'), 'deadline');
        $this->deadline_item->setInfo($this->i18N->txt('frm_lp_deadline_desc'));
        $this->deadline_item->setShowTime(true);
        $deadline_mode_option_fixed = new ilRadioOption($this->i18N->txt('frm_deadline_mode_fixed'), self::DEADLINE_MODE_FIXED);
        $deadline_mode_option_fixed->addSubItem($this->deadline_item);
        $this->deadline_mode_item->addOption($deadline_mode_option_fixed);

        if ($this->is_worksheet) {
            $this->timelimit_item = new ilDurationInputGUI($this->i18N->txt('frm_timelimit'), 'timelimit');
            $this->timelimit_item->setInfo($this->i18N->txt('frm_timelimit_desc'));
            $this->timelimit_item->setShowHours(true);
            $this->timelimit_item->setShowMinutes(true);
            $deadline_mode_option_timelimit = new ilRadioOption($this->i18N->txt('frm_deadline_mode_timelimit'), self::DEADLINE_MODE_TIMELIMIT);
            $deadline_mode_option_timelimit->addSubItem($this->timelimit_item);
            $this->deadline_mode_item->addOption($deadline_mode_option_timelimit);
        }

        $this->addItem($this->deadline_mode_item);
    }

    private function getGradepoolInfo()
    {
        $gradepool_info = $this->i18N->txt('frm_privategradepool_desc') . '<br><br>';
        if (!$this->disable_grade_pool_selection) {
            $gradepool_info .= $this->i18N->txt('frm_privategradepool_undecided');
        } else {
            $gradepool_info .= $this->i18N->txt('frm_privategradepool_decided');
        }

        return $gradepool_info;
    }

    public function checkInput(): bool
    {
        $ok = parent::checkInput();

        return $ok;
    }
}
