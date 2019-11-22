<?php
require_once('Services/Form/classes/class.ilCustomInputGUI.php');
class ilMumieTaskFormButtonGUI extends ilCustomInputGUI
{
    protected $link;
    protected $buttonLabel;

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
        $tpl->setVariable("BUTTON_LABEL", $this->buttonLabel);

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
     * Set the value of buttonLabel
     *
     * @return  self
     */
    public function setButtonLabel($buttonLabel)
    {
        $this->buttonLabel = $buttonLabel;

        return $this;
    }
}
