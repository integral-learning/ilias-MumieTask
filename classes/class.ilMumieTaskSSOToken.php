<?php

/**
 * MumieTask plugin
 *
 * @copyright   2019 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @author      Nicolas Zunker (nicolas.zunker@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * This class represents SSO tokens used to validate a user's access to the MUMIE platform
 */
class ilMumieTaskSSOToken
{
    public const MUMIETOKENS_TABLE_NAME = "xmum_sso_tokens";
    public const TOKEN_LENGTH = 30;
    private string $token;
    private $user;
    private $timecreated;

    public function __construct($user)
    {
        $this->user = $user;
    }

    private function generateToken(): string
    {
        $token = "";
        $codealphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $codealphabet .= "abcdefghijklmnopqrstuvwxyz";
        $codealphabet .= "0123456789";
        $max = strlen($codealphabet) - 1;

        for ($i = 0; $i < self::TOKEN_LENGTH; $i++) {
            $token .= $codealphabet[rand(0, $max)];
        }

        return $token;
    }

    private function create(): void
    {
        global $ilDB;
        $ilDB->insert(
            self::MUMIETOKENS_TABLE_NAME,
            array(
                'id' => array('integer', $ilDB->nextID(self::MUMIETOKENS_TABLE_NAME)),
                'token' => array('text', $this->token),
                'timecreated' => array('integer', time()),
                'user' => array('text',  $this->user))
        );
    }

    public function read(): void
    {
        global $ilDB;
        $query = "SELECT * FROM "
        . self::MUMIETOKENS_TABLE_NAME
        . " WHERE user = "
        . $ilDB->quote($this->user, 'text');

        $result = $ilDB->fetchAssoc($ilDB->query($query));
        if (!is_null($result)) {
            $this->setToken($result["token"]);
            $this->setTimecreated($result["timecreated"]);
        }
    }

    private function update(): void
    {
        global $ilDB;
        $ilDB->update(
            self::MUMIETOKENS_TABLE_NAME,
            array(
                'token' => array('text', $this->token),
                'timecreated' => array('integer', time()),
            ),
            array(
                'user' => array('text', $this->user),
            )
        );
    }

    public function delete(): void
    {
        global $ilDB;
        $ilDB->manipulate(
            "DELETE FROM "
            . self::MUMIETOKENS_TABLE_NAME
            . " WHERE user = "
            . $ilDB->quote($this->user, 'text')
        );
    }

    public function insertOrRefreshToken(): void
    {
        $this->read();
        $this->token = $this->generateToken();
        if (!$this->tokenExistsForHashedUser($this->user)) {
            $this->create();
        } else {
            $this->update();
        }
    }

    private static function tokenExistsForHashedUser($hashedUser): bool
    {
        $mumie_token = new ilMumieTaskSSOToken($hashedUser);
        $mumie_token->read();
        return !is_null($mumie_token->timecreated) && !is_null($mumie_token->token);
    }

    public static function tokenExistsForIliasUser($iliasUserId): bool
    {
        global $ilDB;
        $query = self::getAllTokensForIliasUserQuery($iliasUserId);
        return !is_null($ilDB->fetchAssoc($ilDB->query($query)));
    }

    private static function getAllTokensForIliasUserQuery($iliasUserId)
    {
        global $ilDB;
        $hashedId = ilMumieTaskIdHashingService::getHashForUser($iliasUserId);
        return "SELECT * FROM "
            . self::MUMIETOKENS_TABLE_NAME
            . " WHERE " . $ilDB->like("user", "text", $hashedId . "%");
    }

    public static function invalidateAllTokensForUser($iliasUserId): void
    {
        global $ilDB;
        $query = self::getAllTokensForIliasUserQuery($iliasUserId);
        while ($result = $ilDB->fetchAssoc($ilDB->query($query))) {
            $mumie_token = new ilMumieTaskSSOToken($result["user"]);
            $mumie_token->delete();
        }
    }

    /**
     * Get the value of timecreated
     */
    public function getTimecreated()
    {
        return $this->timecreated;
    }

    /**
     * Set the value of timecreated
     *
     * @param $timecreated
     * @return  self
     */
    public function setTimecreated($timecreated): static
    {
        $this->timecreated = $timecreated;

        return $this;
    }

    /**
     * Get the value of token
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * Set the value of token
     *
     * @param string $token
     * @return  self
     */
    public function setToken(string $token): static
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get the value of user
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set the value of user
     *
     * @param $user
     * @return  self
     */
    public function setUser($user): static
    {
        $this->user = $user;

        return $this;
    }
}
