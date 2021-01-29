<?php

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\Library;

use Mockery;
use Mockery\MockInterface;
use Amp\PHPUnit\AsyncTestCase;
use Symfony\Component\Process\Process;
use Zooroyal\CodingStandard\CommandLine\Library\Exceptions\TerminalCommandNotFoundException;
use Zooroyal\CodingStandard\CommandLine\Library\ProcessRunner;
use Zooroyal\CodingStandard\CommandLine\Library\TerminalCommandFinder;
use Zooroyal\CodingStandard\Tests\Tools\SubjectFactory;

class TerminalCommandFinderTest extends AsyncTestCase
{
    /** @var TerminalCommandFinder */
    private $subject;
    /** @var MockInterface[] */
    private $subjectParameters;

    protected function setUp(): void
    {
        parent::setUp();
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
    public function findTerminalCommandReturnsCommandIfFound()
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
    public function findTerminalCommandThrowsExceptionIfCommandNotFound()
    {
        $forgedCommand = 'bnlablalbal';
        $mockedProcess = Mockery::mock(Process::class);

        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcessReturningProcessObject')
            ->once()->with('npx --no-install ' . $forgedCommand . ' --help')->andReturn($mockedProcess);

        $mockedProcess->shouldReceive('getExitCode')->once()->withNoArgs()->andReturn(127);

        $this->expectException(TerminalCommandNotFoundException::class);
        $this->expectExceptionCode(1595949828);

        $this->subject->findTerminalCommand($forgedCommand);
    }
}
