<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\Library;

class CommitishComparator
{
    private ?string $localHeadHash = null;
    private GitInputValidator $gitInputValidator;
    private ProcessRunner $processRunner;

    public function __construct(GitInputValidator $gitInputValidator, ProcessRunner $processRunner)
    {
        $this->gitInputValidator = $gitInputValidator;
        $this->processRunner = $processRunner;
    }

    /**
     * Compare if the HEAD of $target Branch equals the HEAD of the local branch.
     */
    public function isLocalBranchEqualTo(?string $targetBranch): bool
    {
        if (!$this->gitInputValidator->isCommitishValid($targetBranch)) {
            return false;
        }
        if ($this->localHeadHash === null) {
            $this->localHeadHash = $this->commitishToHash('HEAD');
        }

        $targetCommitHash = $this->commitishToHash($targetBranch);

        return $targetCommitHash === $this->localHeadHash;
    }

    /**
     * Converts a commit-tish to a commit hash.
     */
    private function commitishToHash(?string $branchName): string
    {
        return $this->processRunner->runAsProcess('git', 'rev-list', '-n 1', $branchName);
    }
}
