<?php

/**
 * MumieTask plugin.
 *
 * @copyright   2026 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Sabine Greiser (sabine.greiser@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/*
 * Minimal stand-ins for the ILIAS core classes our plugin classes extend/implement,
 * just enough to unit test pure logic without a full ILIAS bootstrap (DB, $DIC, ...).
 *
 * Declared via eval() rather than plain `class`/`interface` syntax: composer's
 * classmap generator statically scans every .php file for class/interface tokens,
 * completely ignoring the class_exists()/interface_exists() guards below. A plain
 * declaration here would get classmapped as *the* definition of these ILIAS core
 * names project-wide, shadowing the real ones everywhere - including in production.
 */

if (!interface_exists('ilLPStatusPluginInterface')) {
    eval('interface ilLPStatusPluginInterface {}');
}

if (!class_exists('ilObjectPlugin')) {
    eval('class ilObjectPlugin { public function __construct($a_ref_id = 0) {} }');
}

require_once __DIR__ . '/../classes/class.ilObjMumieTask.php';
