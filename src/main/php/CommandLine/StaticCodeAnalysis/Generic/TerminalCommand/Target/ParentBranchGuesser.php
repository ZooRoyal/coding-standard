<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Target;

use Zooroyal\CodingStandard\CommandLine\Process\ProcessRunner;

class ParentBranchGuesser
{
    /** @var string */
    private const GIT = 'git';
    private ProcessRunner $processRunner;

    public function __construct(
        ProcessRunner $processRunner
    ) {
        $this->processRunner = $processRunner;
    }

    /**
     * This method searches the first parent commit which is part of another branch and returns this commit as merge
     * base with parent branch.
     */
    public function guessParentBranchAsCommitHash(string $branchName = 'HEAD'): string
    {
        $initialNumberOfContainingBranches = $this->getCountOfContainingBranches($branchName);
        while ($this->isParentCommitishACommit($branchName)) {
            $branchName .= '^';
            $numberOfContainingBranches = $this->getCountOfContainingBranches($branchName);

            if ($numberOfContainingBranches !== $initialNumberOfContainingBranches) {
                break;
            }
        }
        $gitCommitHash = $this->processRunner->runAsProcess(self::GIT, 'rev-parse', $branchName);

        return $gitCommitHash;
    }

    /**
     * Calls git to retriev the count of branches this commit is part of.
     */
    private function getCountOfContainingBranches(string $targetCommit): int
    {
        $process = $this->processRunner->runAsProcess(
            self::GIT,
            'branch',
            '-a',
            '--contains',
            $targetCommit
        );

        $numberOfContainingBranches = substr_count(
            $process,
            PHP_EOL
        );

        return $numberOfContainingBranches;
    }

    /**
     * Returns true if $targetCommit commit-ish is a valid commit.
     */
    private function isParentCommitishACommit(string $targetCommit): bool
    {
        $targetType = $this->processRunner->runAsProcess(self::GIT, 'cat-file', '-t', $targetCommit . '^');

        return $targetType === 'commit';
    }
}
