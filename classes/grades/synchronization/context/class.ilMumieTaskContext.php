<?php

class ilMumieTaskContext implements JsonSerializable {
    /**
     * @var array
     */
    private array $objectcontexts;

    /**
     * Create a new instance.
     */
    public function __construct() {
        $this->objectcontexts = array();
    }

    /**
     * Add a new ObjectContext to this context.
     * @param string         $objectid
     * @param ilMumieTaskObjectContext $objectcontext
     * @return void
     */
    public function add_object_context(string $objectid, ilMumieTaskObjectContext $objectcontext): void {
        $this->objectcontexts[$objectid] = $objectcontext;
    }

    /**
     * Custom json serialization.
     * @return array
     */
    public function jsonSerialize() : array {
        return $this->objectcontexts;
    }
}
