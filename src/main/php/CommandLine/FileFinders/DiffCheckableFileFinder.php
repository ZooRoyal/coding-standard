<?php
namespace Zooroyal\CodingStandard\CommandLine\FileFinders;

use Zooroyal\CodingStandard\CommandLine\Factories\GitChangeSetFactory;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\Library\FileFilter;
use Zooroyal\CodingStandard\CommandLine\Library\ProcessRunner;
use Zooroyal\CodingStandard\CommandLine\ValueObjects\GitChangeSet;

class DiffCheckableFileFinder implements FileFinderInterface
{
    /** @var ProcessRunner */
    private $processRunner;
    /** @var Environment */
    private $environment;
    /** @var FileFilter */
    private $fileFilter;
    /** @var GitChangeSetFactory */
    private $gitChangeSetFactory;

    /**
     * CheckableFileFinder constructor.
     *
     * @param ProcessRunner       $processRunner
     * @param Environment         $environment
     * @param FileFilter          $fileFilter
     * @param GitChangeSetFactory $gitChangeSetFactory
     */
    public function __construct(
        ProcessRunner $processRunner,
        Environment $environment,
        FileFilter $fileFilter,
        GitChangeSetFactory $gitChangeSetFactory
    ) {
        $this->processRunner       = $processRunner;
        $this->environment         = $environment;
        $this->fileFilter          = $fileFilter;
        $this->gitChangeSetFactory = $gitChangeSetFactory;
    }

    /**
     * This function searches for files to check in a certain diff only.
     *
     * @param string $targetBranch
     * @param string $filter
     * @param string $stopword
     *
     * @return GitChangeSet
     */
    public function findFiles($filter = '', $stopword = '', $targetBranch = '')
    {
        $rawDiff = $this->findRawDiff($targetBranch);
        $this->fileFilter->filterByBlacklistFilterStringAndStopword($rawDiff, $filter, $stopword);

        return $rawDiff;
    }

    /**
     * This function computes the raw diff without filtering by blacklist and such.
     *
     * @param string $targetBranch
     *
     * @return GitChangeSet
     */
    private function findRawDiff($targetBranch = '')
    {
        if ($targetBranch === null || $this->environment->isLocalBranchEqualTo($targetBranch)) {
            $rawDiff = $this->findFilesOfBranch();
        } else {
            $rawDiff = $this->findFilesInDiffToTarget($targetBranch);
        }

        return $rawDiff;
    }

    /**
     * This method returns all files of parent commits of local branches HEAD, which are not part of another branch.
     *
     * @return GitChangeSet
     */
    private function findFilesOfBranch()
    {
        $targetCommit = 'HEAD';

        $initialNumberOfContainingBranches = $this->processRunner->runAsProcess(
            'git branch -a --contains HEAD | wc -l'
        );

        while ($this->isParentCommitishACommit($targetCommit)) {
            $targetCommit               .= '^';
            $numberOfContainingBranches = $this->processRunner->runAsProcess(
                'git branch -a --contains ' . $targetCommit . ' | wc -l'
            );

            if ($numberOfContainingBranches !== $initialNumberOfContainingBranches) {
                break;
            }
        }
        $gitCommitHash           = $this->processRunner->runAsProcess('git rev-parse "' . $targetCommit.'"');
        $rawDiffUnfilteredString = $this->processRunner->runAsProcess(
            'git diff --name-only --diff-filter=d ' . $gitCommitHash
        );

        $rawDiffUnfiltered = explode("\n", trim($rawDiffUnfilteredString));

        $result = $this->gitChangeSetFactory->build($rawDiffUnfiltered, $gitCommitHash);

        return $result;
    }

    /**
     * Returns true if $targetCommit commit-ish is a valid commit.
     *
     * @param string $targetCommit
     *
     * @return bool
     */
    private function isParentCommitishACommit($targetCommit)
    {
        $targetType = $this->processRunner->runAsProcess('git cat-file -t ' . $targetCommit . '^');

        return $targetType === 'commit';
    }

    /**
     * This method finds all files in diff to target branch.
     *
     * @param $targetBranch
     *
     * @return GitChangeSet
     */
    private function findFilesInDiffToTarget($targetBranch)
    {
        $mergeBase = $this->processRunner->runAsProcess('git merge-base HEAD ' . $targetBranch);

        $rawDiffUnfilteredString = $this->processRunner->runAsProcess(
            'git diff --name-only --diff-filter=d ' . $mergeBase
        );

        $rawDiffUnfiltered = explode("\n", trim($rawDiffUnfilteredString));

        $result = $this->gitChangeSetFactory->build($rawDiffUnfiltered, $mergeBase);

        return $result;
    }
}
