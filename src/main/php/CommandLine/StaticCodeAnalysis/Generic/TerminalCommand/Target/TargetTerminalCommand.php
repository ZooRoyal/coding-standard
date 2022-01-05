<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Target;

use Zooroyal\CodingStandard\CommandLine\EnhancedFileInfo\EnhancedFileInfo;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\TerminalCommand;

interface TargetTerminalCommand extends TerminalCommand
{
    /**
     * Specifies a set of files which have to be check.
     *
     * @param array<EnhancedFileInfo> $targetedFiles
     */
    public function addTargets(array $targetedFiles): void;
}
