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
}
