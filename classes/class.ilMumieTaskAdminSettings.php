<?php
/**
 * MumieTask plugin
 *
 * @copyright   2019 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * This class is used to store and retrieve admin settings for the MumieTask plugin.
 *
 * We are not using active records since they didn't deliver reliable results during development
 */
class ilMumieTaskAdminSettings
{
    public const TABLE_NAME = 'xmum_admin_settings';

    protected $share_first_name;
    protected $share_last_name;
    protected $share_email;
    protected $api_key;
    protected $org;
    protected $id;

    /**
     * Always use this method to get access to the current settings
     */
    public static function getInstance()
    {
        $instance = new ilMumieTaskAdminSettings();
        $instance->load();
        return $instance;
    }

    /**
     * Load all values from the database
     */
    private function load()
    {
        global $ilDB;
        $result = $ilDB->fetchObject($ilDB->query("SELECT * FROM " . ilMumieTaskAdminSettings::TABLE_NAME . " WHERE id = 1"));
        $this->id = $result->id;
        $this->share_first_name = $result->share_first_name;
        $this->share_last_name = $result->share_last_name;
        $this->share_email = $result->share_email;
        $this->api_key = $result->api_key;
        $this->org = $result->org;
    }

    public function update()
    {
        global $ilDB;
        $ilDB->update(
            ilMumieTaskAdminSettings::TABLE_NAME,
            array(
            "share_first_name" => array("integer", $this->share_first_name),
            "share_last_name" => array("integer", $this->share_last_name),
            "share_email" => array("integer", $this->share_email),
            "api_key" => array("text", $this->api_key),
            "org" => array("text", $this->org),
        ),
            array(
            "id" => array("int", $this->id),
        )
        );
    }

    public function getShareFirstName()
    {
        return $this->share_first_name;
    }

    public function setShareFirstName($share_first_name)
    {
        $this->share_first_name = $share_first_name;

        return $this;
    }

    public function getShareLastName()
    {
        return $this->share_last_name;
    }

    public function setShareLastName($share_last_name)
    {
        $this->share_last_name = $share_last_name;

        return $this;
    }

    public function getShareEmail()
    {
        return $this->share_email;
    }

    public function setShareEmail($share_email)
    {
        $this->share_email = $share_email;

        return $this;
    }

    public function getApiKey()
    {
        return $this->api_key;
    }

    public function setApiKey($api_key)
    {
        $this->api_key = $api_key;

        return $this;
    }

    public function getOrg()
    {
        return $this->org;
    }

    public function setOrg($org)
    {
        $this->org = $org;

        return $this;
    }
}
