<?php

include_once ("./Services/Repository/classes/class.ilRepositoryObjectPlugin.php");

/**
 */
class ilMumieTaskPlugin extends ilRepositoryObjectPlugin {
    const ID = "xmum";

    // must correspond to the plugin subdirectory
    function getPluginName() {
        return "MumieTask";
    }

    protected function uninstallCustom() {
        // TODO: Nothing to do here.
    }
}
?>