<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\FileFinders;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Zooroyal\CodingStandard\CommandLine\FileFinders\DiffCheckableFileFinder;
use Zooroyal\CodingStandard\CommandLine\Git\GitChangeSet;
use Zooroyal\CodingStandard\CommandLine\Git\GitChangeSetFactory;
use Zooroyal\CodingStandard\CommandLine\Git\GitChangeSetFilter;
use Zooroyal\CodingStandard\CommandLine\Process\ProcessRunner;
use Zooroyal\CodingStandard\Tests\Tools\SubjectFactory;

class DiffCheckableFileFinderTest extends TestCase
{
    /** @var array<MockInterface>|array<mixed> */
    private array $subjectParameters;
    private DiffCheckableFileFinder $subject;

    protected function setUp(): void
    {
        $subjectFactory = new SubjectFactory();
        $buildFragments = $subjectFactory->buildSubject(DiffCheckableFileFinder::class);
        $this->subject = $buildFragments['subject'];
        $this->subjectParameters = $buildFragments['parameters'];
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function findFilesWithoutTargetBranchMakesNoSense(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(1553857649);
        $this->subject->findFiles([], '', '', '');
    }

    /**
     * @test
     */
    public function findDiffByGitDiff(): void
    {
        $mockedTargetBranch = 'blaBranch';
        $mockedAllowedFileEndings = ['blaFilter'];
        $mockedBlacklistToken = 'blaStopword';
        $mockedWhitelistToken = 'blaGO';
        $mockedMergeBase = 'alsdkfujh178290346';
        $mockedFileDiff = 'composer.json' . "\n" . 'package.json' . "\n";
        $mockedFiles = ['composer.json', 'package.json'];
        $mockedChangeSet = Mockery::mock(GitChangeSet::class);

        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')
            ->with('git', 'merge-base', 'HEAD', $mockedTargetBranch)
            ->andReturn($mockedMergeBase);
        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')
            ->with('git', 'diff', '--name-only', '--diff-filter=d', $mockedMergeBase)
            ->andReturn($mockedFileDiff);

        $this->subjectParameters[GitChangeSetFactory::class]->shouldReceive('build')->once()
            ->with($mockedFiles, $mockedMergeBase)->andReturn($mockedChangeSet);

        $this->subjectParameters[GitChangeSetFilter::class]->shouldReceive('filter')
            ->with($mockedChangeSet, $mockedAllowedFileEndings, $mockedBlacklistToken, $mockedWhitelistToken)
            ->andReturn($mockedFileDiff);

        $this->subject->findFiles($mockedAllowedFileEndings, $mockedBlacklistToken, $mockedWhitelistToken, $mockedTargetBranch);
    }
}
