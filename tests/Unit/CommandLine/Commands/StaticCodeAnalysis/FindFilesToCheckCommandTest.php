<?php

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\Commands\StaticCodeAnalysis;

use Hamcrest\MatcherAssert;
use Hamcrest\Matchers as H;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symplify\SmartFileSystem\SmartFileInfo;
use Zooroyal\CodingStandard\CommandLine\Commands\StaticCodeAnalysis\FindFilesToCheckCommand;
use Zooroyal\CodingStandard\CommandLine\Factories\BlacklistFactory;
use Zooroyal\CodingStandard\CommandLine\FileFinders\AdaptableFileFinder;
use Zooroyal\CodingStandard\CommandLine\ValueObjects\GitChangeSet;
use Zooroyal\CodingStandard\Tests\Tools\SubjectFactory;

/**
 * Class FindFilesToCheckCommandTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FindFilesToCheckCommandTest extends TestCase
{
    /** @var MockInterface[]|mixed[] */
    private array $subjectParameters;
    private FindFilesToCheckCommand $subject;

    protected function setUp(): void
    {
        $subjectFactory = new SubjectFactory();
        $buildFragments = $subjectFactory->buildSubject(FindFilesToCheckCommand::class);
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
    public function configure(): void
    {
        /** @var MockInterface|FindFilesToCheckCommand $localSubject */
        $localSubject = Mockery::mock(FindFilesToCheckCommand::class, $this->subjectParameters)->makePartial();

        $localSubject->shouldReceive('setName')->once()->with('find-files');
        $localSubject->shouldReceive('setDescription')->once()
            ->with('Finds files for code style checks.');
        $localSubject->shouldReceive('setHelp')->once()
            ->with('This tool finds files, which should be considered for code style checks.');
        $localSubject->shouldReceive('setDefinition')->once()
            ->with(
                Mockery::on(
                    function ($value) {
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
            );

        $localSubject->configure();
    }

    /**
     * @test
     */
    public function executeInExclusionMode(): void
    {
        $mockedAllowedFileEndings = ['myFilter'];
        $mockedBlacklistToken = 'myStopword';
        $mockedWhitelistToken = 'myGoword';
        $mockedTargetBranch = 'mockedTarget';
        $mockedExclusiveFlag = true;

        $forgedBlacklistDirectory1 = new SmartFileInfo('tests');
        $forgedBlacklistDirectory2 = new SmartFileInfo('config');

        $expectedArray = [$forgedBlacklistDirectory1, $forgedBlacklistDirectory2];

        /** @var MockInterface|InputInterface $mockedInputInterface */
        $mockedInputInterface = Mockery::mock(InputInterface::class);
        /** @var MockInterface|OutputInterface $mockedOutputInterface */
        $mockedOutputInterface = Mockery::mock(OutputInterface::class);

        $this->prepareMockedInputInterface(
            $mockedInputInterface,
            $mockedBlacklistToken,
            $mockedWhitelistToken,
            $mockedAllowedFileEndings,
            $mockedTargetBranch,
            $mockedExclusiveFlag
        );

        $this->subjectParameters[BlacklistFactory::class]->shouldReceive('build')->once()
            ->with($mockedBlacklistToken)->andReturn($expectedArray);

        $mockedOutputInterface->shouldReceive('writeln')->once()
            ->with('tests/' . PHP_EOL . 'config/');

        $result = $this->subject->execute($mockedInputInterface, $mockedOutputInterface);

        self::assertSame(0, $result);
    }

    /**
     * Prepare $mockedInputInterface for test.
     *
     * @param MockInterface $mockedInputInterface
     * @param string $mockedBlacklistToken
     * @param string $mockedWhitelistToken
     * @param string[] $allowedFileEndings
     * @param string $mockedTargetBranch
     * @param bool $mockedExclusiveFlag
     */
    protected function prepareMockedInputInterface(
        MockInterface $mockedInputInterface,
        string $mockedBlacklistToken,
        string $mockedWhitelistToken,
        array $allowedFileEndings,
        string $mockedTargetBranch,
        bool $mockedExclusiveFlag
    ): void {
        $mockedInputInterface->shouldReceive('getOption')->once()
            ->with('blacklist-token')->andReturn($mockedBlacklistToken);
        $mockedInputInterface->shouldReceive('getOption')->once()
            ->with('whitelist-token')->andReturn($mockedWhitelistToken);
        $mockedInputInterface->shouldReceive('getOption')->once()
            ->with('allowed-file-endings')->andReturn($allowedFileEndings);
        $mockedInputInterface->shouldReceive('getOption')->once()
            ->with('target')->andReturn($mockedTargetBranch);
        $mockedInputInterface->shouldReceive('getOption')->once()
            ->with('auto-target')->andReturn(false);
        $mockedInputInterface->shouldReceive('getOption')->once()
            ->with('exclusionList')->andReturn($mockedExclusiveFlag);
    }

    /**
     * @test
     */
    public function executeTriggeringAllFiles(): void
    {
        $allowedFileEndings = ['myFilter'];
        $mockedBlacklistToken = 'myStopword';
        $mockedWhitelistToken = 'myGoword';
        $mockedTargetBranch = '';
        $mockedExclusiveFlag = false;
        $expectedResult1 = 'phpunit.xml';
        $expectedResult2 = 'composer.json';
        $forgedExpectedResult1 = new SmartFileInfo($expectedResult1);
        $forgedExpectedResult2 = new SmartFileInfo($expectedResult2);

        $mockedGitChangeSet = Mockery::mock(GitChangeSet::class);

        /** @var MockInterface|InputInterface $mockedInputInterface */
        $mockedInputInterface = Mockery::mock(InputInterface::class);
        /** @var MockInterface|OutputInterface $mockedOutputInterface */
        $mockedOutputInterface = Mockery::mock(OutputInterface::class);

        $this->prepareMockedInputInterface(
            $mockedInputInterface,
            $mockedBlacklistToken,
            $mockedWhitelistToken,
            $allowedFileEndings,
            $mockedTargetBranch,
            $mockedExclusiveFlag
        );

        $this->subjectParameters[AdaptableFileFinder::class]->shouldReceive('findFiles')->once()
            ->with($allowedFileEndings, $mockedBlacklistToken, $mockedWhitelistToken, $mockedTargetBranch)
            ->andReturn($mockedGitChangeSet);
        $mockedGitChangeSet->shouldReceive('getFiles')->once()
            ->withNoArgs()->andReturn([$forgedExpectedResult1,$forgedExpectedResult2]);
        $mockedOutputInterface->shouldReceive('writeln')->once()
            ->with($expectedResult1 . PHP_EOL . $expectedResult2);

        $this->subject->execute($mockedInputInterface, $mockedOutputInterface);
    }
}
