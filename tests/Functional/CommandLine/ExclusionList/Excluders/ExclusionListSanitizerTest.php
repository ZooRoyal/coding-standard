<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\Tests\Functional\CommandLine\ExclusionList\Excluders;

use PHPUnit\Framework\TestCase;
use Zooroyal\CodingStandard\CommandLine\EnhancedFileInfo\EnhancedFileInfo;
use Zooroyal\CodingStandard\CommandLine\ExclusionList\ExclusionListSanitizer;

class ExclusionListSanitizerTest extends TestCase
{
    private ExclusionListSanitizer $subject;

    protected function setUp(): void
    {
        $this->subject = new ExclusionListSanitizer();
    }

    /**
     * @test
     */
    public function sanitizeExclusionList(): void
    {
        $expectedResult = $this->prepareMockedEnhancedFileInfo([
            __DIR__ . '/../../../fixtures/asd',
            __DIR__ . '/../../../fixtures/asdqwe',
            __DIR__ . '/../../../fixtures/yxc/asd',
        ]);
        $input = array_merge(
            $expectedResult,
            $this->prepareMockedEnhancedFileInfo([
                __DIR__ . '/../../../fixtures/asd',
                __DIR__ . '/../../../fixtures/asd/asdqwe',
            ]),
        );

        $result = $this->subject->sanitizeExclusionList($input);

        self::assertSame($expectedResult, $result);
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
        foreach ($filePaths as $filePath) {
            $enhancedFileMocks[] = new EnhancedFileInfo($filePath, '/');
        }
        return $enhancedFileMocks;
    }
}
