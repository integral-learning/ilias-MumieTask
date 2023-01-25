<?php
/**
 * MumieTask plugin
 *
 * @copyright   2022 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('Services/Form/classes/class.ilFormPropertyGUI.php');
class ilMumieTaskDropZone extends ilFormPropertyGUI
{
    public function insert($a_tpl)
    {
        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $this->render());
        $a_tpl->parseCurrentBlock();
    }

    public function render()
    {
        global $tpl;
        require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/templates/class.ilMumieTaskTemplateEngine.php');
        $dropzone_template = ilMumieTaskTemplateEngine::getDropzoneTemplate();
        $dropzone_template->setVariable("DESCRIPTION", "TODO");
        $tpl->addJavaScript('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/js/ilMumieTaskDropzone.js');

        return $dropzone_template->get();
    }

    public function setValueByArray($a_values)
    {
    }
}
