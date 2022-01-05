<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic;

use DI\Annotation\Inject;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\NoUsefulCommandFoundException;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\TerminalCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\TerminalCommandDecorator;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\TerminalCommandRunner;

abstract class AbstractToolCommand extends Command
{
    protected string $exclusionListToken;
    /** @var array<string> */
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
            TerminalCommandDecorator::KEY_EXCLUSION_LIST_TOKEN => $this->exclusionListToken,
            TerminalCommandDecorator::KEY_ALLOWED_FILE_ENDINGS => $this->allowedFileEndings,
            TerminalCommandDecorator::KEY_INPUT => $input,
            TerminalCommandDecorator::KEY_OUTPUT => $output,
        ];

        $event = new GenericEvent($this->terminalCommand, $arguments);
        // @phpstan-ignore-next-line because there is a hack in the symfony/event-dispatcher-contract regarding dispatch
        $this->eventDispatcher->dispatch(
            $event,
            TerminalCommandDecorator::EVENT_DECORATE_TERMINAL_COMMAND
        );

        try {
            $exitCode = $this->terminalCommandRunner->run($this->terminalCommand);
        } catch (NoUsefulCommandFoundException $exception) {
            $output->writeln('Skipping tool.');
            $output->writeln(
                'Reason to skip tool: ' . $exception->getMessage() . PHP_EOL
                . 'Code: ' . $exception->getCode(),
                OutputInterface::VERBOSITY_VERBOSE
            );
            $exitCode = 0;
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
