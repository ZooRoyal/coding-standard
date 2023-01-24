<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\FileFinder;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Zooroyal\CodingStandard\CommandLine\FileFinder\AllCheckableFileFinder;
use Zooroyal\CodingStandard\CommandLine\FileFinder\GitChangeSet;
use Zooroyal\CodingStandard\CommandLine\FileFinder\GitChangeSetFactory;
use Zooroyal\CodingStandard\CommandLine\FileFinder\GitChangeSetFilter;
use Zooroyal\CodingStandard\CommandLine\Process\ProcessRunner;
use Zooroyal\CodingStandard\Tests\Tools\SubjectFactory;

class AllCheckableFileFinderTest extends TestCase
{
    /** @var array<MockInterface>|array<mixed> */
    private array $subjectParameters;
    private AllCheckableFileFinder $subject;

    protected function setUp(): void
    {
        $subjectFactory = new SubjectFactory();
        $buildFragments = $subjectFactory->buildSubject(AllCheckableFileFinder::class);
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
    public function findAll(): void
    {
        $mockedAllowedFileEndings = ['asd'];
        $expectedBlacklistToken = 'StopMeNow';
        $mockedGitChangeSet = Mockery::mock(GitChangeSet::class);

        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')
            ->with('git', 'ls-files')->andReturn('asd' . "\n" . 'qwe' . "\n");

        $this->subjectParameters[GitChangeSetFactory::class]->shouldReceive('build')
            ->with(['asd', 'qwe'])->andReturn($mockedGitChangeSet);

        $this->subjectParameters[GitChangeSetFilter::class]->shouldReceive('filter')->once()
            ->with($mockedGitChangeSet, $mockedAllowedFileEndings, $expectedBlacklistToken);

        $result = $this->subject->findFiles($mockedAllowedFileEndings, $expectedBlacklistToken);

        self::assertSame($mockedGitChangeSet, $result);
    }

    /**
     * @test
     */
    public function findAllWithNoParameter(): void
    {
        $mockedGitChangeSet = Mockery::mock(GitChangeSet::class);

        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')
            ->with('git', 'ls-files')->andReturn('asd' . "\n" . 'qwe' . "\n");

        $this->subjectParameters[GitChangeSetFactory::class]->shouldReceive('build')
            ->with(['asd', 'qwe'])->andReturn($mockedGitChangeSet);

        $this->subjectParameters[GitChangeSetFilter::class]->shouldReceive('filter')->once()
            ->with($mockedGitChangeSet, [], '');

        $result = $this->subject->findFiles();

        self::assertSame($mockedGitChangeSet, $result);
    }
}
