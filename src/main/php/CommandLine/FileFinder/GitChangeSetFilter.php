<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\FileFinder;

use SplObjectStorage;
use Symfony\Component\Console\Exception\LogicException;
use Zooroyal\CodingStandard\CommandLine\EnhancedFileInfo\EnhancedFileInfo;
use Zooroyal\CodingStandard\CommandLine\ExclusionList\Excluders\TokenExcluder;
use Zooroyal\CodingStandard\CommandLine\ExclusionList\ExclusionListFactory;

class GitChangeSetFilter
{
    /**
     * FileFilter constructor.
     */
    public function __construct(
        private ExclusionListFactory $exclusionListFactory,
        private TokenExcluder $tokenExcluder,
    ) {
    }

    /**
     * Filters file paths by filter and global Blacklist.
     *
     * @param array<string> $allowedFileEndings
     */
    public function filter(
        GitChangeSet $gitChangeSet,
        array $allowedFileEndings = [],
        string $exclusionListToken = '',
        string $inclusionListToken = ''
    ): void {
        $inclusionlist = [];
        $deDuped = true;

        if ($inclusionListToken !== '') {
            $deDuped = false;
            $inclusionlist = $this->tokenExcluder->getPathsToExclude([], ['token' => $inclusionListToken]);
        }
        $exclusionList = $this->exclusionListFactory->build($exclusionListToken, $deDuped);

        $list = $this->mergeLists($exclusionList, $inclusionlist);
        $files = $gitChangeSet->getFiles();

        $result = $this->applyFilters($allowedFileEndings, $files, $list);

        $gitChangeSet->setFiles($result);
    }

    /**
     * Generate merged list.
     *
     * @param array<EnhancedFileInfo> $exclusionList
     * @param array<EnhancedFileInfo> $inclusionlist
     *
     * @throws LogicException
     */
    private function mergeLists(
        array $exclusionList,
        array $inclusionlist
    ): SplObjectStorage {
        if (count(array_intersect($exclusionList, $inclusionlist)) !== 0) {
            throw new LogicException('Directories can\'t be black- and inclusionlisted at the same time', 1553780055);
        }
        $result = new SplObjectStorage();

        foreach ($exclusionList as $exclusionListItem) {
            $result->attach($exclusionListItem, false);
        }
        foreach ($inclusionlist as $inclusionlistItem) {
            $result->attach($inclusionlistItem, true);
        }

        return $result;
    }

    /**
     * Iterates over the files and returns files as configured in list and filter.
     *
     * @param array<string>           $allowedFileEndings
     * @param array<EnhancedFileInfo> $files
     *
     * @return array<EnhancedFileInfo>
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
     * @param array<EnhancedFileInfo> $result
     * @param array<string>           $allowedFileEndings
     */
    private function filterByAllowedFileEndings(array &$result, array $allowedFileEndings): void
    {
        if (!empty($allowedFileEndings)) {
            $result = array_filter(
                $result,
                static function ($file) use ($allowedFileEndings): bool {
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
     * @param array<EnhancedFileInfo> $result
     */
    private function filterByList(array &$result, SplObjectStorage $list): void
    {
        $result = array_filter(
            $result,
            static function (EnhancedFileInfo $file) use ($list) {
                $priority = 0;
                $result = true;
                foreach ($list as $directoryPattern) {
                    /** @var EnhancedFileInfo $directoryPattern */
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
        $result = array_values($result);
    }
}
