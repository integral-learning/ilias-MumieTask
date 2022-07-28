<?php
/**
 * MumieTask plugin
 *
 * @copyright   2019 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 /**
  * Some organizations might use personal data like matriculation numbers as user id. That's why we need a way to replace them with another unique identifier.
  */
class ilMumieTaskIdHashingService
{
    private $id;
    private $user_id;
    private $hash;

    const TABLE_NAME = 'xmum_id_hashes';

    private function __construct($user_id)
    {
        $this->user_id = $user_id;
        $this->hash = $hash;
    }

    public static function getHashForUser($user_id,$taskObj)
    {
        $service = new ilMumieTaskIdHashingService($user_id);
        $service->upsertHash($taskObj);      
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

    private function upsertHash($taskObj)
    {
        global $ilDB;
        $this->hash = $this->generateHash();
        global $DIC;
        $tree = $DIC['tree'];
        $parent_ref = $tree->getParentId($taskObj->getRefId());
        if ($taskObj->getPrivateGradepool()) {
            $this->hash .= '@gradepool' . $parent_ref . '@';
        }   
        $result = $ilDB->fetchObject(
            $ilDB->query(
                'SELECT * FROM '
                . SELF::TABLE_NAME
                . " WHERE usr_id = "
                . $ilDB->quote($this->user_id, "integer") . " AND hash = " . $ilDB->quote($this->hash, 'text')
            )
        );
        if (!is_null($result)) {
            $this->id = $result->id;
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
                'id' => array('integer', $ilDB->nextID(self::TABLE_NAME)),
                'usr_id' => array('integer', $this->user_id),
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
                "usr_id" => array('integer', $this->user_id),
            ),
            array(
                'id' => array('integer', $this->id)
            )
        );
    }

    private function generateHash()
    {
        require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskAdminSettings.php');
        $adminSettings = ilMumieTaskAdminSettings::getInstance();
        return hash("sha512", $this->user_id . substr(ilMumieTaskAdminSettings::getInstance()->getApiKey(), 0, 10));
    }

    /**
     * Get the value of hash
     */
    public function getHash()
    {
        return $this->hash;
    }
}
