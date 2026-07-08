<?php

use PHPUnit\Framework\TestCase;

class ilObjMumieTaskTest extends TestCase
{
    public function testIsWorksheetIsTrueForWorksheetPrefix()
    {
        $task = new ilObjMumieTask();
        $task->setTaskurl('worksheet_14981');
        $this->assertTrue($task->isWorksheet());
    }

    public function testIsWorksheetIsFalseForPlainProblem()
    {
        $task = new ilObjMumieTask();
        $task->setTaskurl('some/plain/problem/path');
        $this->assertFalse($task->isWorksheet());
    }

    public function testIsWorksheetIsFalseForEmptyTaskurl()
    {
        $task = new ilObjMumieTask();
        $task->setTaskurl('');
        $this->assertFalse($task->isWorksheet());
    }

    public function testIsWorksheetIsFalseForNullTaskurl()
    {
        $task = new ilObjMumieTask();
        $task->setTaskurl(null);
        $this->assertFalse($task->isWorksheet());
    }

    public function testGetWorksheetIdStripsPrefix()
    {
        $task = new ilObjMumieTask();
        $task->setTaskurl('worksheet_14981');
        $this->assertEquals('14981', $task->getWorksheetId());
    }

    public function testGetWorksheetIdIsNullForPlainProblem()
    {
        $task = new ilObjMumieTask();
        $task->setTaskurl('some/plain/problem/path');
        $this->assertNull($task->getWorksheetId());
    }

    public function testIsWorksheetIsFalseForPrefixOnlyString()
    {
        $task = new ilObjMumieTask();
        $task->setTaskurl('worksheet_');
        $this->assertTrue($task->isWorksheet());
        $this->assertEquals('', $task->getWorksheetId());
    }
}
