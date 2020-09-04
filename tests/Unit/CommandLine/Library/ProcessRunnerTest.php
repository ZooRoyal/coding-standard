<?php

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\Library;

use Hamcrest\MatcherAssert;
use Hamcrest\Matchers;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Zooroyal\CodingStandard\CommandLine\Library\ProcessRunner;

class ProcessRunnerTest extends TestCase
{
    /** @var ProcessRunner */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new ProcessRunner();
    }

    /**
     * @test
     */
    public function runAsProcess()
    {
        $result = $this->subject->runAsProcess('ls');
        self::assertIsString($result);
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

    /**
     * @test
     */
    public function runAsProcessReturningProcessObjectWithArgumentsInjection()
    {
        $this->expectException(ProcessFailedException::class);
        $this->subject->runAsProcess('git', 'version\'; ls');
    }

    /**
     * @test
     */
    public function runProcessWithArguments()
    {
        $result = $this->subject->runAsProcess('git', 'version');

        MatcherAssert::assertThat($result, Matchers::startsWith('git version'));
        self::assertTrue(true);
    }

    /**
     * @test
     */
    public function runProcessWithArgumentsInjection()
    {
        $this->expectException(ProcessFailedException::class);
        $this->subject->runAsProcess('git', 'version\'; ls');
    }
}
