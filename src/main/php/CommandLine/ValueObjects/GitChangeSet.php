<?php

namespace Zooroyal\CodingStandard\CommandLine\ValueObjects;

/**
 * This class holds a set of files, which changed since a certain commit.
 */
class GitChangeSet
{
    /** @var array<EnhancedFileInfo> */
    private $files;

    /** @var string */
    private $commitHash;

    /**
     * GitChangeSet constructor.
     *
     * @param array<EnhancedFileInfo> $files
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
     * @return array<EnhancedFileInfo>
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * Overwrite changed files.
     *
     * @param array<EnhancedFileInfo> $files
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
