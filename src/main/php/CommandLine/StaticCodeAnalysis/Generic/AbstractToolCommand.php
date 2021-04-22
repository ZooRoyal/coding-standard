<?php

namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic;

use DI\Annotation\Inject;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\TerminalCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\TerminalCommandRunner;

abstract class AbstractToolCommand extends Command
{
    /** @var string */
    public const EVENT_DECORATE_TERMINAL_COMMAND = 'eventDecorateTerminalCommand';
    public const KEY_EXCLUSION_LIST_TOKEN = 'exclusionListToken';
    public const KEY_ALLOWED_FILE_ENDINGS = 'allowedFileEndings';
    public const KEY_INPUT = 'input';
    public const KEY_OUTPUT = 'output';
    protected string $exclusionListToken;
    protected array $allowedFileEndings;
    protected TerminalCommand $terminalCommand;
    protected string $terminalCommandName;
    private TerminalCommandRunner $terminalCommandRunner;
    private EventDispatcherInterface $eventDispatcher;

    /**
     * {@inheritDoc}
     *
     * @throws RuntimeException
     */
    public function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $output->writeln(PHP_EOL . '<comment>Running ' . $this->terminalCommandName . '</comment>');

        $arguments = [
            self::KEY_EXCLUSION_LIST_TOKEN => $this->exclusionListToken,
            self::KEY_ALLOWED_FILE_ENDINGS => $this->allowedFileEndings,
            self::KEY_INPUT => $input,
            self::KEY_OUTPUT => $output,
        ];

        $event = new GenericEvent($this->terminalCommand, $arguments);
        $this->eventDispatcher->dispatch($event, self::EVENT_DECORATE_TERMINAL_COMMAND);

        try {
            $exitCode = $this->terminalCommandRunner->run($this->terminalCommand);
        } catch (Exception $exception) {
            throw new RuntimeException(
                'Something went wrong while executing a terminal command.',
                1617786765,
                $exception
            );
        }

        return $exitCode;
    }

    /**
     * This method accepts all dependencies needed to use this class properly.
     * It's annotated for use with PHP-DI.
     *
     * @param TerminalCommandRunner    $terminalCommandRunner
     * @param EventDispatcherInterface $eventDispatcher
     *
     * @see http://php-di.org/doc/annotations.html
     *
     * @Inject
     */
    public function injectDependenciesToolCommand(
        TerminalCommandRunner $terminalCommandRunner,
        EventDispatcherInterface $eventDispatcher
    ): void {
        $this->terminalCommandRunner = $terminalCommandRunner;
        $this->eventDispatcher = $eventDispatcher;
    }
}
