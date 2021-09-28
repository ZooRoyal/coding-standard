<?php
declare(strict_types = 1);
namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand;

interface FixingTerminalCommand extends TerminalCommand
{
    /**
     * Lets the command know if it should run in fixing mode.
     */
    public function setFixingMode(bool $fixingMode): void;
}
