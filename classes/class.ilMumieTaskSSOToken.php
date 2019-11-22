<?php

class ilMumieTaskSSOToken
{
    const MUMIETOKENS_TABLE_NAME = "xmum_sso_tokens";
    const TOKEN_LENGTH = 30;
    private $token;
    private $user;
    private $timecreated;

    public function __construct($user)
    {
        $this->user = $user;
    }

    private function generateToken()
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

    private function create()
    {
        global $ilDB;
        $ilDB->insert(
            self::MUMIETOKENS_TABLE_NAME,
            array(
                'id' => array('integer', $ilDB->nextID(self::MUMIETOKENS_TABLE_NAME)),
                'token' => array('text', $this->token),
                'timecreated' => array('integer', time()),
                'user' => array('integer', $this->user))
        );
    }

    public function read()
    {
        global $ilDB;
        $query = "SELECT * FROM "
        . self::MUMIETOKENS_TABLE_NAME
        . " WHERE user = "
        . $ilDB->quote($this->user, 'integer');

        $result = $ilDB->fetchAssoc($ilDB->query($query));
        $this->setToken($result["token"]);
        $this->setTimecreated($result["timecreated"]);
    }

    private function update()
    {
        global $ilDB;
        $ilDB->update(
            self::MUMIETOKENS_TABLE_NAME,
            array(
                'token' => array('text', $this->token),
                'timecreated' => array('integer', time()),
            ),
            array(
                'user' => array('integer', $this->user),
            )
        );
    }

    public function delete()
    {
        global $ilDB;
        $ilDB->manipulate(
            "DELETE FROM "
            . self::MUMIETOKENS_TABLE_NAME
            . " WHERE user = "
            . $ilDB->quote($this->user, 'integer')
        );
    }

    public function insertOrRefreshToken()
    {
        $this->read();
        $this->token = $this->generateToken();
        if (!$this->tokenExistsForUser($this->user)) {
            $this->create();
        } else {
            $this->update();
        }
    }

    public static function tokenExistsForUser($userId)
    {
        $mumie_token = new ilMumieTaskSSOToken($userId);
        $mumie_token->read();
        return !is_null($mumie_token->timecreated) && !is_null($mumie_token->token);
    }

    public static function invalidateTokenForUser($userId)
    {
        $mumie_token = new ilMumieTaskSSOToken($userId);
        $mumie_token->delete();
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
     * @return  self
     */
    public function setTimecreated($timecreated)
    {
        $this->timecreated = $timecreated;

        return $this;
    }

    /**
     * Get the value of token
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set the value of token
     *
     * @return  self
     */
    public function setToken($token)
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
     * @return  self
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }
}
