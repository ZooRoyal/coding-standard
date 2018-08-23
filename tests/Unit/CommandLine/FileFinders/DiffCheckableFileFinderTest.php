<?php
namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\FileFinders;

use Hamcrest\Matchers as H;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Zooroyal\CodingStandard\CommandLine\Factories\GitChangeSetFactory;
use Zooroyal\CodingStandard\CommandLine\FileFinders\DiffCheckableFileFinder;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\Library\FileFilter;
use Zooroyal\CodingStandard\CommandLine\Library\ProcessRunner;
use Zooroyal\CodingStandard\CommandLine\ValueObjects\GitChangeSet;
use Zooroyal\CodingStandard\Tests\Tools\SubjectFactory;

class DiffCheckableFileFinderTest extends TestCase
{
    /** @var MockInterface[]|mixed[] */
    private $subjectParameters;
    /** @var DiffCheckableFileFinder */
    private $subject;
    /** @var string */
    private $mockedRootDirectory = '/my/root';

    protected function setUp()
    {
        $subjectFactory          = new SubjectFactory();
        $buildFragments          = $subjectFactory->buildSubject(DiffCheckableFileFinder::class);
        $this->subject           = $buildFragments['subject'];
        $this->subjectParameters = $buildFragments['parameters'];

        $this->subjectParameters[Environment::class]->shouldReceive('getRootDirectory')
            ->withNoArgs()->andReturn($this->mockedRootDirectory);
    }

    protected function tearDown()
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function findDiffByGitDiff()
    {
        $mockedTargetBranch = 'blaBranch';
        $mockedFilter       = 'blaFilter';
        $mockedStopWord     = 'blaStopword';
        $mockedMergeBase    = 'alsdkfujh178290346';
        $mockedFileDiff     = 'dir1/file1' . "\n" . 'dir2/file2' . "\n";
        $mockedFiles        = ['dir1/file1', 'dir2/file2'];
        $mockedChangeSet    = new GitChangeSet($mockedFiles, $mockedMergeBase);

        $this->subjectParameters[Environment::class]->shouldReceive('isLocalBranchEqualTo')->once()
            ->with($mockedTargetBranch)->andReturn(false);
        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')
            ->with('git merge-base HEAD ' . $mockedTargetBranch)
            ->andReturn($mockedMergeBase);
        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')
            ->with('git diff --name-only --diff-filter=d ' . $mockedMergeBase)
            ->andReturn($mockedFileDiff);

        $this->subjectParameters[GitChangeSetFactory::class]->shouldReceive('build')->once()
            ->with($mockedFiles, $mockedMergeBase)->andReturn($mockedChangeSet);

        $this->subjectParameters[FileFilter::class]->shouldReceive('filterByBlacklistFilterStringAndStopword')
            ->with($mockedChangeSet, $mockedFilter, $mockedStopWord)
            ->andReturn($mockedFileDiff);

        $this->subject->findFiles($mockedFilter, $mockedStopWord, $mockedTargetBranch);
    }

    public function findDiffByRecursiveCommitSearchDataProvider()
    {
        return [
            'with same branch'  => ['branch' => 'blaaarg', 'target' => 'blaaarg', 'isLocalBranchEqualToCount' => 1],
            'with empty target' => ['branch' => 'blaaarg', 'target' => null, 'isLocalBranchEqualToCount' => 0],
        ];
    }

    /**
     * @test
     * @dataProvider findDiffByRecursiveCommitSearchDataProvider
     */
    public function findDiffByRecursiveCommitSearch($branch, $target, $isLocalBranchEqualToCount)
    {
        $mockedFilter           = 'blaFilter';
        $mockedStopWord         = 'blaStopword';
        $mockedTargetCommitHash = 'asdasdqwe1231';
        $mockedFiles            = ['dir1/file1', 'dir2/file2'];
        $mockedChangeSet        = new GitChangeSet($mockedFiles, $mockedTargetCommitHash);

        $this->subjectParameters[Environment::class]->shouldReceive('isLocalBranchEqualTo')
            ->times($isLocalBranchEqualToCount)->with($branch)->andReturn(true);
        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')
            ->with(H::startsWith('git cat-file -t HEAD'))
            ->andReturn('commit', 'commit', 'tag');
        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')
            ->with('git branch -a --contains HEAD | wc -l')
            ->andReturn('1');
        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')
            ->with('git branch -a --contains HEAD^ | wc -l')
            ->andReturn('1');
        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')
            ->with('git branch -a --contains HEAD^^ | wc -l')
            ->andReturn('2');
        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')->once()
            ->with('git rev-parse ' . 'HEAD^^')
            ->andReturn($mockedTargetCommitHash);
        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')
            ->with('git diff --name-only --diff-filter=d ' . $mockedTargetCommitHash)
            ->andReturn('dir1/file1' . "\n" . 'dir2/file2' . "\n");
        $this->subjectParameters[GitChangeSetFactory::class]->shouldReceive('build')->once()
            ->with($mockedFiles, $mockedTargetCommitHash)->andReturn($mockedChangeSet);
        $this->subjectParameters[FileFilter::class]->shouldReceive('filterByBlacklistFilterStringAndStopword')
            ->with($mockedChangeSet, $mockedFilter, $mockedStopWord);

        $this->subject->findFiles($mockedFilter, $mockedStopWord, $target);
        self::assertSame($mockedChangeSet->getFiles(), $mockedFiles);
        self::assertSame($mockedChangeSet->getCommitHash(), $mockedTargetCommitHash);
    }

}
