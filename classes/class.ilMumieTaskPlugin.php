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

    private static ?string $relative_directory = null;

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
        return 'public/' . self::getAssetPath();
    }

    /**
     * Path to the plugin directory relative to the web root, for use with
     * ilGlobalTemplate::addCss()/addJavaScript(), whose values are emitted
     * as-is into the HTML as browser-facing URLs.
     */
    public static function getAssetPath(): string
    {
        if (null === self::$relative_directory) {
            global $DIC;
            self::$relative_directory = $DIC['component.factory']->getPlugin(self::ID)->getRelativeDirectory();
        }

        return self::$relative_directory;
    }

    protected function uninstallCustom(): void
    {
        // TODO: Nothing to do here.
    }
}
