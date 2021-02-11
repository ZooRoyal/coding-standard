<?php

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\Library;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Zooroyal\CodingStandard\CommandLine\Factories\BlacklistFactory;
use Zooroyal\CodingStandard\CommandLine\FileFinders\AdaptableFileFinder;
use Zooroyal\CodingStandard\CommandLine\Library\GenericCommandRunner;
use Zooroyal\CodingStandard\CommandLine\Library\ProcessRunner;
use Zooroyal\CodingStandard\CommandLine\ValueObjects\GitChangeSet;
use Zooroyal\CodingStandard\Tests\Tools\SubjectFactory;
use function Safe\sprintf;

class GenericCommandRunnerTest extends TestCase
{
    /** @var GenericCommandRunner */
    private $subject;
    /** @var MockInterface[] */
    private $subjectParameters;
    /** @var MockInterface|Process */
    private $mockedProcess;
    /** @var MockInterface|GitChangeSet */
    private $mockedGitChangeSet;

    protected function setUp(): void
    {
        $this->mockedGitChangeSet = Mockery::mock(GitChangeSet::class);

        $subjectFactory = new SubjectFactory();
        $buildFragments = $subjectFactory->buildSubject(GenericCommandRunner::class);
        $this->subject = $buildFragments['subject'];
        $this->subjectParameters = $buildFragments['parameters'];

        $this->mockedProcess = Mockery::mock(Process::class);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function runWhitelistCommandWithAllParametersDataProvider()
    {
        return [
            'success propagation' => [0],
            'failure propagation' => [2],
        ];
    }

    /**
     * @test
     * @dataProvider runWhitelistCommandWithAllParametersDataProvider
     *
     * @param int $mockedExitCode
     */
    public function runWhitelistCommandWithAllParameters(int $mockedExitCode)
    {
        $mockedTemplate = 'My Template %1$s';
        $mockedTargetBranch = 'MyTarget';
        $mockedStopword = 'HALT';
        $mockedAllowedFileEndings = ['Morty'];
        $mockedProcessIsolation = true;
        $glue = 'juhu';
        $mockedChangedFiles = ['mocked', 'files'];
        $mockedOutput = 'Das hab ich zu sagen.';
        $mockedErrorOutput = 'ROOOOOOOOOOORERROR!';

        $this->prepareMocksForFindFiles($mockedAllowedFileEndings, $mockedStopword, $mockedTargetBranch, $mockedChangedFiles);
        $this->prepareMocksForRunAndWriteToOutputProcessIsolation(
            $mockedChangedFiles,
            $mockedTemplate,
            $mockedOutput,
            $mockedErrorOutput
        );

        $this->mockedProcess->shouldReceive('getExitCode')->withNoArgs()->andReturn($mockedExitCode);
        $this->subjectParameters[OutputInterface::class]->shouldReceive('writeln')->times($mockedExitCode)
            ->with($mockedOutput, OutputInterface::OUTPUT_NORMAL);
        $this->subjectParameters[OutputInterface::class]->shouldReceive('writeln')->times($mockedExitCode)
            ->with($mockedErrorOutput, OutputInterface::VERBOSITY_NORMAL);

        $result = $this->subject->runWhitelistCommand(
            $mockedTemplate,
            $mockedTargetBranch,
            $mockedStopword,
            $mockedAllowedFileEndings,
            $mockedProcessIsolation,
            $glue
        );

        self::assertSame($mockedExitCode, $result);
    }

    public function runWhitelistCommandWithNoProcessIsolationDataProvider()
    {
        return [
            'success propagation' => [0],
            'failure propagation' => [1],
        ];
    }

    /**
     * @test
     * @dataProvider runWhitelistCommandWithNoProcessIsolationDataProvider
     *
     * @param int $mockedExitCode
     */
    public function runWhitelistCommandWithNoProcessIsolation($mockedExitCode)
    {
        $mockedTemplate = 'My Template %1$s';
        $mockedTargetBranch = 'MyTarget';
        $mockedStopword = 'HALT';
        $mockedAllowedFileEndings = ['Morty'];
        $mockedProcessIsolation = false;
        $mockedGlue = 'juhu';
        $mockedChangedFiles = ['mocked', 'files'];
        $mockedOutput = 'Das hab ich zu sagen.';
        $mockedErrorOutput = 'ERROR ERRRRRRRRRROR';

        $this->prepareMocksForFindFiles($mockedAllowedFileEndings, $mockedStopword, $mockedTargetBranch, $mockedChangedFiles);
        $this->prepareMocksForRunAndWriteToOutput(
            $mockedChangedFiles,
            $mockedTemplate,
            $mockedOutput,
            $mockedErrorOutput,
            $mockedGlue
        );

        $this->mockedProcess->shouldReceive('getExitCode')->withNoArgs()->andReturn($mockedExitCode);
        $this->subjectParameters[OutputInterface::class]->shouldReceive('writeln')->times($mockedExitCode)
            ->with($mockedOutput, OutputInterface::OUTPUT_NORMAL);
        $this->subjectParameters[OutputInterface::class]->shouldReceive('writeln')->times($mockedExitCode)
            ->with($mockedErrorOutput, OutputInterface::VERBOSITY_NORMAL);

        $result = $this->subject->runWhitelistCommand(
            $mockedTemplate,
            $mockedTargetBranch,
            $mockedStopword,
            $mockedAllowedFileEndings,
            $mockedProcessIsolation,
            $mockedGlue
        );

        self::assertSame($mockedExitCode, $result);
    }

    /**
     * @test
     */
    public function runBlacklistCommand()
    {
        $mockedTemplate = 'My Template %1$s';
        $mockedStopword = 'HALT';
        $mockedPrefix = 'teil mich!';
        $mockedGlue = 'juhu';
        $mockedBlacklist = ['mocked', 'files'];
        $mockedOutput = 'Das hab ich zu sagen.';
        $mockedErrorOutput = 'ERRRRRRRRRRRRROROROROROR';
        $mockedExitCode = 0;

        $this->prepareMocksForRunAndWriteToOutput(
            $mockedBlacklist,
            $mockedTemplate,
            $mockedOutput,
            $mockedErrorOutput,
            $mockedGlue,
            $mockedPrefix
        );
        $this->subjectParameters[BlacklistFactory::class]->shouldReceive('build')->once()
            ->with($mockedStopword)->andReturn($mockedBlacklist);

        $this->mockedProcess->shouldReceive('getExitCode')->withNoArgs()->andReturn($mockedExitCode);
        $this->subjectParameters[OutputInterface::class]->shouldReceive('writeln')->times($mockedExitCode)
            ->with($mockedOutput, OutputInterface::OUTPUT_NORMAL);

        $result = $this->subject->runBlacklistCommand(
            $mockedTemplate,
            $mockedStopword,
            $mockedPrefix,
            $mockedGlue
        );

        self::assertSame($mockedExitCode, $result);
    }

    /**
     * @test
     */
    public function runBlacklistCommandEscaped()
    {
        $mockedTemplate = 'My Template %1$s';
        $mockedStopword = 'HALT';
        $mockedPrefix = 'teil mich!';
        $mockedGlue = 'juhu';
        $mockedBlacklist = ['mocked', '.files'];
        $mockedEscapedBlacklist = ['mocked', '\\\\\.files'];
        $mockedOutput = 'Das hab ich zu sagen.';
        $mockedErrorOutput = 'ERRRRRRRRRRRRROROROROROR';
        $mockedExitCode = 0;

        $this->prepareMocksForRunAndWriteToOutput(
            $mockedEscapedBlacklist,
            $mockedTemplate,
            $mockedOutput,
            $mockedErrorOutput,
            $mockedGlue,
            $mockedPrefix
        );
        $this->subjectParameters[BlacklistFactory::class]->shouldReceive('build')->once()
            ->with($mockedStopword)->andReturn($mockedBlacklist);

        $this->mockedProcess->shouldReceive('getExitCode')->withNoArgs()->andReturn($mockedExitCode);
        $this->subjectParameters[OutputInterface::class]->shouldReceive('writeln')->times($mockedExitCode)
            ->with($mockedOutput, OutputInterface::OUTPUT_NORMAL);

        $result = $this->subject->runBlacklistCommand(
            $mockedTemplate,
            $mockedStopword,
            $mockedPrefix,
            $mockedGlue,
            true
        );

        self::assertSame($mockedExitCode, $result);
    }

    /**
     * Prepares mocks for calls of private buildCommand with no ProcessIsolation
     *
     * @param string[] $mockedChangedFiles
     * @param string   $mockedTemplate
     * @param string   $mockedOutput
     * @param string   $mockedGlue
     * @param string   $mockedPrefix
     */
    private function prepareMocksForRunAndWriteToOutput(
        $mockedChangedFiles,
        $mockedTemplate,
        $mockedOutput,
        $mockedErrorOutput,
        $mockedGlue,
        $mockedPrefix = ''
    ) {
        $mockedCommand = sprintf(
            $mockedTemplate,
            $mockedPrefix . implode($mockedGlue . $mockedPrefix, $mockedChangedFiles)
        );

        $this->subjectParameters[OutputInterface::class]->shouldReceive('writeln')
            ->with('Checking diff to asd', OutputInterface::OUTPUT_NORMAL);
        $this->subjectParameters[OutputInterface::class]->shouldReceive('writeln')->once()
            ->with('Calling following command:' . "\n" . $mockedCommand, OutputInterface::VERBOSITY_DEBUG);
        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcessReturningProcessObject')
            ->once()->with($mockedCommand)->andReturn($this->mockedProcess);

        $this->mockedProcess->shouldReceive('getOutput')->withNoArgs()->andReturn($mockedOutput);
        $this->mockedProcess->shouldReceive('getErrorOutput')->withNoArgs()->andReturn($mockedErrorOutput);
    }

    /**
     * Prepares mocks for calls of private buildCommand
     *
     * @param array $mockedChangedFiles
     * @param string $mockedTemplate
     * @param string $mockedOutput
     * @param string $mockedErrorOutput
     */
    private function prepareMocksForRunAndWriteToOutputProcessIsolation(
        array $mockedChangedFiles,
        string $mockedTemplate,
        string $mockedOutput,
        string $mockedErrorOutput
    ) {
        foreach ($mockedChangedFiles as $mockedChangedFile) {
            $mockedCommand = sprintf($mockedTemplate, $mockedChangedFile);

            $this->subjectParameters[OutputInterface::class]->shouldReceive('writeln')
                ->with('Checking diff to asd', OutputInterface::OUTPUT_NORMAL);
            $this->subjectParameters[OutputInterface::class]->shouldReceive('writeln')->once()
                ->with('Calling following command:' . "\n" . $mockedCommand, OutputInterface::VERBOSITY_DEBUG);
            $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcessReturningProcessObject')
                ->once()->with($mockedCommand)->andReturn($this->mockedProcess);
        }

        $this->mockedProcess->shouldReceive('getOutput')->withNoArgs()->andReturn($mockedOutput);
        $this->mockedProcess->shouldReceive('getErrorOutput')->withNoArgs()->andReturn($mockedErrorOutput);
    }

    /**
     * Prepare mocks for call to findFiles.
     *
     * @param string[] $mockedAllowedFileEndings
     * @param string   $mockedStopword
     * @param string   $mockedTargetBranch
     * @param string[] $mockedChangedFiles
     */
    private function prepareMocksForFindFiles(
        array $mockedAllowedFileEndings,
        string $mockedStopword,
        string $mockedTargetBranch,
        array $mockedChangedFiles
    ) {
        $this->mockedGitChangeSet->shouldReceive('getCommitHash')->andReturn('asd');
        $this->mockedGitChangeSet->shouldReceive('getFiles')->andReturn($mockedChangedFiles);

        $this->subjectParameters[AdaptableFileFinder::class]->shouldReceive('findFiles')->once()
            ->with($mockedAllowedFileEndings, $mockedStopword, '', $mockedTargetBranch)->andReturn(
                $this->mockedGitChangeSet
            );

        $this->subjectParameters[OutputInterface::class]->shouldReceive('writeln')->once()
            ->with(
                'Files to handle:' . "\n" . implode("\n", $mockedChangedFiles) . "\n",
                OutputInterface::VERBOSITY_VERBOSE
            );
    }
}
