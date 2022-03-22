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
    private ProcessRunner $processRunner;
    private EnhancedFileInfoFactory $enhancedFileInfoFactory;
    /** @var string */
    private const GIT = 'git';

    public function __construct(
        ProcessRunner $processRunner,
        EnhancedFileInfoFactory $enhancedFileInfoFactory
    ) {
        $this->processRunner = $processRunner;
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
}
