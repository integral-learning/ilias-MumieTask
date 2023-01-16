<?php
/**
 * MumieTask plugin
 *
 * @copyright   2022 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * This class changes default behavior of ilDateTime. It's only meant to be used within MUMIE Task plugins
 */
class ilMumieTaskDateTime extends ilDateTime {
    public function __construct($a_date = null, $a_format = IL_CAL_UNIX, $a_tz = '')
    {
        parent::__construct($a_date, $a_format, $a_tz);
    }

    public function get($a_format = IL_CAL_FKT_DATE, $a_format_str = 'd.m.Y - H:i', $a_tz = '')
    {
        return parent::get($a_format, $a_format_str, $a_tz);
    }

    public function __toString()
    {
        return $this::get();
    }

}