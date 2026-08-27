<?php

/**
 * MumieTask plugin.
 *
 * @copyright   2022 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ilMumieTaskDropZoneGUI extends ilFormPropertyGUI
{
    private ilMumieTaskI18N $i18n;
    private string $value = '';

    public function __construct($a_title = '', $post_var = '')
    {
        parent::__construct($a_title, $post_var);
        $this->i18n = new ilMumieTaskI18N();
    }

    public function insert($a_tpl)
    {
        $a_tpl->setCurrentBlock('prop_generic');
        $a_tpl->setVariable('PROP_GENERIC', $this->render());
        $a_tpl->parseCurrentBlock();
    }

    public function render()
    {
        global $DIC;

        $dropzone_template = ilMumieTaskTemplateEngine::getDropzoneTemplate();
        $dropzone_template->setVariable('DESCRIPTION', $this->i18n->txt('dropzone_description'));
        $dropzone_template->setVariable('MULTI_PROBLEM_LIST_HEADER', $this->i18n->txt('multi_problem_list_description'));
        $dropzone_template->setVariable('POST_VAR', $this->getPostVar());
        $dropzone_template->setVariable('VALUE', ilLegacyFormElementsUtil::prepareFormOutput($this->value));
        $DIC->ui()->mainTemplate()->addJavaScript(ilMumieTaskPlugin::getAssetPath() . '/js/ilMumieTaskDropzone.js');

        return $dropzone_template->get();
    }

    public function checkInput(): bool
    {
        return true;
    }

    public function setValueByArray($a_values)
    {
        $this->value = (string) ($a_values[$this->getPostVar()] ?? '');
    }
}
