<?php

/**
 * MumieTask plugin.
 *
 * @copyright   2019 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ilMumieTaskPlugin extends ilRepositoryObjectPlugin
{
    public const ID = 'xmum';

    // must correspond to the plugin subdirectory
    public function getPluginName(): string
    {
        return 'MumieTask';
    }

    /**
     * Path to the plugin directory relative to the ILIAS root, for use with
     * ilTemplate (new ilTemplate(...), addBlockFile(), setRowTemplate()).
     */
    public static function getPluginPath(): string
    {
        global $DIC;

        return 'public/' . $DIC['component.factory']->getPlugin(self::ID)->getRelativeDirectory();
    }

    /**
     * Path to the plugin directory relative to the web root, for use with
     * ilGlobalTemplate::addCss()/addJavaScript(), whose values are emitted
     * as-is into the HTML as browser-facing URLs.
     */
    public static function getAssetPath(): string
    {
        global $DIC;

        return $DIC['component.factory']->getPlugin(self::ID)->getRelativeDirectory();
    }

    protected function uninstallCustom(): void
    {
        // TODO: Nothing to do here.
    }
}
