<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\FileFinder;

use Symfony\Component\Console\Exception\InvalidArgumentException;

/**
 * This class searches for files in Git. It decides for itself if it returns a diff to a branch or all relevant files.
 */
class AdaptableFileFinder implements FileFinderInterface
{
    private GitInputValidator $gitInputValidator;
    private AllCheckableFileFinder $allCheckableFileFinder;
    private DiffCheckableFileFinder $diffCheckableFileFinder;
    private CommitishComparator $commitishComparator;

    /**
     * AdaptableFileFinder constructor.
     */
    public function __construct(
        GitInputValidator $gitInputValidator,
        AllCheckableFileFinder $allCheckableFileFinder,
        DiffCheckableFileFinder $diffCheckableFileFinder,
        CommitishComparator $commitishComparator
    ) {
        $this->gitInputValidator = $gitInputValidator;
        $this->allCheckableFileFinder = $allCheckableFileFinder;
        $this->diffCheckableFileFinder = $diffCheckableFileFinder;
        $this->commitishComparator = $commitishComparator;
    }


    /**
     * {@inheritDoc}
     *
     * @throws InvalidArgumentException
     */
    public function findFiles(
        array $allowedFileEndings = [],
        string $exclusionListToken = '',
        string $whitelistToken = '',
        ?string $targetBranch = null
    ): GitChangeSet {
        if ($targetBranch !== null
            && !$this->gitInputValidator->isCommitishValid($targetBranch)
        ) {
            throw new InvalidArgumentException('Target ' . $targetBranch . ' is no valid commit-ish.', 1553766210);
        }

        $finder = $targetBranch === null || $this->commitishComparator->isLocalBranchEqualTo($targetBranch)
            ? $this->allCheckableFileFinder
            : $this->diffCheckableFileFinder;

        $result = $finder->findFiles($allowedFileEndings, $exclusionListToken, $whitelistToken, $targetBranch);

        return $result;
    }
}
