<?php

/**
 * MumieTask plugin.
 *
 * @copyright   2019 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * A MUMIE server is an instance of the MUMIE E-Learning platform.
 */
class ilMumieTaskServer extends ilMumieTaskServerStructure implements JsonSerializable
{
    private $server_id;
    private $name;
    private $url_prefix;

    /**
     * This is used as parameter when requesting available courses and tasks.
     */
    public const MUMIE_JSON_FORMAT_VERSION = 3;

    /**
     * This is used as parameter when synchronizing grades.
     */
    public const MUMIE_GRADE_SYNC_VERSION = 2;

    private static string $SERVER_TABLE_NAME = 'xmum_mumie_servers';

    public function __construct($id = 0)
    {
        $this->server_id = $id;
    }

    /**
     * Construct server object from URL.
     */
    public static function fromUrl($url): ilMumieTaskServer
    {
        $server = new ilMumieTaskServer();
        $server->setUrlPrefix($url);

        return $server;
    }

    private function create(): void
    {
        global $DIC;
        $db = $DIC->database();
        $this->server_id = $db->nextId(ilMumieTaskServer::$SERVER_TABLE_NAME);
        $db->insert(ilMumieTaskServer::$SERVER_TABLE_NAME, [
            'server_id' => ['integer', $this->server_id],
            'name' => ['text', $this->name],
            'url_prefix' => ['text', $this->url_prefix],
        ]);
    }

    public function upsert(): void
    {
        if ($this->server_id > 0) {
            $this->update();
        } else {
            $this->create();
        }
    }

    /**
     * Get a list of all saved server configurations.
     */
    public static function getAllServerData(): array
    {
        global $DIC;
        $query = 'SELECT * FROM ' . ilMumieTaskServer::$SERVER_TABLE_NAME;
        $result = $DIC->database()->query($query);
        $servers = [];
        while ($row = $DIC->database()->fetchAssoc($result)) {
            $servers[] = $row;
        }

        return $servers;
    }

    /**
     * Get a list of all saved server configurations including their course structure.
     *
     * @throws ilCurlConnectionException
     */
    public static function getAllServers(): array
    {
        $servers = [];
        foreach (ilMumieTaskServer::getAllServerData() as $data) {
            $server = new ilMumieTaskServer($data['server_id']);
            $server->setName($data['name']);
            $server->setUrlPrefix($data['url_prefix']);
            $server->buildStructure();
            array_push($servers, $server);
        }

        return $servers;
    }

    public function setName($name): void
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setUrlPrefix($url_prefix): void
    {
        $url_prefix = ('/' == substr($url_prefix, -1) ? trim($url_prefix) : trim($url_prefix) . '/');
        $this->url_prefix = $url_prefix;
    }

    public function getUrlPrefix()
    {
        return $this->url_prefix;
    }

    public function update(): void
    {
        global $DIC;

        $DIC->database()->update(
            ilMumieTaskServer::$SERVER_TABLE_NAME,
            [
                'name' => ['text', $this->name],
                'url_prefix' => ['text', $this->url_prefix],
            ],
            [
                'server_id' => ['int', $this->server_id],
            ],
        );
    }

    public function delete(): void
    {
        global $DIC;
        $db = $DIC->database();
        $query = 'DELETE FROM ' . ilMumieTaskServer::$SERVER_TABLE_NAME . ' WHERE server_id = ' . $db->quote($this->server_id, 'integer');
        $db->manipulate($query);
    }

    public function load(): void
    {
        global $DIC;
        $db = $DIC->database();
        $query = 'SELECT * FROM ' . ilMumieTaskServer::$SERVER_TABLE_NAME . ' WHERE server_id = ' . $db->quote($this->server_id, 'integer');
        $result = $db->fetchObject($db->query($query));
        $this->name = $result->name;
        $this->url_prefix = $result->url_prefix;
    }

    public function nameExistsInDB(): bool
    {
        global $DIC;
        $db = $DIC->database();
        $query = 'SELECT * FROM ' . ilMumieTaskServer::$SERVER_TABLE_NAME . ' WHERE name = ' . $db->quote($this->name, 'text');
        $result = $db->query($query);

        return $db->numRows($result) > 0;
    }

    public function urlPrefixExistsInDB(): bool
    {
        global $DIC;
        $db = $DIC->database();
        $query = 'SELECT * FROM ' . ilMumieTaskServer::$SERVER_TABLE_NAME . ' WHERE url_prefix = ' . $db->quote($this->url_prefix, 'text');
        $result = $db->query($query);

        return $db->numRows($result) > 0;
    }

    /**
     * Return false, if the server did not give any meaningful response.
     *
     * @throws ilCurlConnectionException
     */
    public function isValidMumieServer(): bool
    {
        return null != $this->getCoursesAndTasks()->courses;
    }

    /**
     * Get all available courses and tasks provided by the MUMIE server.
     *
     * @throws ilCurlConnectionException
     */
    public function getCoursesAndTasks()
    {
        $proxy_settings = ilProxySettings::_getInstance();

        $curl = new ilCurlConnection($this->getCoursesAndTasksURL());
        $curl->init();
        if ($proxy_settings->isActive()) {
            $curl->setOpt(CURLOPT_HTTPPROXYTUNNEL, true);
            $curl->setOpt(CURLOPT_PROXY, $proxy_settings->getHost());
            $curl->setOpt(CURLOPT_PROXYPORT, $proxy_settings->getPort());
        }

        $curl->setOpt(CURLOPT_RETURNTRANSFER, 1);
        $curl->setOpt(CURLOPT_USERAGENT, 'MUMIE Task for Ilias');
        $response = $curl->exec();
        $curl->close();

        return json_decode($response);
    }

    private function getCoursesAndTasksURL(): string
    {
        return $this->url_prefix . 'public/courses-and-tasks?v=' . self::MUMIE_JSON_FORMAT_VERSION . '&org=' . ilMumieTaskAdminSettings::getInstance()->getOrg();
    }

    /**
     * @throws ilCurlConnectionException
     */
    public function buildStructure(): void
    {
        parent::loadStructure($this->getCoursesAndTasks());
    }

    public function jsonSerialize(): array
    {
        $parentVars = (array) parent::jsonSerialize();
        $vars = get_object_vars($this);

        return array_merge($vars, $parentVars);
    }

    /**
     * @throws ilCurlConnectionException
     */
    public static function serverConfigExistsForUrl($url): bool
    {
        return in_array(
            $url,
            array_map(function ($server) {
                return $server->getUrlPrefix();
            }, ilMumieTaskServer::getAllServers()),
        );
    }

    public function getLoginUrl(): string
    {
        return $this->url_prefix . 'public/xapi/auth/sso/login';
    }

    public function getLogoutUrl(): string
    {
        return $this->url_prefix . 'public/xapi/auth/sso/logout/' . ilMumieTaskAdminSettings::getInstance()->getOrg();
    }

    public function getGradeSyncURL(): string
    {
        return $this->url_prefix . 'public/xapi?v=' . self::MUMIE_GRADE_SYNC_VERSION;
    }
}
