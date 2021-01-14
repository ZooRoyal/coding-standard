<?php

namespace Zooroyal\CodingStandard\CommandLine\Library;

use SplObjectStorage;
use Symfony\Component\Console\Exception\LogicException;
use Symplify\SmartFileSystem\SmartFileInfo;
use Zooroyal\CodingStandard\CommandLine\Factories\BlacklistFactory;
use Zooroyal\CodingStandard\CommandLine\ValueObjects\GitChangeSet;

class GitChangeSetFilter
{
    private BlacklistFactory $blacklistFactory;

    /**
     * FileFilter constructor.
     *
     * @param BlacklistFactory $blacklistFactory
     */
    public function __construct(BlacklistFactory $blacklistFactory)
    {
        $this->blacklistFactory = $blacklistFactory;
    }

    /**
     * Filters file paths by filter and global Blacklist.
     *
     * @param GitChangeSet $gitChangeSet
     * @param string[] $allowedFileEndings
     * @param string $blacklistToken
     * @param string $whitelistToken
     */
    public function filter(
        GitChangeSet $gitChangeSet,
        array $allowedFileEndings = [],
        string $blacklistToken = '',
        string $whitelistToken = ''
    ): void {
        $whitelist = [];
        $deDuped = true;

        if ($whitelistToken !== '') {
            $deDuped = false;
            $whitelist = $this->blacklistFactory->findTokenDirectories($whitelistToken);
        }
        $blacklist = $this->blacklistFactory->build($blacklistToken, $deDuped);

        $list = $this->mergeLists($blacklist, $whitelist);
        $files = $gitChangeSet->getFiles();

        $result = $this->applyFilters($allowedFileEndings, $files, $list);

        $gitChangeSet->setFiles($result);
    }

    /**
     * Generate merged list.
     *
     * @param array<SmartFileInfo> $blacklist
     * @param array<SmartFileInfo> $whitelist
     *
     * @return SplObjectStorage
     *
     * @throws LogicException
     */
    private function mergeLists(
        array $blacklist,
        array $whitelist
    ): SplObjectStorage {
        if (count(array_intersect($blacklist, $whitelist)) !== 0) {
            throw new LogicException('Directories can\'t be black- and whitelisted at the same time', 1553780055);
        }
        $result = new SplObjectStorage();

        foreach ($blacklist as $blacklistItem) {
            $result->attach($blacklistItem, false);
        }
        foreach ($whitelist as $whitelistItem) {
            $result->attach($whitelistItem, true);
        }

        return $result;
    }

    /**
     * Iterates over the files and returns files as configured in list and filter.
     *
     * @param string[] $allowedFileEndings
     * @param array<SmartFileInfo> $files
     * @param SplObjectStorage $list
     *
     * @return array
     */
    private function applyFilters(array $allowedFileEndings, array $files, SplObjectStorage $list): array
    {
        $result = $files;
        $this->filterByAllowedFileEndings($result, $allowedFileEndings);
        $this->filterByList($result, $list);

        return $result;
    }

    /**
     * If only certain file endings are allowed remove every other file from result.
     *
     * @param array<SmartFileInfo> $result
     * @param array<string> $allowedFileEndings
     */
    private function filterByAllowedFileEndings(array &$result, array $allowedFileEndings): void
    {
        if (!empty($allowedFileEndings)) {
            $result = array_filter(
                $result,
                static function ($file) use ($allowedFileEndings) {
                    foreach ($allowedFileEndings as $allowedFileEnding) {
                        if ($file->endsWith($allowedFileEnding)) {
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
     * @param array<SmartFileInfo> $result
     * @param SplObjectStorage $list
     */
    private function filterByList(array &$result, SplObjectStorage $list): void
    {
        $result = array_filter(
            $result,
            static function ($file) use ($list) {
                $priority = 0;
                $result = true;
                foreach ($list as $directoryPattern) {
                    /** @var SmartFileInfo $directoryPattern */
                    $allowanceRule = $list[$directoryPattern];
                    $path = $directoryPattern->getRealPath();
                    if ($file->startsWith($path . '/') && $priority < strlen($path)) {
                        $priority = strlen($path);
                        $result = $allowanceRule;
                    }
                }
                return $result;
            }
        );
    }
}
