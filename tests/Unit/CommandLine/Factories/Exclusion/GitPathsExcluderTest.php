<?php

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\Factories\Exclusion;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Zooroyal\CodingStandard\CommandLine\Factories\EnhancedFileInfoFactory;
use Zooroyal\CodingStandard\CommandLine\Factories\Exclusion\GitPathsExcluder;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\Library\ProcessRunner;
use Zooroyal\CodingStandard\CommandLine\ValueObjects\EnhancedFileInfo;
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
        $expectedResult = array_map(
            fn($paths) => new EnhancedFileInfo(
                $this->forgedRootDirectory . DIRECTORY_SEPARATOR . $paths,
                $this->forgedRootDirectory
            ),
            $forgedExcludedDirectories
        );

        $expectedCommand = 'find ' . $this->forgedRootDirectory . ' -mindepth 2 -name .git';

        $forgedCommandResult = $this->forgedRootDirectory
            . DIRECTORY_SEPARATOR . $forgedExcludedDirectories[0]
            . DIRECTORY_SEPARATOR . '.git' . PHP_EOL
            . $this->forgedRootDirectory . DIRECTORY_SEPARATOR . $forgedExcludedDirectories[1]
            . DIRECTORY_SEPARATOR . '.git' . PHP_EOL;

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
        $forgedAlreadyExcluded = ['asdasd', 'blubblub'];
        $forgedExcludedDirectories = ['asdasd', 'qweqwe'];
        $forgedRemainingPaths = ['qweqwe'];
        $expectedResult = [
            new EnhancedFileInfo(
                $this->forgedRootDirectory . DIRECTORY_SEPARATOR . 'qweqwe',
                $this->forgedRootDirectory
            ),
        ];

        $expectedCommand = 'find ' . $this->forgedRootDirectory . ' -mindepth 2 -name .git'
            . ' -not -path "./' . $forgedAlreadyExcluded[0] . '" -not -path "./' . $forgedAlreadyExcluded[1] . '"';

        $forgedCommandResult = $this->forgedRootDirectory
            . DIRECTORY_SEPARATOR . $forgedExcludedDirectories[1]
            . DIRECTORY_SEPARATOR . '.git' . PHP_EOL;

        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')->once()
            ->with($expectedCommand)->andReturn($forgedCommandResult);

        $this->subjectParameters[EnhancedFileInfoFactory::class]->shouldReceive('buildFromArrayOfPaths')
            ->once()->with($forgedRemainingPaths)->andReturn($expectedResult);

        $result = $this->subject->getPathsToExclude($forgedAlreadyExcluded);

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

        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')->once()
            ->with($expectedCommand)->andReturn($forgedCommandResult);

        $result = $this->subject->getPathsToExclude([]);

        self::assertSame($expectedResult, $result);
    }
}
