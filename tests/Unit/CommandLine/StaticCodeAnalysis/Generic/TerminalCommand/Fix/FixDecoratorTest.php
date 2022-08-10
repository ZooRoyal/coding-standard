<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Fix;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\DecorateEvent;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Fix\FixDecorator;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Fix\FixTerminalCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\TerminalCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\TerminalCommandDecorator;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\ToolCommandFacet\FixableInputFacet;

class FixDecoratorTest extends TestCase
{
    private FixDecorator $subject;
    /** @var MockInterface|\Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Fix\FixTerminalCommand */
    private FixTerminalCommand $mockedTerminalCommand;
    /** @var MockInterface|DecorateEvent */
    private DecorateEvent $mockedEvent;
    /** @var MockInterface|InputInterface */
    private InputInterface $mockedInput;
    /** @var MockInterface|OutputInterface */
    private OutputInterface $mockedOutput;

    protected function setUp(): void
    {
        $this->mockedEvent = Mockery::mock(DecorateEvent::class);
        $this->mockedTerminalCommand = Mockery::mock(FixTerminalCommand::class);
        $this->mockedInput = Mockery::mock(InputInterface::class);
        $this->mockedOutput = Mockery::mock(OutputInterface::class);

        $this->mockedEvent->shouldReceive('getInput')->andReturn($this->mockedInput);
        $this->mockedEvent->shouldReceive('getOutput')->andReturn($this->mockedOutput);

        $this->subject = new FixDecorator();
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
        $this->mockedEvent->shouldReceive('getTerminalCommand')->atLeast()->once()->andReturn(
            $this->mockedTerminalCommand
        );
        $this->mockedInput->shouldReceive('getOption')->once()->with(FixableInputFacet::OPTION_FIX)->andReturn(true);

        $this->mockedOutput->shouldReceive('writeln')->once()
            ->with('<info>Command will run in fixing mode.</info>' . PHP_EOL);

        $this->mockedTerminalCommand->shouldReceive('setFixingMode')->once()->with(true);

        $this->subject->decorate($this->mockedEvent);
    }

    /**
     * @test
     */
    public function decorateNotChangeFixingFlagIfFalse(): void
    {
        $this->mockedEvent->shouldReceive('getTerminalCommand')->atLeast()->once()->andReturn(
            $this->mockedTerminalCommand
        );
        $this->mockedInput->shouldReceive('getOption')->once()->with(FixableInputFacet::OPTION_FIX)->andReturn(false);

        $this->mockedOutput->shouldReceive('writeln')->never();

        $this->mockedTerminalCommand->shouldReceive('setFixingMode')->never();

        $this->subject->decorate($this->mockedEvent);
    }

    /**
     * @test
     */
    public function decorateShouldNotReactToOtherTerminalCommands(): void
    {
        $mockedTerminalCommand = Mockery::mock(TerminalCommand::class);
        $this->mockedEvent->shouldReceive('getTerminalCommand')->atLeast()->once()->andReturn($mockedTerminalCommand);

        $this->mockedTerminalCommand->shouldReceive('addExclusions')->never();

        $this->subject->decorate($this->mockedEvent);
    }

    /**
     * @test
     */
    public function getSubscribedEventsReturnsExpectedEvents(): void
    {
        $expectedEvents = [TerminalCommandDecorator::EVENT_DECORATE_TERMINAL_COMMAND => ['decorate', 50]];

        $result = FixDecorator::getSubscribedEvents();

        self::assertSame($expectedEvents, $result);
    }
}
