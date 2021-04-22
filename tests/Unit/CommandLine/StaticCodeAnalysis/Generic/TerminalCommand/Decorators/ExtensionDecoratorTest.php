<?php

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Decorators;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\AbstractToolCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Decorators\ExtensionDecorator;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Decorators\TerminalCommandDecorator;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\FileExtensionTerminalCommand;

class ExtensionDecoratorTest extends TestCase
{
    private ExtensionDecorator $subject;
    /** @var MockInterface|FileExtensionTerminalCommand */
    private FileExtensionTerminalCommand $mockedTerminalCommand;
    /** @var MockInterface|GenericEvent */
    private GenericEvent $mockedEvent;
    /** @var MockInterface|OutputInterface */
    private OutputInterface $mockedOutput;

    protected function setUp(): void
    {
        $this->mockedEvent = Mockery::mock(GenericEvent::class);
        $this->mockedTerminalCommand = Mockery::mock(FileExtensionTerminalCommand::class);
        $this->mockedOutput = Mockery::mock(OutputInterface::class);

        $this->mockedEvent->shouldReceive('getArgument')
            ->with(AbstractToolCommand::KEY_OUTPUT)->andReturn($this->mockedOutput);

        $this->subject = new ExtensionDecorator();
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

        $this->mockedEvent->shouldReceive('getSubject')->atLeast()->once()->andReturn($this->mockedTerminalCommand);
        $this->mockedEvent->shouldReceive('getArgument')->atLeast()->once()
            ->with(AbstractToolCommand::KEY_ALLOWED_FILE_ENDINGS)->andReturn($forgedAllowedFileEndings);

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

        $result = ExtensionDecorator::getSubscribedEvents();

        self::assertSame($expectedEvents, $result);
    }
}
