<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Fix;

use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\DecorateEvent;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\TerminalCommandDecorator;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\ToolCommandFacet\FixableInputFacet;

class FixDecorator extends TerminalCommandDecorator
{
    /**
     * {@inheritDoc}
     */
    public function decorate(DecorateEvent $event): void
    {
        $terminalCommand = $event->getTerminalCommand();

        if (!$terminalCommand instanceof FixTerminalCommand) {
            return;
        }

        $shouldBeFixing = $event->getInput()->getOption(FixableInputFacet::OPTION_FIX);

        if (!$shouldBeFixing) {
            return;
        }

        $event->getOutput()->writeln('<info>Command will run in fixing mode.</info>' . PHP_EOL);

        $terminalCommand->setFixingMode(true);
    }
}
