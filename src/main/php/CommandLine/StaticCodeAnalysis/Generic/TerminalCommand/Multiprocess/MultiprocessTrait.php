<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Multiprocess;

trait MultiprocessTrait
{
    protected int $maxConcurrentProcesses;

    /**
     * {@inheritDoc}
     */
    public function setMaximalConcurrentProcesses(int $maxConcurrentProcesses): void
    {
        $this->maxConcurrentProcesses = $maxConcurrentProcesses;
    }
}
