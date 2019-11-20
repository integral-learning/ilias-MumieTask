<?php

class ilMumieTaskFormAvailabilityGUI extends ilPropertyFormGUI {

    private $onlineItem, $actTypeItem, $durationItem;
    public function setFields() {
        global $lng;
        $onlineItem = new ilCheckboxInputGUI($this->lng->txt('rep_activation_online'), 'online');
        $onlineItem->setInfo($lng->txt('rep_robj_xmum_frm_online_info'));
        $this->addItem($onlineItem);
        $this->onlineItem = $onlineItem;

        $actTypeItem = new ilCheckboxInputGUI($this->lng->txt('rep_visibility_until'), 'activation_type');

        include_once "Services/Form/classes/class.ilDateDurationInputGUI.php";
        $durationItem = new ilDateDurationInputGUI($this->lng->txt("rep_time_period"), "access_period");
        $durationItem->setRequired(true);
        $durationItem->setShowTime(true);
        $durationItem->setStart(new ilDateTime(time(), IL_CAL_UNIX));
        $durationItem->setStartText($this->lng->txt('rep_activation_limited_start'));
        $durationItem->setEnd(new ilDateTime(time(), IL_CAL_UNIX));
        $durationItem->setEndText($this->lng->txt('rep_activation_limited_end'));
        $actTypeItem->addSubItem($durationItem);
        $this->durationItem = $durationItem;

        $visiblityItem = new ilCheckboxInputGUI($this->lng->txt('rep_activation_limited_visibility'), 'activation_visibility');
        $visiblityItem->setInfo($this->lng->txt('rep_robj_xmum_activation_limited_visibility_info'));
        $actTypeItem->addSubItem($visiblityItem);
        $this->addItem($actTypeItem);
        $this->actTypeItem = $actTypeItem;

    }

    public function checkInput() {
        $ok = parent::checkInput();

        return $ok;
    }

    public function setValuesByArray($values, $a_restrict_to_value_keys = false) {
        $period = $values['period'];
        $this->durationItem->setStart(new ilDateTime($period->startingTime ?? time(), IL_CAL_UNIX));
        $this->durationItem->setEnd(new ilDateTime($period->endingTime ?? time(), IL_CAL_UNIX));
        parent::setValuesByArray($values, $a_restrict_to_value_keys);
    }

}