<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\FileFinder;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Zooroyal\CodingStandard\CommandLine\Process\ProcessRunner;

class GitInputValidator
{
    /**
     * GitInputValidator constructor.
     */
    public function __construct(private ProcessRunner $processRunner)
    {
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
        } catch (ProcessFailedException) {
            return false;
        }
        return true;
    }
}
