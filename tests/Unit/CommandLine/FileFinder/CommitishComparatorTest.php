<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\FileFinder;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Zooroyal\CodingStandard\CommandLine\FileFinder\CommitishComparator;
use Zooroyal\CodingStandard\CommandLine\FileFinder\GitInputValidator;
use Zooroyal\CodingStandard\CommandLine\Process\ProcessRunner;
use Zooroyal\CodingStandard\Tests\Tools\SubjectFactory;

class CommitishComparatorTest extends TestCase
{
    private CommitishComparator $subject;
    /** @var array<MockInterface>|array<mixed> */
    private array $subjectParameters;

    protected function setUp(): void
    {
        $subjectFactory = new SubjectFactory();
        $buildFragments = $subjectFactory->buildSubject(
            CommitishComparator::class
        );
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
    public function isLocalBranchEqualToReturnsTrueIfCommitHashesAreEqual(): void
    {
        $mockedBranchName = 'my/mocked/branch';
        $mockedCommitHash = '123qwe0';

        $this->subjectParameters[GitInputValidator::class]->shouldReceive('isCommitishValid')->once()
            ->with($mockedBranchName)->andReturn(true);

        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')->once()
            ->with('git', 'rev-list', '-n 1', 'HEAD')->andReturn($mockedCommitHash);
        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')->once()
            ->with('git', 'rev-list', '-n 1', $mockedBranchName)->andReturn($mockedCommitHash);

        $result = $this->subject->isLocalBranchEqualTo($mockedBranchName);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function isLocalBranchEqualToReturnsFalseIfCommitHashesAreUnequal(): void
    {
        $mockedBranchName = 'my/mocked/branch';
        $mockedCommitHash = '123qwe0';
        $mockedLocalCommitHash = '0ewq321';

        $this->subjectParameters[GitInputValidator::class]->shouldReceive('isCommitishValid')->once()
            ->with($mockedBranchName)->andReturn(true);

        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')->once()
            ->with('git', 'rev-list', '-n 1', 'HEAD')->andReturn($mockedLocalCommitHash);
        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')->once()
            ->with('git', 'rev-list', '-n 1', $mockedBranchName)->andReturn($mockedCommitHash);

        $result = $this->subject->isLocalBranchEqualTo($mockedBranchName);
        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function isLocalBranchEqualToCachesLocalHeadHash(): void
    {
        $mockedBranchName = 'my/mocked/branch';
        $mockedCommitHash = '123qwe0';
        $mockedLocalCommitHash = '0ewq321';

        $this->subjectParameters[GitInputValidator::class]->shouldReceive('isCommitishValid')->twice()
            ->with($mockedBranchName)->andReturn(true);

        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')->once()
            ->with('git', 'rev-list', '-n 1', 'HEAD')->andReturn($mockedLocalCommitHash);
        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')->twice()
            ->with('git', 'rev-list', '-n 1', $mockedBranchName)->andReturn($mockedCommitHash);

        $this->subject->isLocalBranchEqualTo($mockedBranchName);
        $result = $this->subject->isLocalBranchEqualTo($mockedBranchName);
        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function isLocalBranchEqualToReturnsFalseIfParameterNoBranch(): void
    {
        $mockedBranchName = 'my/mocked/branch';
        $mockedLocalCommitHash = '0ewq321';

        $this->subjectParameters[GitInputValidator::class]->shouldReceive('isCommitishValid')->once()
            ->with($mockedBranchName)->andReturn(false);

        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')->never()
            ->with('git', 'rev-list', '-n 1', 'HEAD')->andReturn($mockedLocalCommitHash);
        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')->never()
            ->with('git', 'rev-list', '-n 1', $mockedBranchName);

        $result = $this->subject->isLocalBranchEqualTo($mockedBranchName);
        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function isLocalBranchEqualToWithNull(): void
    {
        $mockedBranchName = null;

        $this->subjectParameters[GitInputValidator::class]->shouldReceive('isCommitishValid')->once()
            ->with($mockedBranchName)->andReturn(false);

        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')->never()
            ->with('git', 'rev-list', '-n 1', 'HEAD');
        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')->never()
            ->with('git', 'rev-list', '-n 1', $mockedBranchName);

        $result = $this->subject->isLocalBranchEqualTo($mockedBranchName);
        self::assertFalse($result);
    }
}
