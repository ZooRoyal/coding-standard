<?php
namespace Zooroyal\CodingStandard\CommandLine\FileFinders;

use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\Library\FileFilter;
use Zooroyal\CodingStandard\CommandLine\Library\ProcessRunner;

class DiffCheckableFileFinder implements FileFinderInterface
{
    /** @var ProcessRunner */
    private $processRunner;
    /** @var \Zooroyal\CodingStandard\CommandLine\Library\Environment */
    private $environment;
    /** @var FileFilter */
    private $fileFilter;

    /**
     * CheckableFileFinder constructor.
     *
     * @param ProcessRunner $processRunner
     * @param Environment   $environment
     * @param FileFilter    $fileFilter
     */
    public function __construct(
        ProcessRunner $processRunner,
        Environment $environment,
        FileFilter $fileFilter
    ) {
        $this->processRunner = $processRunner;
        $this->environment   = $environment;
        $this->fileFilter    = $fileFilter;
    }

    /**
     * This function searches for files to check in a certain diff only.
     *
     * @param string $targetBranch
     * @param string $filter
     * @param string $stopword
     *
     * @return string[]
     */
    public function findFiles($filter = '', $stopword = '', $targetBranch = '')
    {
        $rawDiff = $this->findRawDiff($targetBranch);
        $diff    = $this->fileFilter->filterByBlacklistFilterStringAndStopword($rawDiff, $filter, $stopword);

        return $diff;
    }

    /**
     * This function computes the raw diff without filtering by blacklist and such.
     *
     * @param string $targetBranch
     *
     * @return string[]
     */
    private function findRawDiff($targetBranch = '')
    {
        $localBranch = $this->environment->getLocalBranch();
        if ($localBranch === $targetBranch) {
            $rawDiffAsString = $this->findFilesOfBranch();
        } else {
            $rawDiffAsString = $this->findFilesInDiffToTarget($targetBranch);
        }

        return explode("\n", trim($rawDiffAsString));
    }

    /**
     * This method returns all files of parent commits of local branches HEAD, which are not part of another branch.
     * The result is a single string as it is the result of the git call.
     *
     * @return string
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
        $rawDiffUnfilteredString = $this->processRunner->runAsProcess('git diff --name-only --diff-filter=d '
            . $targetCommit);

        return $rawDiffUnfilteredString;
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
     * The result is a single string as it is the result of the git call.
     *
     * @param $targetBranch
     *
     * @return string
     */
    private function findFilesInDiffToTarget($targetBranch)
    {
        $mergeBase = $this->processRunner->runAsProcess('git merge-base HEAD ' . $targetBranch);

        $rawDiffUnfilteredString = $this->processRunner->runAsProcess('git diff --name-only --diff-filter=d '
            . $mergeBase);

        return $rawDiffUnfilteredString;
    }
}
