<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\Git;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Zooroyal\CodingStandard\CommandLine\Process\ProcessRunner;

class GitInputValidator
{
    private ProcessRunner $processRunner;

    /**
     * GitInputValidator constructor.
     */
    public function __construct(ProcessRunner $processRunner)
    {
        $this->processRunner = $processRunner;
    }

    /**
     * Checks if a commit-ish is known to the local git repository.
     */
    public function isCommitishValid(?string $commitish): bool
    {
        if ($commitish === null) {
            return false;
        }

        try {
            $this->processRunner->runAsProcess('git', 'rev-parse', $commitish);
        } catch (ProcessFailedException $processFailedException) {
            return false;
        }
        return true;
    }
}
