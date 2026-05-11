<?php

/**
 * MumieTask plugin.
 *
 * @copyright   2022 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ilMumieTaskUser
{
    private $user_id;
    private $first_name;
    private $last_name;

    public function __construct($user_id, $first_name, $last_name)
    {
        $this->user_id = $user_id;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
    }

    public function getUserId()
    {
        return $this->user_id;
    }

    public function getFirstName()
    {
        return $this->first_name;
    }

    public function getLastName()
    {
        return $this->last_name;
    }

    public function getFullname()
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}
