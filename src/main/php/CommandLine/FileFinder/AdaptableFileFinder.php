<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\FileFinder;

use Symfony\Component\Console\Exception\InvalidArgumentException;

/**
 * This class searches for files in Git. It decides for itself if it returns a diff to a branch or all relevant files.
 */
class AdaptableFileFinder implements FileFinderInterface
{
    /**
     * AdaptableFileFinder constructor.
     */
    public function __construct(
        private readonly GitInputValidator $gitInputValidator,
        private readonly AllCheckableFileFinder $allCheckableFileFinder,
        private readonly DiffCheckableFileFinder $diffCheckableFileFinder,
        private readonly CommitishComparator $commitishComparator,
    ) {
    }


    /**
     * {@inheritDoc}
     *
     * @throws InvalidArgumentException
     */
    public function findFiles(
        array $allowedFileEndings = [],
        string $exclusionListToken = '',
        string $inclusionListToken = '',
        ?string $targetBranch = null,
    ): GitChangeSet {
        if (
            $targetBranch !== null
            && !$this->gitInputValidator->isCommitishValid($targetBranch)
        ) {
            throw new InvalidArgumentException('Target ' . $targetBranch . ' is no valid commit-ish.', 1553766210);
        }

        $finder = $targetBranch === null || $this->commitishComparator->isLocalBranchEqualTo($targetBranch)
            ? $this->allCheckableFileFinder
            : $this->diffCheckableFileFinder;

        $result = $finder->findFiles($allowedFileEndings, $exclusionListToken, $inclusionListToken, $targetBranch);

        return $result;
    }
}
