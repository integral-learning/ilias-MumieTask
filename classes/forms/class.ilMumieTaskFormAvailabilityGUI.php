<?php
/**
 * MumieTask plugin
 *
 * @copyright   2019 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once ('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/i18n/class.ilMumieTaskI18N.php');

/**
 * This class is used to display and validate the custom availability form for MumieTask
 */
class ilMumieTaskFormAvailabilityGUI extends ilPropertyFormGUI
{
    private $online_item;
    private $act_type_item;
    private $duration_item;
    private ilMumieTaskI18N $i18N;

    public function __construct()
    {
        parent::__construct();
        $this->i18N = new ilMumieTaskI18N();
    }

    public function setFields($disable_online_selection)
    {
        global $lng;
        $online_item = new ilCheckboxInputGUI($this->i18N->globalTxt('rep_activation_online'), 'online');

        $online_item->setInfo($this->getOnlineItemInfo($disable_online_selection));
        $online_item->setDisabled($disable_online_selection);
        $this->addItem($online_item);
        $this->online_item = $online_item;

        $act_type_item = new ilCheckboxInputGUI($this->i18N->globalTxt('rep_visibility_until'), 'activation_type');

        include_once "Services/Form/classes/class.ilDateDurationInputGUI.php";
        $duration_item = new ilDateDurationInputGUI($this->i18N->globalTxt("rep_time_period"), "access_period");
        $duration_item->setRequired(true);
        $duration_item->setShowTime(true);
        $duration_item->setStart(new ilDateTime(time(), IL_CAL_UNIX));
        $duration_item->setStartText($this->i18N->globalTxt('rep_activation_limited_start'));
        $duration_item->setEnd(new ilDateTime(time(), IL_CAL_UNIX));
        $duration_item->setEndText($this->i18N->globalTxt('rep_activation_limited_end'));
        $act_type_item->addSubItem($duration_item);
        $this->duration_item = $duration_item;

        $this->addItem($act_type_item);
        $this->act_type_item = $act_type_item;
    }

    public function checkInput(): bool
    {
        $ok = parent::checkInput();

        return $ok;
    }

    public function setValuesByArray($values, $a_restrict_to_value_keys = false): void
    {
        $period = $values['period'];
        $this->duration_item->setStart(new ilDateTime($period->startingTime ?? time(), IL_CAL_UNIX));
        $this->duration_item->setEnd(new ilDateTime($period->endingTime ?? time(), IL_CAL_UNIX));
        parent::setValuesByArray($values, $a_restrict_to_value_keys);
    }

    private function getOnlineItemInfo($disable_online_selection)
    {
        $online_info = $this->i18N->txt('frm_online_info');
        if ($disable_online_selection) {
            $online_info .= '<br><br>' . $this->i18N->txt('frm_online_disabled_warning');
        }

        return $online_info;
    }
}
