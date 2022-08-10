<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Fix;

use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\TerminalCommand;

interface FixTerminalCommand extends TerminalCommand
{
    /**
     * Lets the command know if it should run in fixing mode.
     */
    public function setFixingMode(bool $fixingMode): void;
}
