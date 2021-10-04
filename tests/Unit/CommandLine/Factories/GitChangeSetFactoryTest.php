<?php declare(strict_types = 1);

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\Factories;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Zooroyal\CodingStandard\CommandLine\Factories\EnhancedFileInfoFactory;
use Zooroyal\CodingStandard\CommandLine\Factories\GitChangeSetFactory;
use Zooroyal\CodingStandard\CommandLine\ValueObjects\EnhancedFileInfo;
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
    public function buildReturns(): void
    {
        $forgedFiles = ['asd', 'qwe'];
        $expectedCommitHash = 'asdasdasd1223213';
        $forgedEnhancedFileInfo = Mockery::mock(EnhancedFileInfo::class);

        $this->subjectParameters[EnhancedFileInfoFactory::class]->shouldReceive('buildFromArrayOfPaths')->once()
            ->with($forgedFiles)->andReturn([$forgedEnhancedFileInfo]);

        $result = $this->subject->build($forgedFiles, $expectedCommitHash);

        $resultingFiles = $result->getFiles();
        $resultingCommitHash = $result->getCommitHash();

        self::assertSame([$forgedEnhancedFileInfo], $resultingFiles);
        self::assertSame($expectedCommitHash, $resultingCommitHash);
    }
}
