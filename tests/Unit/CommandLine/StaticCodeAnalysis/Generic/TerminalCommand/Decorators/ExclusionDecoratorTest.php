<?php

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Decorators;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Zooroyal\CodingStandard\CommandLine\Factories\ExclusionListFactory;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\AbstractToolCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Decorators\ExclusionDecorator;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Decorators\TerminalCommandDecorator;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\ExcludingTerminalCommand;
use Zooroyal\CodingStandard\CommandLine\ValueObjects\EnhancedFileInfo;
use Zooroyal\CodingStandard\Tests\Tools\SubjectFactory;

class ExclusionDecoratorTest extends TestCase
{
    private ExclusionDecorator $subject;
    /** @var MockInterface[] */
    private array $subjectParameters;
    /** @var MockInterface|ExcludingTerminalCommand */
    private ExcludingTerminalCommand $mockedTerminalCommand;
    /** @var MockInterface|GenericEvent */
    private GenericEvent $mockedEvent;
    /** @var MockInterface|InputInterface */
    private InputInterface $mockedInput;
    /** @var MockInterface|OutputInterface */
    private OutputInterface $mockedOutput;

    protected function setUp(): void
    {
        $this->mockedEvent = Mockery::mock(GenericEvent::class);
        $this->mockedTerminalCommand = Mockery::mock(ExcludingTerminalCommand::class);
        $this->mockedInput = Mockery::mock(InputInterface::class);
        $this->mockedOutput = Mockery::mock(OutputInterface::class);

        $this->mockedEvent->shouldReceive('getArgument')
            ->with(AbstractToolCommand::KEY_INPUT)->andReturn($this->mockedInput);
        $this->mockedEvent->shouldReceive('getArgument')
            ->with(AbstractToolCommand::KEY_OUTPUT)->andReturn($this->mockedOutput);

        $subjectFactory = new SubjectFactory();
        $buildFragments = $subjectFactory->buildSubject(ExclusionDecorator::class);
        $this->subject = $buildFragments['subject'];
        $this->subjectParameters = $buildFragments['parameters'];
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    /**
     * @test
     */
    public function decorateAddsExclusionToTerminalCommand()
    {
        $forgedMockedRealPath = 'wubwub';
        $forgedToken = '.asdasdqweqwe';
        $mockedEnhancedFileInfo = Mockery::mock(EnhancedFileInfo::class);
        $mockedExclusionList = [$mockedEnhancedFileInfo];

        $this->mockedEvent->shouldReceive('getSubject')->atLeast()->once()->andReturn($this->mockedTerminalCommand);
        $this->mockedEvent->shouldReceive('getArgument')->atLeast()->once()
            ->with(AbstractToolCommand::KEY_EXCLUSION_LIST_TOKEN)->andReturn($forgedToken);

        $this->subjectParameters[ExclusionListFactory::class]->shouldReceive('build')->once()
            ->with($forgedToken)->andReturn($mockedExclusionList);

        $this->mockedOutput->shouldReceive('writeln')->once()
            ->with('<info>Following Paths will be excluded</info>', OutputInterface::VERBOSITY_VERBOSE);
        $mockedEnhancedFileInfo->shouldReceive('getRealPath')->atLeast()->once()->andReturn($forgedMockedRealPath);
        $this->mockedOutput->shouldReceive('writeln')->once()
            ->with($forgedMockedRealPath, OutputInterface::VERBOSITY_VERBOSE);
        $this->mockedOutput->shouldReceive('writeln')->once()
            ->with('', OutputInterface::VERBOSITY_VERBOSE);

        $this->mockedTerminalCommand->shouldReceive('addExclusions')->once()->with($mockedExclusionList);

        $this->subject->decorate($this->mockedEvent);
    }

    /**
     * @test
     */
    public function decorateShouldNotReactToOtherTerminalCommands()
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

        $result = ExclusionDecorator::getSubscribedEvents();

        self::assertSame($expectedEvents, $result);
    }
}
