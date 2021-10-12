<?php declare(strict_types = 1);

namespace Zooroyal\CodingStandard\CommandLine\FileFinders;

use Zooroyal\CodingStandard\CommandLine\ValueObjects\GitChangeSet;

interface FileFinderInterface
{
    /**
     * This method searches for files by using Git as indexing service.
     *
     * @param array<string> $allowedFileEndings
     */
    public function findFiles(
        array $allowedFileEndings = [],
        string $blacklistToken = '',
        string $whitelistToken = '',
        ?string $targetBranch = null
    ): GitChangeSet;
}
