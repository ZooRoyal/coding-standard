<?php

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\Factories;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Zooroyal\CodingStandard\CommandLine\Factories\Exclusion\ExcluderInterface;
use Zooroyal\CodingStandard\CommandLine\Factories\Exclusion\ExclusionListSanitizer;
use Zooroyal\CodingStandard\CommandLine\Factories\ExclusionListFactory;

class ExclusionListFactoryTest extends TestCase
{
    /** @var ExclusionListFactory */
    private $subject;
    /** @var MockInterface|ExcluderInterface */
    private $mockedExcluder1;
    /** @var MockInterface|ExcluderInterface */
    private $mockedExcluder2;
    /** @var array<MockInterface|ExcluderInterface> */
    private $mockedExcluders;
    /** @var Mockery\LegacyMockInterface|MockInterface|ExclusionListSanitizer */
    private $mockedExclusionListSanitizer;

    protected function setUp(): void
    {
        $this->mockedExcluder1 = Mockery::mock(ExcluderInterface::class);
        $this->mockedExcluder2 = Mockery::mock(ExcluderInterface::class);
        $this->mockedExcluders = [$this->mockedExcluder1, $this->mockedExcluder2];

        $this->mockedExclusionListSanitizer = Mockery::mock(ExclusionListSanitizer::class);

        $this->subject = new ExclusionListFactory($this->mockedExcluders, $this->mockedExclusionListSanitizer);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function getBlacklistWithNoStopword(): void
    {
        $forgedResultExcluder1 = ['blub', 'bla'];
        $forgedResultExcluder2 = ['wub', 'bla/blarg'];

        $expectedSanitationInput = ['blub', 'bla', 'wub', 'bla/blarg'];
        $expectedResult = ['blub', 'bla', 'wub'];

        $this->mockedExcluder1->shouldReceive('getPathsToExclude')->once()
            ->with([], [])->andReturn($forgedResultExcluder1);
        $this->mockedExcluder2->shouldReceive('getPathsToExclude')->once()
            ->with($forgedResultExcluder1, [])->andReturn($forgedResultExcluder2);

        $this->mockedExclusionListSanitizer->shouldReceive('sanitizeExclusionList')->once()
            ->with($expectedSanitationInput)->andReturn($expectedResult);

        $result1 = $this->subject->build();
        $result2 = $this->subject->build();

        self::assertSame($expectedResult, $result1);
        self::assertSame($result1, $result2);
    }

    /**
     * @test
     */
    public function getBlacklistWithStopword(): void
    {
        $forgedResultExcluder1 = ['blub', 'bla'];
        $forgedResultExcluder2 = ['wub', 'bla/blarg'];
        $forgedStopword = 'halt!';

        $expectedSanitationInput = ['blub', 'bla', 'wub', 'bla/blarg'];
        $expectedResult = ['blub', 'bla', 'wub'];
        $expectedConfig = ['token' => $forgedStopword];

        $this->mockedExcluder1->shouldReceive('getPathsToExclude')->once()
            ->with([], $expectedConfig)->andReturn($forgedResultExcluder1);
        $this->mockedExcluder2->shouldReceive('getPathsToExclude')->once()
            ->with($forgedResultExcluder1, $expectedConfig)->andReturn($forgedResultExcluder2);

        $this->mockedExclusionListSanitizer->shouldReceive('sanitizeExclusionList')->once()
            ->with($expectedSanitationInput)->andReturn($expectedResult);

        $result1 = $this->subject->build($forgedStopword);
        $result2 = $this->subject->build($forgedStopword);

        self::assertSame($expectedResult, $result1);
        self::assertSame($result1, $result2);
    }

    /**
     * @test
     */
    public function getBlacklistWithoutSanitizing(): void
    {
        $forgedResultExcluder1 = ['blub', 'bla'];
        $forgedResultExcluder2 = ['wub', 'bla/blarg'];

        $expectedResult = ['blub', 'bla', 'wub', 'bla/blarg'];

        $this->mockedExcluder1->shouldReceive('getPathsToExclude')->once()
            ->with([], [])->andReturn($forgedResultExcluder1);
        $this->mockedExcluder2->shouldReceive('getPathsToExclude')->once()
            ->with($forgedResultExcluder1, [])->andReturn($forgedResultExcluder2);

        $this->mockedExclusionListSanitizer->shouldReceive('sanitizeExclusionList')->never();

        $result1 = $this->subject->build('', false);
        $result2 = $this->subject->build('', false);

        self::assertSame($expectedResult, $result1);
        self::assertSame($result1, $result2);
    }
}
