<?php
require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/models/class.ilMumieTaskServerStructure.php');
require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/debugToConsole.php');

class ilMumieTaskServer extends ilMumieTaskServerStructure implements \JsonSerializable {
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

    public static function getAllServers() {
        $servers = array();
        foreach (ilMumieTaskServer::getAllServerData() as $data) {
            $server = new ilMumieTaskServer($data["server_id"]);
            $server->setName($data["name"]);
            $server->setUrlPrefix($data["url_prefix"]);
            $server->buildStructure();
            array_push($servers, $server);
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
        $url_prefix = (substr($url_prefix, -1) == '/' ? $url_prefix : $url_prefix . '/');
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

    public function nameExistsInDB() {
        global $DIC;
        $query = 'SELECT * FROM ' . ilMumieTaskServer::$SERVER_TABLE_NAME . ' WHERE name = ' . $DIC->database()->quote($this->name, 'text');
        $result = $DIC->database()->query($query);

        return $DIC->database()->numRows($result) > 0;
    }

    public function urlPrefixExistsInDB() {
        global $DIC;
        $query = "SELECT * FROM " . ilMumieTaskServer::$SERVER_TABLE_NAME . " WHERE url_prefix = " . $DIC->database()->quote($this->url_prefix, 'text');
        $result = $DIC->database()->query($query);
        return $DIC->database()->numRows($result) > 0;
    }

    public function isValidMumieServer() {

        return $this->getCoursesAndTasks()->courses != null;
    }

    public function getCoursesAndTasks() {
        //TODO: REMOVE THIS BEFORE PUBLISH
        //MOCK START

        if ($this->getUrlPrefix() == "test/") {           
            $largeJSON = file_get_contents("./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/newest.json");
            $tester = json_decode($largeJSON,true);
            return json_decode($largeJSON);
        }
        
        //MOCK END
        $curl = curl_init($this->getCoursesAndTasksURL());
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_USERAGENT => 'Codular Sample cURL Request',
        ]);
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response);
    }

    private function getCoursesAndTasksURL() {
        return $this->url_prefix . 'public/courses-and-tasks';
    }

    public function buildStructure() {
        parent::loadStructure($this->getCoursesAndTasks());
    }

    public function jsonSerialize() {
        $vars = parent::jsonSerialize();
        array_push($vars, ...array_values(get_object_vars($this)));
        return $vars;
    }

    public static function serverExistsForUrl($url) {
        return in_array(
            $url,
            array_map(function ($server) {
                return $server->getUrlPrefix();
            }, ilMumieTaskServer::getAllServers())
        );
    }
}
