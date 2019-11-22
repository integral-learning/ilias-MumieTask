<?php
include_once('./Services/Table/classes/class.ilTable2GUI.php');

class ilMumieTaskServerTableGUI extends ilTable2GUI
{
    public function __construct($a_parent_obj, $a_parent_cmd = '', $a_template_context = '')
    {
        // this uses the cached plugin object
        $this->plugin_object = ilPlugin::getPluginObject(IL_COMP_SERVICE, 'Repository', 'robj', 'MumieTask');

        parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);
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

        $this->setTitle($lng->txt("rep_robj_xmum_tab_servers"));
        $this->addColumn($lng->txt('id'), 'server_id', '10%');
        $this->addColumn($lng->txt('name'), 'name', '20%');
        $this->addColumn($lng->txt('rep_robj_xmum_url_prefix'), 'url_prefix', '50%');
        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->addCommandButton('addServer', $lng->txt('rep_robj_xmum_add_server'));
        $this->setRowTemplate('tpl.servers_row.html', 'Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask');
        $this->getServerData();
    }

    private function getServerData()
    {
        $this->plugin_object->includeClass('class.ilMumieTaskServer.php');
        $this->setData(ilMumieTaskServer::getAllServerData());
    }

    protected function fillRow($set)
    {
        global $lng, $ilCtrl;

        $ilCtrl->setParameter($this->parent_obj, 'server_id', $set['server_id']);

        $this->tpl->setVariable("TXT_ID", $set["server_id"]);
        $this->tpl->setVariable("TXT_NAME", $set["name"]);
        $this->tpl->setVariable("TXT_URL_PREFIX", $set["url_prefix"]);

        $this->tpl->setVariable("TXT_DELETE", $lng->txt('delete'));
        $this->tpl->setVariable("LINK_DELETE", $ilCtrl->getLinkTarget($this->parent_obj, 'deleteServer'));

        $this->tpl->setVariable("TXT_EDIT", "EDIT");
        $this->tpl->setVariable("LINK_EDIT", $ilCtrl->getLinkTarget($this->parent_obj, 'editServer'));
    }
}
