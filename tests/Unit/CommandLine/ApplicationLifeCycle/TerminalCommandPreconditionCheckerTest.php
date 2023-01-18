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
use Zooroyal\CodingStandard\CommandLine\ApplicationLifeCycle\TerminalCommandPreconditionChecker;
use Zooroyal\CodingStandard\CommandLine\Process\ProcessRunner;
use Zooroyal\CodingStandard\Tests\Tools\SubjectFactory;

class TerminalCommandPreconditionCheckerTest extends TestCase
{
    private TerminalCommandPreconditionChecker $subject;
    /** @var array<MockInterface> */
    private array $subjectParameters;
    /** @var array<string>  */
    private array $commandsToCheck = ['git', 'find'];

    public function setUp(): void
    {
        $subjectFactory = new SubjectFactory();
        $buildFragments = $subjectFactory->buildSubject(TerminalCommandPreconditionChecker::class);
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

        MatcherAssert::assertThat(
            $events,
            H::hasKeyValuePair(
                ConsoleEvents::COMMAND,
                [
                    'checkForTerminalCommands',
                    100,
                ],
            ),
        );
    }

    /**
     * @test
     */
    public function checkForTerminalCommands(): void
    {
        $mockedProcess = Mockery::mock(Process::class);

        $mockedProcess->shouldReceive('getExitCode')->withNoArgs()->andReturn(0);

        foreach ($this->commandsToCheck as $command) {
            $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcessReturningProcessObject')
                ->once()->with('which ' . $command)->andReturn($mockedProcess);
        }

        $this->subject->checkForTerminalCommands();
        $this->subject->checkForTerminalCommands();
    }

    /**
     * @test
     */
    public function checkForTerminalCommandsThrowsExceptionOnFailedCommand(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(1613124231);
        $this->expectExceptionMessage('The coding-standard CLI needs git to be installed and findable by \'which\'.');

        $mockedProcess = Mockery::mock(Process::class);

        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcessReturningProcessObject')->once()
            ->with('which git')->andReturn($mockedProcess);

        $mockedProcess->shouldReceive('getExitCode')->once()->withNoArgs()->andReturn(1);

        $this->subject->checkForTerminalCommands();
    }
}
