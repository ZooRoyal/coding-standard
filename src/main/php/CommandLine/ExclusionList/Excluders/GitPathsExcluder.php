<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\ExclusionList\Excluders;

use Zooroyal\CodingStandard\CommandLine\EnhancedFileInfo\EnhancedFileInfo;
use Zooroyal\CodingStandard\CommandLine\EnhancedFileInfo\EnhancedFileInfoFactory;
use Zooroyal\CodingStandard\CommandLine\Environment\Environment;
use Zooroyal\CodingStandard\CommandLine\Process\ProcessRunner;

class GitPathsExcluder implements ExcluderInterface
{
    /** @var array<string,array<EnhancedFileInfo>> */
    private array $cache = [];

    /**
     * GitPathsExcluder constructor.
     */
    public function __construct(
        private readonly Environment $environment,
        private readonly ProcessRunner $processRunner,
        private readonly EnhancedFileInfoFactory $enhancedFileInfoFactory,
        private readonly CacheKeyGenerator $cacheKeyGenerator,
    ) {
    }

    /**
     * The methods search for Git submodules and returns their paths.
     *
     * @param array<EnhancedFileInfo> $alreadyExcludedPaths
     * @param array<mixed>            $config
     *
     * @return array<EnhancedFileInfo>
     */
    public function getPathsToExclude(array $alreadyExcludedPaths, array $config = []): array
    {
        $cacheKey = $this->cacheKeyGenerator->generateCacheKey($alreadyExcludedPaths);

        if (!empty($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $excludeParameters = '';
        if (!empty($alreadyExcludedPaths)) {
            $excludeParameters = ' -not -path "./' . implode('" -not -path "./', $alreadyExcludedPaths) . '"';
        }

        $rootDirectory = $this->environment->getRootDirectory()->getRealPath();
        $finderResult = $this->processRunner->runAsProcess(
            'find ' . $rootDirectory . ' -mindepth 2 -name .git' . $excludeParameters,
        );

        if (empty($finderResult)) {
            $this->cache[$cacheKey] = [];
            return [];
        }

        $rawExcludePathsByFileByGit = explode(PHP_EOL, trim($finderResult));

        $relativeDirectories = array_map(
            static fn($value): string => substr(dirname($value), strlen($rootDirectory) + 1),
            $rawExcludePathsByFileByGit,
        );

        $result = $this->enhancedFileInfoFactory->buildFromArrayOfPaths($relativeDirectories);

        $this->cache[$cacheKey] = $result;
        return $result;
    }
}
