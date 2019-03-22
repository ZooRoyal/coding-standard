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
use Zooroyal\CodingStandard\CommandLine\Commands\StaticCodeAnalysis\FindFilesToCheckCommand;
use Zooroyal\CodingStandard\CommandLine\Factories\BlacklistFactory;
use Zooroyal\CodingStandard\CommandLine\FileFinders\AdaptableFileFinder;
use Zooroyal\CodingStandard\CommandLine\ValueObjects\GitChangeSet;
use Zooroyal\CodingStandard\Tests\Tools\SubjectFactory;

class FindFilesToCheckCommandTest extends TestCase
{
    /** @var MockInterface[]|mixed[] */
    private $subjectParameters;
    /** @var FindFilesToCheckCommand */
    private $subject;

    protected function setUp()
    {
        $subjectFactory = new SubjectFactory();
        $buildFragments = $subjectFactory->buildSubject(FindFilesToCheckCommand::class);
        $this->subject = $buildFragments['subject'];
        $this->subjectParameters = $buildFragments['parameters'];
    }

    protected function tearDown()
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function configure()
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
    public function executeInExclusionMode()
    {
        $mockedFilter = 'myFilter';
        $mockedBlacklistToken = 'myStopword';
        $mockedWhitelistToken = 'myGoword';
        $mockedTargetBranch = 'mockedTarget';
        $mockedExclusiveFlag = true;

        $expectedArray = ['resulting', 'array'];

        /** @var MockInterface|InputInterface $mockedInputInterface */
        $mockedInputInterface = Mockery::mock(InputInterface::class);
        /** @var MockInterface|OutputInterface $mockedOutputInterface */
        $mockedOutputInterface = Mockery::mock(OutputInterface::class);

        $this->prepareMockedInputInterface(
            $mockedInputInterface,
            $mockedBlacklistToken,
            $mockedWhitelistToken,
            $mockedFilter,
            $mockedTargetBranch,
            $mockedExclusiveFlag
        );

        $this->subjectParameters[BlacklistFactory::class]->shouldReceive('build')->once()
            ->with($mockedBlacklistToken)->andReturn($expectedArray);

        $mockedOutputInterface->shouldReceive('writeln')->once()
            ->with(implode("\n", array_values($expectedArray)));

        $this->subject->execute($mockedInputInterface, $mockedOutputInterface);
    }

    /**
     * @test
     */
    public function executeTriggeringAllFiles()
    {
        $mockedFilter = 'myFilter';
        $mockedBlacklistToken = 'myStopword';
        $mockedWhitelistToken = 'myGoword';
        $mockedTargetBranch = '';
        $mockedExclusiveFlag = false;

        $expectedArray = ['resulting', 'array'];
        $mockedGitChangeSet = Mockery::mock(GitChangeSet::class);

        /** @var MockInterface|InputInterface $mockedInputInterface */
        $mockedInputInterface = Mockery::mock(InputInterface::class);
        /** @var MockInterface|OutputInterface $mockedOutputInterface */
        $mockedOutputInterface = Mockery::mock(OutputInterface::class);

        $this->prepareMockedInputInterface(
            $mockedInputInterface,
            $mockedBlacklistToken,
            $mockedWhitelistToken,
            $mockedFilter,
            $mockedTargetBranch,
            $mockedExclusiveFlag
        );

        $this->subjectParameters[AdaptableFileFinder::class]->shouldReceive('findFiles')->once()
            ->with($mockedFilter, $mockedBlacklistToken, $mockedWhitelistToken, $mockedTargetBranch)
            ->andReturn($mockedGitChangeSet);

        $mockedGitChangeSet->shouldReceive('getFiles')->andReturn($expectedArray);

        $mockedOutputInterface->shouldReceive('writeln')->once()
            ->with(implode("\n", array_values($expectedArray)));

        $this->subject->execute($mockedInputInterface, $mockedOutputInterface);
    }

    /**
     * Prepare $mockedInputInterface for test.
     *
     * @param $mockedInputInterface
     * @param string $mockedBlacklistToken
     * @param string $mockedWhitelistToken
     * @param string $mockedFilter
     * @param string $mockedTargetBranch
     * @param bool $mockedExclusiveFlag
     */
    protected function prepareMockedInputInterface(
        MockInterface $mockedInputInterface,
        string $mockedBlacklistToken,
        string $mockedWhitelistToken,
        string $mockedFilter,
        string $mockedTargetBranch,
        bool $mockedExclusiveFlag
    ) {
        $mockedInputInterface->shouldReceive('getOption')->once()
            ->with('blacklist-token')->andReturn($mockedBlacklistToken);
        $mockedInputInterface->shouldReceive('getOption')->once()
            ->with('whitelist-token')->andReturn($mockedWhitelistToken);
        $mockedInputInterface->shouldReceive('getOption')->once()
            ->with('filter')->andReturn($mockedFilter);
        $mockedInputInterface->shouldReceive('getOption')->once()
            ->with('target')->andReturn($mockedTargetBranch);
        $mockedInputInterface->shouldReceive('getOption')->once()
            ->with('auto-target')->andReturn(false);
        $mockedInputInterface->shouldReceive('getOption')->once()
            ->with('exclusionList')->andReturn($mockedExclusiveFlag);
    }
}
