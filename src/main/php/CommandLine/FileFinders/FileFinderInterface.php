<?php

namespace Zooroyal\CodingStandard\CommandLine\FileFinders;

use Zooroyal\CodingStandard\CommandLine\ValueObjects\GitChangeSet;

interface FileFinderInterface
{
    /**
     * This method searches for files by using Git as indexing service.
     *
     * @param string[]    $allowedFileEndings
     * @param string      $blacklistToken
     * @param string      $whitelistToken
     * @param string|bool $targetBranch
     *
     * @return GitChangeSet
     */
    public function findFiles(
        array $allowedFileEndings = [],
        string $blacklistToken = '',
        string $whitelistToken = '',
        $targetBranch = ''
    ) : GitChangeSet;
}
