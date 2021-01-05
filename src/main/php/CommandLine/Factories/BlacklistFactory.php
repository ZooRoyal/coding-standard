<?php

namespace Zooroyal\CodingStandard\CommandLine\Factories;

use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\Library\FinderToPathsConverter;

class BlacklistFactory
{
    /** @var FinderToPathsConverter */
    private $finderToRealPathConverter;
    /** @var Environment */
    private $environment;
    /** @var FinderFactory */
    private $finderFactory;
    /** @var array<string, mixed> */
    private $blackListCache = [];

    /**
     * BlacklistFactory constructor.
     *
     * @param FinderToPathsConverter $finderToRealPathConverter
     * @param Environment $environment
     * @param FinderFactory $finderFactory
     */
    public function __construct(
        FinderToPathsConverter $finderToRealPathConverter,
        Environment $environment,
        FinderFactory $finderFactory
    ) {
        $this->finderToRealPathConverter = $finderToRealPathConverter;
        $this->environment = $environment;
        $this->finderFactory = $finderFactory;
    }

    /**
     * This function computes a blacklist of directories which should not be checked.
     *
     * @param string $token
     * @param bool $deDuped
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
        $tokenFinder = $this->finderFactory->build();
        $tokenFinder->in($this->environment->getRootDirectory())->files()->name($token);
        $rawExcludePathsByToken = $this->finderToRealPathConverter->finderToArrayOfPaths($tokenFinder);
        return $rawExcludePathsByToken;
    }

    /**
     * Finds submodules of in the project directory.
     *
     * @return string[]
     */
    private function findGitDirectories(): array
    {
        $finderGit = $this->finderFactory->build();
        $finderGit->in($this->environment->getRootDirectory())->depth('> 0')->path('/.git$/');
        $rawExcludePathsByFileByGit = $this->finderToRealPathConverter->finderToArrayOfPaths($finderGit);
        return array_map(
            static function ($value) {
                return dirname($value) . '/';
            },
            $rawExcludePathsByFileByGit
        );
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
        $finderBlacklist = $this->finderFactory->build();
        $finderBlacklist->in($environment->getRootDirectory())->directories();
        foreach ($environment->getBlacklistedDirectories() as $blacklistedDirectory) {
            $finderBlacklist->path('/' . preg_quote($blacklistedDirectory, '/') . '$/')
                ->notPath('/' . preg_quote($blacklistedDirectory, '/') . './');
        }
        $rawExcludePathsByBlacklist = $this->finderToRealPathConverter->finderToArrayOfPaths($finderBlacklist);
        return $rawExcludePathsByBlacklist;
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
                function ($value) use ($item) {
                    return !(strlen($value) !== strlen($item) && strpos($value, $item) === 0);
                }
            );
        }
        $filteredArray = array_values($filteredArray);

        return $filteredArray;
    }
}
