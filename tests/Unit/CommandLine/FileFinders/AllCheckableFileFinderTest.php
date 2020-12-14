<?php

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\FileFinders;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Zooroyal\CodingStandard\CommandLine\Factories\GitChangeSetFactory;
use Zooroyal\CodingStandard\CommandLine\FileFinders\AllCheckableFileFinder;
use Zooroyal\CodingStandard\CommandLine\Library\GitChangeSetFilter;
use Zooroyal\CodingStandard\CommandLine\Library\ProcessRunner;
use Zooroyal\CodingStandard\CommandLine\ValueObjects\GitChangeSet;
use Zooroyal\CodingStandard\Tests\Tools\SubjectFactory;

class AllCheckableFileFinderTest extends TestCase
{
    /** @var MockInterface[]|mixed[] */
    private $subjectParameters;
    /** @var AllCheckableFileFinder */
    private $subject;

    protected function setUp()
    {
        $subjectFactory = new SubjectFactory();
        $buildFragments = $subjectFactory->buildSubject(AllCheckableFileFinder::class);
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
     */
    public function findAll()
    {
        $mockedAllowedFileEndings = ['asd'];
        $expectedBlacklistToken = 'StopMeNow';
        $mockedGitChangeSet = Mockery::mock(GitChangeSet::class);

        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')
            ->with('git', 'ls-files')->andReturn('asd' . "\n" . 'qwe' . "\n");

        $this->subjectParameters[GitChangeSetFactory::class]->shouldReceive('build')
            ->with(['asd', 'qwe'], null)->andReturn($mockedGitChangeSet);

        $this->subjectParameters[GitChangeSetFilter::class]->shouldReceive('filter')
            ->with($mockedGitChangeSet, $mockedAllowedFileEndings, $expectedBlacklistToken);

        $result = $this->subject->findFiles($mockedAllowedFileEndings, $expectedBlacklistToken);

        self::assertSame($mockedGitChangeSet, $result);
    }

    /**
     * @test
     */
    public function findAllWithNoParameter()
    {
        $mockedGitChangeSet = Mockery::mock(GitChangeSet::class);

        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')
            ->with('git', 'ls-files')->andReturn('asd' . "\n" . 'qwe' . "\n");

        $this->subjectParameters[GitChangeSetFactory::class]->shouldReceive('build')
            ->with(['asd', 'qwe'], null)->andReturn($mockedGitChangeSet);

        $this->subjectParameters[GitChangeSetFilter::class]->shouldReceive('filter')
            ->with($mockedGitChangeSet, [], '');

        $result = $this->subject->findFiles();

        self::assertSame($mockedGitChangeSet, $result);
    }
}
