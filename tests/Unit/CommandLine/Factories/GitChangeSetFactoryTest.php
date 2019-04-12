<?php
namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\Factories;

use PHPUnit\Framework\TestCase;
use Zooroyal\CodingStandard\CommandLine\Factories\GitChangeSetFactory;

class GitChangeSetFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function buildReturns()
    {
        $expectedFiles      = ['asd', 'qwe'];
        $expectedCommitHash = 'asdasdasd1223213';

        $subject = new GitChangeSetFactory();
        $result  = $subject->build($expectedFiles, $expectedCommitHash);

        $resultingFiles      = $result->getFiles();
        $resultingCommitHash = $result->getCommitHash();

        self::assertSame($expectedFiles, $resultingFiles);
        self::assertSame($expectedCommitHash, $resultingCommitHash);
    }
}
