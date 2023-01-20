<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\ExclusionList\Excluders;

use Hamcrest\Matchers;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Zooroyal\CodingStandard\CommandLine\EnhancedFileInfo\EnhancedFileInfo;
use Zooroyal\CodingStandard\CommandLine\EnhancedFileInfo\EnhancedFileInfoFactory;
use Zooroyal\CodingStandard\CommandLine\Environment\Environment;
use Zooroyal\CodingStandard\CommandLine\ExclusionList\Excluders\CacheKeyGenerator;
use Zooroyal\CodingStandard\CommandLine\ExclusionList\Excluders\TokenExcluder;
use Zooroyal\CodingStandard\CommandLine\Process\ProcessRunner;
use Zooroyal\CodingStandard\Tests\Tools\SubjectFactory;

class TokenExcluderTest extends TestCase
{
    private TokenExcluder $subject;
    private string $forgedRootDirectory = '/rootDirectory';
    /** @var array<MockInterface> */
    private array $subjectParameters;

    protected function setUp(): void
    {
        $subjectFactory = new SubjectFactory();
        $buildFragments = $subjectFactory->buildSubject(TokenExcluder::class);
        $this->subject = $buildFragments['subject'];
        $this->subjectParameters = $buildFragments['parameters'];

        $this->subjectParameters[Environment::class]->shouldReceive('getRootDirectory->getRealPath')->atMost()->once()
            ->withNoArgs()->andReturn($this->forgedRootDirectory);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function getPathsToExcludeFinderFindsNothing(): void
    {
        $expectedResult = [];

        $forgedConfig = ['token' => 'bla'];

        $this->subjectParameters[CacheKeyGenerator::class]->shouldReceive('generateCacheKey')->once()
            ->with([], $forgedConfig)->andReturn('asdasdqweqwe12123');

        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')->once()
            ->with(Matchers::stringValue())->andReturn('');

        $result = $this->subject->getPathsToExclude([], $forgedConfig);

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
        $forgedAlreadyExcluded = [$mockedEnhancedFileInfo1, $mockedEnhancedFileInfo2];
        $expectedResult = [
            new EnhancedFileInfo(
                $this->forgedRootDirectory . DIRECTORY_SEPARATOR . $mockedEnhancedFileInfo1,
                $this->forgedRootDirectory,
            ),
        ];

        $forgedConfig = ['token' => 'bla'];

        $expectedCommand = 'find ' . $this->forgedRootDirectory . ' -name ' . $forgedConfig['token']
            . ' -not -path "./' . $forgedAlreadyExcluded[0] . '" -not -path "./' . $forgedAlreadyExcluded[1] . '"';

        $forgedCommandResult = $this->forgedRootDirectory . DIRECTORY_SEPARATOR . $mockedEnhancedFileInfoRemaining
            . DIRECTORY_SEPARATOR . $forgedConfig['token'] . PHP_EOL;

        $this->subjectParameters[CacheKeyGenerator::class]->shouldReceive('generateCacheKey')
            ->with($forgedAlreadyExcluded, $forgedConfig)->andReturn('asdasdqweqwe12123');

        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')->once()
            ->with($expectedCommand)->andReturn($forgedCommandResult);

        $this->subjectParameters[EnhancedFileInfoFactory::class]->shouldReceive('buildFromArrayOfPaths')
            ->once()->with([$this->forgedRootDirectory . DIRECTORY_SEPARATOR . $mockedEnhancedFileInfoRemaining])
            ->andReturn($expectedResult);

        $result1 = $this->subject->getPathsToExclude($forgedAlreadyExcluded, $forgedConfig);
        $result = $this->subject->getPathsToExclude($forgedAlreadyExcluded, $forgedConfig);

        self::assertSame($result1, $result);
        self::assertSame($expectedResult, $result);
    }

    /**
     * @test
     */
    public function getPathsToExcludeWithDontFilesInRoot(): void
    {
        $forgedExcludedDirectories = [$this->forgedRootDirectory];
        $expectedResult = [
            new EnhancedFileInfo(
                $this->forgedRootDirectory,
                $this->forgedRootDirectory,
            ),
        ];

        $forgedConfig = ['token' => 'bla'];

        $expectedCommand = 'find ' . $this->forgedRootDirectory . ' -name ' . $forgedConfig['token'];

        $forgedCommandResult = $this->forgedRootDirectory . DIRECTORY_SEPARATOR . $forgedConfig['token'] . PHP_EOL;

        $this->subjectParameters[CacheKeyGenerator::class]->shouldReceive('generateCacheKey')->once()
            ->with([], $forgedConfig)->andReturn('asdasdqweqwe12123');

        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')->once()
            ->with($expectedCommand)->andReturn($forgedCommandResult);

        $this->subjectParameters[EnhancedFileInfoFactory::class]->shouldReceive('buildFromArrayOfPaths')
            ->once()->with($forgedExcludedDirectories)->andReturn($expectedResult);

        $result = $this->subject->getPathsToExclude([], $forgedConfig);

        self::assertSame($expectedResult, $result);
    }

    /**
     * @test
     */
    public function getPathsToExcludeWithoutAlreadyExcluded(): void
    {
        $forgedExcludedDirectories = [
            $this->forgedRootDirectory . DIRECTORY_SEPARATOR . 'asdasd',
            $this->forgedRootDirectory . DIRECTORY_SEPARATOR . 'qweqwe',
        ];
        $expectedResult = array_map(
            fn($paths) => new EnhancedFileInfo(
                $paths,
                $this->forgedRootDirectory,
            ),
            $forgedExcludedDirectories,
        );

        $forgedConfig = ['token' => 'bla'];

        $expectedCommand = 'find ' . $this->forgedRootDirectory . ' -name ' . $forgedConfig['token'];

        $forgedCommandResult = $forgedExcludedDirectories[0] . DIRECTORY_SEPARATOR . $forgedConfig['token'] . PHP_EOL
            . $forgedExcludedDirectories[1] . DIRECTORY_SEPARATOR . $forgedConfig['token'] . PHP_EOL;

        $this->subjectParameters[CacheKeyGenerator::class]->shouldReceive('generateCacheKey')->once()
            ->with([], $forgedConfig)->andReturn('asdasdqweqwe12123');

        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')->once()
            ->with($expectedCommand)->andReturn($forgedCommandResult);

        $this->subjectParameters[EnhancedFileInfoFactory::class]->shouldReceive('buildFromArrayOfPaths')
            ->once()->with($forgedExcludedDirectories)->andReturn($expectedResult);

        $result = $this->subject->getPathsToExclude([], $forgedConfig);

        self::assertSame($expectedResult, $result);
    }

    /**
     * @test
     */
    public function getPathsToExcludeWithoutToken(): void
    {
        $result = $this->subject->getPathsToExclude([]);

        self::assertSame([], $result);
    }
}
