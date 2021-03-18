<?php

namespace Zooroyal\CodingStandard\CommandLine\Library;

use Symfony\Component\Process\Exception\ProcessFailedException;

class GitInputValidator
{
    private ProcessRunner $processRunner;

    /**
     * GitInputValidator constructor.
     *
     * @param ProcessRunner $processRunner
     */
    public function __construct(ProcessRunner $processRunner)
    {
        $this->processRunner = $processRunner;
    }

    public function isCommitishValid($commitish) : bool
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
