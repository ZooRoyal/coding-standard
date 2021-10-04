<?php declare(strict_types = 1);

namespace Zooroyal\CodingStandard\CommandLine\Library;

use SplObjectStorage;
use Symfony\Component\Console\Exception\LogicException;
use Zooroyal\CodingStandard\CommandLine\Factories\Excluders\TokenExcluder;
use Zooroyal\CodingStandard\CommandLine\Factories\ExclusionListFactory;
use Zooroyal\CodingStandard\CommandLine\ValueObjects\EnhancedFileInfo;
use Zooroyal\CodingStandard\CommandLine\ValueObjects\GitChangeSet;

class GitChangeSetFilter
{
    private ExclusionListFactory $exclusionListFactory;
    private TokenExcluder $tokenExcluder;

    /**
     * FileFilter constructor.
     */
    public function __construct(
        ExclusionListFactory $exclusionListFactory,
        TokenExcluder $tokenExcluder
    ) {
        $this->exclusionListFactory = $exclusionListFactory;
        $this->tokenExcluder = $tokenExcluder;
    }

    /**
     * Filters file paths by filter and global Blacklist.
     *
     * @param string[]     $allowedFileEndings
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
            $whitelist = $this->tokenExcluder->getPathsToExclude([], ['token' => $whitelistToken]);
        }
        $blacklist = $this->exclusionListFactory->build($blacklistToken, $deDuped);

        $list = $this->mergeLists($blacklist, $whitelist);
        $files = $gitChangeSet->getFiles();

        $result = $this->applyFilters($allowedFileEndings, $files, $list);

        $gitChangeSet->setFiles($result);
    }

    /**
     * Generate merged list.
     *
     * @param array<EnhancedFileInfo> $blacklist
     * @param array<EnhancedFileInfo> $whitelist
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
     * @param string[]                $allowedFileEndings
     * @param array<EnhancedFileInfo> $files
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
