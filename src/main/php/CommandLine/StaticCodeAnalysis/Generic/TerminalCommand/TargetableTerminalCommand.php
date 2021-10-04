<?php declare(strict_types = 1);

namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand;

use Zooroyal\CodingStandard\CommandLine\ValueObjects\EnhancedFileInfo;

interface TargetableTerminalCommand extends TerminalCommand
{
    /**
     * Specifies a set of files which have to be check.
     *
     * @param array<EnhancedFileInfo> $targetedFiles
     */
    public function addTargets(array $targetedFiles): void;
}
