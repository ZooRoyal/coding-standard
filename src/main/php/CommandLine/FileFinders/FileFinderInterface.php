<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\FileFinders;

use Zooroyal\CodingStandard\CommandLine\Git\GitChangeSet;

interface FileFinderInterface
{
    /**
     * This method searches for files by using Git as indexing service.
     *
     * @param array<string> $allowedFileEndings
     */
    public function findFiles(
        array $allowedFileEndings = [],
        string $exclusionListToken = '',
        string $whitelistToken = '',
        ?string $targetBranch = null
    ): GitChangeSet;
}
