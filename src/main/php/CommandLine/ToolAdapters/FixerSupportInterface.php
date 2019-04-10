<?php
namespace Zooroyal\CodingStandard\CommandLine\ToolAdapters;

interface FixerSupportInterface
{
    /**
     * Tries to fix violations.
     *
     * @param string|null $targetBranch
     * @param bool   $processIsolation
     *
     * @return int|null
     */
    public function fixViolations($targetBranch = '', bool $processIsolation = false);
}
