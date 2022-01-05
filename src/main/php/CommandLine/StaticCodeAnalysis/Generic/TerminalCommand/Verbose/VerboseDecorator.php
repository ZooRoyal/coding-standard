<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Verbose;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\TerminalCommandDecorator;

class VerboseDecorator extends TerminalCommandDecorator
{
    /**
     * {@inheritDoc}
     */
    public function decorate(GenericEvent $genericEvent): void
    {
        $terminalCommand = $genericEvent->getSubject();

        if (!$terminalCommand instanceof VerboseTerminalCommand) {
            return;
        }

        $input = $genericEvent->getArgument(TerminalCommandDecorator::KEY_INPUT);
        $output = $genericEvent->getArgument(TerminalCommandDecorator::KEY_OUTPUT);

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
