<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Multiprocess;

use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\TerminalCommand;

interface MultiprocessTerminalCommand extends TerminalCommand
{
    /**
     * This method receives the maximal number of processes the TerminalCommand may use.
     */
    public function setMaximalConcurrentProcesses(int $maxConcurrentProcesses): void;
}
