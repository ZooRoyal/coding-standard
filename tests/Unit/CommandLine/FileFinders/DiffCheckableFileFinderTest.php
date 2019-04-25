<?php

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\FileFinders;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Zooroyal\CodingStandard\CommandLine\Factories\GitChangeSetFactory;
use Zooroyal\CodingStandard\CommandLine\FileFinders\DiffCheckableFileFinder;
use Zooroyal\CodingStandard\CommandLine\Library\GitChangeSetFilter;
use Zooroyal\CodingStandard\CommandLine\Library\ProcessRunner;
use Zooroyal\CodingStandard\CommandLine\ValueObjects\GitChangeSet;
use Zooroyal\CodingStandard\Tests\Tools\SubjectFactory;

class DiffCheckableFileFinderTest extends TestCase
{
    /** @var MockInterface[]|mixed[] */
    private $subjectParameters;
    /** @var DiffCheckableFileFinder */
    private $subject;

    protected function setUp()
    {
        $subjectFactory = new SubjectFactory();
        $buildFragments = $subjectFactory->buildSubject(DiffCheckableFileFinder::class);
        $this->subject = $buildFragments['subject'];
        $this->subjectParameters = $buildFragments['parameters'];
    }

    protected function tearDown()
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @test
     * @expectedException Symfony\Component\Console\Exception\InvalidArgumentException
     */
    public function findFilesWithoutTargetBranchMakesNoSense()
    {
        $this->subject->findFiles('', '', '', '');
    }

    /**
     * @test
     */
    public function findDiffByGitDiff()
    {
        $mockedTargetBranch = 'blaBranch';
        $mockedFilter = 'blaFilter';
        $mockedBlacklistToken = 'blaStopword';
        $mockedWhitelistToken = 'blaGO';
        $mockedMergeBase = 'alsdkfujh178290346';
        $mockedFileDiff = 'dir1/file1' . "\n" . 'dir2/file2' . "\n";
        $mockedFiles = ['dir1/file1', 'dir2/file2'];
        $mockedChangeSet = new GitChangeSet($mockedFiles, $mockedMergeBase);

        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')
            ->with('git', 'merge-base', 'HEAD', $mockedTargetBranch)
            ->andReturn($mockedMergeBase);
        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')
            ->with('git', 'diff', '--name-only', '--diff-filter=d', $mockedMergeBase)
            ->andReturn($mockedFileDiff);

        $this->subjectParameters[GitChangeSetFactory::class]->shouldReceive('build')->once()
            ->with($mockedFiles, $mockedMergeBase)->andReturn($mockedChangeSet);

        $this->subjectParameters[GitChangeSetFilter::class]->shouldReceive('filter')
            ->with($mockedChangeSet, $mockedFilter, $mockedBlacklistToken, $mockedWhitelistToken)
            ->andReturn($mockedFileDiff);

        $this->subject->findFiles($mockedFilter, $mockedBlacklistToken, $mockedWhitelistToken, $mockedTargetBranch);
    }
}
