<?php

namespace Zooroyal\CodingStandard\CommandLine\Factories;

use Zooroyal\CodingStandard\CommandLine\ValueObjects\GitChangeSet;

/**
 * This Class provides GitChangeSets.
 */
class GitChangeSetFactory
{
    private SmartFileInfoFactory $smartFileInfoFactory;

    /**
     * GitChangeSetFactory constructor.
     *
     * @param SmartFileInfoFactory $smartFileInfoFactory
     */
    public function __construct(SmartFileInfoFactory $smartFileInfoFactory)
    {
        $this->smartFileInfoFactory = $smartFileInfoFactory;
    }
    /**
     * Build provides GitChangeSet instances.
     *
     * @param string[] $files
     * @param string   $commitHash
     *
     * @return GitChangeSet
     */
    public function build(array $files, $commitHash = '')
    {
        $fileInfos = $this->smartFileInfoFactory->buildFromArrayOfPaths($files);
        return new GitChangeSet($fileInfos, $commitHash);
    }
}
