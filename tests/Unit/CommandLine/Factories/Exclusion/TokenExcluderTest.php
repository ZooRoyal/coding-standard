<?php

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\Factories\Exclusion;

use Hamcrest\Matchers;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Zooroyal\CodingStandard\CommandLine\Factories\EnhancedFileInfoFactory;
use Zooroyal\CodingStandard\CommandLine\Factories\Excluders\TokenExcluder;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\Library\ProcessRunner;
use Zooroyal\CodingStandard\CommandLine\ValueObjects\EnhancedFileInfo;
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
        $forgedExcludedDirectories = [$mockedEnhancedFileInfo1, $mockedEnhancedFileInfoRemaining];
        $forgedRemainingPaths = [$mockedEnhancedFileInfoRemaining];
        $expectedResult = [
            new EnhancedFileInfo(
                $this->forgedRootDirectory . DIRECTORY_SEPARATOR . $mockedEnhancedFileInfo1,
                $this->forgedRootDirectory
            ),
        ];

        $forgedConfig = ['token' => 'bla'];

        $expectedCommand = 'find ' . $this->forgedRootDirectory . ' -name ' . $forgedConfig['token']
            . ' -not -path "./' . $forgedAlreadyExcluded[0] . '" -not -path "./' . $forgedAlreadyExcluded[1] . '"';

        $forgedCommandResult = $this->forgedRootDirectory . DIRECTORY_SEPARATOR . $forgedExcludedDirectories[1]
            . DIRECTORY_SEPARATOR . $forgedConfig['token'] . PHP_EOL;

        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')->once()
            ->with($expectedCommand)->andReturn($forgedCommandResult);

        $this->subjectParameters[EnhancedFileInfoFactory::class]->shouldReceive('buildFromArrayOfPaths')
            ->once()->with($forgedRemainingPaths)->andReturn($expectedResult);

        $result = $this->subject->getPathsToExclude($forgedAlreadyExcluded, $forgedConfig);

        self::assertSame($expectedResult, $result);
    }

    /**
     * @test
     */
    public function getPathsToExcludeWithDontFilesInRoot(): void
    {
        $forgedExcludedDirectories = ['.'];
        $expectedResult = [new EnhancedFileInfo(
            $this->forgedRootDirectory,
            $this->forgedRootDirectory
        )];

        $forgedConfig = ['token' => 'bla'];

        $expectedCommand = 'find ' . $this->forgedRootDirectory . ' -name ' . $forgedConfig['token'];

        $forgedCommandResult = $this->forgedRootDirectory . DIRECTORY_SEPARATOR . $forgedConfig['token'] . PHP_EOL;

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
        $forgedExcludedDirectories = ['asdasd', 'qweqwe'];
        $expectedResult = array_map(
            fn($paths) => new EnhancedFileInfo(
                $this->forgedRootDirectory . DIRECTORY_SEPARATOR . $paths,
                $this->forgedRootDirectory
            ),
            $forgedExcludedDirectories
        );

        $forgedConfig = ['token' => 'bla'];

        $expectedCommand = 'find ' . $this->forgedRootDirectory . ' -name ' . $forgedConfig['token'];

        $forgedCommandResult = $this->forgedRootDirectory
            . DIRECTORY_SEPARATOR . $forgedExcludedDirectories[0]
            . DIRECTORY_SEPARATOR . $forgedConfig['token'] . PHP_EOL
            . $this->forgedRootDirectory . DIRECTORY_SEPARATOR . $forgedExcludedDirectories[1]
            . DIRECTORY_SEPARATOR . $forgedConfig['token'] . PHP_EOL;

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
