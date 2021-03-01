<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\Factories;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Zooroyal\CodingStandard\CommandLine\Factories\ExcluderInterface;
use Zooroyal\CodingStandard\CommandLine\Factories\Excluders\ExclusionListSanitizer;
use Zooroyal\CodingStandard\CommandLine\Factories\ExclusionListFactory;
use Zooroyal\CodingStandard\CommandLine\ValueObjects\EnhancedFileInfo;

class ExclusionListFactoryTest extends TestCase
{
    private ExclusionListFactory $subject;
    private MockInterface|ExcluderInterface $mockedExcluder1;
    private MockInterface|ExcluderInterface $mockedExcluder2;
    /** @var array<MockInterface|ExcluderInterface> */
    private array $mockedExcluders;
    private MockInterface|ExclusionListSanitizer $mockedExclusionListSanitizer;

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
        $forgedItemsToBeExcluded = $this->prepareMockedEnhancedFileInfo(['bla/blarg']);
        $forgedItemsToStay = $this->prepareMockedEnhancedFileInfo(['wub']);

        $forgedResultExcluder1 = $this->prepareMockedEnhancedFileInfo(['blub', 'bla']);
        $forgedResultExcluder2 = array_merge($forgedItemsToStay, $forgedItemsToBeExcluded);

        $expectedSanitationInput = array_merge($forgedResultExcluder1, $forgedResultExcluder2);
        $expectedResult = array_merge($forgedResultExcluder1, $forgedItemsToStay);

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
        $forgedItemsToBeExcluded = $this->prepareMockedEnhancedFileInfo(['bla/blarg']);
        $forgedItemsToStay = $this->prepareMockedEnhancedFileInfo(['wub']);

        $forgedResultExcluder1 = $this->prepareMockedEnhancedFileInfo(['blub', 'bla']);
        $forgedResultExcluder2 = array_merge($forgedItemsToStay, $forgedItemsToBeExcluded);
        $forgedStopword = 'halt!';

        $expectedSanitationInput = array_merge($forgedResultExcluder1, $forgedResultExcluder2);
        $expectedResult = array_merge($forgedResultExcluder1, $forgedItemsToStay);
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
        $forgedResultExcluder1 = $this->prepareMockedEnhancedFileInfo(['blub', 'bla']);
        $forgedResultExcluder2 = $this->prepareMockedEnhancedFileInfo(['wub', 'bla/blarg']);

        $expectedResult = array_merge($forgedResultExcluder1, $forgedResultExcluder2);

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

    /**
     * Converts file paths to enhancedFileInfos
     *
     * @param array<string> $filePaths
     *
     * @return array<EnhancedFileInfo>
     */
    private function prepareMockedEnhancedFileInfo(array $filePaths): array
    {
        $enhancedFileMocks = [];
        for ($i=0, $iMax = count($filePaths); $i < $iMax; $i++) {
            $enhancedFileMocks[] = Mockery::mock(EnhancedFileInfo::class);
        }
        return $enhancedFileMocks;
    }
}
