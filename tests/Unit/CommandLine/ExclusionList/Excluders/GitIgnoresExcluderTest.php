<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\ExclusionList\Excluders;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Exception\ProcessFailedException;
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
    /** @var array<MockInterface> */
    private array $subjectParameters;

    protected function setUp(): void
    {
        $subjectFactory = new SubjectFactory();
        $buildFragments = $subjectFactory->buildSubject(GitIgnoresExcluder::class);
        $this->subject = $buildFragments['subject'];
        $this->subjectParameters = $buildFragments['parameters'];

        $this->subjectParameters[Environment::class]->shouldReceive('getRootDirectory->getRealPath')
            ->once()->withNoArgs()->andReturn($this->forgedRootDirectory);
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

        $expectedCommand = 'find ' . $this->forgedRootDirectory . ' -type d | git check-ignore --stdin';

        $forgedCommandResult = '.' . DIRECTORY_SEPARATOR . $forgedExcludedDirectories[0] . PHP_EOL
            . '.' . DIRECTORY_SEPARATOR . $forgedExcludedDirectories[1] . PHP_EOL;

        $expectedBuildFromArrayParameter = array_map(
            static fn($value): string => '.' . DIRECTORY_SEPARATOR . $value,
            $forgedExcludedDirectories
        );

        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')
            ->once()->with($expectedCommand)->andReturn($forgedCommandResult);

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
        $mockedEnhancedFileInfo1RelativePath = 'asdasd';

        $mockedEnhancedFileInfo2 = Mockery::mock(EnhancedFileInfo::class);
        $mockedEnhancedFileInfo2RelativePath = 'qweqwe';

        $forgedAlreadyExcluded = [$mockedEnhancedFileInfo1, $mockedEnhancedFileInfo2];

        $mockedEnhancedFileInfo1->shouldReceive('getRelativePathname')->withNoArgs()
            ->andReturn($mockedEnhancedFileInfo1RelativePath);
        $mockedEnhancedFileInfo2->shouldReceive('getRelativePathname')->withNoArgs()
            ->andReturn($mockedEnhancedFileInfo2RelativePath);

        $expectedCommand = 'find ' . $this->forgedRootDirectory
            . ' -type d '
            . '-not -path "./' . $mockedEnhancedFileInfo1RelativePath . '/*" '
            . '-not -path "./' . $mockedEnhancedFileInfo2RelativePath . '/*" '
            . '| git check-ignore --stdin';

        $forgedCommandResult = '';

        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')->once()
            ->with($expectedCommand)->andReturn($forgedCommandResult);

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

        $expectedCommand = 'find ' . $this->forgedRootDirectory . ' -type d | git check-ignore --stdin';

        $mockedException = Mockery::mock(ProcessFailedException::class);
        $mockedException->shouldReceive('getProcess->getExitCode')->once()->andReturn(1);

        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')->once()
            ->with($expectedCommand)->andThrows($mockedException);

        $result = $this->subject->getPathsToExclude([]);

        self::assertSame($expectedResult, $result);
    }

    /**
     * @test
     */
    public function getPathsToExcludeFinderRethrowsRealProcessErrors(): void
    {
        $this->expectException(ProcessFailedException::class);
        $expectedCommand = 'find ' . $this->forgedRootDirectory . ' -type d | git check-ignore --stdin';

        $mockedException = Mockery::mock(ProcessFailedException::class);
        $mockedException->shouldReceive('getProcess->getExitCode')->once()->andReturn(2);

        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')->once()
            ->with($expectedCommand)->andThrows($mockedException);

        $this->subject->getPathsToExclude([]);
    }
}
