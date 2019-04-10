<?php

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\Commands\Checks;

use Hamcrest\Matcher;
use Hamcrest\MatcherAssert;
use Hamcrest\Matchers as H;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\Commands\Checks\ForbiddenChangesCommand;
use Zooroyal\CodingStandard\CommandLine\Commands\StaticCodeAnalysis\FindFilesToCheckCommand;
use Zooroyal\CodingStandard\CommandLine\FileFinders\DiffCheckableFileFinder;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\ValueObjects\GitChangeSet;
use Zooroyal\CodingStandard\Tests\Tools\SubjectFactory;

class ForbiddenChangesCommandTest extends TestCase
{
    /** @var ForbiddenChangesCommand */
    private $subject;
    /** @var MockInterface[] */
    private $subjectParameters;
    /** @var string */
    private $blacklistToken = '.dontChangeFiles';
    /** @var string */
    private $whitelistToken = '.doChangeFiles';

    protected function setUp()
    {
        $subjectFactory = new SubjectFactory();
        $buildFragments = $subjectFactory->buildSubject(ForbiddenChangesCommand::class);
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
        $localSubject = Mockery::mock(ForbiddenChangesCommand::class)->makePartial();

        $localSubject->shouldReceive('setName')->once()->with('checks:forbidden-changes');
        $localSubject->shouldReceive('setDescription')->once()
            ->with('Checks for unwanted code changes.');
        $localSubject->shouldReceive('setHelp')->once()
            ->with(
                'This tool checks if there where changes made to files. If a parent directory contains a '
                . ' ' . $this->blacklistToken . ' file the tools will report the violation. Changes in subdirectories of a '
                . 'marked directory may be allowed by placing a ' . $this->whitelistToken . ' file in the subdirectory.'
                . ' Use parameter to determine if this should be handled as Warning or not.'
            );
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
                                H::arrayWithSize(2),
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
     * Data Provider for executeInteractsWithWarningFlag.
     *
     * @return mixed[]
     */
    public function executeInteractsWithWarningFlagDataProvider() : array
    {
        return [
            'warning' => [
                'warning' => true,
                'expectedResult' => H::is(0),
                'messageMatcher' => h::allOf(
                    H::containsString('The following files violate change constraints: '),
                    H::containsString('Some file')
                ),
                'expectedWrongfullyChangesFiles' => ['Some file'],
                'mockedTargetBranch' => 'myTarget',
                'mockedTargetGuess' => null,

            ],
            'error' => [
                'warning' => false,
                'expectedResult' => H::not(0),
                'messageMatcher' => h::allOf(
                    H::containsString('The following files violate change constraints: '),
                    H::containsString('Some file')
                ),
                'expectedWrongfullyChangesFiles' => ['Some file'],
                'mockedTargetBranch' => null,
                'mockedTargetGuess' => 'GuessedTargetBranch',
            ],
            'no files found' => [
                'warning' => false,
                'expectedResult' => H::is(0),
                'messageMatcher' => H::containsString('All good!'),
                'expectedWrongfullyChangesFiles' => [],
                'mockedTargetBranch' => 'myTarget',
                'mockedTargetGuess' => null,
            ],
        ];
    }

    /**
     * @test
     * @dataProvider executeInteractsWithWarningFlagDataProvider
     *
     * @param bool $warning
     * @param Matcher $expectedResultMatcher
     * @param Matcher $messageMatcher
     * @param string[] $expectedWrongfullyChangesFiles
     * @param string|null $mockedTargetBranch
     * @param string|null $mockedTargetGuess
     */
    public function executeInteractsWithWarningFlag(
        bool $warning,
        Matcher $expectedResultMatcher,
        Matcher $messageMatcher,
        array $expectedWrongfullyChangesFiles,
        $mockedTargetBranch,
        $mockedTargetGuess
    ) {
        /** @var MockInterface|InputInterface $mockedInputInterface */
        $mockedInputInterface = Mockery::mock(InputInterface::class);
        /** @var MockInterface|OutputInterface $mockedOutputInterface */
        $mockedOutputInterface = Mockery::mock(OutputInterface::class);

        $mockedInputInterface->shouldReceive('getOption')->once()
            ->with('warn')->andReturn($warning);
        $mockedInputInterface->shouldReceive('getOption')->atMost()->once()
            ->with('target')->andReturn($mockedTargetBranch);

        $this->subjectParameters[Environment::class]->shouldReceive('guessParentBranchAsCommitHash')
            ->atMost()->once()->withNoArgs()->andReturn($mockedTargetGuess);

        $mockedOutputInterface->shouldReceive('writeln')->once()
            ->with('Checking diff to ' . ($mockedTargetBranch ?? $mockedTargetGuess) . ' for forbidden changes.');

        $this->subjectParameters[DiffCheckableFileFinder::class]->shouldReceive('findFiles')
            ->with('', '.doChangeFiles', '.dontChangeFiles', $mockedTargetBranch ?? $mockedTargetGuess)
            ->andReturn(new GitChangeSet($expectedWrongfullyChangesFiles));

        $mockedOutputInterface->shouldReceive('writeln')->once()
            ->with($messageMatcher);

        $result = $this->subject->execute($mockedInputInterface, $mockedOutputInterface);

        MatcherAssert::assertThat($result, $expectedResultMatcher);
    }
}
