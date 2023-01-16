<?php

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