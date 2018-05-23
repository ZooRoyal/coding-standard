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

    /**
     * BlacklistFactory constructor.
     *
     * @param FinderToPathsConverter $finderToRealPathConverter
     * @param Environment            $environment
     * @param FinderFactory          $finderFactory
     */
    public function __construct(
        FinderToPathsConverter $finderToRealPathConverter,
        Environment $environment,
        FinderFactory $finderFactory
    ) {
        $this->finderToRealPathConverter = $finderToRealPathConverter;
        $this->environment               = $environment;
        $this->finderFactory             = $finderFactory;
    }

    /**
     * This function computes a blacklist of directories which should not be checked.
     *
     * @param $stopword
     *
     * @return string[]
     */
    public function build($stopword = '')
    {
        $rawExcludePathsByFileByStopword = [];

        if ($stopword !== '') {
            $findStopword = $this->finderFactory->build();
            $findStopword->in($this->environment->getRootDirectory())->files()->name($stopword);
            $rawExcludePathsByFileByStopword = $this->finderToRealPathConverter
                ->finderToArrayOfPaths($findStopword);
        }

        $finderGit = $this->finderFactory->build();
        $finderGit->in($this->environment->getRootDirectory())->depth('> 0')->path('/.*git$/');
        $rawExcludePathsByFileByGit = $this->finderToRealPathConverter->finderToArrayOfPaths($finderGit);

        $finderBlacklist = $this->finderFactory->build();
        $finderBlacklist->in($this->environment->getRootDirectory())->directories();
        foreach ($this->environment->getBlacklistedDirectories() as $blacklistedDirectory) {
            $finderBlacklist->path('/' . preg_quote($blacklistedDirectory, '/') . '$/')
                ->notPath('/' . preg_quote($blacklistedDirectory, '/') . './');
        }
        $rawExcludePathsByBlacklist = $this->finderToRealPathConverter->finderToArrayOfPaths($finderBlacklist);

        $rawExcludePathsUntrimmed = array_merge($rawExcludePathsByFileByStopword, $rawExcludePathsByFileByGit);
        $rawExcludePathsFromFiles = array_map('dirname', $rawExcludePathsUntrimmed);

        $rawExcludePaths = array_merge($rawExcludePathsByBlacklist, $rawExcludePathsFromFiles);

        $filteredArray = $this->deDupePaths($rawExcludePaths);

        return $filteredArray;
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
        $count         = count($filteredArray);
        for ($i = 0; $count > $i; $i++) {
            if (!isset($filteredArray[$i])) {
                continue;
            }
            $item          = $filteredArray[$i];
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
