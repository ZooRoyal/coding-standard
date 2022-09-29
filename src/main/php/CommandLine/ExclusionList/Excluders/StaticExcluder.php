<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\ExclusionList\Excluders;

use Zooroyal\CodingStandard\CommandLine\EnhancedFileInfo\EnhancedFileInfo;
use Zooroyal\CodingStandard\CommandLine\EnhancedFileInfo\EnhancedFileInfoFactory;

class StaticExcluder implements ExcluderInterface
{
    /** @var array<string> */
    private const PATHS_TO_EXCLUDE
        = [
            '.git',
            '.idea',
            '.vagrant',
            'node_modules',
            'vendor',
            'bower_components',
            '.pnpm',
            '.pnpm-store',
        ];
    private EnhancedFileInfoFactory $enhancedFileInfoFactory;
    /** @var array<EnhancedFileInfo> */
    private array $cache = [];

    /**
     * StaticExcluder constructor.
     */
    public function __construct(
        EnhancedFileInfoFactory $enhancedFileInfoFactory
    ) {
        $this->enhancedFileInfoFactory = $enhancedFileInfoFactory;
    }

    /**
     * This method searches for default directories and returns them if it finds them.
     *
     * @param array<EnhancedFileInfo> $alreadyExcludedPaths
     * @param array<mixed>            $config
     *
     * @return array<EnhancedFileInfo>
     */
    public function getPathsToExclude(array $alreadyExcludedPaths, array $config = []): array
    {
        if (!empty($this->cache)) {
            return $this->cache;
        }

        $result = $this->enhancedFileInfoFactory->buildFromArrayOfPaths(array_values(self::PATHS_TO_EXCLUDE));

        $this->cache = $result;
        return $result;
    }
}
