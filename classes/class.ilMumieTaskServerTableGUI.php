<?php
/**
 * MumieTask plugin
 *
 * @copyright   2019 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

include_once('./Services/Table/classes/class.ilTable2GUI.php');
require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/i18n/class.ilMumieTaskI18N.php');

/**
 * This GUI provides a way to list MUMIE servers with buttons to edit and delete entries
 */
class ilMumieTaskServerTableGUI extends ilTable2GUI
{
    private ilMumieTaskI18N $i18n;

    public function __construct($a_parent_obj, $a_parent_cmd = '', $a_template_context = '')
    {
        // this uses the cached plugin object
        $this->plugin_object = $a_parent_obj;

        parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);
        $this->i18n = new ilMumieTaskI18N();
    }

    /**
     * Init the table with some configuration
     *
     *
     * @access public
     */
    public function init($a_parent_obj)
    {
        global $ilCtrl, $lng;
        $i18n = $this->i18n;

        $this->setTitle($i18n->txt('tab_servers'));
        $this->addColumn($i18n->globalTxt('id'), 'server_id', '10%');
        $this->addColumn($i18n->globalTxt('name'), 'name', '20%');
        $this->addColumn($i18n->txt('url_prefix'), 'url_prefix', '50%');
        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->addCommandButton('addServer', $i18n->txt('add_server'));
        $this->setRowTemplate('tpl.servers_row.html', 'Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask');
        $this->getServerData();
    }

    private function getServerData()
    {
        $this->setData(ilMumieTaskServer::getAllServerData());
    }

    protected function fillRow($set): void
    {
        global $ilCtrl;

        $ilCtrl->setParameter($this->parent_obj, 'server_id', $set['server_id']);

        $this->tpl->setVariable("TXT_ID", $set["server_id"]);
        $this->tpl->setVariable("TXT_NAME", $set["name"]);
        $this->tpl->setVariable("TXT_URL_PREFIX", $set["url_prefix"]);

        $this->tpl->setVariable("TXT_DELETE", $this->i18n->globalTxt('delete'));
        $this->tpl->setVariable("LINK_DELETE", $ilCtrl->getLinkTarget($this->parent_obj, 'deleteServer'));

        $this->tpl->setVariable("TXT_EDIT", "EDIT");
        $this->tpl->setVariable("LINK_EDIT", $ilCtrl->getLinkTarget($this->parent_obj, 'editServer'));
    }
}
