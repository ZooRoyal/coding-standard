<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Multiprocess;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\Process\ProcessRunner;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\DecorateEvent;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Multiprocess\MultiprocessDecorator;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Multiprocess\MultiprocessTerminalCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\TerminalCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\TerminalCommandDecorator;

class MultiprocessDecoratorTest extends TestCase
{
    /** @var MockInterface|DecorateEvent */
    private DecorateEvent $mockedEvent;
    /** @var MockInterface|\Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Multiprocess\MultiprocessTerminalCommand */
    private MultiprocessTerminalCommand $mockedTerminalCommand;
    /** @var MockInterface|OutputInterface */
    private OutputInterface $mockedOutput;
    /** @var MockInterface|\Zooroyal\CodingStandard\CommandLine\Process\ProcessRunner */
    private ProcessRunner $mockedProcessRunner;
    private MultiprocessDecorator $subject;

    protected function setUp(): void
    {
        $this->mockedEvent = Mockery::mock(DecorateEvent::class);
        $this->mockedTerminalCommand = Mockery::mock(MultiprocessTerminalCommand::class);
        $this->mockedOutput = Mockery::mock(OutputInterface::class);

        $this->mockedEvent->shouldReceive('getOutput')->andReturn($this->mockedOutput);

        $this->mockedProcessRunner = Mockery::mock(ProcessRunner::class);

        $this->subject = new MultiprocessDecorator($this->mockedProcessRunner);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    /**
     * @test
     */
    public function decorateAddsFixingFlagToTerminalCommandIfTrue(): void
    {
        $forgedCpuCores = '23';
        $this->mockedEvent->shouldReceive('getTerminalCommand')->atLeast()->once()->andReturn($this->mockedTerminalCommand);

        $this->mockedProcessRunner->shouldReceive('runAsProcess')->once()
            ->with('getconf _NPROCESSORS_ONLN')->andReturn($forgedCpuCores);

        $this->mockedOutput->shouldReceive('writeln')->once()
            ->with(
                '<info>Command can use ' . $forgedCpuCores . ' processes</info>' . PHP_EOL,
                OutputInterface::VERBOSITY_VERY_VERBOSE,
            );

        $this->mockedTerminalCommand->shouldReceive('setMaximalConcurrentProcesses')->once()->with($forgedCpuCores);

        $this->subject->decorate($this->mockedEvent);
    }

    /**
     * @test
     */
    public function decorateShouldNotReactToOtherTerminalCommands(): void
    {
        $mockedTerminalCommand = Mockery::mock(TerminalCommand::class);
        $this->mockedEvent->shouldReceive('getTerminalCommand')->atLeast()->once()->andReturn($mockedTerminalCommand);

        $this->mockedTerminalCommand->shouldReceive('setMaximalConcurrentProcesses')->never();

        $this->subject->decorate($this->mockedEvent);
    }

    /**
     * @test
     */
    public function getSubscribedEventsReturnsExpectedEvents(): void
    {
        $expectedEvents = [TerminalCommandDecorator::EVENT_DECORATE_TERMINAL_COMMAND => ['decorate', 50]];

        $result = MultiprocessDecorator::getSubscribedEvents();

        self::assertSame($expectedEvents, $result);
    }
}
