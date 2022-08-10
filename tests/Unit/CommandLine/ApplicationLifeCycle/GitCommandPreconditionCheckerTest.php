<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\ApplicationLifeCycle;

use Hamcrest\MatcherAssert;
use Hamcrest\Matchers as H;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Process\Process;
use Zooroyal\CodingStandard\CommandLine\ApplicationLifeCycle\GitCommandPreconditionChecker;
use Zooroyal\CodingStandard\CommandLine\Process\ProcessRunner;
use Zooroyal\CodingStandard\Tests\Tools\SubjectFactory;

class GitCommandPreconditionCheckerTest extends TestCase
{
    private GitCommandPreconditionChecker $subject;
    /** @var array<MockInterface> */
    private array $subjectParameters;

    public function setUp(): void
    {
        $subjectFactory = new SubjectFactory();
        $buildFragments = $subjectFactory->buildSubject(GitCommandPreconditionChecker::class);
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
    public function getSubscribedEvents(): void
    {
        $events = $this->subject::getSubscribedEvents();

        MatcherAssert::assertThat($events, H::hasKeyValuePair(ConsoleEvents::COMMAND, ['checkForGit', 50]));
    }

    /**
     * @test
     */
    public function checkForGitRunsCommand(): void
    {
        $mockedProcess = Mockery::mock(Process::class);

        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcessReturningProcessObject')->once()
            ->with('git rev-parse --git-dir')->andReturn($mockedProcess);

        $mockedProcess->shouldReceive('getExitCode')->once()->withNoArgs()->andReturn(0);

        $this->subject->checkForGit();
        $this->subject->checkForGit();
    }

    /**
     * @test
     */
    public function checkForGitThrowsExceptionOnFailedCommand(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(1612348705);
        $this->expectExceptionMessage('The coding-standard CLI can\'t be used outside of a git context.');

        $mockedProcess = Mockery::mock(Process::class);

        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcessReturningProcessObject')->once()
            ->with('git rev-parse --git-dir')->andReturn($mockedProcess);

        $mockedProcess->shouldReceive('getExitCode')->once()->withNoArgs()->andReturn(1);

        $this->subject->checkForGit();
    }
}
