<?php declare(strict_types = 1);

namespace Zooroyal\CodingStandard\CommandLine\Factories;

use Zooroyal\CodingStandard\CommandLine\ValueObjects\EnhancedFileInfo;

interface ExcluderInterface
{
    /**
     * This method returns an array of relative paths to directories which should be excluded from static code analysis.
     *
     * @param array<EnhancedFileInfo> $alreadyExcludedPaths
     * @param array<mixed>  $config
     *
     * @return array<EnhancedFileInfo>
     */
    public function getPathsToExclude(array $alreadyExcludedPaths, array $config = []): array;
}
