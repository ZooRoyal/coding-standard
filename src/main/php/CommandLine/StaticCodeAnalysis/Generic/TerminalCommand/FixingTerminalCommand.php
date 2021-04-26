<?php

namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand;

interface FixingTerminalCommand extends TerminalCommand
{
    /**
     * Lets the command know if it should run in fixing mode.
     *
     * @param bool $fixingMode
     */
    public function setFixingMode(bool $fixingMode): void;
}
