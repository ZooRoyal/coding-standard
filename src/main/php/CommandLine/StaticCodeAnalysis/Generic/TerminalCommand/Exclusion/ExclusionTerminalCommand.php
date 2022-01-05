<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Exclusion;

use Zooroyal\CodingStandard\CommandLine\EnhancedFileInfo\EnhancedFileInfo;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\TerminalCommand;

interface ExclusionTerminalCommand extends TerminalCommand
{
    /**
     * Specify a set of files paths which should **NOT** be checked.
     *
     * @param array<EnhancedFileInfo> $excludesFiles
     */
    public function addExclusions(array $excludesFiles): void;
}
