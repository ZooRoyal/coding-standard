<?php

namespace Zooroyal\CodingStandard\CommandLine\Factories\Exclusion;

use Zooroyal\CodingStandard\CommandLine\Factories\EnhancedFileInfoFactory;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\Library\ProcessRunner;
use Zooroyal\CodingStandard\CommandLine\ValueObjects\EnhancedFileInfo;
use function Safe\substr;

class GitPathsExcluder implements ExcluderInterface
{
    private Environment $environment;
    private ProcessRunner $processRunner;
    private EnhancedFileInfoFactory $enhancedFileInfoFactory;

    /**
     * GitPathsExcluder constructor.
     *
     * @param Environment             $environment
     * @param ProcessRunner           $processRunner
     * @param EnhancedFileInfoFactory $enhancedFileInfoFactory
     */
    public function __construct(
        Environment $environment,
        ProcessRunner $processRunner,
        EnhancedFileInfoFactory $enhancedFileInfoFactory
    ) {
        $this->environment = $environment;
        $this->processRunner = $processRunner;
        $this->enhancedFileInfoFactory = $enhancedFileInfoFactory;
    }

    /**
     * The methods searches for Git submodules and returns their paths.
     *
     * @param array<string> $alreadyExcludedPaths
     * @param array<mixed>  $config
     *
     * @return array<EnhancedFileInfo>
     */
    public function getPathsToExclude(array $alreadyExcludedPaths, array $config = []): array
    {
        $excludeParameters = '';
        if (!empty($alreadyExcludedPaths)) {
            $excludeParameters = ' -not -path "./' . implode('" -not -path "./', $alreadyExcludedPaths) . '"';
        }

        $rootDirectory = $this->environment->getRootDirectory()->getRealPath();
        $finderResult = $this->processRunner->runAsProcess(
            'find ' . $rootDirectory . ' -type d -mindepth 2 -name .git' . $excludeParameters
        );

        if (empty($finderResult)) {
            return [];
        }

        $rawExcludePathsByFileByGit = explode(PHP_EOL, trim($finderResult));

        $relativeDirectories = array_map(
            static fn($value): string => substr(dirname($value), strlen($rootDirectory) + 1),
            $rawExcludePathsByFileByGit
        );

        $result = $this->enhancedFileInfoFactory->buildFromArrayOfPaths($relativeDirectories);

        return $result;
    }
}
