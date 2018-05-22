<?php
namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\FileFinders;

use Hamcrest\Matchers as H;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Zooroyal\CodingStandard\CommandLine\FileFinders\DiffCheckableFileFinder;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\Library\FileFilter;
use Zooroyal\CodingStandard\CommandLine\Library\ProcessRunner;
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
        $mockedLocalBranch  = 'blaLocalBranch';
        $mockedMergeBase    = 'alsdkfujh178290346';
        $mockedFileDiff     = 'dir1/file1' . "\n" . 'dir2/file2' . "\n";
        $mockedFiles        = ['dir1/file1', 'dir2/file2'];

        $this->subjectParameters[Environment::class]->shouldReceive('getLocalBranch')
            ->withNoArgs()->andReturn($mockedLocalBranch);
        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')
            ->with('git merge-base HEAD ' . $mockedTargetBranch)
            ->andReturn($mockedMergeBase);
        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')
            ->with('git diff --name-only --diff-filter=d ' . $mockedMergeBase)
            ->andReturn($mockedFileDiff);

        $this->subjectParameters[FileFilter::class]->shouldReceive('filterByBlacklistFilterStringAndStopword')
            ->with($mockedFiles, $mockedFilter, $mockedStopWord)
            ->andReturn($mockedFileDiff);

        $this->subject->findFiles($mockedFilter, $mockedStopWord, $mockedTargetBranch);
    }

    /**
     * @test
     */
    public function findDiffByRecursiveCommitSearch()
    {
        $mockedTargetBranch = 'blaBranch';
        $mockedFilter       = 'blaFilter';
        $mockedStopWord     = 'blaStopword';
        $mockedLocalBranch  = $mockedTargetBranch;
        $mockedFileDiff     = 'dir1/file1' . "\n" . 'dir2/file2' . "\n";
        $mockedFiles        = ['dir1/file1', 'dir2/file2'];

        $this->subjectParameters[Environment::class]->shouldReceive('getLocalBranch')
            ->withNoArgs()->andReturn($mockedLocalBranch);
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
        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')
            ->with('git diff --name-only --diff-filter=d HEAD^^')
            ->andReturn($mockedFileDiff);

        $this->subjectParameters[FileFilter::class]->shouldReceive('filterByBlacklistFilterStringAndStopword')
            ->with($mockedFiles, $mockedFilter, $mockedStopWord)
            ->andReturn($mockedFileDiff);

        $this->subject->findFiles($mockedFilter, $mockedStopWord, $mockedTargetBranch);
    }

}
