<?php

namespace Zooroyal\CodingStandard\CommandLine\ToolAdapters;

interface ToolAdapterInterface
{
    /**
     * Search for violations by using PHPCS and write finds to screen.
     *
     * @param string|null $targetBranch
     * @param bool $processIsolation
     *
     * @return int|null
     */
    public function writeViolationsToOutput($targetBranch = '', bool $processIsolation = false): ?int;
}
