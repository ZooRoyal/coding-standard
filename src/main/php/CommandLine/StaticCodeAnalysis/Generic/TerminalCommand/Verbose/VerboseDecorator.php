<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Verbose;

use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\DecorateEvent;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\TerminalCommandDecorator;

class VerboseDecorator extends TerminalCommandDecorator
{
    /**
     * {@inheritDoc}
     */
    public function decorate(DecorateEvent $event): void
    {
        $terminalCommand = $event->getTerminalCommand();

        if (!$terminalCommand instanceof VerboseTerminalCommand) {
            return;
        }

        $input = $event->getInput();
        $output = $event->getOutput();

        if ($input->getOption('verbose') === true) {
            $terminalCommand->addVerbosityLevel(OutputInterface::VERBOSITY_VERBOSE);
            $output->writeln(
                '<info>Command will be executed verbosely</info>' . PHP_EOL,
                OutputInterface::VERBOSITY_VERBOSE
            );
        } elseif ($input->getOption('quiet') === true) {
            $terminalCommand->addVerbosityLevel(OutputInterface::VERBOSITY_QUIET);
        }
    }
}
