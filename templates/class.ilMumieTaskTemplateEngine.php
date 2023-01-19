<?php

class ilMumieTaskTemplateEngine
{
    const EMPTY_CELL = '-';
    public static function getTemplate(string $path): ilTemplate
    {
        return new ilTemplate($path, true, true, true, "DEFAULT", true);
    }

}