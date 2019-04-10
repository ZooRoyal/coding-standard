<?php

namespace Zooroyal\CodingStandard\CommandLine\Library;

use Symfony\Component\Console\Exception\LogicException;
use Zooroyal\CodingStandard\CommandLine\Factories\BlacklistFactory;
use Zooroyal\CodingStandard\CommandLine\ValueObjects\GitChangeSet;

class GitChangeSetFilter
{
    /** @var BlacklistFactory */
    private $blacklistFactory;
    /** @var Environment */
    private $environment;

    /**
     * FileFilter constructor.
     *
     * @param BlacklistFactory $blacklistFactory
     * @param Environment      $environment
     */
    public function __construct(BlacklistFactory $blacklistFactory, Environment $environment)
    {
        $this->blacklistFactory = $blacklistFactory;
        $this->environment = $environment;
    }

    /**
     * Filters file paths by filter and global Blacklist.
     *
     * @param GitChangeSet $gitChangeSet
     * @param string       $filter
     * @param string       $blacklistToken
     * @param string       $whitelistToken
     */
    public function filter(
        GitChangeSet $gitChangeSet,
        string $filter = '',
        string $blacklistToken = '',
        string $whitelistToken = ''
    ) {
        $whitelist = [];
        $deDuped = true;

        if ($whitelistToken !== '') {
            $deDuped = false;
            $whitelist = $this->blacklistFactory->findTokenDirectories($whitelistToken);
        }
        $blacklist = $this->blacklistFactory->build($blacklistToken, $deDuped);

        $list = $this->mergeLists($blacklist, $whitelist);
        $files = $gitChangeSet->getFiles();

        $result = $this->iterateOverFiles($filter, $files, $list);

        $gitChangeSet->setFiles($result);
    }

    /**
     * Iterates over the files and returns files as configured in list and filter.
     *
     * @param string   $filter
     * @param string[] $files
     * @param string[] $list
     *
     * @return array
     */
    protected function iterateOverFiles(string $filter, array $files, array $list) : array
    {
        $result = [];
        foreach ($files as $filePath) {
            //Filter by filter.
            if ($filter !== '' && substr($filePath, -strlen($filter)) !== $filter) {
                continue;
            }
            //Filter by black/white List
            $directory = dirname($filePath);
            $lastDirectoryPath = $filePath;
            while (!in_array($directory, [$this->environment->getRootDirectory(), '', $lastDirectoryPath], true)
            ) {
                if (array_key_exists($directory, $list)) {
                    if ($list[$directory] === true) {
                        $result[] = $filePath;
                    }
                    continue 2;
                }
                $lastDirectoryPath = $directory;
                $directory = dirname($directory);
            }
            $result[] = $filePath;
        }
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
    private function mergeLists(array $blacklist, array $whitelist) : array
    {
        if (count(array_intersect($blacklist, $whitelist)) !== 0) {
            throw new LogicException('Directories can\'t be black- and whitelisted at the same time', 1553780055);
        }

        return array_merge(
            array_fill_keys($blacklist, false),
            array_fill_keys($whitelist, true)
        );
    }
}
