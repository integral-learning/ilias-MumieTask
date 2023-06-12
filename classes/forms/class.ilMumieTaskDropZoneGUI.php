<?php
/**
 * MumieTask plugin
 *
 * @copyright   2022 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('Services/Form/classes/class.ilFormPropertyGUI.php');
class ilMumieTaskDropZoneGUI extends ilFormPropertyGUI
{
    private ilMumieTaskI18N $i18n;
    public function __construct($a_title = "", $post_var = "")
    {
        parent::__construct($a_title, $post_var);
        $this->i18n = new ilMumieTaskI18N();
    }

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
        $dropzone_template->setVariable("DESCRIPTION", $this->i18n->txt('dropzone_description'));
        $dropzone_template->setVariable("MULTI_PROBLEM_LIST_HEADER", $this->i18n->txt('multi_problem_list_description'));
        $dropzone_template->setVariable("TXT_DRAG_PROBLEMS_HERE", $this->i18n->txt('form_drag_mt_here'));
        $dropzone_template->setVariable("POST_VAR", $this->getPostVar());
        $tpl->addJavaScript('./Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/js/ilMumieTaskDropzone.js');

        return $dropzone_template->get();
    }

    public function checkInput(): bool
    {
        return true;
    }

    public function setValueByArray($a_values)
    {
    }
}
