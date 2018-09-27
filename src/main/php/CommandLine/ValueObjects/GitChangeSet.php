<?php
namespace Zooroyal\CodingStandard\CommandLine\ValueObjects;

/**
 * This class holds a set of files, which changed since a certain commit.
 */
class GitChangeSet
{
    /** @var string[] */
    private $files;

    /** @var string */
    private $commitHash;

    /**
     * GitChangeSet constructor.
     *
     * @param string[] $files
     * @param string   $commitHash
     */
    public function __construct(array $files, $commitHash = '')
    {
        $this->files      = $files;
        $this->commitHash = $commitHash;
    }

    /**
     * Returns changed files since commit.
     *
     * @return string[]
     */
    public function getFiles()
    {
        return $this->files;
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

    /**
     * Overwrite changed files.
     *
     * @param string[] $files
     */
    public function setFiles($files)
    {
        $this->files = $files;
    }
}
