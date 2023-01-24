<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand;

interface TerminalCommand
{
    /**
     * Returns a array of command parts. The parts can be used to build a bash command by imploding them with ' ' as
     * glue.
     *
     * @return array<string>
     */
    public function toArray(): array;

    /**
     * Returns a string which can be used in a bash shell to execute the command
     */
    public function __toString(): string;
}
