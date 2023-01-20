<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\ExclusionList\Excluders;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Zooroyal\CodingStandard\CommandLine\EnhancedFileInfo\EnhancedFileInfo;
use Zooroyal\CodingStandard\CommandLine\EnhancedFileInfo\EnhancedFileInfoFactory;
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
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function getPathsToExclude(): void
    {
        $forgedExcludedDirectories = ['asdasd', 'qweqwe'];
        $expectedResult = array_map(
            fn($path) => new EnhancedFileInfo(
                $this->forgedRootDirectory . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR,
                $this->forgedRootDirectory,
            ),
            $forgedExcludedDirectories,
        );

        $expectedCommand = 'git ls-files -io --exclude-standard --directory';
        $forgedCommandResult = $forgedExcludedDirectories[0] . DIRECTORY_SEPARATOR . PHP_EOL
            . $forgedExcludedDirectories[1] . DIRECTORY_SEPARATOR . PHP_EOL
            . 'asd.js' . PHP_EOL;

        $expectedBuildFromArrayParameter = array_map(
            static fn($value): string => $value . DIRECTORY_SEPARATOR,
            $forgedExcludedDirectories,
        );

        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')
            ->once()->with($expectedCommand)->andReturn($forgedCommandResult);

        $this->subjectParameters[EnhancedFileInfoFactory::class]->shouldReceive('buildFromArrayOfPaths')
            ->once()->with($expectedBuildFromArrayParameter)->andReturn($expectedResult);

        $result = $this->subject->getPathsToExclude([]);
        $result1 = $this->subject->getPathsToExclude([]);

        self::assertSame($result1, $result);
        self::assertSame($expectedResult, $result);
    }

    /**
     * @test
     */
    public function getPathsToExcludeFinderFindsNothing(): void
    {
        $expectedResult = [];

        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')->once()->andReturn('');

        $result = $this->subject->getPathsToExclude([]);

        self::assertSame($expectedResult, $result);
    }
}
