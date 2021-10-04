<?php
declare(strict_types = 1);
namespace Zooroyal\CodingStandard\CommandLine\EventSubscriber;

use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zooroyal\CodingStandard\CommandLine\Library\ProcessRunner;

/**
 * Class TerminalCommandPreconditionChecker
 *
 * This EventSubscriber is meant to be subscribed to the EventDispatcher of the coding-standard application. It
 * subscribes to the event just before the first command is run and makes sure, that all dependencies on commandline
 * tools are met.
 */
class TerminalCommandPreconditionChecker implements EventSubscriberInterface
{
    private ProcessRunner $processRunner;
    /** @var array<string,int> */
    private array $results = [];
    /** @var array<string> */
    private const COMMANDS = ['git', 'find'];

    /**
     * TerminalCommandPreconditionChecker constructor.
     */
    public function __construct(ProcessRunner $processRunner)
    {
        $this->processRunner = $processRunner;
    }

    /**
     * Returns the command event to be subscribed to.
     *
     * @return array<string, array<int, int|string>>
     */
    public static function getSubscribedEvents(): array
    {
        return [ConsoleEvents::COMMAND => ['checkForTerminalCommands', 100]];
    }

    /**
     * This method checks for the existence of the command line tools found in $this->commands. If one does not exist
     * a exception will be thrown.
     *
     * @throws RuntimeException
     */
    public function checkForTerminalCommands(): void
    {
        foreach (self::COMMANDS as $command) {
            if (!isset($this->results[$command])) {
                $process = $this->processRunner->runAsProcessReturningProcessObject('which ' . $command);

                $this->results[$command] = $process->getExitCode();
            }

            if ($this->results[$command] !== 0) {
                throw new RuntimeException(
                    'The coding-standard CLI needs ' . $command . ' to be installed and findable by \'which\'.',
                    1613124231
                );
            }
        }
    }
}
