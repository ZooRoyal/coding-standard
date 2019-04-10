<?php

namespace Zooroyal\CodingStandard\CommandLine\FileFinders;

use Zooroyal\CodingStandard\CommandLine\ValueObjects\GitChangeSet;

interface FileFinderInterface
{
    /**
     * This method searches for files by using Git as indexing service.
     *
     * @param string $filter
     * @param string $blacklistToken
     * @param string $whitelistToken
     * @param string|false $targetBranch
     *
     * @return GitChangeSet
     */
    public function findFiles(
        string $filter = '',
        string $blacklistToken = '',
        string $whitelistToken = '',
        $targetBranch = ''
    ): GitChangeSet;
}
