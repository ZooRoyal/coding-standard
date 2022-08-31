<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\ExclusionList\Excluders;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Zooroyal\CodingStandard\CommandLine\EnhancedFileInfo\EnhancedFileInfo;
use Zooroyal\CodingStandard\CommandLine\EnhancedFileInfo\EnhancedFileInfoFactory;
use Zooroyal\CodingStandard\CommandLine\Environment\Environment;
use Zooroyal\CodingStandard\CommandLine\ExclusionList\Excluders\GitIgnoresExcluder;
use Zooroyal\CodingStandard\CommandLine\Process\ProcessRunner;
use Zooroyal\CodingStandard\Tests\Tools\SubjectFactory;

class GitIgnoresExcluderTest extends TestCase
{
    private GitIgnoresExcluder $subject;
    private string $forgedRootDirectory = '/rootDirectory';
    private string $expectedCheckCommand = 'git check-ignore --stdin';
    /** @var array<MockInterface> */
    private array $subjectParameters;
    /** @var MockInterface|Process */
    private $mockedCheckProcess;

    protected function setUp(): void
    {
        $subjectFactory = new SubjectFactory();
        $buildFragments = $subjectFactory->buildSubject(GitIgnoresExcluder::class);
        $this->mockedCheckProcess = Mockery::mock(Process::class);

        $this->subject = $buildFragments['subject'];
        $this->subjectParameters = $buildFragments['parameters'];

        $this->subjectParameters[Environment::class]->shouldReceive('getRootDirectory->getRealPath')
            ->once()->withNoArgs()->andReturn($this->forgedRootDirectory);

        $this->subjectParameters[ProcessRunner::class]->shouldReceive('createProcess')
            ->once()->with($this->expectedCheckCommand)->andReturn($this->mockedCheckProcess);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function getPathsToExcludeWithoutParameters(): void
    {
        $forgedExcludedDirectories = ['asdasd', 'qweqwe'];
        $expectedResult = array_map(
            fn($paths) => new EnhancedFileInfo(
                $this->forgedRootDirectory . DIRECTORY_SEPARATOR . $paths,
                $this->forgedRootDirectory
            ),
            $forgedExcludedDirectories
        );

        $forgedCheckCommandResult = DIRECTORY_SEPARATOR . $forgedExcludedDirectories[0] . PHP_EOL
            . DIRECTORY_SEPARATOR . $forgedExcludedDirectories[1] . PHP_EOL;

        $expectedCommand = 'find ' . $this->forgedRootDirectory . ' -type d';
        $forgedCommandResult = $forgedCheckCommandResult . DIRECTORY_SEPARATOR . 'bruno/labadia' . PHP_EOL;

        $expectedBuildFromArrayParameter = array_map(
            static fn($value): string => DIRECTORY_SEPARATOR . $value,
            $forgedExcludedDirectories
        );

        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')
            ->once()->with($expectedCommand)->andReturn($forgedCommandResult);

        $this->mockedCheckProcess->shouldReceive('setInput')->once()->with($forgedCommandResult);
        $this->mockedCheckProcess->shouldReceive('mustRun->getOutput')->once()->withNoArgs()
            ->andReturn($forgedCheckCommandResult);

        $this->subjectParameters[EnhancedFileInfoFactory::class]->shouldReceive('buildFromArrayOfPaths')
            ->once()->with($expectedBuildFromArrayParameter)->andReturn($expectedResult);

        $result = $this->subject->getPathsToExclude([]);

        self::assertSame($expectedResult, $result);
    }

    /**
     * @test
     */
    public function getPathsToExcludeWithAlreadyExcluded(): void
    {
        $mockedEnhancedFileInfo1 = Mockery::mock(EnhancedFileInfo::class);
        $mockedEnhancedFileInfo1Path = '/asdasd';

        $mockedEnhancedFileInfo2 = Mockery::mock(EnhancedFileInfo::class);
        $mockedEnhancedFileInfo2Path = '/qweqwe';

        $forgedAlreadyExcluded = [$mockedEnhancedFileInfo1, $mockedEnhancedFileInfo2];

        $mockedEnhancedFileInfo1->shouldReceive('__toString')->withNoArgs()
            ->andReturn($mockedEnhancedFileInfo1Path);
        $mockedEnhancedFileInfo2->shouldReceive('__toString')->withNoArgs()
            ->andReturn($mockedEnhancedFileInfo2Path);

        $expectedCommand = 'find ' . $this->forgedRootDirectory
            . ' -type d '
            . '-not -path ' . $mockedEnhancedFileInfo1Path . '/* '
            . '-not -path ' . $mockedEnhancedFileInfo2Path . '/*';

        $forgedCommandResult = '';
        $forgedCheckCommandResult = '';

        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')->once()
            ->with($expectedCommand)->andReturn($forgedCommandResult);
        $this->mockedCheckProcess->shouldReceive('setInput')->once()->with($forgedCommandResult);
        $this->mockedCheckProcess->shouldReceive('mustRun->getOutput')->once()->withNoArgs()
            ->andReturn($forgedCheckCommandResult);

        $this->subjectParameters[EnhancedFileInfoFactory::class]->shouldReceive('buildFromArrayOfPaths')
            ->never();

        $this->subject->getPathsToExclude($forgedAlreadyExcluded);
    }

    /**
     * @test
     */
    public function getPathsToExcludeFinderFindsNothing(): void
    {
        $expectedResult = [];

        $mockedException = Mockery::mock(ProcessFailedException::class);
        $mockedException->shouldReceive('getProcess->getExitCode')->once()->andReturn(1);

        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')->once()->andReturn('');
        $this->mockedCheckProcess->shouldReceive('setInput')->once();
        $this->mockedCheckProcess->shouldReceive('mustRun')->once()->andThrows($mockedException);

        $result = $this->subject->getPathsToExclude([]);

        self::assertSame($expectedResult, $result);
    }

    /**
     * @test
     */
    public function getPathsToExcludeFinderRethrowsRealProcessErrors(): void
    {
        $this->expectException(ProcessFailedException::class);

        $mockedException = Mockery::mock(ProcessFailedException::class);
        $mockedException->shouldReceive('getProcess->getExitCode')->once()->andReturn(2);

        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')->once()->andReturn('');
        $this->mockedCheckProcess->shouldReceive('setInput')->once();
        $this->mockedCheckProcess->shouldReceive('mustRun')->once()->andThrows($mockedException);

        $this->subject->getPathsToExclude([]);
    }
}
