<?php
class ilMumieTaskIdHashingService
{
    private $userid;
    private $hash;

    const TABLE_NAME = 'xmum_id_hashes';

    private function __construct($userid)
    {
        $this->userid = $userid;
        $this->hash = $hash;
    }

    public static function getHashForUser($userid)
    {
        $service = new ilMumieTaskIdHashingService($userid);
        $service->upsertHash();
        return $service->getHash();
    }

    public static function getUserFromHash($hash)
    {
        global $ilDB;
        $result = $ilDB->fetchObject(
            $ilDB->query(
                'SELECT * FROM '
                . SELF::TABLE_NAME
                . " WHERE hash = "
                . $ilDB->quote($hash, "text")
            )
        );

        return $result->usr_id;
    }

    private function upsertHash()
    {
        global $ilDB;
        $this->hash = $this->generateHash();
        $result = $ilDB->fetchObject(
            $ilDB->query(
                'SELECT * FROM '
                . SELF::TABLE_NAME
                . " WHERE usr_id = "
                . $ilDB->quote($this->userid, "integer")
            )
        );
        if (!is_null($result) && !is_null($result->hash)) {
            $this->update();
        } else {
            $this->create();
        }
    }

    private function create()
    {
        global $ilDB;

        $ilDB->insert(
            self::TABLE_NAME,
            array(
                'usr_id' => array('integer', $this->userid),
                'hash' => array('text', $this->hash),
            )
        );
    }

    private function update()
    {
        global $ilDB;

        $ilDB->update(
            self::TABLE_NAME,
            array(
                'hash' => array('text', $this->hash),
            ),
            array(
                "usr_id" => array('integer', $this->userid),
            )
        );
    }

    private function generateHash()
    {
        require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskAdminSettings.php');
        $adminSettings = ilMumieTaskAdminSettings::getInstance();
        return hash("sha512", $this->userid . substr(ilMumieTaskAdminSettings::getInstance()->getApiKey(), 0, 10));
    }

    /**
     * Get the value of hash
     */
    public function getHash()
    {
        return $this->hash;
    }
}
