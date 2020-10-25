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

    public function setUp()
    {
        $subjectFactory = new SubjectFactory(TerminalCommandFinder::class);
        $this->subjectParameters = $subjectFactory->buildParameters();
        $this->subject = $subjectFactory->buildSubjectInstance($this->subjectParameters);
    }

    protected function tearDown()
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
