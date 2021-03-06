<?php
/**
 * MumieTask plugin
 *
 * @copyright   2019 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 /**
  * This class is used to display and validate the custom availability form for MumieTask
  */
class ilMumieTaskFormAvailabilityGUI extends ilPropertyFormGUI
{
    private $online_item;
    private $act_type_item;
    private $duration_item;
    public function setFields()
    {
        global $lng;
        $online_item = new ilCheckboxInputGUI($this->lng->txt('rep_activation_online'), 'online');
        $online_item->setInfo($lng->txt('rep_robj_xmum_frm_online_info'));
        $this->addItem($online_item);
        $this->online_item = $online_item;

        $act_type_item = new ilCheckboxInputGUI($this->lng->txt('rep_visibility_until'), 'activation_type');

        include_once "Services/Form/classes/class.ilDateDurationInputGUI.php";
        $duration_item = new ilDateDurationInputGUI($this->lng->txt("rep_time_period"), "access_period");
        $duration_item->setRequired(true);
        $duration_item->setShowTime(true);
        $duration_item->setStart(new ilDateTime(time(), IL_CAL_UNIX));
        $duration_item->setStartText($this->lng->txt('rep_activation_limited_start'));
        $duration_item->setEnd(new ilDateTime(time(), IL_CAL_UNIX));
        $duration_item->setEndText($this->lng->txt('rep_activation_limited_end'));
        $act_type_item->addSubItem($duration_item);
        $this->duration_item = $duration_item;

        $visiblity_item = new ilCheckboxInputGUI($this->lng->txt('rep_activation_limited_visibility'), 'activation_visibility');
        $visiblity_item->setInfo($this->lng->txt('rep_robj_xmum_activation_limited_visibility_info'));
        $act_type_item->addSubItem($visiblity_item);
        $this->addItem($act_type_item);
        $this->act_type_item = $act_type_item;
    }

    public function checkInput()
    {
        $ok = parent::checkInput();

        return $ok;
    }

    public function setValuesByArray($values, $a_restrict_to_value_keys = false)
    {
        $period = $values['period'];
        $this->duration_item->setStart(new ilDateTime($period->startingTime ?? time(), IL_CAL_UNIX));
        $this->duration_item->setEnd(new ilDateTime($period->endingTime ?? time(), IL_CAL_UNIX));
        parent::setValuesByArray($values, $a_restrict_to_value_keys);
    }
}
