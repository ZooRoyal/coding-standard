<?php
declare(strict_types = 1);
namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand;

use InvalidArgumentException;

interface VerboseTerminalCommand extends TerminalCommand
{
    /**
     * Specify a set of files paths which should **NOT** be checked.
     *
     * @param int $verbosityLevel The constants from \Symfony\Component\Console\Output\OutputInterface should be used.
     *
     * @throws InvalidArgumentException
     */
    public function addVerbosityLevel(int $verbosityLevel): void;
}
