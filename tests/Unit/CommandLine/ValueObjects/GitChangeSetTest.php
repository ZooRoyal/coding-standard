<?php

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\ValueObjects;

use Mockery;
use PHPUnit\Framework\TestCase;
use Zooroyal\CodingStandard\CommandLine\ValueObjects\EnhancedFileInfo;
use Zooroyal\CodingStandard\CommandLine\ValueObjects\GitChangeSet;

class GitChangeSetTest extends TestCase
{
    /**
     * @test
     */
    public function readWriteCycle(): void
    {
        $expectedFiles = [Mockery::mock(EnhancedFileInfo::class), Mockery::mock(EnhancedFileInfo::class)];
        $expectedCommitHash = 'asdasdasd1223213';

        $subject = new GitChangeSet($expectedFiles, $expectedCommitHash);

        $resultingFiles = $subject->getFiles();
        $resultingCommitHash = $subject->getCommitHash();

        self::assertSame($expectedFiles, $resultingFiles);
        self::assertSame($expectedCommitHash, $resultingCommitHash);
    }

    /**
     * @test
     */
    public function readWriteCycleWithSetter(): void
    {
        $mockeryEnhancedFileInfo = Mockery::mock(EnhancedFileInfo::class);
        $forgedFiles = [$mockeryEnhancedFileInfo];
        $expectedFiles = [$mockeryEnhancedFileInfo, Mockery::mock(EnhancedFileInfo::class)];
        $expectedCommitHash = 'asdasdasd1223213';

        $subject = new GitChangeSet($forgedFiles, $expectedCommitHash);

        $subject->setFiles($expectedFiles);
        $resultingFiles = $subject->getFiles();

        self::assertSame($expectedFiles, $resultingFiles);
    }
}
