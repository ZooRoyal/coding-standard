<?php
namespace Zooroyal\CodingStandard\CommandLine\Library;

/**
 * This Class supplies information about the environment the script is running in.
 */
class Environment
{
    /** @var string */
    private $rootDirectory;
    /** @var string */
    private $localHeadHash;

    /** @var string[] */
    private $blacklistedDirectories = [
        '.eslintrc.js',
        '.git',
        '.idea',
        '.vagrant',
        'node_modules',
        'vendor',
        'bower_components',
    ];
    /** @var ProcessRunner */
    private $processRunner;

    public function __construct(ProcessRunner $processRunner)
    {
        $this->processRunner = $processRunner;
    }

    public function getRootDirectory()
    {
        if ($this->rootDirectory === null) {
            $this->rootDirectory = $this->processRunner->runAsProcess('git rev-parse --show-toplevel');
        }

        return $this->rootDirectory;
    }

    public function getPackageDirectory()
    {
        return realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR . '');
    }

    public function getBlacklistedDirectories()
    {
        return $this->blacklistedDirectories;
    }

    /**
     * Compare if the HEAD of $targetBrnach equals the HEAD of the local branch.
     *
     * @param string $targetBranch
     *
     * @return bool
     */
    public function isLocalBranchEqualTo($targetBranch)
    {
        if ($this->localHeadHash === null) {
            $this->localHeadHash = $this->commitishToHash('HEAD');
        }
        $targetCommitHash = $this->commitishToHash($targetBranch);

        return $targetCommitHash === $this->localHeadHash;
    }

    /**
     * Converts a commit-tish to a commit hash.
     *
     * @param string $branchName
     *
     * @return string
     */
    private function commitishToHash($branchName)
    {
        return $this->processRunner->runAsProcess('git rev-list -n 1 ' . escapeshellarg($branchName));
    }
}
