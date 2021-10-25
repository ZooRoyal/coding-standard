<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\Library;

use ComposerLocator;
use Zooroyal\CodingStandard\CommandLine\Factories\EnhancedFileInfoFactory;
use Zooroyal\CodingStandard\CommandLine\ValueObjects\EnhancedFileInfo;
use function Safe\realpath;

/**
 * This Class supplies information about the environment the script is running in.
 */
class Environment
{
    private ?string $localHeadHash = null;
    private ProcessRunner $processRunner;
    private GitInputValidator $gitInputValidator;
    private EnhancedFileInfoFactory $enhancedFileInfoFactory;
    /** @var string */
    private const GIT = 'git';

    public function __construct(
        ProcessRunner $processRunner,
        GitInputValidator $gitInputValidator,
        EnhancedFileInfoFactory $enhancedFileInfoFactory
    ) {
        $this->processRunner = $processRunner;
        $this->gitInputValidator = $gitInputValidator;
        $this->enhancedFileInfoFactory = $enhancedFileInfoFactory;
    }

    /**
     * Returns the directory of the root composer.json. As the vendor directory can be moved
     * we can not determine the directory relative to our own package.
     */
    public function getRootDirectory(): EnhancedFileInfo
    {
        $projectRootPath = $this->processRunner->runAsProcess(self::GIT, 'rev-parse', '--show-toplevel');
        $enhancedFileInfo = $this->enhancedFileInfoFactory->buildFromPath(realpath($projectRootPath));

        return $enhancedFileInfo;
    }

    /**
     * Returns vendor path where coding-standard is installed.
     */
    public function getVendorPath(): EnhancedFileInfo
    {
        $vendorPath = ComposerLocator::getRootPath() . DIRECTORY_SEPARATOR . 'vendor';
        $enhancedFileInfo = $this->enhancedFileInfoFactory->buildFromPath($vendorPath);
        return $enhancedFileInfo;
    }

    /**
     * Returns the directory of out package
     */
    public function getPackageDirectory(): EnhancedFileInfo
    {
        $packagePath = dirname(__DIR__, 5);
        $enhancedFileInfo = $this->enhancedFileInfoFactory->buildFromPath($packagePath);
        return $enhancedFileInfo;
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
        return $this->processRunner->runAsProcess(self::GIT, 'rev-list', '-n 1', $branchName);
    }

    /**
     * This method searches the first parent commit which is part of another branch and returns this commit as merge base
     * with parent branch.
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
