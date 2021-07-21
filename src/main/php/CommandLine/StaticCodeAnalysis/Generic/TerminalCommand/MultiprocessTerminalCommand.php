<?php
declare(strict_types = 1);
namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand;

interface MultiprocessTerminalCommand extends TerminalCommand
{
    /**
     * This method receives the maximal number of processes the TerminalCommand may use.
     *
     * @param int $maxConcurrentProcesses
     */
    public function setMaximalConcurrentProcesses(int $maxConcurrentProcesses): void;
}
