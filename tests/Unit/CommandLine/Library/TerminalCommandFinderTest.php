<?php

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\Library;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;
use Zooroyal\CodingStandard\CommandLine\Library\Exceptions\TerminalCommandNotFoundException;
use Zooroyal\CodingStandard\CommandLine\Library\ProcessRunner;
use Zooroyal\CodingStandard\CommandLine\Library\TerminalCommandFinder;
use Zooroyal\CodingStandard\Tests\Tools\SubjectFactory;

class TerminalCommandFinderTest extends TestCase
{
    /** @var TerminalCommandFinder */
    private $subject;
    /** @var MockInterface[] */
    private $subjectParameters;

    protected function setUp(): void
    {
        $subjectFactory = new SubjectFactory();
        $buildFragments = $subjectFactory->buildSubject(TerminalCommandFinder::class);
        $this->subject = $buildFragments['subject'];
        $this->subjectParameters = $buildFragments['parameters'];
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function findTerminalCommandReturnsCommandIfFound(): void
    {
        $forgedCommand = 'bnlablalbal';
        $expectedCommand = 'npx --no-install ' . $forgedCommand;
        $mockedProcess = Mockery::mock(Process::class);

        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcessReturningProcessObject')
            ->once()->with('npx --no-install ' . $forgedCommand . ' --help')->andReturn($mockedProcess);

        $mockedProcess->shouldReceive('getExitCode')->once()->withNoArgs()->andReturn(0);

        $result = $this->subject->findTerminalCommand($forgedCommand);

        self::assertSame($expectedCommand, $result);
    }

    /**
     * @test
     */
    public function findTerminalCommandThrowsExceptionIfCommandNotFound(): void
    {
        $forgedCommand = 'bnlablalbal';
        $mockedProcess = Mockery::mock(Process::class);

        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcessReturningProcessObject')
            ->once()->with('npx --no-install ' . $forgedCommand . ' --help')->andReturn($mockedProcess);

        $mockedProcess->shouldReceive('getExitCode')->once()->withNoArgs()->andReturn(127);

        $this->expectException(TerminalCommandNotFoundException::class);
        $this->expectExceptionCode(1595949828);
        $this->expectErrorMessageMatches('/^Bnlablalbal could not be found in path or by npm.*/');

        $this->subject->findTerminalCommand($forgedCommand);
    }
}
