<?php
/**
 * MumieTask plugin
 *
 * @copyright   2013 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ilMumieTaskI18N {
    private ilObjMumieTask $plugin;
    private ilLanguage $language;

    public function __construct()
    {
        global $DIC;
        $this->language = $DIC->language();
        $this->plugin = new ilObjMumieTask();
    }

    public function txt($key): string {
        return $this->plugin->txt($key);
    }

    public function globalTxt($key): string {
        return$this->language->txt($key);
    }
}