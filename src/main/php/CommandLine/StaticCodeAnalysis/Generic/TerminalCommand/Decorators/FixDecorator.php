<?php
declare(strict_types = 1);
namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Decorators;

use Symfony\Component\EventDispatcher\GenericEvent;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\AbstractToolCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\FixingTerminalCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommandDecorator;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\ToolCommandFacet\FixableInputFacet;

class FixDecorator implements TerminalCommandDecorator
{
    /**
     * {@inheritDoc}
     */
    public function decorate(GenericEvent $genericEvent): void
    {
        $terminalCommand = $genericEvent->getSubject();

        if (!$terminalCommand instanceof FixingTerminalCommand) {
            return;
        }

        $input = $genericEvent->getArgument(AbstractToolCommand::KEY_INPUT);
        $output = $genericEvent->getArgument(AbstractToolCommand::KEY_OUTPUT);

        $shouldBeFixing = $input->getOption(FixableInputFacet::OPTION_FIX);

        if (!$shouldBeFixing) {
            return;
        }

        $output->writeln('<info>Command will run in fixing mode.</info>' . PHP_EOL);

        $terminalCommand->setFixingMode(true);
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
