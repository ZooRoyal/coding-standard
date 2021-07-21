<?php

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Decorators;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\AbstractToolCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Decorators\VerbosityDecorator;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\VerboseTerminalCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommandDecorator;

class VerbosityDecoratorTest extends TestCase
{
    private VerbosityDecorator $subject;
    /** @var MockInterface|VerboseTerminalCommand */
    private VerboseTerminalCommand $mockedTerminalCommand;
    /** @var MockInterface|GenericEvent */
    private GenericEvent $mockedEvent;
    /** @var MockInterface|InputInterface */
    private InputInterface $mockedInput;
    /** @var MockInterface|OutputInterface */
    private OutputInterface $mockedOutput;

    protected function setUp(): void
    {
        $this->mockedEvent = Mockery::mock(GenericEvent::class);
        $this->mockedTerminalCommand = Mockery::mock(VerboseTerminalCommand::class);
        $this->mockedInput = Mockery::mock(InputInterface::class);
        $this->mockedOutput = Mockery::mock(OutputInterface::class);

        $this->mockedEvent->shouldReceive('getArgument')
            ->with(AbstractToolCommand::KEY_INPUT)->andReturn($this->mockedInput);
        $this->mockedEvent->shouldReceive('getArgument')
            ->with(AbstractToolCommand::KEY_OUTPUT)->andReturn($this->mockedOutput);

        $this->subject = new VerbosityDecorator();
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    /**
     * @test
     * @dataProvider decoradeAddsVerboseFlagIfApplicableDataProvider
     *
     * @param bool $isVerbose
     * @param bool $isQuiet
     * @param int  $verboseRuns
     * @param int  $quietRuns
     */
    public function decoradeAddsVerboseFlagIfApplicable(
        bool $isVerbose,
        bool $isQuiet,
        int $verboseRuns,
        int $quietRuns
    ): void {
        $this->mockedEvent->shouldReceive('getSubject')->atLeast()->once()->andReturn($this->mockedTerminalCommand);

        $this->mockedInput->shouldReceive('getOption')->atLeast()->once()->with('verbose')->andReturn($isVerbose);
        $this->mockedInput->shouldReceive('getOption')->times($quietRuns)->with('quiet')->andReturn($isQuiet);

        $this->mockedTerminalCommand->shouldReceive('addVerbosityLevel')->times($verboseRuns)
            ->with(OutputInterface::VERBOSITY_VERBOSE);
        $this->mockedTerminalCommand->shouldReceive('addVerbosityLevel')->times($quietRuns)
            ->with(OutputInterface::VERBOSITY_QUIET);

        $this->mockedOutput->shouldReceive('writeln')->times($verboseRuns)
            ->with('<info>Command will be executed verbosely</info>' . PHP_EOL, OutputInterface::VERBOSITY_VERBOSE);

        $this->subject->decorate($this->mockedEvent);
    }

    public function decoradeAddsVerboseFlagIfApplicableDataProvider(): array
    {
        return [
            'verbose' => [true, false, 1, 0],
            'quiet' => [false, true, 0, 1],
            'booth' => [true, true, 1, 0],
        ];
    }

    /**
     * @test
     */
    public function decorateShouldNotReactToOtherTerminalCommands(): void
    {
        $mockedTerminalCommand = Mockery::mock(TerminalCommandDecorator::class);
        $this->mockedEvent->shouldReceive('getSubject')->atLeast()->once()->andReturn($mockedTerminalCommand);

        $this->mockedTerminalCommand->shouldReceive('addExclusions')->never();

        $this->subject->decorate($this->mockedEvent);
    }

    /**
     * @test
     */
    public function getSubscribedEventsReturnsExpectedEvents(): void
    {
        $expectedEvents = [AbstractToolCommand::EVENT_DECORATE_TERMINAL_COMMAND => ['decorate', 50]];

        $result = VerbosityDecorator::getSubscribedEvents();

        self::assertSame($expectedEvents, $result);
    }
}
