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
        $result = $ilDB->fetchall($ilDB->query("SELECT * FROM " . ilMumieTaskAdminSettings::TABLE_NAME));
        $this->id = $result[0]["value"];
        $this->share_first_name = $result[1]["value"];
        $this->share_last_name = $result[2]["value"];
        $this->share_email = $result[3]["value"];
        $this->api_key = $result[4]["value"];
        $this->org = $result[5]["value"];
    }

    public function update()
    {
        global $ilDB;
        if (empty($this->share_first_name)) {
            $this->share_first_name = 0;
        }
        $ilDB->update(
            ilMumieTaskAdminSettings::TABLE_NAME,
            array(
                "value" => array("text", $this->share_first_name)
            ),
            array(
                "name" => array("text", "share_first_name")
            )
        );
        ilLoggerFactory::getLogger('xmum')->info(empty($this->share_last_name));
        if (empty($this->share_last_name)) {
            $this->share_last_name = 0;
        }
        $ilDB->update(
            ilMumieTaskAdminSettings::TABLE_NAME,
            array(
                "value" => array("text", $this->share_last_name)
            ),
            array(
                "name" => array("text", "share_last_name")
            )
        );

        if (empty($this->share_email)) {
            $this->share_email = 0;
        }
        $ilDB->update(
            ilMumieTaskAdminSettings::TABLE_NAME,
            array(
                "value" => array("text", $this->share_email)
            ),
            array(
                "name" => array("text", "share_email")
            )
        );

        $ilDB->update(
            ilMumieTaskAdminSettings::TABLE_NAME,
            array(
                "value" => array("text", $this->api_key)
            ),
            array(
                "name" => array("text", "api_key")
            )
        );

        $ilDB->update(
            ilMumieTaskAdminSettings::TABLE_NAME,
            array(
                "value" => array("text", $this->org)
            ),
            array(
                "name" => array("text", "org")
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
