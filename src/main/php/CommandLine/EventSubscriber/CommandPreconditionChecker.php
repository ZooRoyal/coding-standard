<?php

namespace Zooroyal\CodingStandard\CommandLine\EventSubscriber;

use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zooroyal\CodingStandard\CommandLine\Library\ProcessRunner;

/**
 * Class CommandPreconditionChecker
 *
 * This EventSubscriber is meant to be subscribed to the EventDispatcher of the coding-standard application. It
 * subscribes to the event just before the first command is run and makes sure, that the command is run from inside a
 * git directory.
 */
class CommandPreconditionChecker implements EventSubscriberInterface
{
    private ProcessRunner $processRunner;
    private ?int $exitCode = null;
    private string $command = 'git rev-parse --git-dir';

    /**
     * CommandPreconditionChecker constructor.
     *
     * @param ProcessRunner $processRunner
     */
    public function __construct(ProcessRunner $processRunner)
    {
        $this->processRunner = $processRunner;
    }

    /**
     * Returns the command event to be subscribed to.
     *
     * @return string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [ConsoleEvents::COMMAND => 'checkForGit'];
    }

    /**
     * Calls a git command to make sure, that the current working directory is managed by git. If so it does nothing.
     * If not a exception is thrown.
     *
     * @throws RuntimeException
     */
    public function checkForGit(): void
    {
        if ($this->exitCode === null) {
            $process = $this->processRunner->runAsProcessReturningProcessObject($this->command);

            $this->exitCode = $process->getExitCode();
        }

        if ($this->exitCode !== 0) {
            throw new RuntimeException(
                'The coding-standard CLI can\'t be used outside of a git context.',
                1612348705
            );
        }
    }
}
