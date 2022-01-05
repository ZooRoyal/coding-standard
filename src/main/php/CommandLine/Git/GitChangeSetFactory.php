<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\Git;

use Zooroyal\CodingStandard\CommandLine\EnhancedFileInfo\EnhancedFileInfoFactory;

/**
 * This Class provides GitChangeSets.
 */
class GitChangeSetFactory
{
    private EnhancedFileInfoFactory $enhancedFileInfoFactory;

    /**
     * GitChangeSetFactory constructor.
     */
    public function __construct(EnhancedFileInfoFactory $enhancedFileInfoFactory)
    {
        $this->enhancedFileInfoFactory = $enhancedFileInfoFactory;
    }
    /**
     * Build provides GitChangeSet instances.
     *
     * @param array<string> $files
     */
    public function build(array $files, string $commitHash = ''): GitChangeSet
    {
        $fileInfos = $this->enhancedFileInfoFactory->buildFromArrayOfPaths($files);
        return new GitChangeSet($fileInfos, $commitHash);
    }
}
