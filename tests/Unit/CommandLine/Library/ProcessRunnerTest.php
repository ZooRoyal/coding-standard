<?php
namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\Library;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;
use Zooroyal\CodingStandard\CommandLine\Library\ProcessRunner;

class ProcessRunnerTest extends TestCase
{
    /** @var ProcessRunner */
    private $subject;

    protected function setUp()
    {
        $this->subject = new ProcessRunner();
    }

    /**
     * @test
     */
    public function runAsProcess()
    {
        $result = $this->subject->runAsProcess('ls');

        self::assertInternalType('string', $result);
    }

    /**
     * @test
     */
    public function runAsProcessReturningProcessObject()
    {
        $expectedResult = $this->subject->runAsProcess('ls');

        $result = $this->subject->runAsProcessReturningProcessObject('ls');

        self::assertInstanceOf(Process::class, $result);
        self::assertSame($expectedResult, trim($result->getOutput()));
    }
}
