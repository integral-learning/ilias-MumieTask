<?php
/**
 * MumieTask plugin
 *
 * @copyright   2022 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class ilMumieTaskTagStructure implements \JsonSerializable
{
    /**
     * Name of the tag
     * @var string
     */
    private $name;
    /**
     * All values for the tag
     * @var string[]
     */
    private $values = array();

    /**
     * Constructor
     * @param string $name
     * @param string[] $values
     */
    public function __construct($name, $values)
    {
        $this->name = $name;
        $this->values = $values;
    }

    /**
     * Necessary to encode this object as json.
     * @return mixed
     */
    public function jsonSerialize()
    {
        $vars = get_object_vars($this);

        return $vars;
    }

    /**
     * Merge this tag with another into a new one
     * @param ilMumieTaskTagStructure $tag the tag to merge with
     * @return ilMumieTaskTagStructure the new tag
     */
    public function merge($tag)
    {
        $mergedtag = new ilMumieTaskTagStructure($this->name, $this->values);
        if ($tag instanceof ilMumieTaskTagStructure && $tag->name == $mergedtag->name) {
            array_push($mergedtag->values, ...$tag->values);
            $mergedtag->values = array_values(array_unique($mergedtag->values));
        }

        return $mergedtag;
    }

    /**
     * Get the name of this tag
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
