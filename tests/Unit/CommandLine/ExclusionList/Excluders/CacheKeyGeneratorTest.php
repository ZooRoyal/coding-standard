<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\ExclusionList\Excluders;

use Mockery;
use PHPUnit\Framework\TestCase;
use Zooroyal\CodingStandard\CommandLine\EnhancedFileInfo\EnhancedFileInfo;
use Zooroyal\CodingStandard\CommandLine\ExclusionList\Excluders\CacheKeyGenerator;

class CacheKeyGeneratorTest extends TestCase
{
    private CacheKeyGenerator $subject;

    protected function setUp(): void
    {
        $this->subject = new CacheKeyGenerator();
    }

    /**
     * @test
     */
    public function generateCacheKeyGeneratesSameKeyWithSameInput(): void
    {
        $mockedEnhancedFileInfo = Mockery::mock(EnhancedFileInfo::class);
        $mockedEnhancedFileInfo1 = Mockery::mock(EnhancedFileInfo::class);
        $forgedAlreadyExcludedPaths = [$mockedEnhancedFileInfo, $mockedEnhancedFileInfo1];
        $forgedConfig = ['asd' => 'qwe'];
        $result = $this->subject->generateCacheKey($forgedAlreadyExcludedPaths, $forgedConfig);
        $result1 = $this->subject->generateCacheKey($forgedAlreadyExcludedPaths, $forgedConfig);

        self::assertSame($result, $result1);
    }

    /**
     * DataProvider for generateCacheKeyGeneratesDifferentKeyWithDifferentInput.
     *
     * @return array<string,array<string,array<int|string,EnhancedFileInfo|string>>>
     */
    public function generateCacheKeyGeneratesDifferentKeyWithDifferentInputDataProvider(): array
    {
        $defaultFiles = [Mockery::mock(EnhancedFileInfo::class), Mockery::mock(EnhancedFileInfo::class)];
        $defaultConfig = ['asd' => 'qwe'];

        return [
            'different Files' => [
                'files1' => $defaultFiles,
                'files2' => [Mockery::mock(EnhancedFileInfo::class)],
                'config1' => $defaultConfig,
                'config2' => $defaultConfig,
            ],
            'different Config' => [
                'files1' => $defaultFiles,
                'files2' => $defaultFiles,
                'config1' => $defaultConfig,
                'config2' => ['asd' => 'qwe', 'yxc' => 'cvb'],
            ],
            'different All' => [
                'files1' => $defaultFiles,
                'files2' => [Mockery::mock(EnhancedFileInfo::class)],
                'config1' => $defaultConfig,
                'config2' => ['asd' => 'qwe', 'yxc' => 'cvb'],
            ],

        ];
    }

    /**
     * @test
     * @dataProvider generateCacheKeyGeneratesDifferentKeyWithDifferentInputDataProvider
     *
     * @param array<EnhancedFileInfo> $files1
     * @param array<EnhancedFileInfo> $files2
     * @param array<string,string>    $config1
     * @param array<string,string>    $config2
     */
    public function generateCacheKeyGeneratesDifferentKeyWithDifferentInput(
        array $files1,
        array $files2,
        array $config1,
        array $config2
    ): void {
        $result = $this->subject->generateCacheKey($files1, $config1);
        $result1 = $this->subject->generateCacheKey($files2, $config2);

        self::assertNotSame($result, $result1);
    }
}
