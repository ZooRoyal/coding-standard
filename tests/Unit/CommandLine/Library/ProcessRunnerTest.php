<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\Library;

use Hamcrest\MatcherAssert;
use Hamcrest\Matchers;
use Mockery;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Zooroyal\CodingStandard\CommandLine\Library\ProcessRunner;

class ProcessRunnerTest extends TestCase
{
    private ProcessRunner $subject;

    protected function setUp(): void
    {
        $this->subject = new ProcessRunner();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @test
     *
     * @runInSeparateProcess
     * @preserveGlobalState  disabled
     */
    public function createProcessCreatesNewProcess(): void
    {
        $overwrittenProcess = Mockery::mock('overload:' . Process::class);

        $overwrittenProcess->shouldReceive('__construct')->once()->with(['ls']);
        $overwrittenProcess->shouldReceive('setIdleTimeout')->once()->with(120);
        $overwrittenProcess->shouldReceive('setTimeout')->once()->with(null);

        $this->subject->createProcess('ls');
    }

    /**
     * @test
     */
    public function runAsProcess(): void
    {
        $result = $this->subject->runAsProcess('ls');
        self::assertIsString($result);
    }

    /**
     * @test
     *
     * @runInSeparateProcess
     * @preserveGlobalState  disabled
     */
    public function runAsProcessIsVersionStable(): void
    {
        $commandInput = 'ls';
        $commandArgument1 = '-l';
        $commandArgument2 = '-a';
        $commandOutput = ['ls', '-l', '-a'];

        $expectedOutput = 'schlurbel';
        $expectedError = 'wurbel';
        $overwrittenProcess = Mockery::mock('overload:' . Process::class);

        $overwrittenProcess->shouldReceive('__construct')->once()->with($commandOutput);
        $overwrittenProcess->shouldReceive('mustRun')->once()->withNoArgs();
        $overwrittenProcess->shouldReceive('setIdleTimeout')->once()->with(120);
        $overwrittenProcess->shouldReceive('setTimeout')->once()->with(null);
        $overwrittenProcess->shouldReceive('getOutput')->once()->withNoArgs()->andReturn($expectedOutput);
        $overwrittenProcess->shouldReceive('getErrorOutput')->once()->withNoArgs()->andReturn($expectedError);

        $result = $this->subject->runAsProcess($commandInput, $commandArgument1, $commandArgument2);

        self::assertSame($expectedOutput . PHP_EOL . $expectedError, $result);
    }

    /**
     * @test
     */
    public function runAsProcessReturningProcessObject(): void
    {
        $expectedResult = $this->subject->runAsProcess('ls');

        $result = $this->subject->runAsProcessReturningProcessObject('ls');

        self::assertInstanceOf(Process::class, $result);
        self::assertSame($expectedResult, trim($result->getOutput()));
    }

    /**
     * @test
     *
     * @runInSeparateProcess
     * @preserveGlobalState  disabled
     */
    public function runAsProcessReturningProcessObjectIsVersionStable(): void
    {
        $commandInput = 'ls -la';
        $commandOutput = ['ls', '-la'];

        $overwrittenProcess = Mockery::mock('overload:' . Process::class);

        $overwrittenProcess->shouldReceive('setTimeout')->once()->with(null);
        $overwrittenProcess->shouldReceive('setIdleTimeout')->once()->with(120);
        $overwrittenProcess->shouldReceive('run')->once();

        $overwrittenProcess->shouldReceive('__construct')->once()->with($commandOutput);

        $result = $this->subject->runAsProcessReturningProcessObject($commandInput);

        self::assertInstanceOf(Process::class, $result);
    }

    /**
     * @test
     */
    public function runAsProcessReturningProcessObjectWithArgumentsInjection(): void
    {
        $this->expectException(ProcessFailedException::class);
        $this->subject->runAsProcess('git', 'version\'; ls');
    }

    /**
     * @test
     */
    public function runProcessWithArguments(): void
    {
        $result = $this->subject->runAsProcess('git', 'version');

        MatcherAssert::assertThat($result, Matchers::startsWith('git version'));
        self::assertTrue(true);
    }

    /**
     * @test
     */
    public function runProcessWithArgumentsInjection(): void
    {
        $this->expectException(ProcessFailedException::class);
        $this->subject->runAsProcess('git', 'version\'; ls');
    }
}
