<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\FileFinders;

use Zooroyal\CodingStandard\CommandLine\Git\GitChangeSet;
use Zooroyal\CodingStandard\CommandLine\Git\GitChangeSetFactory;
use Zooroyal\CodingStandard\CommandLine\Process\ProcessRunner;

class AllCheckableFileFinder implements FileFinderInterface
{
    private ProcessRunner $processRunner;
    private GitChangeSetFilter $gitChangeSetFilter;
    private GitChangeSetFactory $gitChangeSetFactory;

    /**
     * AllCheckableFileFinder constructor.
     */
    public function __construct(
        ProcessRunner $processRunner,
        GitChangeSetFilter $gitChangeSetFilter,
        GitChangeSetFactory $gitChangeSetFactory
    ) {
        $this->processRunner = $processRunner;
        $this->gitChangeSetFilter = $gitChangeSetFilter;
        $this->gitChangeSetFactory = $gitChangeSetFactory;
    }

    /**
     * This function finds all files to check.
     *
     * @param array<string> $allowedFileEndings
     */
    public function findFiles(
        array $allowedFileEndings = [],
        string $exclusionListToken = '',
        string $whitelistToken = '',
        ?string $targetBranch = null
    ): GitChangeSet {
        $filesFromGit = explode("\n", trim($this->processRunner->runAsProcess('git', 'ls-files')));
        $gitChangeSet = $this->gitChangeSetFactory->build($filesFromGit, '');

        $this->gitChangeSetFilter->filter($gitChangeSet, $allowedFileEndings, $exclusionListToken);

        return $gitChangeSet;
    }
}
