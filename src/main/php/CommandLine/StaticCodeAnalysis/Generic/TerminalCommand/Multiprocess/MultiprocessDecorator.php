<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Multiprocess;

use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\Process\ProcessRunner;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\DecorateEvent;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\TerminalCommandDecorator;

class MultiprocessDecorator extends TerminalCommandDecorator
{
    private ?int $possibleProcesses = null;

    public function __construct(private readonly ProcessRunner $processRunner)
    {
    }

    public function decorate(DecorateEvent $event): void
    {
        $terminalCommand = $event->getTerminalCommand();

        if (!$terminalCommand instanceof MultiprocessTerminalCommand) {
            return;
        }

        $this->possibleProcesses ??= (int) $this->processRunner->runAsProcess('getconf _NPROCESSORS_ONLN');

        $event->getOutput()->writeln(
            '<info>Command can use ' . $this->possibleProcesses . ' processes</info>' . PHP_EOL,
            OutputInterface::VERBOSITY_VERY_VERBOSE,
        );

        $terminalCommand->setMaximalConcurrentProcesses($this->possibleProcesses);
    }
}
