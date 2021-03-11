<?php

namespace Zooroyal\CodingStandard\CommandLine\ToolAdapters;

interface ToolAdapterInterface
{
    /**
     * Search for violations by using PHPCS and write finds to screen.
     *
     * @param string|null $targetBranch
     */
    public function writeViolationsToOutput($targetBranch = ''): ?int;
}
