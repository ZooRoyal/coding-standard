<?php

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\Factories\Exclusion;

use Hamcrest\MatcherAssert;
use Hamcrest\Matchers as H;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Zooroyal\CodingStandard\CommandLine\Factories\Exclusion\StaticExcluder;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;

class StaticExcluderTest extends TestCase
{
    private StaticExcluder $subject;
    /** @var MockInterface|Environment */
    private $mockedEnvironment;
    private $forgedRootDirectory;

    protected function setUp(): void
    {
        $this->forgedRootDirectory = dirname(__DIR__, 5);

        $this->mockedEnvironment = Mockery::mock(Environment::class);

        $this->mockedEnvironment->shouldReceive('getRootDirectory')->once()
            ->withNoArgs()->andReturn($this->forgedRootDirectory);

        $this->subject = new StaticExcluder($this->mockedEnvironment);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function getPathsToExclude()
    {
        $expectedResult = ['.git', 'node_modules', 'vendor'];

        $result = $this->subject->getPathsToExclude([]);
        MatcherAssert::assertThat($result, H::hasItems(...$expectedResult));
    }
}
