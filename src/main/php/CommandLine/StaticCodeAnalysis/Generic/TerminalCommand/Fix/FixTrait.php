<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Fix;

trait FixTrait
{
    protected bool $fixingMode = false;

    /**
     * {@inheritDoc}
     */
    public function setFixingMode(bool $fixingMode): void
    {
        $this->fixingMode = $fixingMode;
    }
}
