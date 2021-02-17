<?php

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\Factories\Exclusion;

use Hamcrest\Matchers;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Zooroyal\CodingStandard\CommandLine\Factories\Exclusion\TokenExcluder;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\Library\ProcessRunner;

class TokenExcluderTest extends TestCase
{
    private TokenExcluder $subject;
    /** @var MockInterface|Environment */
    private $mockedEnvironment;
    /** @var MockInterface|ProcessRunner */
    private $mockedProcessRunner;
    private string $forgedRootDirectory = '/rootDirectory';

    protected function setUp(): void
    {
        $this->mockedEnvironment = Mockery::mock(Environment::class);
        $this->mockedProcessRunner = Mockery::mock(ProcessRunner::class);

        $this->mockedEnvironment->shouldReceive('getRootDirectory')->atMost()->once()
            ->withNoArgs()->andReturn($this->forgedRootDirectory);

        $this->subject = new TokenExcluder($this->mockedEnvironment, $this->mockedProcessRunner);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function getPathsToExcludeWithoutToken()
    {
        $result = $this->subject->getPathsToExclude([]);

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function getPathsToExcludeFinderFindsNothing(): void
    {
        $expectedResult = [];

        $forgedConfig = ['token' => 'bla'];

        $this->mockedProcessRunner->shouldReceive('runAsProcess')->once()
            ->with(Matchers::stringValue())->andReturn('');

        $result = $this->subject->getPathsToExclude([], $forgedConfig);

        self::assertSame($expectedResult, $result);
    }

    /**
     * @test
     */
    public function getPathsToExcludeWithoutAlreadyExcluded(): void
    {
        $forgedExcludedDirectories = ['asdasd', 'qweqwe'];
        $expectedResult = $forgedExcludedDirectories;

        $forgedConfig = ['token' => 'bla'];

        $expectedCommand = 'find ' . $this->forgedRootDirectory . ' -name ' . $forgedConfig['token'];

        $forgedCommandResult = $this->forgedRootDirectory
            . DIRECTORY_SEPARATOR . $forgedExcludedDirectories[0]
            . DIRECTORY_SEPARATOR . $forgedConfig['token'] . PHP_EOL
            . $this->forgedRootDirectory . DIRECTORY_SEPARATOR . $forgedExcludedDirectories[1]
            . DIRECTORY_SEPARATOR . $forgedConfig['token'] . PHP_EOL;

        $this->mockedProcessRunner->shouldReceive('runAsProcess')->once()
            ->with($expectedCommand)->andReturn($forgedCommandResult);

        $result = $this->subject->getPathsToExclude([], $forgedConfig);

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

        $forgedConfig = ['token' => 'bla'];

        $expectedCommand = 'find ' . $this->forgedRootDirectory . ' -name ' . $forgedConfig['token']
            . ' -not -path "./' . $forgedAlreadyExcluded[0] . '" -not -path "./' . $forgedAlreadyExcluded[1] . '"';

        $forgedCommandResult = $this->forgedRootDirectory . DIRECTORY_SEPARATOR . $forgedExcludedDirectories[1]
            . DIRECTORY_SEPARATOR . $forgedConfig['token'] . PHP_EOL;

        $this->mockedProcessRunner->shouldReceive('runAsProcess')->once()
            ->with($expectedCommand)->andReturn($forgedCommandResult);

        $result = $this->subject->getPathsToExclude($forgedAlreadyExcluded, $forgedConfig);

        self::assertSame($expectedResult, $result);
    }
}
