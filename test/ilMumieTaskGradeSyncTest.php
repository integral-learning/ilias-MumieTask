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

class ilMumieTaskGradeSyncTest extends TestCase
{
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
            $triggered[] = $errstr;

            return true;
        });
        $result = $sync->getValidAndNewXapiGradesForUser($user_id);
        restore_error_handler();

        $this->assertSame([], $triggered, 'Expected no PHP warning/notice, got: ' . implode(', ', $triggered));
        $this->assertNull($result);
    }
}
