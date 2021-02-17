<?php

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\Library;

use Hamcrest\MatcherAssert;
use Hamcrest\Matchers;
use Mockery;
use PackageVersions\Versions;
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

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
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

    public function runAsProcessReturningProcessObjectIsVersionStableDataProvider(): array
    {
        return [
            'as String' => ['v3.5@wubwub', 'ls -la', 'ls -la'],
            'as Array' => ['v5.5@wubwub', 'ls -la', ['ls', '-la']],
        ];
    }

    /**
     * @test
     * @dataProvider runAsProcessReturningProcessObjectIsVersionStableDataProvider
     *
     * @runInSeparateProcess
     * @preserveGlobalState  disabled
     *
     * @param string $versionString
     * @param string $commandInput
     * @param mixed  $commandOutput
     */
    public function runAsProcessReturningProcessObjectIsVersionStable(
        string $versionString,
        string $commandInput,
        $commandOutput
    ): void {
        $overwrittenVersions = Mockery::mock('overload:' . Versions::class);
        $overwrittenProcess = Mockery::mock('overload:' . Process::class);

        $overwrittenProcess->shouldReceive('setTimeout')->once()->with(null);
        $overwrittenProcess->shouldReceive('setIdleTimeout')->once()->with(60);
        $overwrittenProcess->shouldReceive('run')->once();

        $overwrittenVersions->shouldReceive('getVersion')->once()
            ->with('symfony/process')->andReturn($versionString);
        $overwrittenProcess->shouldReceive('__construct')->once()->with($commandOutput);

        $result = $this->subject->runAsProcessReturningProcessObject($commandInput);

        self::assertInstanceOf(Process::class, $result);
    }

    public function runAsProcessIsVersionStableDataProvider(): array
    {
        return [
            'as String' => ['v3.5@wubwub', 'ls', '-l', '-a', 'ls -l -a'],
            'as Array' => ['v5.5@wubwub', 'ls', '-l', '-a', ['ls', '-l', '-a']],
        ];
    }

    /**
     * @test
     * @dataProvider runAsProcessIsVersionStableDataProvider
     *
     * @runInSeparateProcess
     * @preserveGlobalState  disabled
     *
     * @param string $versionString
     * @param string $commandInput
     * @param string $commandArgument1
     * @param string $commandArgument2
     * @param mixed  $commandOutput
     */
    public function runAsProcessIsVersionStable(
        string $versionString,
        string $commandInput,
        string $commandArgument1,
        string $commandArgument2,
        $commandOutput
    ): void {
        $expectedOutput = 'schlurbel';
        $expectedError = 'wurbel';
        $overwrittenVersions = Mockery::mock('overload:' . Versions::class);
        $overwrittenProcess = Mockery::mock('overload:' . Process::class);

        $overwrittenVersions->shouldReceive('getVersion')->once()
            ->with('symfony/process')->andReturn($versionString);

        $overwrittenProcess->shouldReceive('__construct')->once()->with($commandOutput);
        $overwrittenProcess->shouldReceive('mustRun')->once()->withNoArgs();
        $overwrittenProcess->shouldReceive('setIdleTimeout')->once()->with(60);
        $overwrittenProcess->shouldReceive('setTimeout')->once()->with(null);
        $overwrittenProcess->shouldReceive('getOutput')->once()->withNoArgs()->andReturn($expectedOutput);
        $overwrittenProcess->shouldReceive('getErrorOutput')->once()->withNoArgs()->andReturn($expectedError);

        $result = $this->subject->runAsProcess($commandInput, $commandArgument1, $commandArgument2);

        self::assertSame($expectedOutput . PHP_EOL . $expectedError, $result);
    }
}
