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
    private $task;

    public const TABLE_NAME = 'xmum_id_hashes';

    private function __construct($user_id, $task)
    {
        $this->user_id = $user_id;
        $this->task = $task;
    }

    public static function getHashForUser($user_id, $task = null)
    {
        $service = new ilMumieTaskIdHashingService($user_id, $task);
        $service->upsertHash();
        return $service->getHash();
    }

    public static function getUserFromHash($hash)
    {
        global $DIC;
        $db = $DIC->database();
        $result = $db->fetchObject(
            $db->query(
                'SELECT * FROM '
                . self::TABLE_NAME
                . " WHERE hash = "
                . $db->quote($hash, "text")
            )
        );

        return $result->usr_id;
    }

    /**
     * @SuppressWarnings("PHPMD.UnusedPrivateMethod")
     */
    private function upsertHash()
    {
        global $DIC;
        $db = $DIC->database();
        $this->hash = $this->generateHash();
        if ($this->task != null && $this->task->getPrivateGradepool() == 1) {
            $this->hash .= '@gradepool' . $this->task->getParentRef() . '@';
        }
        $result = $db->fetchObject(
            $db->query(
                'SELECT * FROM '
                . self::TABLE_NAME
                . " WHERE usr_id = "
                . $db->quote($this->user_id, "integer")
                . " AND hash = "
                . $db->quote($this->hash, 'text')
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
        global $DIC;
        $db = $DIC->database();
        $db->insert(
            self::TABLE_NAME,
            array(
                'id' => array('integer', $db->nextID(self::TABLE_NAME)),
                'usr_id' => array('integer', $this->user_id),
                'hash' => array('text', $this->hash),
            )
        );
    }

    private function update()
    {
        global $DIC;
        $DIC->database()->update(
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
