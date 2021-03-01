<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\ValueObjects;

/**
 * This class holds a set of files, which changed since a certain commit.
 */
class GitChangeSet
{
    /**
     * GitChangeSet constructor.
     *
     * @param array<EnhancedFileInfo> $files
     */
    public function __construct(private array $files, private string $commitHash = '')
    {
    }

    /**
     * Returns changed files since commit.
     *
     * @return array<EnhancedFileInfo>
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * Overwrite changed files.
     *
     * @param array<EnhancedFileInfo> $files
     */
    public function setFiles(array $files): void
    {
        $this->files = $files;
    }

    /**
     * Returns target commit.
     */
    public function getCommitHash(): string
    {
        return $this->commitHash;
    }
}
