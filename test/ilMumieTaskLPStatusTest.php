<?php

use PHPUnit\Framework\TestCase;

/**
 * Records manipulate() calls instead of touching a real database, so tests can assert on the
 * exact SQL deleteLPForTask() builds - that's the only way to catch a regression to the historic
 * bug where the ut_lp_marks DELETE quoted $task->getId() into the usr_id clause instead of
 * $user_id (silently deleting nothing, or the wrong row, for a single-user force-update).
 */
class ilMumieTaskLPStatusFakeDb
{
    public array $manipulated_queries = [];

    /**
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     */
    public function quote($value, $type)
    {
        return (string) $value;
    }

    public function manipulate($query)
    {
        $this->manipulated_queries[] = $query;

        return 0;
    }
}

class ilMumieTaskLPStatusFakeDic
{
    public function __construct(private $db)
    {
    }

    public function database()
    {
        return $this->db;
    }
}

class ilMumieTaskLPStatusTestTask
{
    public function __construct(private int $id)
    {
    }

    public function getId()
    {
        return $this->id;
    }
}

class ilMumieTaskLPStatusTest extends TestCase
{
    private $original_dic;

    protected function setUp(): void
    {
        global $DIC;
        $this->original_dic = $DIC ?? null;
        ilChangeEvent::$deleted_read_events_calls = [];
    }

    protected function tearDown(): void
    {
        global $DIC;
        $DIC = $this->original_dic;
    }

    public function testDeleteLPForTaskWithUserIdOnlyDeletesMarksForThatUser()
    {
        $db = $this->deleteLPForTask(999, 42);

        $this->assertCount(1, $db->manipulated_queries);
        $this->assertStringContainsString('obj_id = 999', $db->manipulated_queries[0]);
        $this->assertStringContainsString('usr_id = 42', $db->manipulated_queries[0]);
        $this->assertStringNotContainsString('usr_id = 999', $db->manipulated_queries[0]);
    }

    public function testDeleteLPForTaskWithUserIdOnlyDeletesReadEventsForThatUser()
    {
        $this->deleteLPForTask(999, 42);

        $this->assertSame([['users', 999, [42]]], ilChangeEvent::$deleted_read_events_calls);
    }

    public function testDeleteLPForTaskWithoutUserIdDeletesMarksForTheWholeTask()
    {
        $db = $this->deleteLPForTask(999);

        $this->assertStringContainsString('obj_id = 999', $db->manipulated_queries[0]);
        $this->assertStringNotContainsString('usr_id', $db->manipulated_queries[0]);
    }

    public function testDeleteLPForTaskWithoutUserIdDeletesReadEventsForTheWholeTask()
    {
        $this->deleteLPForTask(999);

        $this->assertSame([['all', 999]], ilChangeEvent::$deleted_read_events_calls);
    }

    private function deleteLPForTask(int $task_id, int $user_id = 0): ilMumieTaskLPStatusFakeDb
    {
        global $DIC;
        $db = new ilMumieTaskLPStatusFakeDb();
        $DIC = new ilMumieTaskLPStatusFakeDic($db);

        $method = new ReflectionMethod(ilMumieTaskLPStatus::class, 'deleteLPForTask');
        $method->setAccessible(true);
        $method->invoke(null, new ilMumieTaskLPStatusTestTask($task_id), $user_id);

        return $db;
    }
}
