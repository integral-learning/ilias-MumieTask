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

    public function testHasTimelimitIsTrueForPositiveValue()
    {
        $task = new ilObjMumieTask();
        $task->setTimelimit(3600);
        $this->assertTrue($task->hasTimelimit());
    }

    public function testHasTimelimitIsFalseForZero()
    {
        $task = new ilObjMumieTask();
        $task->setTimelimit(0);
        $this->assertFalse($task->hasTimelimit());
    }

    public function testHasTimelimitIsFalseForNull()
    {
        $task = new ilObjMumieTask();
        $task->setTimelimit(null);
        $this->assertFalse($task->hasTimelimit());
    }

    public function testRequiresDeadlineSignatureIsFalseForPlainProblemWithDeadline()
    {
        $task = new ilObjMumieTask();
        $task->setTaskurl('some/plain/problem/path');
        $task->setDeadline(time() + 3600);
        $this->assertFalse($task->requiresDeadlineSignature());
    }

    public function testRequiresDeadlineSignatureIsFalseForWorksheetWithoutDeadlineOrTimelimit()
    {
        $task = new ilObjMumieTask();
        $task->setTaskurl('worksheet_14981');
        $this->assertFalse($task->requiresDeadlineSignature());
    }

    public function testRequiresDeadlineSignatureIsTrueForWorksheetWithFixedDeadline()
    {
        $task = new ilObjMumieTask();
        $task->setTaskurl('worksheet_14981');
        $task->setDeadline(time() + 3600);
        $this->assertTrue($task->requiresDeadlineSignature());
    }

    public function testRequiresDeadlineSignatureIsTrueForWorksheetWithTimelimit()
    {
        $task = new ilObjMumieTask();
        $task->setTaskurl('worksheet_14981');
        $task->setTimelimit(3600);
        $this->assertTrue($task->requiresDeadlineSignature());
    }

    public function testHasAnyDeadlineIsTrueForPlainProblemWithFixedDeadline()
    {
        $task = new ilObjMumieTask();
        $task->setTaskurl('some/plain/problem/path');
        $task->setDeadline(time() + 3600);
        $this->assertTrue($task->hasAnyDeadline());
    }

    public function testHasAnyDeadlineIsTrueForPlainProblemWithTimelimit()
    {
        $task = new ilObjMumieTask();
        $task->setTaskurl('some/plain/problem/path');
        $task->setTimelimit(3600);
        $this->assertTrue($task->hasAnyDeadline());
    }

    public function testHasAnyDeadlineIsFalseWithoutDeadlineOrTimelimit()
    {
        $task = new ilObjMumieTask();
        $task->setTaskurl('some/plain/problem/path');
        $this->assertFalse($task->hasAnyDeadline());
    }
}
