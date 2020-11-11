<?php
/**
 * MumieTask plugin
 *
 * @copyright   2019 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/models/class.ilMumieTaskServerStructure.php');

/**
 * A MUMIE server is an instance of the MUMIE E-Learning platform.
 */
class ilMumieTaskServer extends ilMumieTaskServerStructure implements \JsonSerializable
{
    private $server_id;
    private $name;
    private $url_prefix;

    /**
     * This is used as parameter when requesting available courses and tasks.
     */
    const MUMIE_JSON_FORMAT_VERSION = 3;

    /**
     * This is used as parameter when synchronizing grades
     */
    const MUMIE_GRADE_SYNC_VERSION = 2;

    private static $SERVER_TABLE_NAME = "xmum_mumie_servers";
    public function __construct($id = 0)
    {
        $this->server_id = $id;
    }

    /**
     * Construct server object from URL
     * @param $url
     * @return ilMumieTaskServer
     *
     */
    public static function fromUrl($url)
    {
        $server = new ilMumieTaskServer();
        $server->setUrlPrefix($url);
        return $server;
    }

    private function create()
    {
        global $ilDB, $DIC;
        $this->server_id = $ilDB->nextId(ilMumieTaskServer::$SERVER_TABLE_NAME);
        $DIC->database()->insert(ilMumieTaskServer::$SERVER_TABLE_NAME, array(
            "server_id" => array('integer', $this->server_id),
            "name" => array('text', $this->name),
            "url_prefix" => array('text', $this->url_prefix),
        ));
    }

    public function upsert()
    {
        if ($this->server_id > 0) {
            $this->update();
        } else {
            $this->create();
        }
    }

    /**
     * Get a list of all saved server configurations
     * @return array
     */
    public static function getAllServerData()
    {
        global $DIC;
        $query = "SELECT * FROM " . ilMumieTaskServer::$SERVER_TABLE_NAME;
        $result = $DIC->database()->query($query);
        $servers = array();
        while ($row = $DIC->database()->fetchAssoc($result)) {
            $servers[] = $row;
        }
        return $servers;
    }

    /**
     * Get a list of all saved server configurations including their course structure
     *
     * @return array
     */
    public static function getAllServers()
    {
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
    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setUrlPrefix($url_prefix)
    {
        $url_prefix = (substr($url_prefix, -1) == '/' ? $url_prefix : $url_prefix . '/');
        $this->url_prefix = $url_prefix;
    }

    public function getUrlPrefix()
    {
        return $this->url_prefix;
    }

    public function update()
    {
        global $DIC;

        $DIC->database()->update(
            ilMumieTaskServer::$SERVER_TABLE_NAME,
            array(
            "name" => array("text", $this->name),
            "url_prefix" => array("text", $this->url_prefix),
        ),
            array(
            "server_id" => array("int", $this->server_id),
        )
        );
    }

    public function delete()
    {
        global $DIC;
        $query = "DELETE FROM " . ilMumieTaskServer::$SERVER_TABLE_NAME . " WHERE server_id = " . $DIC->database()->quote($this->server_id, 'integer');
        $DIC->database()->manipulate($query);
    }

    public function load()
    {
        global $DIC;
        $query = "SELECT * FROM " . ilMumieTaskServer::$SERVER_TABLE_NAME . " WHERE server_id = " . $DIC->database()->quote($this->server_id, 'integer');
        $result = $DIC->database()->fetchObject($DIC->database()->query($query));
        $this->name = $result->name;
        $this->url_prefix = $result->url_prefix;
    }

    public function nameExistsInDB()
    {
        global $DIC;
        $query = 'SELECT * FROM ' . ilMumieTaskServer::$SERVER_TABLE_NAME . ' WHERE name = ' . $DIC->database()->quote($this->name, 'text');
        $result = $DIC->database()->query($query);

        return $DIC->database()->numRows($result) > 0;
    }

    public function urlPrefixExistsInDB()
    {
        global $DIC;
        $query = "SELECT * FROM " . ilMumieTaskServer::$SERVER_TABLE_NAME . " WHERE url_prefix = " . $DIC->database()->quote($this->url_prefix, 'text');
        $result = $DIC->database()->query($query);
        return $DIC->database()->numRows($result) > 0;
    }

    /**
     * Return false, if the server did not give any meaningful response
     * @return boolean
     */
    public function isValidMumieServer()
    {
        return $this->getCoursesAndTasks()->courses != null;
    }


    /**
     * Get all available courses and tasks provided by the MUMIE server
     */
    public function getCoursesAndTasks()
    {
        require_once './Services/Http/classes/class.ilProxySettings.php';
        $proxy_settings = ilProxySettings::_getInstance();

        $curl = new ilCurlConnection($this->getCoursesAndTasksURL());
        $curl->init();
        if (ilProxySettings::_getInstance()->isActive()) {
            $curl->setOpt(CURLOPT_HTTPPROXYTUNNEL, true);
            $curl->setOpt(CURLOPT_PROXY, ilProxySettings::_getInstance()->getHost());
            $curl->setOpt(CURLOPT_PROXYPORT, ilProxySettings::_getInstance()->getPort());
        }

        $curl->setOpt(CURLOPT_RETURNTRANSFER, 1);
        $curl->setOpt(CURLOPT_USERAGENT, 'MUMIE Task for Ilias');
        $response = $curl->exec();
        $curl->close();
        return json_decode($response);
    }

    private function getCoursesAndTasksURL()
    {
        return $this->url_prefix . 'public/courses-and-tasks?v=' . self::MUMIE_JSON_FORMAT_VERSION . '&org=' . ilMumieTaskAdminSettings::getInstance()->getOrg();
    }

    public function buildStructure()
    {
        parent::loadStructure($this->getCoursesAndTasks());
    }

    public function jsonSerialize()
    {
        $parentVars = (array) parent::jsonSerialize();
        $vars = (array) get_object_vars($this);
        return array_merge($vars, $parentVars);
    }

    public static function serverConfigExistsForUrl($url)
    {
        return in_array(
            $url,
            array_map(function ($server) {
                return $server->getUrlPrefix();
            }, ilMumieTaskServer::getAllServers())
        );
    }

    public function getLoginUrl()
    {
        return $this->url_prefix . 'public/xapi/auth/sso/login';
    }
    public function getLogoutUrl()
    {
        require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskAdminSettings.php');
        return urlencode($this->url_prefix . 'public/xapi/auth/sso/logout/' . ilMumieTaskAdminSettings::getInstance()->getOrg());
    }

    public function getGradeSyncURL()
    {
        return $this->url_prefix . 'public/xapi?v=' . self::MUMIE_GRADE_SYNC_VERSION;
    }
}
