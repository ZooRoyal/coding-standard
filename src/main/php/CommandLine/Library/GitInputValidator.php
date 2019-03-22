<?php

namespace Zooroyal\CodingStandard\CommandLine\Library;

use Symfony\Component\Process\Exception\ProcessFailedException;

class GitInputValidator
{
    /** @var ProcessRunner */
    private $processRunner;

    /**
     * GitInputValidator constructor.
     *
     * @param ProcessRunner $processRunner
     */
    public function __construct(ProcessRunner $processRunner)
    {
        $this->processRunner = $processRunner;
    }

    public function isCommitishValid(string $commitish)
    {
        try {
            $this->processRunner->runAsProcess('git', 'rev-parse', $commitish);
        } catch (ProcessFailedException $processFailedException) {
            return false;
        }
        return true;
    }
}
