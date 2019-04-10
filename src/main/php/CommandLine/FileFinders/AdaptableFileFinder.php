<?php

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
    /** @var GitInputValidator */
    private $gitInputValidator;
    /** @var AllCheckableFileFinder */
    private $allCheckableFileFinder;
    /** @var DiffCheckableFileFinder */
    private $diffCheckableFileFinder;
    /** @var Environment */
    private $environment;

    /**
     * AdaptableFileFinder constructor.
     *
     * @param GitInputValidator       $gitInputValidator
     * @param AllCheckableFileFinder  $allCheckableFileFinder
     * @param DiffCheckableFileFinder $diffCheckableFileFinder
     * @param Environment             $environment
     */
    public function __construct(
        GitInputValidator $gitInputValidator,
        AllCheckableFileFinder $allCheckableFileFinder,
        DiffCheckableFileFinder $diffCheckableFileFinder,
        Environment $environment
    ) {
        $this->gitInputValidator = $gitInputValidator;
        $this->allCheckableFileFinder = $allCheckableFileFinder;
        $this->diffCheckableFileFinder = $diffCheckableFileFinder;
        $this->environment = $environment;
    }


    /**
     * {@inheritDoc}
     *
     * @throws InvalidArgumentException
     */
    public function findFiles(
        string $filter = '',
        string $blacklistToken = '',
        string $whitelistToken = '',
        $targetBranchInput = ''
    ) : GitChangeSet {
        if ($targetBranchInput !== false
            && $targetBranchInput !== null
            && !$this->gitInputValidator->isCommitishValid($targetBranchInput)
        ) {
            throw new InvalidArgumentException('Target ' . $targetBranchInput . ' is no valid commit-ish.', 1553766210);
        }

        $finder = $targetBranchInput === false || $this->environment->isLocalBranchEqualTo($targetBranchInput)
            ? $this->allCheckableFileFinder
            : $this->diffCheckableFileFinder;

        $targetBranch = $targetBranchInput ?? $this->environment->guessParentBranchAsCommitHash();

        $result = $finder->findFiles($filter, $blacklistToken, $whitelistToken, $targetBranch);

        return $result;
    }
}
