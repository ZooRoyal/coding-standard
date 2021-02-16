<?php

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\Factories\Exclusion;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Zooroyal\CodingStandard\CommandLine\Factories\Exclusion\GitPathsExcluder;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\Library\ProcessRunner;

class GitPathsExcluderTest extends TestCase
{
    private GitPathsExcluder $subject;
    /** @var MockInterface|Environment */
    private $mockedEnvironment;
    /** @var MockInterface|ProcessRunner */
    private $mockedProcessRunner;
    private $forgedRootDirectory = '/rootDirectory';

    protected function setUp(): void
    {
        $this->mockedEnvironment = Mockery::mock(Environment::class);
        $this->mockedProcessRunner = Mockery::mock(ProcessRunner::class);

        $this->mockedEnvironment->shouldReceive('getRootDirectory')->once()
            ->withNoArgs()->andReturn($this->forgedRootDirectory);

        $this->subject = new GitPathsExcluder($this->mockedEnvironment, $this->mockedProcessRunner);
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
        $expectedResult = $forgedExcludedDirectories;

        $expectedCommand = 'find ' . $this->forgedRootDirectory . ' -type d -mindepth 2 -name .git';

        $forgedCommandResult = $this->forgedRootDirectory
            . DIRECTORY_SEPARATOR . $forgedExcludedDirectories[0]
            . DIRECTORY_SEPARATOR . '.git' . PHP_EOL
            . $this->forgedRootDirectory . DIRECTORY_SEPARATOR . $forgedExcludedDirectories[1]
            . DIRECTORY_SEPARATOR . '.git' . PHP_EOL;

        $this->mockedProcessRunner->shouldReceive('runAsProcess')->once()
            ->with($expectedCommand)->andReturn($forgedCommandResult);

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
        $expectedResult = ['qweqwe'];

        $expectedCommand = 'find ' . $this->forgedRootDirectory . ' -type d -mindepth 2 -name .git'
        . ' -not -path "./' . $forgedAlreadyExcluded[0] . '" -not -path "./' . $forgedAlreadyExcluded[1] . '"';

        $forgedCommandResult = $this->forgedRootDirectory
            . DIRECTORY_SEPARATOR . $forgedExcludedDirectories[1]
            . DIRECTORY_SEPARATOR . '.git' . PHP_EOL;

        $this->mockedProcessRunner->shouldReceive('runAsProcess')->once()
            ->with($expectedCommand)->andReturn($forgedCommandResult);

        $result = $this->subject->getPathsToExclude($forgedAlreadyExcluded);

        self::assertSame($expectedResult, $result);
    }

    /**
     * @test
     */
    public function getPathsToExcludeFinderFindsNothing(): void
    {
        $expectedResult = [];

        $expectedCommand = 'find ' . $this->forgedRootDirectory . ' -type d -mindepth 2 -name .git';

        $forgedCommandResult = '';

        $this->mockedProcessRunner->shouldReceive('runAsProcess')->once()
            ->with($expectedCommand)->andReturn($forgedCommandResult);

        $result = $this->subject->getPathsToExclude([]);

        self::assertSame($expectedResult, $result);
    }
}
