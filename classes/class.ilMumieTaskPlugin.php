<?php
/**
 * MumieTask plugin
 *
 * @copyright   2019 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

include_once("./Services/Repository/classes/class.ilRepositoryObjectPlugin.php");

class ilMumieTaskPlugin extends ilRepositoryObjectPlugin
{
    public const ID = "xmum";

    // must correspond to the plugin subdirectory
    public function getPluginName() : string
    {
        return "MumieTask";
    }

    protected function uninstallCustom() : void
    {
        // TODO: Nothing to do here.
    }
}
