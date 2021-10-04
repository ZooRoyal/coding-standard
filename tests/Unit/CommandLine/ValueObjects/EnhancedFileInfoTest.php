<?php declare(strict_types = 1);

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\ValueObjects;

use PHPUnit\Framework\TestCase;
use SplFileInfo;
use Zooroyal\CodingStandard\CommandLine\ValueObjects\EnhancedFileInfo;

class EnhancedFileInfoTest extends TestCase
{
    /**
     * @test
     */
    public function constructionWithBasePath(): void
    {
        $forgedBasePath = __DIR__;
        $forgedFilename = __FILE__;
        $expectedRelativPathname = basename(__FILE__);

        $subject = new EnhancedFileInfo($forgedFilename, $forgedBasePath);
        $result = $subject->getRelativePathname();

        self::assertInstanceOf(SplFileInfo::class, $subject);
        self::assertSame($result, $expectedRelativPathname);
    }

    /**
     * @test
     */
    public function constructionWithBasePathEqualToFilePath(): void
    {
        $forgedBasePath = __FILE__;
        $forgedFilename = __FILE__;

        $subject = new EnhancedFileInfo($forgedFilename, $forgedBasePath);
        $result = $subject->getRelativePathname();

        self::assertInstanceOf(SplFileInfo::class, $subject);
        self::assertSame('.', $result);
    }

    /**
     * @test
     */
    public function endsWithReturnsIntendedValues(): void
    {
        $forgedBasePath = __DIR__;
        $forgedFilename = __FILE__;

        $subject = new EnhancedFileInfo($forgedFilename, $forgedBasePath);
        $result1 = $subject->endsWith('Test.php');
        $result2 = $subject->endsWith('blarg');

        self::assertTrue($result1);
        self::assertFalse($result2);
    }

    /**
     * @test
     */
    public function startsWithReturnsIntendedValues(): void
    {
        $forgedBasePath = __DIR__;
        $forgedFilename = __FILE__;

        $subject = new EnhancedFileInfo($forgedFilename, $forgedBasePath);
        $result1 = $subject->startsWith('/');
        $result2 = $subject->startsWith('blarg');

        self::assertTrue($result1);
        self::assertFalse($result2);
    }
}
