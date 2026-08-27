<?php

use PHPUnit\Framework\TestCase;

/**
 * Stands in for $DIC->repositoryTree(): canForParentType() results are supplied per
 * (ref_id, type) pair, mirroring how ilTree::checkForParentType() itself only ever
 * walks upward from a node (ancestors), never down to its descendants.
 */
class ilMumieTaskFakeTree
{
    private array $parent_by_ref_id_and_type;

    public function __construct(array $parent_by_ref_id_and_type)
    {
        $this->parent_by_ref_id_and_type = $parent_by_ref_id_and_type;
    }

    public function checkForParentType(int $a_ref_id, string $a_type, bool $a_exclude_source_check = false): int
    {
        return $this->parent_by_ref_id_and_type[$a_ref_id][$a_type] ?? 0;
    }
}

class ilMumieTaskFakeDIC
{
    private ilMumieTaskFakeTree $tree;

    public function __construct(ilMumieTaskFakeTree $tree)
    {
        $this->tree = $tree;
    }

    public function repositoryTree(): ilMumieTaskFakeTree
    {
        return $this->tree;
    }
}

class ilMumieTaskParticipantServiceTest extends TestCase
{
    private $original_dic;

    protected function setUp(): void
    {
        global $DIC;
        $this->original_dic = $DIC ?? null;
    }

    protected function tearDown(): void
    {
        global $DIC;
        $DIC = $this->original_dic;
    }

    public function testReturnsNullWhenNeitherCourseNorGroupIsAnAncestor()
    {
        $this->assertNull($this->findEnclosingCourseOrGroupRefId(100, []));
    }

    public function testReturnsCourseRefIdWhenOnlyACourseIsAnAncestor()
    {
        $ref_id = $this->findEnclosingCourseOrGroupRefId(100, [
            100 => ['crs' => 20],
        ]);

        $this->assertSame(20, $ref_id);
    }

    public function testReturnsGroupRefIdWhenOnlyAGroupIsAnAncestor()
    {
        $ref_id = $this->findEnclosingCourseOrGroupRefId(100, [
            100 => ['grp' => 30],
        ]);

        $this->assertSame(30, $ref_id);
    }

    public function testReturnsTheNestedCourseWhenAGroupEnclosesIt()
    {
        // root -> grp(10) -> crs(20) -> task(100): the course is the nearer container.
        $ref_id = $this->findEnclosingCourseOrGroupRefId(100, [
            100 => ['crs' => 20, 'grp' => 10],
            20 => ['grp' => 10],
        ]);

        $this->assertSame(20, $ref_id);
    }

    public function testReturnsTheNestedGroupWhenACourseEnclosesIt()
    {
        // root -> crs(30) -> grp(40) -> task(100): the group is the nearer container.
        $ref_id = $this->findEnclosingCourseOrGroupRefId(100, [
            100 => ['crs' => 30, 'grp' => 40],
            30 => ['grp' => 0],
        ]);

        $this->assertSame(40, $ref_id);
    }

    private function findEnclosingCourseOrGroupRefId(int $ref_id, array $parent_by_ref_id_and_type): ?int
    {
        global $DIC;
        $DIC = new ilMumieTaskFakeDIC(new ilMumieTaskFakeTree($parent_by_ref_id_and_type));

        $method = new ReflectionMethod(ilMumieTaskParticipantService::class, 'findEnclosingCourseOrGroupRefId');
        $method->setAccessible(true);

        return $method->invoke(null, $ref_id);
    }
}
