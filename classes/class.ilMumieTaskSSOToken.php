<?php

/**
 * MumieTask plugin.
 *
 * @copyright   2019 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @author      Nicolas Zunker (nicolas.zunker@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * This class represents SSO tokens used to validate a user's access to the MUMIE platform.
 */
class ilMumieTaskSSOToken
{
    public const MUMIETOKENS_TABLE_NAME = 'xmum_sso_tokens';
    public const TOKEN_LENGTH = 30;
    private $token;
    private $user;
    private $timecreated;

    public function __construct($user)
    {
        $this->user = $user;
    }

    private function generateToken()
    {
        $token = '';
        $codealphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $codealphabet .= 'abcdefghijklmnopqrstuvwxyz';
        $codealphabet .= '0123456789';
        $max = strlen($codealphabet) - 1;

        for ($i = 0; $i < self::TOKEN_LENGTH; ++$i) {
            $token .= $codealphabet[rand(0, $max)];
        }

        return $token;
    }

    private function create()
    {
        global $DIC;
        $db = $DIC->database();
        $db->insert(
            self::MUMIETOKENS_TABLE_NAME,
            [
                'id' => ['integer', $db->nextID(self::MUMIETOKENS_TABLE_NAME)],
                'token' => ['text', $this->token],
                'timecreated' => ['integer', time()],
                'user' => ['text', $this->user]],
        );
    }

    public function read()
    {
        global $DIC;
        $db = $DIC->database();
        $query = 'SELECT * FROM '
            . self::MUMIETOKENS_TABLE_NAME
            . ' WHERE user = '
            . $db->quote($this->user, 'text');

        $result = $db->fetchAssoc($db->query($query));
        if (!is_null($result)) {
            $this->setToken($result['token']);
            $this->setTimecreated($result['timecreated']);
        }
    }

    private function update()
    {
        global $DIC;
        $DIC->database()->update(
            self::MUMIETOKENS_TABLE_NAME,
            [
                'token' => ['text', $this->token],
                'timecreated' => ['integer', time()],
            ],
            [
                'user' => ['text', $this->user],
            ],
        );
    }

    public function delete()
    {
        global $DIC;
        $db = $DIC->database();
        $db->manipulate(
            'DELETE FROM '
            . self::MUMIETOKENS_TABLE_NAME
            . ' WHERE user = '
            . $db->quote($this->user, 'text'),
        );
    }

    public function insertOrRefreshToken()
    {
        $this->read();
        $this->token = $this->generateToken();
        if (!$this->tokenExistsForHashedUser($this->user)) {
            $this->create();
        } else {
            $this->update();
        }
    }

    private static function tokenExistsForHashedUser($hashedUser)
    {
        $mumie_token = new ilMumieTaskSSOToken($hashedUser);
        $mumie_token->read();

        return !is_null($mumie_token->timecreated) && !is_null($mumie_token->token);
    }

    public static function tokenExistsForIliasUser($iliasUserId)
    {
        global $DIC;
        $db = $DIC->database();
        $query = self::getAllTokensForIliasUserQuery($iliasUserId);

        return !is_null($db->fetchAssoc($db->query($query)));
    }

    private static function getAllTokensForIliasUserQuery($iliasUserId)
    {
        global $DIC;
        $hashedId = ilMumieTaskIdHashingService::getHashForUser($iliasUserId);

        return 'SELECT * FROM '
            . self::MUMIETOKENS_TABLE_NAME
            . ' WHERE ' . $DIC->database()->like('user', 'text', $hashedId . '%');
    }

    public static function invalidateAllTokensForUser($iliasUserId)
    {
        global $DIC;
        $db = $DIC->database();
        $query = self::getAllTokensForIliasUserQuery($iliasUserId);
        while ($result = $db->fetchAssoc($db->query($query))) {
            $mumie_token = new ilMumieTaskSSOToken($result['user']);
            $mumie_token->delete();
        }
    }

    /**
     * Get the value of timecreated.
     */
    public function getTimecreated()
    {
        return $this->timecreated;
    }

    /**
     * Set the value of timecreated.
     *
     * @return self
     */
    public function setTimecreated($timecreated)
    {
        $this->timecreated = $timecreated;

        return $this;
    }

    /**
     * Get the value of token.
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set the value of token.
     *
     * @return self
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get the value of user.
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set the value of user.
     *
     * @return self
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }
}
