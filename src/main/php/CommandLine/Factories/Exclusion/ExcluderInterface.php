<?php

namespace Zooroyal\CodingStandard\CommandLine\Factories\Exclusion;

interface ExcluderInterface
{
    /**
     * This method returns an array of relative paths to directories which should be excluded from static code analysis.
     *
     * @param array<string> $alreadyExcludedPaths
     * @param array<mixed>  $config
     *
     * @return array<string>
     */
    public function getPathsToExclude(array $alreadyExcludedPaths, array $config = []): array;
}
