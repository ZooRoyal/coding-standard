<?php
namespace Zooroyal\CodingStandard\CommandLine\Factories;

use Zooroyal\CodingStandard\CommandLine\ValueObjects\GitChangeSet;

/**
 * This Class provides GitChangeSets.
 */
class GitChangeSetFactory
{
    /**
     * Build provides GitChangeSet instances.
     *
     * @param string[] $files
     * @param string   $commitHash
     *
     * @return GitChangeSet
     */
    public function build(array $files, $commitHash)
    {
        return new GitChangeSet($files, $commitHash);
    }
}
