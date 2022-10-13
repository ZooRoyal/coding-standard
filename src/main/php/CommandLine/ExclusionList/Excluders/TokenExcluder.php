<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\ExclusionList\Excluders;

use Zooroyal\CodingStandard\CommandLine\EnhancedFileInfo\EnhancedFileInfo;
use Zooroyal\CodingStandard\CommandLine\EnhancedFileInfo\EnhancedFileInfoFactory;
use Zooroyal\CodingStandard\CommandLine\Environment\Environment;
use Zooroyal\CodingStandard\CommandLine\Process\ProcessRunner;

class TokenExcluder implements ExcluderInterface
{
    private Environment $environment;
    private ProcessRunner $processRunner;
    private EnhancedFileInfoFactory $enhancedFileInfoFactory;
    private CacheKeyGenerator $cacheKeyGenerator;
    /** @var array<string,array<EnhancedFileInfo>> */
    private array $cache = [];

    /**
     * TokenExcluder constructor.
     */
    public function __construct(
        Environment $environment,
        ProcessRunner $processRunner,
        EnhancedFileInfoFactory $enhancedFileInfoFactory,
        CacheKeyGenerator $cacheKeyGenerator
    ) {
        $this->environment = $environment;
        $this->processRunner = $processRunner;
        $this->enhancedFileInfoFactory = $enhancedFileInfoFactory;
        $this->cacheKeyGenerator = $cacheKeyGenerator;
    }

    /**
     * This method searches for paths which contain a file by the name of $config['token']. It will not search in
     * $alreadyExcludedPaths to speed things up.
     *
     * @param array<EnhancedFileInfo> $alreadyExcludedPaths
     * @param array<mixed>            $config
     *
     * @return array<EnhancedFileInfo>
     */
    public function getPathsToExclude(array $alreadyExcludedPaths, array $config = []): array
    {
        if (!isset($config['token'])) {
            return [];
        }

        $cacheKey = $this->cacheKeyGenerator->generateCacheKey($alreadyExcludedPaths, $config);

        if (!empty($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $token = $config['token'];

        $rootDirectory = $this->environment->getRootDirectory()->getRealPath();

        $excludeParameters = '';
        if (!empty($alreadyExcludedPaths)) {
            $excludeParameters = ' -not -path "./' . implode('" -not -path "./', $alreadyExcludedPaths) . '"';
        }
        $finderResult = $this->processRunner->runAsProcess(
            'find ' . $rootDirectory . ' -name ' . $token . $excludeParameters
        );

        if (empty($finderResult)) {
            $this->cache[$cacheKey] = [];
            return [];
        }

        $rawExcludePathsByToken = explode(PHP_EOL, trim($finderResult));
        $absoluteDirectories = array_map('dirname', $rawExcludePathsByToken);

        $result = $this->enhancedFileInfoFactory->buildFromArrayOfPaths($absoluteDirectories);

        $this->cache[$cacheKey] = $result;
        return $result;
    }
}
