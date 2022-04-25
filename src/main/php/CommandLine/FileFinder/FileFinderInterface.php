<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\FileFinder;

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
        string $inclusionListToken = '',
        ?string $targetBranch = null
    ): GitChangeSet;
}
