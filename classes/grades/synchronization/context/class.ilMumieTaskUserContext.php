<?php


/**
 * This class represents the context in which a user is working on a MUMIE Task.
 *
 * @package mod_mumie
 * @copyright  2017-2023 integral-learning GmbH (https://www.integral-learning.de/)
 * @author Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ilMumieTaskUserContext implements JsonSerializable {
    /**
     * @var int
     */
    private int $deadline;

    /**
     * Create new instance.
     * @param int $deadline
     */
    public function __construct(int $deadline) {
        $this->deadline = $deadline;
    }

    /**
     * Custom JSON serializer.
     * @return array
     */
    public function jsonSerialize(): array {
        return get_object_vars($this);
    }
}
