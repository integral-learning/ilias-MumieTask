<?php
class ilMumieTaskLPSettingsFormGUI extends ilPropertyFormGUI
{
    public function __construct()
    {
        parent::__construct();
    }

    private $modusItem;
    private $passingThresholdItem;
    public function setFields()
    {
        global $lng;
        $this->modusItem = new ilRadioGroupInputGUI($lng->txt('rep_robj_xmum_frm_sync_lp'), "lp_modus");
        $this->modusItem->setInfo($lng->txt('rep_robj_xmum_frm_sync_lp_desc'));
        $modusOptionTrue = new ilRadioOption($lng->txt('rep_robj_xmum_frm_enable'), 1);
        $modusOptionFalse = new ilRadioOption($lng->txt('rep_robj_xmum_frm_disable'), 0);
        $this->modusItem->addOption($modusOptionTrue);
        $this->modusItem->addOption($modusOptionFalse);
        $this->addItem($this->modusItem);

        $this->passingThresholdItem = new ilNumberInputGUI($lng->txt('rep_robj_xmum_frm_passing_grade'), 'passing_grade');
        $this->passingThresholdItem->setRequired(true);
        $this->passingThresholdItem->setMinValue(0);
        $this->passingThresholdItem->setMaxValue(100);
        $this->passingThresholdItem->setDecimals(0);
        $this->addItem($this->passingThresholdItem);
        $this->passingThresholdItem->setInfo($lng->txt('rep_robj_xmum_frm_passing_grade_desc'));
    }
}
