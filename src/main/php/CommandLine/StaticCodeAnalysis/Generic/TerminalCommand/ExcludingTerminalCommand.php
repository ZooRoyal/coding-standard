<?php
declare(strict_types = 1);
namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand;

use Zooroyal\CodingStandard\CommandLine\ValueObjects\EnhancedFileInfo;

interface ExcludingTerminalCommand extends TerminalCommand
{
    /**
     * Specify a set of files paths which should **NOT** be checked.
     *
     * @param array<EnhancedFileInfo> $excludesFiles
     */
    public function addExclusions(array $excludesFiles): void;
}
