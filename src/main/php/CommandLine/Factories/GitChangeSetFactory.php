<?php

namespace Zooroyal\CodingStandard\CommandLine\Factories;

use Zooroyal\CodingStandard\CommandLine\ValueObjects\GitChangeSet;

/**
 * This Class provides GitChangeSets.
 */
class GitChangeSetFactory
{
    private EnhancedFileInfoFactory $enhancedFileInfoFactory;

    /**
     * GitChangeSetFactory constructor.
     *
     * @param EnhancedFileInfoFactory $enhancedFileInfoFactory
     */
    public function __construct(EnhancedFileInfoFactory $enhancedFileInfoFactory)
    {
        $this->enhancedFileInfoFactory = $enhancedFileInfoFactory;
    }
    /**
     * Build provides GitChangeSet instances.
     *
     * @param string[] $files
     * @param string   $commitHash
     */
    public function build(array $files, string $commitHash = ''): GitChangeSet
    {
        $fileInfos = $this->enhancedFileInfoFactory->buildFromArrayOfPaths($files);
        return new GitChangeSet($fileInfos, $commitHash);
    }
}
