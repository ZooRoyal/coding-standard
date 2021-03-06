<?php

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Decorators;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Zooroyal\CodingStandard\CommandLine\FileFinders\AdaptableFileFinder;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\AbstractToolCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Decorators\TargetDecorator;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Decorators\TerminalCommandDecorator;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\TargetableTerminalCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\ToolCommandFacet\TargetableInputFacet;
use Zooroyal\CodingStandard\CommandLine\ValueObjects\EnhancedFileInfo;
use Zooroyal\CodingStandard\CommandLine\ValueObjects\GitChangeSet;
use Zooroyal\CodingStandard\Tests\Tools\SubjectFactory;

class TargetDecoratorTest extends TestCase
{
    private TargetDecorator $subject;
    /** @var MockInterface[] */
    private array $subjectParameters;
    /** @var MockInterface|TargetableTerminalCommand */
    private TargetableTerminalCommand $mockedTerminalCommand;
    /** @var MockInterface|GenericEvent */
    private GenericEvent $mockedEvent;
    /** @var MockInterface|InputInterface */
    private InputInterface $mockedInput;
    /** @var MockInterface|OutputInterface */
    private OutputInterface $mockedOutput;
    /** @var array<string> */
    private array $forgedAllowedFileEndings = ['asd', 'qwe'];
    private string $forgedExclusionListToken = 'uptiwubti';

    protected function setUp(): void
    {
        $this->mockedEvent = Mockery::mock(GenericEvent::class);
        $this->mockedTerminalCommand = Mockery::mock(TargetableTerminalCommand::class);
        $this->mockedInput = Mockery::mock(InputInterface::class);
        $this->mockedOutput = Mockery::mock(OutputInterface::class);

        $this->mockedEvent->shouldReceive('getArgument')
            ->with(AbstractToolCommand::KEY_INPUT)->andReturn($this->mockedInput);
        $this->mockedEvent->shouldReceive('getArgument')
            ->with(AbstractToolCommand::KEY_OUTPUT)->andReturn($this->mockedOutput);
        $this->mockedEvent->shouldReceive('getArgument')
            ->with(AbstractToolCommand::KEY_ALLOWED_FILE_ENDINGS)->andReturn($this->forgedAllowedFileEndings);
        $this->mockedEvent->shouldReceive('getArgument')
            ->with(AbstractToolCommand::KEY_EXCLUSION_LIST_TOKEN)->andReturn($this->forgedExclusionListToken);

        $subjectFactory = new SubjectFactory();
        $buildFragments = $subjectFactory->buildSubject(TargetDecorator::class);
        $this->subject = $buildFragments['subject'];
        $this->subjectParameters = $buildFragments['parameters'];
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    /**
     * @test
     *
     * @dataProvider decorateAddsTargetsToTerminalCommandDataProvider
     *
     * @param bool         $forgedAutoTarget
     * @param string|false $forgedTarget
     * @param string|null  $expectedTargetBranch
     */
    public function decorateAddsTargetsToTerminalCommand(
        bool $forgedAutoTarget,
        $forgedTarget,
        ?string $expectedTargetBranch
    ): void {
        $forgedRealPath = 'asdasd';
        $forgedCommitHash = 'asdasdwqeqwe';
        $mockedGitChangeSet = Mockery::mock(GitChangeSet::class);
        $mockedEnhancedFileInfo = Mockery::mock(EnhancedFileInfo::class);
        $mockedEnhancedFileInfos = [$mockedEnhancedFileInfo, $mockedEnhancedFileInfo];

        $this->mockedEvent->shouldReceive('getSubject')->atLeast()->once()->andReturn($this->mockedTerminalCommand);

        $this->mockedInput->shouldReceive('getOption')->once()
            ->with(TargetableInputFacet::OPTION_AUTO_TARGET)->andReturn($forgedAutoTarget);
        $this->mockedInput->shouldReceive('getOption')->once()
            ->with(TargetableInputFacet::OPTION_TARGET)->andReturn($forgedTarget);

        $this->mockedOutput->shouldReceive('writeln')->once()
            ->with('<info>Checking diff to ' . $forgedCommitHash . '</info>', OutputInterface::VERBOSITY_NORMAL);
        $this->mockedOutput->shouldReceive('writeln')->once()
            ->with('<info>Following files will be checked</info>', OutputInterface::VERBOSITY_VERBOSE);
        $this->mockedOutput->shouldReceive('writeln')->twice()
            ->with($forgedRealPath, OutputInterface::VERBOSITY_VERBOSE);
        $this->mockedOutput->shouldReceive('writeln')->once()->with('');

        $this->subjectParameters[AdaptableFileFinder::class]->shouldReceive('findFiles')->once()
            ->with($this->forgedAllowedFileEndings, $this->forgedExclusionListToken, '', $expectedTargetBranch)
            ->andReturn($mockedGitChangeSet);

        $mockedEnhancedFileInfo->shouldReceive('getRealPath')->twice()->andReturn($forgedRealPath);

        $mockedGitChangeSet->shouldReceive('getCommitHash')->atLeast()->once()->andReturn($forgedCommitHash);
        $mockedGitChangeSet->shouldReceive('getFiles')->atLeast()->once()->andReturn($mockedEnhancedFileInfos);

        $this->mockedTerminalCommand->shouldReceive('addTargets')->once()->with($mockedEnhancedFileInfos);
        $this->subject->decorate($this->mockedEvent);
    }

    public function decorateAddsTargetsToTerminalCommandDataProvider(): array
    {
        return [
            'autoTargeting' => [true, false, null],
            'targeted' => [false, 'origin/asdqwe', 'origin/asdqwe'],
            'both' => [true, 'origin/asdqwe', null],
        ];
    }

    /**
     * @test
     */
    public function decorateShouldNotReactToNonTargetedInput(): void
    {
        $this->mockedEvent->shouldReceive('getSubject')->atLeast()->once()->andReturn($this->mockedTerminalCommand);

        $this->mockedInput->shouldReceive('getOption')->once()
            ->with(TargetableInputFacet::OPTION_AUTO_TARGET)->andReturn(false);
        $this->mockedInput->shouldReceive('getOption')->once()
            ->with(TargetableInputFacet::OPTION_TARGET)->andReturn(false);

        $this->mockedOutput->shouldReceive('writeln')->never();

        $this->mockedTerminalCommand->shouldReceive('addTargets')->never();

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

        $result = TargetDecorator::getSubscribedEvents();

        self::assertSame($expectedEvents, $result);
    }
}
