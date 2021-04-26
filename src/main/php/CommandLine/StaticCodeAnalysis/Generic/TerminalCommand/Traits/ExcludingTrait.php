<?php

namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Traits;

use Zooroyal\CodingStandard\CommandLine\ValueObjects\EnhancedFileInfo;

trait ExcludingTrait
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
