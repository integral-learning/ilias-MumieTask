<?php


/**
 * This class represents a single MUMIE Tasks context required for some XAPI requests.
 *
 * @package mod_mumie
 * @copyright  2017-2023 integral-learning GmbH (https://www.integral-learning.de/)
 * @author Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ilMumieTaskObjectContext implements JsonSerializable {
    /**
     * @var array
     */
    private array $usercontexts;

    /**
     * @var string
     */
    private string $language;

    /**
     * Create a new instance.
     * @param string       $lang
     */
    public function __construct($lang) {
        $this->usercontexts = array();
        $this->language = $lang;
    }

    /**
     * Add a new context for a given user.
     * @param string       $userid
     * @param ilMumieTaskUserContext $usercontext
     * @return void
     */
    public function add_user_context(string $userid,  ilMumieTaskUserContext $usercontext): void {
        $this->usercontexts[$userid] = $usercontext;
    }

    /**
     * Custom JSON serializer.
     * @return array
     */
    public function jsonSerialize(): array {
        return ['language' => $this->language, 'userContexts' => $this->usercontexts];
    }
}
