<?php

namespace Zooroyal\CodingStandard\CommandLine\FileFinders;

use Zooroyal\CodingStandard\CommandLine\Factories\GitChangeSetFactory;
use Zooroyal\CodingStandard\CommandLine\Library\GitChangeSetFilter;
use Zooroyal\CodingStandard\CommandLine\Library\ProcessRunner;
use Zooroyal\CodingStandard\CommandLine\ValueObjects\GitChangeSet;

class AllCheckableFileFinder implements FileFinderInterface
{
    /** @var ProcessRunner */
    private $processRunner;
    /** @var GitChangeSetFilter */
    private $gitChangeSetFilter;
    /** @var GitChangeSetFactory */
    private $gitChangeSetFactory;

    /**
     * AllCheckableFileFinder constructor.
     *
     * @param ProcessRunner $processRunner
     * @param GitChangeSetFilter $gitChangeSetFilter
     * @param GitChangeSetFactory $gitChangeSetFactory
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
     * @param string $filter
     * @param string $blacklistToken
     * @param string $whitelistToken
     * @param string $targetBranch
     *
     * @return GitChangeSet
     */
    public function findFiles($filter = '', $blacklistToken = '', $whitelistToken = '', $targetBranch = ''): GitChangeSet
    {
        $filesFromGit = explode("\n", trim($this->processRunner->runAsProcess('git', 'ls-files')));
        $gitChangeSet = $this->gitChangeSetFactory->build($filesFromGit, '');

        $this->gitChangeSetFilter->filter($gitChangeSet, $filter, $blacklistToken);

        return $gitChangeSet;
    }
}
