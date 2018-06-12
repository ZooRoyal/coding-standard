<?php
namespace Zooroyal\CodingStandard\CommandLine\Library;

class Environment
{
    /** @var string */
    private $rootDirectory;
    /** @var string */
    private $localBranch;

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

    public function getLocalBranch()
    {
        if ($this->localBranch === null) {
            $this->localBranch = $this->processRunner->runAsProcess('git name-rev --exclude=tag\* --name-only HEAD');
        }

        return $this->localBranch;
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
}
