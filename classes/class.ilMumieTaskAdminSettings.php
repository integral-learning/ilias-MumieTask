<?php
class ilMumieTaskAdminSettings extends ActiveRecord {
    const TABLE_NAME = 'xmum_admin_settings';

    /**
     * @return string
     */
    static function returnDbTableName() {
        return self::TABLE_NAME;
    }

    /**
     * @var string
     *
     * @con_has_field true
     * @con_fieldtype text
     * @con_length    7
     */
    protected $share_first_name;
    /**
     * @var string
     *
     * @con_has_field true
     * @con_fieldtype text
     * @con_length    7
     */
    protected $share_last_name;
    /**
     * @var string
     *
     * @con_has_field true
     * @con_fieldtype text
     * @con_length    7
     */
    protected $share_email;
    /**
     * @var string
     *
     * @con_has_field true
     * @con_fieldtype text
     * @con_length    255
     */
    protected $api_key;

    /**
     * @var string
     *
     * @con_has_field true
     * @con_fieldtype text
     * @con_length    7
     */
    protected $org;
    /**
     * @var int
     *
     * @con_is_primary true
     * @con_sequence true
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     */
    protected $id;

    /**
     *
     */
    public static function getInstance() {
        $instance = new ilMumieTaskAdminSettings();
        return $instance->first();
    }

    /**
     * Get the value of id
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set the value of id
     *
     * @return  self
     */
    public function setId($id) {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of share_first_name
     *
     * @return  string
     */
    public function getShare_first_name() {
        return $this->share_first_name;
    }

    /**
     * Set the value of share_first_name
     *
     * @param  string  $share_first_name
     *
     * @return  self
     */
    public function setShare_first_name(string $share_first_name) {
        $this->share_first_name = $share_first_name;

        return $this;
    }

    /**
     * Get the value of share_last_name
     *
     * @return  string
     */
    public function getShare_last_name() {
        return $this->share_last_name;
    }

    /**
     * Set the value of share_last_name
     *
     * @param  string  $share_last_name
     *
     * @return  self
     */
    public function setShare_last_name(string $share_last_name) {
        $this->share_last_name = $share_last_name;

        return $this;
    }

    /**
     * Get the value of share_email
     *
     * @return  string
     */
    public function getShare_email() {
        return $this->share_email;
    }

    /**
     * Set the value of share_email
     *
     * @param  string  $share_email
     *
     * @return  self
     */
    public function setShare_email(string $share_email) {
        $this->share_email = $share_email;

        return $this;
    }

    /**
     * Get the value of api_key
     *
     * @return  string
     */
    public function getApi_key() {
        return $this->api_key;
    }

    /**
     * Set the value of api_key
     *
     * @param  string  $api_key
     *
     * @return  self
     */
    public function setApi_key(string $api_key) {
        $this->api_key = $api_key;

        return $this;
    }

    /**
     * Get the value of org
     *
     * @return  string
     */
    public function getOrg() {
        return $this->org;
    }

    /**
     * Set the value of org
     *
     * @param  string  $org
     *
     * @return  self
     */
    public function setOrg(string $org) {
        $this->org = $org;

        return $this;
    }
}

?>