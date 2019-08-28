<?php

class ilMumieTaskServer {
    private $server_id;
    private $name;
    private $url_prefix;

    private static $SERVER_TABLE_NAME = "xmum_mumie_servers";
    public function __construct($id = 0) {
        $this->server_id = $id;
    }

    private function create() {
        global $ilDB, $DIC;
        $this->server_id = $ilDB->nextId(ilMumieTaskServer::$SERVER_TABLE_NAME);
        $DIC->database()->insert(ilMumieTaskServer::$SERVER_TABLE_NAME, array(
            "server_id" => array('integer', $this->server_id),
            "name" => array('text', $this->name),
            "url_prefix" => array('text', $this->url_prefix),
        ));
    }

    public function upsert() {
        if ($this->server_id > 0) {
            $this->update();
        } else {
            $this->create();
        }
    }

    public static function getAllServerData() {
        global $DIC;
        $query = "SELECT * FROM " . ilMumieTaskServer::$SERVER_TABLE_NAME;
        $result = $DIC->database()->query($query);
        $servers = array();
        while ($row = $DIC->database()->fetchAssoc($result)) {
            $servers[] = $row;
        }
        return $servers;
    }
    public function setName($name) {
        $this->name = $name;
    }

    public function getName() {
        return $this->name;
    }

    public function setUrlPrefix($url_prefix) {
        $this->url_prefix = $url_prefix;
    }

    public function getUrlPrefix() {
        return $this->url_prefix;
    }

    public function update() {
        global $DIC;

        $DIC->database()->update(ilMumieTaskServer::$SERVER_TABLE_NAME, array(
            "name" => array("text", $this->name),
            "url_prefix" => array("text", $this->url_prefix),
        ), array(
            "server_id" => array("int", $this->server_id),
        )
        );
    }

    public function delete() {
        global $DIC;
        $query = "DELETE FROM " . ilMumieTaskServer::$SERVER_TABLE_NAME . " WHERE server_id = " . $DIC->database()->quote($this->server_id, 'integer');
        $DIC->database()->manipulate($query);
    }

    public function load() {
        global $DIC;
        $query = "SELECT * FROM " . ilMumieTaskServer::$SERVER_TABLE_NAME . " WHERE server_id = " . $DIC->database()->quote($this->server_id, 'integer');
        $result = $DIC->database()->fetchObject($DIC->database()->query($query));
        $this->name = $result->name;
        $this->url_prefix = $result->url_prefix;
    }

    public function nameExists() {
        global $DIC;
        $query = "SELECT * FROM " . ilMumieTaskServer::$SERVER_TABLE_NAME . " WHERE name = " . $DIC->database()->quote($this->name, 'text');
        return $DIC->database()->query($query);
    }

    public function urlPrefixExists() {
        global $DIC;
        $query = "SELECT * FROM " . ilMumieTaskServer::$SERVER_TABLE_NAME . " WHERE url_prefix = " . $DIC->database()->quote($this->url_prefix, 'text');
        return $DIC->database()->query($query);
    }
}