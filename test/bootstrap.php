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

if (!class_exists('ilLPStatus')) {
    eval('class ilLPStatus {
        public const LP_STATUS_NOT_ATTEMPTED_NUM = 0;
        public const LP_STATUS_IN_PROGRESS_NUM = 1;
        public const LP_STATUS_COMPLETED_NUM = 2;
        public const LP_STATUS_FAILED_NUM = 3;
    }');
}

if (!class_exists('ilLPStatusPlugin')) {
    eval('class ilLPStatusPlugin extends ilLPStatus {}');
}

/*
 * Stands in for ILIAS' read-tracking API. Tests drive it directly via the public static
 * properties below instead of faking $DIC/$ilDB, since the real methods are themselves
 * thin, already-tested DB one-liners - only the call (which obj_id/user_ids) matters here.
 */
if (!class_exists('ilChangeEvent')) {
    eval('class ilChangeEvent {
        public static array $get_all_user_ids_results = [];
        public static array $deleted_read_events_calls = [];

        public static function _getAllUserIds(int $a_obj_id): array {
            return self::$get_all_user_ids_results[$a_obj_id] ?? [];
        }

        public static function _deleteReadEvents(int $a_obj_id): void {
            self::$deleted_read_events_calls[] = ["all", $a_obj_id];
        }

        public static function _deleteReadEventsForUsers(int $a_obj_id, array $a_user_ids): void {
            self::$deleted_read_events_calls[] = ["users", $a_obj_id, $a_user_ids];
        }
    }');
}

require_once __DIR__ . '/../classes/class.ilObjMumieTask.php';
require_once __DIR__ . '/../classes/class.ilMumieTaskAdminSettings.php';
require_once __DIR__ . '/../classes/class.ilMumieTaskGradeSync.php';
require_once __DIR__ . '/../classes/class.ilMumieTaskLPStatus.php';
require_once __DIR__ . '/../classes/users/class.ilMumieTaskParticipantService.php';
require_once __DIR__ . '/../classes/cryptography/class.ilMumieTaskCryptographyService.php';
