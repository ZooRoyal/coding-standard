<?php

namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Traits;

use Zooroyal\CodingStandard\CommandLine\ValueObjects\EnhancedFileInfo;

trait TargetableTrait
{
    /** @var array<EnhancedFileInfo> */
    protected array $targetedFiles = [];

    /**
     * Specifies a set of files which have to be check.
     *
     * @param array<EnhancedFileInfo> $targetedFiles
     */
    final public function addTargets(array $targetedFiles): void
    {
        $this->targetedFiles = [...$this->targetedFiles, ...$targetedFiles];
    }
}
