<?php
class ilMumieTaskLPSettingsFormGUI extends ilPropertyFormGUI
{
    public function __construct()
    {
        parent::__construct();
    }

    private $modus_item;
    private $passing_threshold_item;
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

        $this->passing_threshold_item = new ilNumberInputGUI($lng->txt('rep_robj_xmum_frm_passing_grade'), 'passing_grade');
        $this->passing_threshold_item->setRequired(true);
        $this->passing_threshold_item->setMinValue(0);
        $this->passing_threshold_item->setMaxValue(100);
        $this->passing_threshold_item->setDecimals(0);
        $this->addItem($this->passing_threshold_item);
        $this->passing_threshold_item->setInfo($lng->txt('rep_robj_xmum_frm_passing_grade_desc'));
    }
}
