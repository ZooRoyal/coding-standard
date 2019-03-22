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
     * @param string $targetBranch
     *
     * @return GitChangeSet
     */
    public function findFiles($filter = '', $blacklistToken = '', $whitelistToken = '', $targetBranch = ''): GitChangeSet;
}
