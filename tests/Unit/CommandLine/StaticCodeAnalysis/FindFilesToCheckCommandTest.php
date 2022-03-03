<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\StaticCodeAnalysis;

use Hamcrest\MatcherAssert;
use Hamcrest\Matchers as H;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\EnhancedFileInfo\EnhancedFileInfo;
use Zooroyal\CodingStandard\CommandLine\ExclusionList\ExclusionListFactory;
use Zooroyal\CodingStandard\CommandLine\FileFinder\AdaptableFileFinder;
use Zooroyal\CodingStandard\CommandLine\FileFinder\GitChangeSet;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\FindFilesToCheckCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Target\ParentBranchGuesser;
use Zooroyal\CodingStandard\Tests\Tools\SubjectFactory;

/**
 * Class FindFilesToCheckCommandTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FindFilesToCheckCommandTest extends TestCase
{
    /** @var array<MockInterface>|array<mixed> */
    private array $subjectParameters;
    private FindFilesToCheckCommand $subject;
    private MockInterface|EnhancedFileInfo $forgedBlacklistDirectory1;
    private MockInterface|EnhancedFileInfo $forgedBlacklistDirectory2;
    /** @var array<int,MockInterface|EnhancedFileInfo> */
    private array $expectedArray;
    private string $expectedResult1 = 'phpunit.xml.dist';
    private string $expectedResult2 = 'composer.json';

    protected function setUp(): void
    {
        $subjectFactory = new SubjectFactory();
        $buildFragments = $subjectFactory->buildSubject(FindFilesToCheckCommand::class);
        $this->subject = $buildFragments['subject'];
        $this->subjectParameters = $buildFragments['parameters'];

        $this->forgedBlacklistDirectory1 = Mockery::mock(EnhancedFileInfo::class);
        $this->forgedBlacklistDirectory2 = Mockery::mock(EnhancedFileInfo::class);
        $this->expectedArray = [$this->forgedBlacklistDirectory1, $this->forgedBlacklistDirectory2];

        $this->forgedBlacklistDirectory1->shouldReceive('getRelativePathname')
            ->withNoArgs()->andReturn($this->expectedResult1);
        $this->forgedBlacklistDirectory2->shouldReceive('getRelativePathname')
            ->withNoArgs()->andReturn($this->expectedResult2);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function checkIfCommandGetsConfigured(): void
    {
        $result = $this->subject->getDefinition()->getOptions();
        self::assertNotEmpty($result);
    }

    /**
     * @test
     */
    public function configure(): void
    {
        /** @var MockInterface|FindFilesToCheckCommand $localSubject */
        $localSubject = Mockery::mock(FindFilesToCheckCommand::class, $this->subjectParameters)->makePartial();

        $localSubject->shouldReceive('setName')->once()->with('find-files')->andReturnSelf();
        $localSubject->shouldReceive('setDescription')->once()
            ->with('Finds files for code style checks.')->andReturnSelf();
        $localSubject->shouldReceive('setHelp')->once()
            ->with('This tool finds files, which should be considered for code style checks.')->andReturnSelf();
        $localSubject->shouldReceive('setDefinition')->once()
            ->with(
                Mockery::on(
                    function ($value): bool {
                        MatcherAssert::assertThat($value, H::anInstanceOf(InputDefinition::class));
                        /** @var InputDefinition $value */
                        $options = $value->getOptions();
                        MatcherAssert::assertThat(
                            $options,
                            H::allOf(
                                H::arrayWithSize(6),
                                H::everyItem(
                                    H::anInstanceOf(InputOption::class)
                                )
                            )
                        );
                        return true;
                    }
                )
            )->andReturnSelf();

        $localSubject->configure();
    }

    /**
     * @test
     */
    public function executeAutoDetectsTarget(): void
    {
        $allowedFileEndings = ['myFilter'];
        $mockedBlacklistToken = 'myStopword';
        $mockedInclusionlistToken = 'myGoword';
        $mockedGuessedTargetBranch = 'auto/target';
        $mockedGitChangeSet = Mockery::mock(GitChangeSet::class);

        /** @var MockInterface|InputInterface $mockedInputInterface */
        $mockedInputInterface = Mockery::mock(InputInterface::class);
        /** @var MockInterface|OutputInterface $mockedOutputInterface */
        $mockedOutputInterface = Mockery::mock(OutputInterface::class);

        $this->prepareMockedInputInterface(
            $mockedInputInterface,
            $mockedBlacklistToken,
            $mockedInclusionlistToken,
            $allowedFileEndings,
            null,
            false,
            true
        );

        $this->subjectParameters[ParentBranchGuesser::class]->shouldReceive('guessParentBranchAsCommitHash')
            ->once()->andReturn($mockedGuessedTargetBranch);

        $this->subjectParameters[AdaptableFileFinder::class]->shouldReceive('findFiles')->once()
            ->with($allowedFileEndings, $mockedBlacklistToken, $mockedInclusionlistToken, $mockedGuessedTargetBranch)
            ->andReturn($mockedGitChangeSet);
        $mockedGitChangeSet->shouldReceive('getFiles')->once()
            ->withNoArgs()->andReturn($this->expectedArray);
        $mockedOutputInterface->shouldReceive('writeln')->once()
            ->with($this->expectedResult1 . PHP_EOL . $this->expectedResult2);

        $this->subject->execute($mockedInputInterface, $mockedOutputInterface);
    }

    /**
     * @test
     */
    public function executeInExclusionMode(): void
    {
        $mockedAllowedFileEndings = ['myFilter'];
        $mockedBlacklistToken = 'myStopword';
        $mockedInclusionlistToken = 'myGoword';
        $mockedTargetBranch = 'mockedTarget';
        $mockedExclusiveFlag = true;

        /** @var MockInterface|InputInterface $mockedInputInterface */
        $mockedInputInterface = Mockery::mock(InputInterface::class);
        /** @var MockInterface|OutputInterface $mockedOutputInterface */
        $mockedOutputInterface = Mockery::mock(OutputInterface::class);

        $this->prepareMockedInputInterface(
            $mockedInputInterface,
            $mockedBlacklistToken,
            $mockedInclusionlistToken,
            $mockedAllowedFileEndings,
            $mockedTargetBranch,
            $mockedExclusiveFlag
        );

        $this->subjectParameters[ExclusionListFactory::class]->shouldReceive('build')->once()
            ->with($mockedBlacklistToken)->andReturn($this->expectedArray);
        $mockedOutputInterface->shouldReceive('writeln')->once()
            ->with($this->expectedResult1 . '/' . PHP_EOL . $this->expectedResult2 . '/');

        $result = $this->subject->execute($mockedInputInterface, $mockedOutputInterface);
        self::assertSame(0, $result);
    }

    /**
     * @test
     */
    public function executeTriggeringAllFiles(): void
    {
        $allowedFileEndings = ['myFilter'];
        $mockedBlacklistToken = 'myStopword';
        $mockedInclusionlistToken = 'myGoword';
        $mockedTargetBranch = '';
        $mockedExclusiveFlag = false;

        $mockedGitChangeSet = Mockery::mock(GitChangeSet::class);

        /** @var MockInterface|InputInterface $mockedInputInterface */
        $mockedInputInterface = Mockery::mock(InputInterface::class);
        /** @var MockInterface|OutputInterface $mockedOutputInterface */
        $mockedOutputInterface = Mockery::mock(OutputInterface::class);

        $this->prepareMockedInputInterface(
            $mockedInputInterface,
            $mockedBlacklistToken,
            $mockedInclusionlistToken,
            $allowedFileEndings,
            $mockedTargetBranch,
            $mockedExclusiveFlag
        );

        $this->subjectParameters[AdaptableFileFinder::class]->shouldReceive('findFiles')->once()
            ->with($allowedFileEndings, $mockedBlacklistToken, $mockedInclusionlistToken, $mockedTargetBranch)
            ->andReturn($mockedGitChangeSet);
        $mockedGitChangeSet->shouldReceive('getFiles')->once()
            ->withNoArgs()->andReturn($this->expectedArray);
        $mockedOutputInterface->shouldReceive('writeln')->once()
            ->with($this->expectedResult1 . PHP_EOL . $this->expectedResult2);

        $this->subject->execute($mockedInputInterface, $mockedOutputInterface);
    }

    /**
     * Prepare $mockedInputInterface for test.
     *
     * @param array<string> $allowedFileEndings
     */
    private function prepareMockedInputInterface(
        MockInterface $mockedInputInterface,
        string $mockedBlacklistToken,
        string $mockedInclusionlistToken,
        array $allowedFileEndings,
        ?string $mockedTargetBranch = null,
        bool $mockedExclusiveFlag = false,
        bool $autoTargetValue = false,
    ): void {
        $mockedInputInterface->shouldReceive('getOption')->once()
            ->with('exclusionlist-token')->andReturn($mockedBlacklistToken);
        $mockedInputInterface->shouldReceive('getOption')->once()
            ->with('inclusionlist-token')->andReturn($mockedInclusionlistToken);
        $mockedInputInterface->shouldReceive('getOption')->once()
            ->with('allowed-file-endings')->andReturn($allowedFileEndings);
        if ($autoTargetValue) {
            $mockedInputInterface->shouldReceive('getOption')->never();
        } else {
            $mockedInputInterface->shouldReceive('getOption')->once()
                ->with('target')->andReturn($mockedTargetBranch);
        }
        $mockedInputInterface->shouldReceive('getOption')->once()
            ->with('auto-target')->andReturn($autoTargetValue);
        $mockedInputInterface->shouldReceive('getOption')->once()
            ->with('exclusionList')->andReturn($mockedExclusiveFlag);
    }
}
