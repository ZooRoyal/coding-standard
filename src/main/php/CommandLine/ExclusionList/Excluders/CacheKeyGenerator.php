<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\ExclusionList\Excluders;

use Zooroyal\CodingStandard\CommandLine\EnhancedFileInfo\EnhancedFileInfo;

class CacheKeyGenerator
{
    /**
     * Build cache key from $alreadyExcludedPaths and $config.
     *
     * @param array<EnhancedFileInfo> $alreadyExcludedPaths
     * @param array<mixed>            $config
     */
    public function generateCacheKey(array $alreadyExcludedPaths = [], array $config = []): string
    {
        $hashSource = ' ' . implode('', $alreadyExcludedPaths);

        if ($config !== []) {
            $hashSource .= md5(serialize($config));
        }

        $cacheKey = hash('md5', $hashSource);
        return $cacheKey;
    }
}
