<?php

/**
 * MumieTask plugin
 *
 * @copyright   2019 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/i18n/class.ilMumieTaskI18N.php');

/**
 * This form is used to add, edit and validate MUMIE Server configurations
 */
class ilMumieTaskServerFormGUI extends ilPropertyFormGUI
{
    private ilMumieTaskI18N $i18N;
    public function __construct()
    {
        parent::__construct();
        $this->i18N = new ilMumieTaskI18N();
    }

    private $url_item;
    private $name_item;

    public function setFields()
    {
        $this->name_item = new ilTextInputGUI($this->i18N->globalTxt('name'), 'name');
        $this->name_item->setRequired(true);
        parent::addItem($this->name_item);
        $this->url_item = new ilTextInputGUI($this->i18N->txt('url_prefix'), 'url_prefix');
        $this->url_item->setRequired(true);
        parent::addItem($this->url_item);
    }

    public function checkInput(): bool
    {
        global $DIC;
        $id = $_GET['server_id'];
        $DIC->ctrl()->setParameter($this, "server_id", $id);

        $ok = parent::checkInput();
        if ($ok) {
            require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/class.ilMumieTaskServer.php');

            $server = new ilMumieTaskServer();
            $server->setName($this->getInput("name"));
            $server->setUrlPrefix($this->getInput("url_prefix"));

            $name_exists = $server->nameExistsInDb();
            $url_prefix_exists = $server->urlPrefixExistsInDb();
            if (!$server->isValidMumieServer()) {
                $ok = false;
                $this->url_item->setAlert($this->i18N->txt('server_not_valid'));
            }

            if (!isset($id)) {
                if ($name_exists) {
                    $ok = false;
                    $this->name_item->setAlert($this->i18N->txt('name_exists'));
                }
                if ($url_prefix_exists) {
                    $ok = false;
                    $this->url_item->setAlert($this->i18N->txt('url_exists'));
                }
            } else {
                $old_server = new ilMumieTaskServer($id);
                $old_server->load();

                if ($name_exists && $old_server->getName() != $server->getName()) {
                    $ok = false;
                    $this->name_item->setAlert($this->i18N->txt('name_exists'));
                }
                if ($url_prefix_exists && $old_server->getUrlPrefix() != $server->getUrlPrefix()) {
                    $ok = false;
                    $this->url_item->setAlert($this->i18N->txt('url_exists'));
                }
            }
        }
        return $ok;
    }
}
