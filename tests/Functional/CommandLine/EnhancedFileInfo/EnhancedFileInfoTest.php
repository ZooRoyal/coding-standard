<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\Tests\Functional\CommandLine\EnhancedFileInfo;

use PHPUnit\Framework\TestCase;
use SplFileInfo;
use Zooroyal\CodingStandard\CommandLine\EnhancedFileInfo\EnhancedFileInfo;

class EnhancedFileInfoTest extends TestCase
{
    /**
     * @test
     */
    public function constructionWithBasePath(): void
    {
        $forgedBasePath = __DIR__;
        $forgedFilename = __FILE__;
        $expectedRelativePathname = basename(__FILE__);

        $subject = new EnhancedFileInfo($forgedFilename, $forgedBasePath);
        $result = $subject->getRelativePathname();

        self::assertInstanceOf(SplFileInfo::class, $subject);
        self::assertSame($result, $expectedRelativePathname);
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

    /**
     * Data provider for isSubdirectoryOfReturnsCorrectValues.
     *
     * @return array<string,array<string,bool|string>>
     */
    public function isSubdirectoryOfReturnsCorrectValuesDataProvider(): array
    {
        return [
            'is subdirectory' => ['directory' => dirname(__DIR__), 'subdirectory' => __DIR__, 'expectation' => true],
            'is not subdirectory' => [
                'directory' => __DIR__,
                'subdirectory' => dirname(__DIR__),
                'expectation' => false,
            ],
            'is partial name' => [
                'directory' => __DIR__ . '/../../fixtures/asdqwe',
                'subdirectory' => __DIR__ . '/../../fixtures/asd',
                'expectation' => false,
            ],
        ];
    }

    /**
     * @test
     * @dataProvider isSubdirectoryOfReturnsCorrectValuesDataProvider
     */
    public function isSubdirectoryOfReturnsCorrectValues(
        string $directory,
        string $subdirectory,
        bool $expectation
    ): void {
        $object = new EnhancedFileInfo($directory, '/');
        $subject = new EnhancedFileInfo($subdirectory, '/');

        $result = $subject->isSubdirectoryOf($object);

        self::assertSame($expectation, $result);
    }
}
