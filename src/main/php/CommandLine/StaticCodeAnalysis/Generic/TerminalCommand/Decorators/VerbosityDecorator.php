<?php
declare(strict_types = 1);
namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Decorators;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\AbstractToolCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\VerboseTerminalCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommandDecorator;

class VerbosityDecorator implements TerminalCommandDecorator
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

        $input = $genericEvent->getArgument(AbstractToolCommand::KEY_INPUT);
        $output = $genericEvent->getArgument(AbstractToolCommand::KEY_OUTPUT);

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

    /**
     * {@inheritDoc}
     *
     * @return array<string,array<int,int|string>>
     */
    public static function getSubscribedEvents()
    {
        return [AbstractToolCommand::EVENT_DECORATE_TERMINAL_COMMAND => ['decorate', 50]];
    }
}
