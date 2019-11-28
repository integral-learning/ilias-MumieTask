<?php
/**
 * MumieTask plugin
 *
 * @copyright   2019 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('Services/Form/classes/class.ilCustomInputGUI.php');

/**
 * A new input type for ilPropertyFormGUI.
 * 
 * Create a button that opens a link if clicked
 */
class ilMumieTaskFormButtonGUI extends ilCustomInputGUI
{
    protected $link;
    protected $button_label;

    public function __construct($a_title = "")
    {
        parent::__construct($a_title, "");
    }

    public function insert($a_tpl)
    {
        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $this->render());
        $a_tpl->parseCurrentBlock();
    }

    public function render()
    {
        $tpl = new ilTemplate("./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/templates/tpl.mumie_form_button.html", true, true, true, "DEFAULT", true);
        $tpl->setVariable("COMMAND_LINK", $this->link);
        $tpl->setVariable("BUTTON_LABEL", $this->button_label);

        return $tpl->get();
    }

    /**
     * Set the value of link
     *
     * @return  self
     */
    public function setLink($link)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * Set the value of button_label
     *
     * @return  self
     */
    public function setButtonLabel($button_label)
    {
        $this->button_label = $button_label;

        return $this;
    }
}
