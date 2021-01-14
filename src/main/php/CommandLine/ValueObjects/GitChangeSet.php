<?php

namespace Zooroyal\CodingStandard\CommandLine\ValueObjects;

use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * This class holds a set of files, which changed since a certain commit.
 */
class GitChangeSet
{
    /** @var array<SmartFileInfo> */
    private $files;

    /** @var string */
    private $commitHash;

    /**
     * GitChangeSet constructor.
     *
     * @param array<SmartFileInfo> $files
     * @param string $commitHash
     */
    public function __construct(array $files, $commitHash = '')
    {
        $this->files = $files;
        $this->commitHash = $commitHash;
    }

    /**
     * Returns changed files since commit.
     *
     * @return array<SmartFileInfo>
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * Overwrite changed files.
     *
     * @param array<SmartFileInfo> $files
     */
    public function setFiles(array $files)
    {
        $this->files = $files;
    }

    /**
     * Returns target commit.
     *
     * @return string
     */
    public function getCommitHash()
    {
        return $this->commitHash;
    }
}
