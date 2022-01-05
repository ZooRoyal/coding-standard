<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Exclusion;

use Zooroyal\CodingStandard\CommandLine\EnhancedFileInfo\EnhancedFileInfo;

trait ExclusionTrait
{
    /** @var array<EnhancedFileInfo> */
    protected array $excludesFiles = [];

    /**
     * {@inheritDoc}
     */
    final public function addExclusions(array $excludesFiles): void
    {
        $this->excludesFiles = [...$this->excludesFiles, ...$excludesFiles];
    }
}
