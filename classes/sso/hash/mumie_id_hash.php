<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This class represents a DB entry in the userId -> hash lookup table xmum_id_hashes.
 *
 * @package auth_mumie
 * @copyright  2017-2020 integral-learning GmbH (https://www.integral-learning.de/)
 * @author Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class mumie_id_hash
{
    /**
     * Name of the database table
     */
    public const HASH_ID_TABLE = "xmum_id_hashes";
    /**
     * @var int
     */
    private int $id;
    /**
     * @var int
     */
    private int $user_id;
    /**
     * @var string
     */
    private string $hash;

    /**
     * Create a new instance
     * @param int    $user_id
     * @param string $hash
     */
    public function __construct(int $user_id, string $hash)
    {
        $this->user_id = $user_id;
        $this->hash = $hash;
    }

    /**
     * Create a database entry, if none exists.
     * @return void
     */
    public function save(): void
    {
        if (!self::find($this->user_id, $this->hash)) {
            $this->create();
        }
    }

    /**
     * Create a new db entry
     * @return void
     * @throws \dml_exception
     */
    public function create(): void
    {
        global $ilDB;
        $ilDB->insert(
            self::HASH_ID_TABLE,
            array(
                'id' => array('integer', $ilDB->nextID(self::HASH_ID_TABLE)),
                'usr_id' => array('integer', $this->user_id),
                'hash' => array('text', $this->hash),
            )
        );
    }

    /**
     * Update existing db entry
     * @return void
     * @throws \dml_exception
     */
    public function update(): void
    {
        global $ilDB;
        $ilDB->update(
            self::HASH_ID_TABLE,
            array(
                'hash' => array('text', $this->hash),
                "usr_id" => array('integer', $this->user_id),
            ),
            array(
                'id' => array('integer', $this->id)
            )
        );
    }

    /**
     * Find id has by moodle user id.
     * @param string $user_id
     * @return mumie_id_hash|null
     * @throws \dml_exception
     */
    public static function find_by_user(string $user_id): ?mumie_id_hash
    {
        global $ilDB;
        $result = $ilDB->fetchObject(
            $ilDB->query(
                'SELECT * FROM '
                    . self::HASH_ID_TABLE
                    . " WHERE usr_id = "
                    . $ilDB->quote($user_id, "integer")
            )
        );

        return self::from_record($result);
    }

    /**
     * Find db entry matching a given user and hash.
     * @param int $user_id
     * @param string $hash
     * @return mumie_id_hash|null
     * @throws \dml_exception
     */
    private static function find(int $user_id, string $hash): ?mumie_id_hash
    {
        global $ilDB;
        $result = $ilDB->fetchObject(
            $ilDB->query(
                'SELECT * FROM '
            . self::HASH_ID_TABLE
            . " WHERE hash = "
            . $ilDB->quote($hash, "text")
            . " and usr_id = "
            . $ilDB->quote($user_id, "integer")
            )
        );

        return self::from_record($result);
    }

    /**
     * Find db entry matching a given hash.
     * @param string $hash
     * @return mumie_id_hash|null
     * @throws \dml_exception
     */
    public static function find_by_hash(string $hash): ?mumie_id_hash
    {
        global $ilDB;
        $result = $ilDB->fetchObject(
            $ilDB->query(
                'SELECT * FROM '
                    . self::HASH_ID_TABLE
                    . " WHERE hash = "
                    . $ilDB->quote($hash, "text")
            )
        );

        return self::from_record($result);
    }

    /**
     * Create class instance from db result.
     * @param  mixed $record
     * @return mumie_id_hash|null
     */
    private static function from_record(mixed $record): ?mumie_id_hash
    {
        if (!$record) {
            return null;
        }
        $idhash = new mumie_id_hash($record->usr_id, $record->hash);
        $idhash->set_id($record->id);
        return $idhash;
    }

    /**
     * Get the id.
     * @return int
     */
    public function get_id(): int
    {
        return $this->id;
    }

    /**
     * Set the id
     * @param int $id
     */
    public function set_id(int $id): void
    {
        $this->id = $id;
    }

    /**
     * Get the moodle user id
     * @return int
     */
    public function get_user_id(): int
    {
        return $this->user_id;
    }

    /**
     * Set the moodle user id
     * @param int $user_id
     */
    public function set_user(int $user_id): void
    {
        $this->user_id = $user_id;
    }

    /**
     * Get the hash
     * @return string
     */
    public function get_hash(): string
    {
        return $this->hash;
    }

    /**
     * Set the hash
     * @param string $hash
     */
    public function set_hash(string $hash): void
    {
        $this->hash = $hash;
    }
}
