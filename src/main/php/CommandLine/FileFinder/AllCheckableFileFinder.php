<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\FileFinder;

use Zooroyal\CodingStandard\CommandLine\Process\ProcessRunner;

class AllCheckableFileFinder implements FileFinderInterface
{
    /**
     * AllCheckableFileFinder constructor.
     */
    public function __construct(
        private ProcessRunner $processRunner,
        private GitChangeSetFilter $gitChangeSetFilter,
        private GitChangeSetFactory $gitChangeSetFactory,
    ) {
    }

    /**
     * This function finds all files to check.
     *
     * @param array<string> $allowedFileEndings
     */
    public function findFiles(
        array $allowedFileEndings = [],
        string $exclusionListToken = '',
        string $inclusionListToken = '',
        ?string $targetBranch = null,
    ): GitChangeSet {
        $filesFromGit = explode("\n", trim($this->processRunner->runAsProcess('git', 'ls-files')));
        $gitChangeSet = $this->gitChangeSetFactory->build($filesFromGit, '');

        $this->gitChangeSetFilter->filter($gitChangeSet, $allowedFileEndings, $exclusionListToken);

        return $gitChangeSet;
    }
}
