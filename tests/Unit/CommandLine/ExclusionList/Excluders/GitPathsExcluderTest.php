<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\ExclusionList\Excluders;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Zooroyal\CodingStandard\CommandLine\EnhancedFileInfo\EnhancedFileInfo;
use Zooroyal\CodingStandard\CommandLine\EnhancedFileInfo\EnhancedFileInfoFactory;
use Zooroyal\CodingStandard\CommandLine\Environment\Environment;
use Zooroyal\CodingStandard\CommandLine\ExclusionList\Excluders\CacheKeyGenerator;
use Zooroyal\CodingStandard\CommandLine\ExclusionList\Excluders\GitPathsExcluder;
use Zooroyal\CodingStandard\CommandLine\Process\ProcessRunner;
use Zooroyal\CodingStandard\Tests\Tools\SubjectFactory;

class GitPathsExcluderTest extends TestCase
{
    private GitPathsExcluder $subject;
    private string $forgedRootDirectory = '/rootDirectory';
    /** @var array<MockInterface> */
    private array $subjectParameters;

    protected function setUp(): void
    {
        $subjectFactory = new SubjectFactory();
        $buildFragments = $subjectFactory->buildSubject(GitPathsExcluder::class);
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
        $forgedCacheKey = 'asdasdqweqwe12123';
        $expectedResult = array_map(
            fn($paths) => new EnhancedFileInfo(
                $this->forgedRootDirectory . DIRECTORY_SEPARATOR . $paths,
                $this->forgedRootDirectory,
            ),
            $forgedExcludedDirectories,
        );

        $expectedCommand = 'find ' . $this->forgedRootDirectory . ' -mindepth 2 -name .git';

        $forgedCommandResult = $this->forgedRootDirectory
            . DIRECTORY_SEPARATOR . $forgedExcludedDirectories[0]
            . DIRECTORY_SEPARATOR . '.git' . PHP_EOL
            . $this->forgedRootDirectory . DIRECTORY_SEPARATOR . $forgedExcludedDirectories[1]
            . DIRECTORY_SEPARATOR . '.git' . PHP_EOL;

        $this->subjectParameters[CacheKeyGenerator::class]->shouldReceive('generateCacheKey')->once()
            ->with([])->andReturn($forgedCacheKey);

        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')
            ->once()->with($expectedCommand)->andReturn($forgedCommandResult);

        $this->subjectParameters[EnhancedFileInfoFactory::class]->shouldReceive('buildFromArrayOfPaths')
            ->once()->with($forgedExcludedDirectories)->andReturn($expectedResult);

        $result = $this->subject->getPathsToExclude([]);

        self::assertSame($expectedResult, $result);
    }

    /**
     * @test
     */
    public function getPathsToExcludeWithAlreadyExcluded(): void
    {
        $mockedEnhancedFileInfo1 = Mockery::mock(EnhancedFileInfo::class);
        $mockedEnhancedFileInfo2 = Mockery::mock(EnhancedFileInfo::class);
        $mockedEnhancedFileInfoRemaining = Mockery::mock(EnhancedFileInfo::class);
        $forgedCacheKey = 'asdasdqweqwe12123';
        $forgedAlreadyExcluded = [$mockedEnhancedFileInfo1, $mockedEnhancedFileInfo2];
        $forgedExcludedDirectories = [$mockedEnhancedFileInfo1, $mockedEnhancedFileInfoRemaining];
        $forgedRemainingPaths = [$mockedEnhancedFileInfoRemaining];
        $expectedResult = [
            new EnhancedFileInfo(
                $this->forgedRootDirectory . DIRECTORY_SEPARATOR . $mockedEnhancedFileInfoRemaining,
                $this->forgedRootDirectory,
            ),
        ];

        $expectedCommand = 'find ' . $this->forgedRootDirectory . ' -mindepth 2 -name .git'
            . ' -not -path "./' . $forgedAlreadyExcluded[0] . '" -not -path "./' . $forgedAlreadyExcluded[1] . '"';

        $forgedCommandResult = $this->forgedRootDirectory
            . DIRECTORY_SEPARATOR . $forgedExcludedDirectories[1]
            . DIRECTORY_SEPARATOR . '.git' . PHP_EOL;

        $this->subjectParameters[CacheKeyGenerator::class]->shouldReceive('generateCacheKey')
            ->with($forgedAlreadyExcluded)->andReturn($forgedCacheKey);

        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')->once()
            ->with($expectedCommand)->andReturn($forgedCommandResult);

        $this->subjectParameters[EnhancedFileInfoFactory::class]->shouldReceive('buildFromArrayOfPaths')
            ->once()->with($forgedRemainingPaths)->andReturn($expectedResult);

        $result = $this->subject->getPathsToExclude($forgedAlreadyExcluded);
        $result1 = $this->subject->getPathsToExclude($forgedAlreadyExcluded);

        self::assertSame($result1, $result);
        self::assertSame($expectedResult, $result);
    }

    /**
     * @test
     */
    public function getPathsToExcludeFinderFindsNothing(): void
    {
        $expectedResult = [];
        $expectedCommand = 'find ' . $this->forgedRootDirectory . ' -mindepth 2 -name .git';

        $forgedCommandResult = '';

        $this->subjectParameters[CacheKeyGenerator::class]->shouldReceive('generateCacheKey')->once()
            ->with([])->andReturn('asdasdqweqwe12123');

        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')->once()
            ->with($expectedCommand)->andReturn($forgedCommandResult);

        $result = $this->subject->getPathsToExclude([]);

        self::assertSame($expectedResult, $result);
    }
}
