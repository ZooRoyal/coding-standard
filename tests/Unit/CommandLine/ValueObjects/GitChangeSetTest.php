<?php

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\ValueObjects;

use PHPUnit\Framework\TestCase;
use Symplify\SmartFileSystem\SmartFileInfo;
use Zooroyal\CodingStandard\CommandLine\ValueObjects\GitChangeSet;

class GitChangeSetTest extends TestCase
{
    /**
     * @test
     */
    public function readWriteCycle(): void
    {
        $expectedFiles = [new SmartFileInfo('config'), new SmartFileInfo('tests')];
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
        $smartFileInfo = new SmartFileInfo('config');
        $forgedFiles = [$smartFileInfo];
        $expectedFiles = [$smartFileInfo, new SmartFileInfo('tests')];
        $expectedCommitHash = 'asdasdasd1223213';

        $subject = new GitChangeSet($forgedFiles, $expectedCommitHash);

        $subject->setFiles($expectedFiles);
        $resultingFiles = $subject->getFiles();

        self::assertSame($expectedFiles, $resultingFiles);
    }
}
