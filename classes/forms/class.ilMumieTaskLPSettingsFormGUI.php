<?php
/**
 * MumieTask plugin
 *
 * @copyright   2019 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * This form is used to edit the Learning Progress settings of MumieTasks
 */
class ilMumieTaskLPSettingsFormGUI extends ilPropertyFormGUI
{
    public function __construct($disable_grade_pool_selection)
    {
        parent::__construct();
        $this->disable_grade_pool_selection = $disable_grade_pool_selection;
    }

    private $modus_item;
    private $gradepool_item;
    private $passing_threshold_item;
    private $disable_grade_pool_selection;

    public function setFields()
    {
        global $lng;
        $this->modus_item = new ilRadioGroupInputGUI($lng->txt('rep_robj_xmum_frm_sync_lp'), "lp_modus");
        $this->modus_item->setInfo($lng->txt('rep_robj_xmum_frm_sync_lp_desc'));
        $modus_option_true = new ilRadioOption($lng->txt('rep_robj_xmum_frm_enable'), 1);
        $modus_option_false = new ilRadioOption($lng->txt('rep_robj_xmum_frm_disable'), 0);
        $this->modus_item->addOption($modus_option_true);
        $this->modus_item->addOption($modus_option_false);
        $this->addItem($this->modus_item);

        $this->gradepool_item = new ilRadioGroupInputGUI($lng->txt('rep_robj_xmum_frm_privategradepool'), "privategradepool");

        $this->gradepool_item->setInfo($this->getGradepoolInfo());
        $gradepool_option_true = new ilRadioOption($lng->txt('rep_robj_xmum_frm_enable'), 0);
        $gradepool_option_false = new ilRadioOption($lng->txt('rep_robj_xmum_frm_disable'), 1);
        $gradepool_option_pending = new ilRadioOption($lng->txt('rep_robj_xmum_frm_gradepool_pending'), -1);
        $gradepool_option_true->setDisabled($this->disable_grade_pool_selection);
        $gradepool_option_false->setDisabled($this->disable_grade_pool_selection);
        $this->gradepool_item->addOption($gradepool_option_true);
        $this->gradepool_item->addOption($gradepool_option_false);
        if (!$this->disable_grade_pool_selection) {
            $this->gradepool_item->addOption($gradepool_option_pending);
        }
        $this->addItem($this->gradepool_item);

        $this->passing_threshold_item = new ilNumberInputGUI($lng->txt('rep_robj_xmum_frm_passing_grade'), 'passing_grade');
        $this->passing_threshold_item->setRequired(true);
        $this->passing_threshold_item->setMinValue(0);
        $this->passing_threshold_item->setMaxValue(100);
        $this->passing_threshold_item->setDecimals(0);
        $this->addItem($this->passing_threshold_item);
        $this->passing_threshold_item->setInfo($lng->txt('rep_robj_xmum_frm_passing_grade_desc'));
    }

    private function getGradepoolInfo()
    {
        global $lng;
        $gradepool_info = $lng->txt('rep_robj_xmum_frm_privategradepool_desc') . '<br><br>';
        if (!$this->disable_grade_pool_selection) {
            $gradepool_info .= $lng->txt('rep_robj_xmum_frm_privategradepool_undecided');
        } else {
            $gradepool_info .= $lng->txt('rep_robj_xmum_frm_privategradepool_decided');
        }

        return $gradepool_info;
    }


    public function checkInput()
    {
        $ok = parent::checkInput();
        return $ok;
    }
}
