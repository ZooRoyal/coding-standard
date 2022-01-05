<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Fix;

use Symfony\Component\EventDispatcher\GenericEvent;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\TerminalCommandDecorator;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\ToolCommandFacet\FixableInputFacet;

class FixDecorator extends TerminalCommandDecorator
{
    /**
     * {@inheritDoc}
     */
    public function decorate(GenericEvent $genericEvent): void
    {
        $terminalCommand = $genericEvent->getSubject();

        if (!$terminalCommand instanceof FixTerminalCommand) {
            return;
        }

        $input = $genericEvent->getArgument(TerminalCommandDecorator::KEY_INPUT);
        $output = $genericEvent->getArgument(TerminalCommandDecorator::KEY_OUTPUT);

        $shouldBeFixing = $input->getOption(FixableInputFacet::OPTION_FIX);

        if (!$shouldBeFixing) {
            return;
        }

        $output->writeln('<info>Command will run in fixing mode.</info>' . PHP_EOL);

        $terminalCommand->setFixingMode(true);
    }
}
