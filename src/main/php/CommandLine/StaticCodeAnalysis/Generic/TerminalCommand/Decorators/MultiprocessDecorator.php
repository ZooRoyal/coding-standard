<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Decorators;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Zooroyal\CodingStandard\CommandLine\Library\ProcessRunner;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\AbstractToolCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\MultiprocessTerminalCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommandDecorator;

class MultiprocessDecorator implements TerminalCommandDecorator
{
    private ?int $possibleProcesses = null;

    public function __construct(private ProcessRunner $processRunner)
    {
    }

    public function decorate(GenericEvent $genericEvent): void
    {
        $terminalCommand = $genericEvent->getSubject();

        if (!$terminalCommand instanceof MultiprocessTerminalCommand) {
            return;
        }

        $this->possibleProcesses ??= (int) $this->processRunner->runAsProcess('getconf _NPROCESSORS_ONLN');

        $output = $genericEvent->getArgument(AbstractToolCommand::KEY_OUTPUT);
        $output->writeln(
            '<info>Command can use ' . $this->possibleProcesses . ' processes</info>' . PHP_EOL,
            OutputInterface::VERBOSITY_VERY_VERBOSE
        );

        $terminalCommand->setMaximalConcurrentProcesses($this->possibleProcesses);
    }

    /**
     * {@inheritDoc}
     *
     * @return array<string,array<int,int|string>>
     */
    public static function getSubscribedEvents(): array
    {
        return [AbstractToolCommand::EVENT_DECORATE_TERMINAL_COMMAND => ['decorate', 50]];
    }
}
