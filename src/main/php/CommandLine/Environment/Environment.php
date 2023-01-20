<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\Environment;

use ComposerLocator;
use Zooroyal\CodingStandard\CommandLine\EnhancedFileInfo\EnhancedFileInfo;
use Zooroyal\CodingStandard\CommandLine\EnhancedFileInfo\EnhancedFileInfoFactory;
use Zooroyal\CodingStandard\CommandLine\Process\ProcessRunner;

use function Safe\realpath;

/**
 * This Class supplies information about the environment the script is running in.
 */
class Environment
{
    /** @var string */
    private const GIT = 'git';

    public function __construct(
        private readonly ProcessRunner $processRunner,
        private readonly EnhancedFileInfoFactory $enhancedFileInfoFactory,
    ) {
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
     * Returns vendor directory where coding-standard is installed.
     */
    public function getVendorDirectory(): EnhancedFileInfo
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
}
