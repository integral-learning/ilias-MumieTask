<?php
class ilMumieTaskLPSettingsFormGUI extends ilPropertyFormGUI {

    function __construct() {
        parent::__construct();
    }

    private $modusItem, $passingThresholdItem;
    function setFields() {
        $this->modusItem = new ilRadioGroupInputGUI('Save learning progress', "lp_modus");
        $modusOptionTrue = new ilRadioOption("Enable", true);
        $modusOptionFalse = new ilRadioOption("Disable", false);
        $this->modusItem->addOption($modusOptionTrue);
        $this->modusItem->addOption($modusOptionFalse);
        $this->addItem($this->modusItem);

        $this->passingThresholdItem = new ilNumberInputGUI('Passing threshold', 'passing_grade');
        $this->passingThresholdItem->setRequired(true);
        $this->passingThresholdItem->setMinValue(0);
        $this->passingThresholdItem->setMaxValue(100);
        $this->passingThresholdItem->setDecimals(0);
        $this->addItem($this->passingThresholdItem);

    }
}
?>
