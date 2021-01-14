<?php

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\Factories;

use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symplify\SmartFileSystem\SmartFileInfo;
use Zooroyal\CodingStandard\CommandLine\Factories\GitChangeSetFactory;
use Zooroyal\CodingStandard\CommandLine\Factories\SmartFileInfoFactory;
use Zooroyal\CodingStandard\Tests\Tools\SubjectFactory;

class GitChangeSetFactoryTest extends TestCase
{
    private GitChangeSetFactory $subject;
    /** @var array<MockInterface>  */
    private array $subjectParameters;

    protected function setUp(): void
    {
        $subjectFactory = new SubjectFactory();
        $buildFragments = $subjectFactory->buildSubject(GitChangeSetFactory::class);
        $this->subject = $buildFragments['subject'];
        $this->subjectParameters = $buildFragments['parameters'];
    }

    /**
     * @test
     */
    public function buildReturns()
    {
        $forgedFiles = ['asd', 'qwe'];
        $expectedCommitHash = 'asdasdasd1223213';
        $forgedSmartFileInfo = new SmartFileInfo(__FILE__);

        $this->subjectParameters[SmartFileInfoFactory::class]->shouldReceive('buildFromArrayOfPaths')->once()
            ->with($forgedFiles)->andReturn([$forgedSmartFileInfo]);

        $result = $this->subject->build($forgedFiles, $expectedCommitHash);

        $resultingFiles = $result->getFiles();
        $resultingCommitHash = $result->getCommitHash();

        self::assertSame([$forgedSmartFileInfo], $resultingFiles);
        self::assertSame($expectedCommitHash, $resultingCommitHash);
    }
}
