<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Multiprocess;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Zooroyal\CodingStandard\CommandLine\Process\ProcessRunner;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\TerminalCommandDecorator;

class MultiprocessDecorator extends TerminalCommandDecorator
{
    private ProcessRunner $processRunner;
    private ?int $possibleProcesses = null;

    public function __construct(ProcessRunner $processRunner)
    {
        $this->processRunner = $processRunner;
    }

    public function decorate(GenericEvent $genericEvent): void
    {
        $terminalCommand = $genericEvent->getSubject();

        if (!$terminalCommand instanceof MultiprocessTerminalCommand) {
            return;
        }

        $this->possibleProcesses ??= (int) $this->processRunner->runAsProcess('getconf _NPROCESSORS_ONLN');

        $output = $genericEvent->getArgument(TerminalCommandDecorator::KEY_OUTPUT);
        $output->writeln(
            '<info>Command can use ' . $this->possibleProcesses . ' processes</info>' . PHP_EOL,
            OutputInterface::VERBOSITY_VERY_VERBOSE
        );

        $terminalCommand->setMaximalConcurrentProcesses($this->possibleProcesses);
    }
}
