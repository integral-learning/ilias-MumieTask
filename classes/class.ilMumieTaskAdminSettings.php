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

    protected string $problem_selector_url;
    protected int $share_first_name;
    protected int $share_last_name;
    protected int $share_email;
    protected string $api_key;
    protected string $org;
    protected int $id;

    /**
     * Always use this method to get access to the current settings
     */
    public static function getInstance(): ilMumieTaskAdminSettings
    {
        $instance = new ilMumieTaskAdminSettings();
        $instance->load();
        return $instance;
    }

    /**
     * Load all values from the database
     * @SuppressWarnings("PHPMD.UnusedPrivateMethod")
     */
    private function load(): void
    {
        global $DIC;
        $db = $DIC->database();
        $result = $db->fetchObject($db->query("SELECT * FROM " . ilMumieTaskAdminSettings::TABLE_NAME . " WHERE id = 1"));
        $this->id = $result->id;
        $this->problem_selector_url = $result->problem_selector_url;
        $this->share_first_name = $result->share_first_name;
        $this->share_last_name = $result->share_last_name;
        $this->share_email = $result->share_email;
        $this->api_key = $result->api_key;
        $this->org = $result->org;
    }

    public function update(): void
    {
        global $DIC;
        $DIC->database()->update(
            ilMumieTaskAdminSettings::TABLE_NAME,
            array(
            "problem_selector_url" => array("text", $this->problem_selector_url),
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

    public function getShareFirstName(): int
    {
        return $this->share_first_name;
    }

    public function setShareFirstName(int $share_first_name): static
    {
        $this->share_first_name = $share_first_name;

        return $this;
    }

    public function getShareLastName(): int
    {
        return $this->share_last_name;
    }

    public function setShareLastName(int $share_last_name): static
    {
        $this->share_last_name = $share_last_name;

        return $this;
    }

    public function getShareEmail(): int
    {
        return $this->share_email;
    }

    public function setShareEmail(int $share_email): static
    {
        $this->share_email = $share_email;

        return $this;
    }

    public function getApiKey(): string
    {
        return $this->api_key;
    }

    public function setApiKey($api_key): static
    {
        $this->api_key = $api_key;

        return $this;
    }

    public function getOrg(): string
    {
        return $this->org;
    }

    public function setOrg($org): static
    {
        $this->org = $org;

        return $this;
    }

    public function getProblemSelectorUrl(): string
    {
        return $this->problem_selector_url;
    }

    public function setProblemSelectorUrl($problem_selector_url): static
    {
        $this->problem_selector_url = $problem_selector_url;

        return $this;
    }
}
