<?php

namespace Zooroyal\CodingStandard\CommandLine\Library;

use Symfony\Component\Console\Exception\LogicException;
use Zooroyal\CodingStandard\CommandLine\Factories\Exclusion\TokenExcluder;
use Zooroyal\CodingStandard\CommandLine\Factories\ExclusionListFactory;
use Zooroyal\CodingStandard\CommandLine\ValueObjects\GitChangeSet;
use function Safe\substr;

class GitChangeSetFilter
{
    private ExclusionListFactory $exclusionListFactory;
    private TokenExcluder $tokenExcluder;
    private Environment $environment;

    /**
     * FileFilter constructor.
     *
     * @param ExclusionListFactory $exclusionListFactory
     * @param TokenExcluder        $tokenExcluder
     * @param Environment          $environment
     */
    public function __construct(
        ExclusionListFactory $exclusionListFactory,
        TokenExcluder $tokenExcluder,
        Environment $environment
    ) {
        $this->exclusionListFactory = $exclusionListFactory;
        $this->tokenExcluder = $tokenExcluder;
        $this->environment = $environment;
    }

    /**
     * Filters file paths by filter and global Blacklist.
     *
     * @param GitChangeSet $gitChangeSet
     * @param string[]     $allowedFileEndings
     * @param string       $blacklistToken
     * @param string       $whitelistToken
     */
    public function filter(
        GitChangeSet $gitChangeSet,
        array $allowedFileEndings = [],
        string $blacklistToken = '',
        string $whitelistToken = ''
    ) {
        $whitelist = [];
        $deDuped = true;

        if ($whitelistToken !== '') {
            $deDuped = false;
            $whitelist = $this->tokenExcluder->getPathsToExclude([], ['token' => $whitelistToken]);
        }
        $blacklist = $this->exclusionListFactory->build($blacklistToken, $deDuped);

        $list = $this->mergeLists($blacklist, $whitelist);
        $files = $gitChangeSet->getFiles();

        $result = $this->applyFilters($allowedFileEndings, $files, $list);

        $gitChangeSet->setFiles($result);
    }

    /**
     * Iterates over the files and returns files as configured in list and filter.
     *
     * @param string[] $allowedFileEndings
     * @param string[] $files
     * @param bool[]   $list
     *
     * @return array
     */
    private function applyFilters(array $allowedFileEndings, array $files, array $list): array
    {
        $result = $files;
        $this->filterByAllowedFileEndings($result, $allowedFileEndings);
        $this->filterByList($result, $list);

        return $result;
    }

    /**
     * Generate merged list.
     *
     * @param string[] $blacklist
     * @param string[] $whitelist
     *
     * @return array
     *
     * @throws LogicException
     */
    private function mergeLists(
        array $blacklist,
        array $whitelist
    ): array {
        if (count(array_intersect($blacklist, $whitelist)) !== 0) {
            throw new LogicException('Directories can\'t be black- and whitelisted at the same time', 1553780055);
        }

        return array_merge(
            array_fill_keys($blacklist, false),
            array_fill_keys($whitelist, true)
        );
    }

    /**
     * If only certain file endings are allowed remove every other file from result.
     *
     * @param array $result
     * @param array $allowedFileEndings
     */
    private function filterByAllowedFileEndings(array &$result, array $allowedFileEndings)
    {
        if (!empty($allowedFileEndings)) {
            $result = array_filter(
                $result,
                static function ($filePath) use ($allowedFileEndings) {
                    foreach ($allowedFileEndings as $allowedFileEnding) {
                        if ($allowedFileEnding !== ''
                            && substr($filePath, -strlen($allowedFileEnding)) === $allowedFileEnding) {
                            return true;
                        }
                    }
                    return false;
                }
            );
        }
    }

    /**
     * Filter result by a true/false list of their respective directories and parent directories.
     *
     * @param array $result
     * @param array $list
     */
    private function filterByList(array &$result, array $list)
    {
        $rootDirectory = $this->environment->getRootDirectory();
        $result = array_filter(
            $result,
            function ($filePath) use ($rootDirectory, $list) {
                $directory = dirname($filePath);
                $lastDirectoryPath = $filePath;
                while (!in_array($directory, [$rootDirectory, '', $lastDirectoryPath], true)
                ) {
                    if (array_key_exists($directory, $list)) {
                        return $list[$directory] === true;
                    }
                    $lastDirectoryPath = $directory;
                    $directory = dirname($directory);
                }
                return true;
            }
        );
    }
}
