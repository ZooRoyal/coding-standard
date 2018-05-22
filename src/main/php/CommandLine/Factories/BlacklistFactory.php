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
        $finderGit->in($this->environment->getRootDirectory())->directories()->depth('> 1')->name('.git');
        $rawExcludePathsByFileByGit = $this->finderToRealPathConverter->finderToArrayOfPaths($finderGit);

        $finderBlacklist = $this->finderFactory->build();
        $finderBlacklist->in($this->environment->getRootDirectory())->directories();
        foreach ($this->environment->getBlacklistedDirectories() as $blacklistedDirectory) {
            $finderBlacklist->path('/' . preg_quote($blacklistedDirectory) . '$/')
                ->notPath('/' . preg_quote($blacklistedDirectory) . './');
        }
        $rawExcludePathsByBlacklist = $this->finderToRealPathConverter->finderToArrayOfPaths($finderBlacklist);

        $rawExcludePathsUntrimmed = array_merge($rawExcludePathsByFileByStopword, $rawExcludePathsByFileByGit);
        $rawExcludePathsFromFiles = array_map(
            function ($value) {
                return dirname($value);
            },
            $rawExcludePathsUntrimmed
        );

        $rawExcludePaths = array_merge($rawExcludePathsByBlacklist, $rawExcludePathsFromFiles);

        return $rawExcludePaths;
    }
}
