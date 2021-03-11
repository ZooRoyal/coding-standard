<?php

namespace Zooroyal\CodingStandard\CommandLine\ToolAdapters;

interface FixerSupportInterface
{
    /**
     * Tries to fix violations.
     *
     * @param string|null $targetBranch
     */
    public function fixViolations($targetBranch = ''): ?int;
}
