<?php

namespace Zooroyal\CodingStandard\CommandLine\Factories\Exclusion;

use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\Library\ProcessRunner;
use function Safe\substr;

class GitPathsExcluder implements ExcluderInterface
{
    /** @var Environment */
    private Environment $environment;
    /** @var ProcessRunner */
    private ProcessRunner $processRunner;

    /**
     * GitPathsExcluder constructor.
     *
     * @param Environment   $environment
     * @param ProcessRunner $processRunner
     */
    public function __construct(Environment $environment, ProcessRunner $processRunner)
    {
        $this->environment = $environment;
        $this->processRunner = $processRunner;
    }

    /**
     * The methods searches for Git submodules and returns their paths.
     *
     * @param array<string> $alreadyExcludedPaths
     * @param array<mixed>  $config
     *
     * @return array<string>
     */
    public function getPathsToExclude(array $alreadyExcludedPaths, array $config = []): array
    {
        $excludeParameters = '';
        if (!empty($alreadyExcludedPaths)) {
            $excludeParameters = ' -not -path "./' . implode('" -not -path "./', $alreadyExcludedPaths) . '"';
        }

        $rootDirectory = $this->environment->getRootDirectory();
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

        return $relativeDirectories;
    }
}
