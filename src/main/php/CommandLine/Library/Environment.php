<?php

namespace Zooroyal\CodingStandard\CommandLine\Library;

use ComposerLocator;
use Symplify\SmartFileSystem\SmartFileInfo;
use Zooroyal\CodingStandard\CommandLine\Factories\SmartFileInfoFactory;

/**
 * This Class supplies information about the environment the script is running in.
 */
class Environment
{
    private ?string $localHeadHash = null;

    /** @var array<SmartFileInfo> */
    private array $blacklistedDirectories;
    private ProcessRunner $processRunner;
    private GitInputValidator $gitInputValidator;
    private SmartFileInfoFactory $smartFileInfoFactory;

    public function __construct(
        ProcessRunner $processRunner,
        GitInputValidator $gitInputValidator,
        SmartFileInfoFactory $smartFileInfoFactory
    ) {
        $this->processRunner = $processRunner;
        $this->gitInputValidator = $gitInputValidator;
        $this->smartFileInfoFactory = $smartFileInfoFactory;

        $this->blacklistedDirectories = $this->smartFileInfoFactory->buildFromArrayOfPaths(
            [
                '.eslintrc.js',
                '.git',
                '.idea',
                '.vagrant',
                'node_modules',
                'vendor',
                'bower_components',
                '.pnpm',
                '.pnpm-store',
            ]
        );
    }

    /**
     * Returns the directory of the root composer.json. As the vendor directory can be moved
     * we can not determine the directory relative to our own package.
     */
    public function getRootDirectory(): SmartFileInfo
    {
        $projectRootPath = realpath(ComposerLocator::getRootPath());
        $smartFileInfo = $this->smartFileInfoFactory->buildFromPath($projectRootPath);

        return $smartFileInfo;
    }

    /**
     * Returns the directory of out package
     */
    public function getPackageDirectory(): SmartFileInfo
    {
        $smartFileInfo = $this->smartFileInfoFactory->buildFromPath((dirname(__DIR__, 5)));
        return $smartFileInfo;
    }

    /**
     * Returns blacklisted directories from the static global blacklist
     *
     * @return array<SmartFileInfo>
     */
    public function getBlacklistedDirectories(): array
    {
        return $this->blacklistedDirectories;
    }

    /**
     * Compare if the HEAD of $target Branch equals the HEAD of the local branch.
     *
     * @param string|null $targetBranch
     *
     * @return bool
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
     *
     * @param string $branchName
     *
     * @return string
     */
    private function commitishToHash(string $branchName): string
    {
        return $this->processRunner->runAsProcess('git', 'rev-list', '-n 1', $branchName);
    }

    /**
     * This method searches the first parent commit which is part of another branch and returns this commit as merge base
     * with parent branch.
     *
     * @param string $branchName
     *
     * @return string
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
        $gitCommitHash = $this->processRunner->runAsProcess('git', 'rev-parse', $branchName);

        return $gitCommitHash;
    }

    /**
     * Calls git to retriev the count of branches this commit is part of.
     *
     * @param string $targetCommit
     *
     * @return int
     */
    private function getCountOfContainingBranches(string $targetCommit): int
    {
        $numberOfContainingBranches = substr_count(
            $this->processRunner->runAsProcess(
                'git',
                'branch',
                '-a',
                '--contains',
                $targetCommit
            ),
            PHP_EOL
        );

        return $numberOfContainingBranches;
    }

    /**
     * Returns true if $targetCommit commit-ish is a valid commit.
     *
     * @param string $targetCommit
     *
     * @return bool
     */
    private function isParentCommitishACommit(string $targetCommit): bool
    {
        $targetType = $this->processRunner->runAsProcess('git', 'cat-file', '-t', $targetCommit . '^');

        return $targetType === 'commit';
    }
}
