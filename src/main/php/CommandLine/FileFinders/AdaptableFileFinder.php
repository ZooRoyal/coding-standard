<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\FileFinders;

use Symfony\Component\Console\Exception\InvalidArgumentException;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\Library\GitInputValidator;
use Zooroyal\CodingStandard\CommandLine\ValueObjects\GitChangeSet;

/**
 * This class searches for files in Git. It decides for itself if it returns a diff to a branch or all relevant files.
 */
class AdaptableFileFinder implements FileFinderInterface
{
    /**
     * AdaptableFileFinder constructor.
     */
    public function __construct(
        private GitInputValidator $gitInputValidator,
        private AllCheckableFileFinder $allCheckableFileFinder,
        private DiffCheckableFileFinder $diffCheckableFileFinder,
        private Environment $environment,
    ) {
    }


    /**
     * {@inheritDoc}
     *
     * @throws InvalidArgumentException
     */
    public function findFiles(
        array $allowedFileEndings = [],
        string $blacklistToken = '',
        string $whitelistToken = '',
        ?string $targetBranch = null,
    ): GitChangeSet {
        if ($targetBranch !== null
            && !$this->gitInputValidator->isCommitishValid($targetBranch)
        ) {
            throw new InvalidArgumentException('Target ' . $targetBranch . ' is no valid commit-ish.', 1553766210);
        }

        $finder = $targetBranch === null || $this->environment->isLocalBranchEqualTo($targetBranch)
            ? $this->allCheckableFileFinder
            : $this->diffCheckableFileFinder;

        $result = $finder->findFiles($allowedFileEndings, $blacklistToken, $whitelistToken, $targetBranch);

        return $result;
    }
}
