<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Extension;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\DecorateEvent;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Extension\FileExtensionDecorator;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Extension\FileExtensionTerminalCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\TerminalCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\TerminalCommandDecorator;

class ExtensionDecoratorTest extends TestCase
{
    private FileExtensionDecorator $subject;
    /** @var MockInterface|FileExtensionTerminalCommand */
    private FileExtensionTerminalCommand $mockedTerminalCommand;
    /** @var MockInterface|DecorateEvent */
    private DecorateEvent $mockedEvent;
    /** @var MockInterface|OutputInterface */
    private OutputInterface $mockedOutput;

    protected function setUp(): void
    {
        $this->mockedEvent = Mockery::mock(DecorateEvent::class);
        $this->mockedTerminalCommand = Mockery::mock(FileExtensionTerminalCommand::class);
        $this->mockedOutput = Mockery::mock(OutputInterface::class);

        $this->mockedEvent->shouldReceive('getOutput')->andReturn($this->mockedOutput);

        $this->subject = new FileExtensionDecorator();
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    /**
     * @test
     */
    public function decorateAddsAllowedFileExtensionsToTerminalCommand(): void
    {
        $forgedAllowedFileEndings = ['asd', 'qwe'];

        $this->mockedEvent->shouldReceive('getTerminalCommand')->atLeast()->once()->andReturn($this->mockedTerminalCommand);
        $this->mockedEvent->shouldReceive('getAllowedFileEndings')->atLeast()->once()->andReturn($forgedAllowedFileEndings);

        $this->mockedOutput->shouldReceive('writeln')->once()
            ->with(
                '<info>Command will only check files with following extensions</info>',
                OutputInterface::VERBOSITY_VERBOSE
            );
        $this->mockedOutput->shouldReceive('writeln')->once()
            ->with(implode(' ', $forgedAllowedFileEndings) . PHP_EOL, OutputInterface::VERBOSITY_VERBOSE);

        $this->mockedTerminalCommand->shouldReceive('addAllowedFileExtensions')->once()
            ->with($forgedAllowedFileEndings);

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

        $result = FileExtensionDecorator::getSubscribedEvents();

        self::assertSame($expectedEvents, $result);
    }
}
