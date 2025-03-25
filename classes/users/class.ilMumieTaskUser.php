<?php

/**
 * MumieTask plugin
 *
 * @copyright   2022 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class ilMumieTaskUser
{
    private int $moodleid;
    private string $firstname;
    private string $lastname;
    private string $mumieid;
    private string $email;
    private string $org;

    /**
     * @param int $moodleid
     * @param string mumieid
     */
    public function __construct(int $moodleid, string $mumieid)
    {
        $this->moodleid = $moodleid;
        $this->mumieid = $mumieid;
        include_once("Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskAdminSettings.php");
        $this->org = ilMumieTaskAdminSettings::getInstance()->getOrg();
    }

    public function load(): bool
    {
        global $ilDB;
        $query = "Select * from usr_data where usr_id = " . $ilDB->quote( $this->moodleid,
        "integer");
        $result = $ilDB->query($query);
        $user = $ilDB->fetchAssoc($result);
        if (!$user) {
            return false;
        }
        $this->firstname = $user['firstname'];
        $this->lastname = $user['lastname'];
        $this->email = $user['email'];
        return true;
    }

    /**
     * Get the sync id.
     *
     * The sync id is the full username on a MUMIE / Lemon server
     * @return string
     */
    public function get_sync_id(): string
    {
        return "gsso_" . $this->org . "_" . $this->mumieid;
    }

    /**
     * Set moodle id
     * @return int
     */
    public function get_moodle_id(): int
    {
        return $this->moodleid;
    }

    /**
     * Get moodle id
     * @param int $moodleid
     */
    public function set_moodle_id(int $moodleid): void
    {
        $this->moodleid = $moodleid;
    }

    /**
     * Get mumie id
     * @return string
     */
    public function get_mumie_id(): string
    {
        return $this->mumieid;
    }

    /**
     * Set mumie id
     * @param string $mumieid
     */
    public function set_mumie_id(string $mumieid): void
    {
        $this->mumieid = $mumieid;
    }

    /**
     * Get first name
     * @return string
     */
    public function get_firstname(): string
    {
        return $this->firstname;
    }

    /**
     * get lastname
     * @return string
     */
    public function get_lastname(): string
    {
        return $this->lastname;
    }

    /**
     * Get email
     * @return string
     */
    public function get_email(): string
    {
        return $this->email;
    }

    /**
     * Get fill name
     * @return string
     */
    public function get_fullname(): string
    {
        return $this->firstname . ' ' . $this->lastname;
    }
}
