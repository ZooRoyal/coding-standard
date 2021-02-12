<?php

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\EventSubscriber;

use Hamcrest\MatcherAssert;
use Hamcrest\Matchers as H;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Process\Process;
use Zooroyal\CodingStandard\CommandLine\EventSubscriber\TerminalCommandPreconditionChecker;
use Zooroyal\CodingStandard\CommandLine\Library\ProcessRunner;
use Zooroyal\CodingStandard\Tests\Tools\SubjectFactory;

class TerminalCommandPreconditionCheckerTest extends TestCase
{
    private TerminalCommandPreconditionChecker $subject;
    /** @var array<MockInterface> */
    private array $subjectParameters;
    private array $commandsToCheck = ['git', 'find'];

    public function setUp(): void
    {
        $subjectFactory = new SubjectFactory();
        $buildFragments = $subjectFactory->buildSubject(TerminalCommandPreconditionChecker::class);
        $this->subject = $buildFragments['subject'];
        $this->subjectParameters = $buildFragments['parameters'];
    }

    /**
     * @test
     */
    public function getSubscribedEvents()
    {
        $events = $this->subject::getSubscribedEvents();

        MatcherAssert::assertThat($events, H::hasKeyValuePair(ConsoleEvents::COMMAND, 'checkForTerminalCommands'));
    }

    /**
     * @test
     */
    public function checkForTerminalCommands()
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
    public function checkForTerminalCommandsThrowsExceptionOnFailedCommand()
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
