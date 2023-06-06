<?php

/**
 * MumieTask plugin
 *
 * @copyright   2019 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * This form is used to add, edit and validate MUMIE Server configurations
 */
class ilMumieTaskServerFormGUI extends ilPropertyFormGUI
{
    public function __construct()
    {
        parent::__construct();
    }

    private $url_item;
    private $name_item;

    public function setFields()
    {
        global $lng;
        $this->name_item = new ilTextInputGUI($lng->txt('name'), 'name');
        $this->name_item->setRequired(true);
        parent::addItem($this->name_item);
        $this->url_item = new ilTextInputGUI($lng->txt('rep_robj_xmum_url_prefix'), 'url_prefix');
        $this->url_item->setRequired(true);
        parent::addItem($this->url_item);
    }

    public function checkInput() : bool
    {
        global $DIC, $lng;
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
                $this->url_item->setAlert($lng->txt("rep_robj_xmum_server_not_valid"));
            }

            if (!isset($id)) {
                if ($name_exists) {
                    $ok = false;
                    $this->name_item->setAlert($lng->txt("rep_robj_xmum_name_exists"));
                }
                if ($url_prefix_exists) {
                    $ok = false;
                    $this->url_item->setAlert($lng->txt("rep_robj_xmum_url_exists"));
                }
            } else {
                $old_server = new ilMumieTaskServer($id);
                $old_server->load();

                if ($name_exists && $old_server->getName() != $server->getName()) {
                    $ok = false;
                    $this->name_item->setAlert($lng->txt("rep_robj_xmum_name_exists"));
                }
                if ($url_prefix_exists && $old_server->getUrlPrefix() != $server->getUrlPrefix()) {
                    $ok = false;
                    $this->url_item->setAlert($lng->txt("rep_robj_xmum_url_exists"));
                }
            }
        }
        return $ok;
    }
}
