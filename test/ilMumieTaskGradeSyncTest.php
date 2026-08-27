<?php

use PHPUnit\Framework\TestCase;

/**
 * Skips the real constructor (it hits ilMumieTaskAdminSettings::getInstance(), which needs
 * $DIC/DB) and cans getValidAndNewXapiGradesByUser(), so getValidAndNewXapiGradesForUser()'s
 * own lookup logic can be exercised in isolation.
 */
class ilMumieTaskGradeSyncTestDouble extends ilMumieTaskGradeSync
{
    private array $canned_grades_by_user;

    public function __construct(array $canned_grades_by_user)
    {
        $this->canned_grades_by_user = $canned_grades_by_user;
    }

    public function getValidAndNewXapiGradesByUser()
    {
        return $this->canned_grades_by_user;
    }
}

/**
 * Minimal ilDB stand-in for ilMumieTaskAdminSettings::load()'s single admin-settings query -
 * the only DB touch left in the real constructor once getAllMemberIds() is routed through the
 * ilChangeEvent stub (see ilMumieTaskParticipantService::getMemberIdsWithoutCourseContext)
 * instead of a live database.
 */
class ilMumieTaskGradeSyncFakeDb
{
    /**
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     */
    public function query($sql)
    {
        return null;
    }

    /**
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     */
    public function fetchObject($result)
    {
        return (object) ['id' => 1, 'problem_selector_url' => '', 'api_key' => 'key', 'org' => 'org'];
    }
}

class ilMumieTaskGradeSyncFakeTree
{
    /**
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     */
    public function checkForParentType(int $a_ref_id, string $a_type, bool $a_exclude_source_check = false): int
    {
        return 0;
    }
}

class ilMumieTaskGradeSyncFakeDic
{
    public function __construct(private $db, private $tree)
    {
    }

    public function database()
    {
        return $this->db;
    }

    public function repositoryTree()
    {
        return $this->tree;
    }
}

/**
 * @see ilMumieTaskParticipantServiceTestTask
 */
class ilMumieTaskGradeSyncTestTask extends ilObjMumieTask
{
    public function __construct(private int $ref_id, private int $id)
    {
    }

    public function getRefId()
    {
        return $this->ref_id;
    }

    public function getId()
    {
        return $this->id;
    }
}

class ilMumieTaskGradeSyncTest extends TestCase
{
    private $original_dic;

    protected function setUp(): void
    {
        global $DIC;
        $this->original_dic = $DIC ?? null;
        ilChangeEvent::$get_all_user_ids_results = [];
    }

    protected function tearDown(): void
    {
        global $DIC;
        $DIC = $this->original_dic;
    }

    public function testConstructorUsesExplicitUserIdsWithoutConsultingAllMembers()
    {
        global $DIC;
        $DIC = new ilMumieTaskGradeSyncFakeDic(new ilMumieTaskGradeSyncFakeDb(), new ilMumieTaskGradeSyncFakeTree());
        // If the ctor looked this up instead of using the given ids, we'd see these instead.
        ilChangeEvent::$get_all_user_ids_results = [777 => [999]];

        $sync = new ilMumieTaskGradeSync(new ilMumieTaskGradeSyncTestTask(100, 777), false, [7, 8]);

        $this->assertSame([7, 8], $this->getUserIds($sync));
    }

    public function testConstructorFallsBackToAllMemberIdsWhenUserIdsOmitted()
    {
        global $DIC;
        $DIC = new ilMumieTaskGradeSyncFakeDic(new ilMumieTaskGradeSyncFakeDb(), new ilMumieTaskGradeSyncFakeTree());
        ilChangeEvent::$get_all_user_ids_results = [777 => [11, 22]];

        $sync = new ilMumieTaskGradeSync(new ilMumieTaskGradeSyncTestTask(100, 777), false);

        $this->assertSame([11, 22], $this->getUserIds($sync));
    }

    private function getUserIds(ilMumieTaskGradeSync $sync): array
    {
        $property = new ReflectionProperty($sync, 'user_ids');
        $property->setAccessible(true);

        return $property->getValue($sync);
    }

    public function testGetValidAndNewXapiGradesForUserReturnsGradeWhenPresent()
    {
        $grade = (object) ['result' => (object) ['score' => (object) ['scaled' => 0.8]]];
        $sync = new ilMumieTaskGradeSyncTestDouble([42 => $grade]);

        $this->assertSame($grade, $sync->getValidAndNewXapiGradesForUser(42));
    }

    public function testGetValidAndNewXapiGradesForUserReturnsNullWhenUserHasNoNewGrade()
    {
        $sync = new ilMumieTaskGradeSyncTestDouble([]);

        $this->assertNullWithoutPhpWarning($sync, 42);
    }

    public function testGetValidAndNewXapiGradesForUserReturnsNullWhenOnlyOtherUsersHaveNewGrades()
    {
        $othersGrade = (object) ['result' => (object) ['score' => (object) ['scaled' => 0.5]]];
        $sync = new ilMumieTaskGradeSyncTestDouble([7 => $othersGrade]);

        $this->assertNullWithoutPhpWarning($sync, 42);
    }

    /**
     * Plain assertNull() alone wouldn't catch a regression to the old `$grades_by_user[$user_id]`
     * (no `?? null`): PHP resolves a missing array key to null either way, it just also raises a
     * warning along the way. That warning is the actual bug (it fed into upsertXapiGrade() as if
     * it were a real, empty grade), so assert on its absence directly instead of relying on a
     * fail-on-warning PHPUnit setting the plugin's own test run may not enable.
     */
    private function assertNullWithoutPhpWarning(ilMumieTaskGradeSync $sync, $user_id): void
    {
        $triggered = [];
        set_error_handler(function (int $errno, string $errstr) use (&$triggered) {
            $triggered[] = "$errno: $errstr";

            return true;
        });
        $result = $sync->getValidAndNewXapiGradesForUser($user_id);
        restore_error_handler();

        $this->assertSame([], $triggered, 'Expected no PHP warning/notice, got: ' . implode(', ', $triggered));
        $this->assertNull($result);
    }
}
