<?php

namespace Zooroyal\CodingStandard\CommandLine\Factories;

use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\Library\ProcessRunner;

class BlacklistFactory
{
    /** @var Environment */
    private $environment;
    /** @var array<string, mixed> */
    private $blackListCache = [];
    private ProcessRunner $processRunner;

    /**
     * BlacklistFactory constructor.
     *
     * @param Environment   $environment
     * @param ProcessRunner $processRunner
     */
    public function __construct(
        Environment $environment,
        ProcessRunner $processRunner
    ) {
        $this->environment = $environment;
        $this->processRunner = $processRunner;
    }

    /**
     * This function computes a blacklist of directories which should not be checked.
     *
     * @param string $token
     * @param bool   $deDuped
     *
     * @return string[]
     */
    public function build(string $token = '', bool $deDuped = true): array
    {
        if (array_key_exists($token, $this->blackListCache)) {
            return $this->blackListCache[$token];
        }

        $rawExcludePathsByFileByToken = [];

        if ($token !== '') {
            $rawExcludePathsByFileByToken = $this->findTokenDirectories($token);
        }
        $rawExcludePathsByFileByGit = $this->findGitDirectories();
        $rawExcludePathsByBlacklist = $this->findDirectoriesFromEnvironment($this->environment);

        $rawExcludePaths = array_merge(
            $rawExcludePathsByBlacklist,
            $rawExcludePathsByFileByToken,
            $rawExcludePathsByFileByGit
        );

        $filteredArray = $deDuped === true ? $this->deDupePaths($rawExcludePaths) : $rawExcludePaths;
        $this->blackListCache[$token] = $filteredArray;

        return $filteredArray;
    }

    /**
     * Searches for directories containing stopword files.
     *
     * @param string $token
     *
     * @return string[]
     */
    public function findTokenDirectories(string $token): array
    {
        $rootDirectory = $this->environment->getRootDirectory();

        $finderResult = $this->processRunner->runAsProcess(
            'find ' . $rootDirectory . ' -name ' . $token
        );

        $rawExcludePathsByToken = explode(PHP_EOL, trim($finderResult));

        $directories = array_map('dirname', $rawExcludePathsByToken);
        $relativeDirectories = array_map(
            fn($value) => substr($value, strlen($rootDirectory) + 1),
            $directories
        );
        return $relativeDirectories;
    }

    /**
     * This method filters subpaths of paths already existing in $rawExcludePaths
     *
     * @param string[] $rawExcludePaths
     *
     * @return string[]
     */
    private function deDupePaths(array $rawExcludePaths)
    {
        $filteredArray = $rawExcludePaths;
        $count = count($filteredArray);
        for ($i = 0; $count > $i; $i++) {
            if (!isset($filteredArray[$i])) {
                continue;
            }
            $item = $filteredArray[$i];
            $filteredArray = array_filter(
                $filteredArray,
                function ($value, $key) use ($item, $i) {
                    if ($key === $i) {
                        return true;
                    }
                    return strpos($value, $item) !== 0;
                },
                ARRAY_FILTER_USE_BOTH
            );
        }
        $filteredArray = array_values($filteredArray);

        return $filteredArray;
    }

    /**
     * Finds submodules of in the project directory.
     *
     * @return string[]
     */
    private function findGitDirectories(): array
    {
        $rootDirectory = $this->environment->getRootDirectory();
        $finderResult = $this->processRunner->runAsProcess(
            'find ' . $rootDirectory . ' -type d -mindepth 2 -name .git'
        );

        if (empty($finderResult)) {
            return [];
        }

        $rawExcludePathsByFileByGit = explode(PHP_EOL, trim($finderResult));

        $relativeDirectories = array_map(
            static fn($value) => substr(dirname($value), strlen($rootDirectory) + 1),
            $rawExcludePathsByFileByGit
        );

        return $relativeDirectories;
    }

    /**
     * Finds blacklisted directories by Environment.
     *
     * @param Environment $environment
     *
     * @return string[]
     */
    private function findDirectoriesFromEnvironment(Environment $environment): array
    {
        $directories = $environment->getBlacklistedDirectories();
        $rootDirectory = $environment->getRootDirectory();

        $filteredDirectories = array_filter(
            $directories,
            static function ($value) use ($rootDirectory) {
                return is_dir($rootDirectory . DIRECTORY_SEPARATOR . $value);
            }
        );

        return $filteredDirectories;
    }
}
