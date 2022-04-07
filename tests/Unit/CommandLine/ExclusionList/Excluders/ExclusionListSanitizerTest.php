<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\Factories\ExclusionList\Excluders;

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
        $expectedResult = $this->prepareMockedEnhancedFileInfo(['bla', 'schackalacka', 'bum/schackalacka']);
        $input = array_merge($expectedResult, $this->prepareMockedEnhancedFileInfo(['bla', 'bla/blub',]));

        $result = $this->subject->sanitizeExclusionList($input);

        self::assertSame($expectedResult, $result);
    }

    /**
     * Converts file paths to enhancedFileInfos
     *
     * @param array<string> $filePaths
     *
     * @return array<\Zooroyal\CodingStandard\CommandLine\EnhancedFileInfo\EnhancedFileInfo>
     */
    private function prepareMockedEnhancedFileInfo(array $filePaths): array
    {
        $forgedRootDirectory = '/IAmRoot';
        $enhancedFileMocks = [];
        foreach ($filePaths as $filePath) {
            $enhancedFileMocks[] = new EnhancedFileInfo(
                $forgedRootDirectory . '/' . $filePath,
                $forgedRootDirectory
            );
        }
        return $enhancedFileMocks;
    }
}