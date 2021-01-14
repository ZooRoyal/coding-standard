<?php

namespace Zooroyal\CodingStandard\CommandLine\Factories;

use Symplify\SmartFileSystem\SmartFileInfo;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\Library\FinderToPathsConverter;

class BlacklistFactory
{
    private FinderToPathsConverter $finderToRealPathConverter;

    private Environment $environment;
    private FinderFactory $finderFactory;
    /** @var array<string, mixed> */
    private array $blackListCache = [];

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
     * @return array<SmartFileInfo>
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
     * @return array<SmartFileInfo>
     */
    public function findTokenDirectories(string $token): array
    {
        $tokenFinder = $this->finderFactory->build();
        $tokenFinder->in($this->environment->getRootDirectory()->getRealPath())->files()->name($token);

        $rawExcludePathsByToken = $this->finderToRealPathConverter->finderToArrayOfDirectories($tokenFinder);

        return $rawExcludePathsByToken;
    }

    /**
     * Finds submodules of in the project directory.
     *
     * @return array<SmartFileInfo>
     */
    private function findGitDirectories(): array
    {
        $finderGit = $this->finderFactory->build();
        $finderGit->in($this->environment->getRootDirectory()->getRealPath())->depth('> 0')->path('/.git$/');

        $rawExcludePathsByFileByGit = $this->finderToRealPathConverter->finderToArrayOfDirectories($finderGit);

        return $rawExcludePathsByFileByGit;
    }

    /**
     * Finds blacklisted directories by Environment.
     *
     * @param Environment $environment
     *
     * @return array<SmartFileInfo>
     */
    private function findDirectoriesFromEnvironment(Environment $environment): array
    {
        $blacklistedDirectories = $environment->getBlacklistedDirectories();
        if (empty($blacklistedDirectories)) {
            return [];
        }
        $finderBlacklist = $this->finderFactory->build();
        $finderBlacklist->in($environment->getRootDirectory()->getRealPath())->directories();
        foreach ($blacklistedDirectories as $blacklistedDirectory) {
            $finderBlacklist->path('/' . preg_quote($blacklistedDirectory->getRelativePathname(), '/') . '$/')
                ->notPath('/' . preg_quote($blacklistedDirectory->getRelativePathname(), '/') . './');
        }
        $rawExcludePathsByBlacklist = $this->finderToRealPathConverter->finderToArray($finderBlacklist);
        return $rawExcludePathsByBlacklist;
    }

    /**
     * This method filters subpaths of paths already existing in $rawExcludePaths
     *
     * @param array<SmartFileInfo> $rawExcludePaths
     *
     * @return array<SmartFileInfo>
     */
    private function deDupePaths(array $rawExcludePaths): array
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
                static function (SmartFileInfo $value) use ($item) {
                    return !($value !== $item && $value->startsWith($item->getRealPath() . '/'));
                }
            );
        }
        $filteredArray = array_values($filteredArray);

        return $filteredArray;
    }

}
