<?php

namespace Zooroyal\CodingStandard\CommandLine\ToolAdapters;

use DI\Annotation\Injectable;

/**
 * Class PHPMessDetectorAdapter
 *
 * @Injectable(lazy=true)
 */
class PHPMessDetectorAdapter extends AbstractBlackAndWhitelistAdapter implements ToolAdapterInterface
{
    protected string $blacklistToken = '.dontMessDetectPHP';
    /** @var array<string> */
    protected array $allowedFileEndings = ['.php'];
    protected string $blacklistGlue = ',';
    protected string $whitelistGlue = ',';
    /** @var string */
    private const TOOL_SHORT_NAME = 'PHPMD';

    /**
     * {@inheritDoc}
     */
    protected function init(): void
    {
        $vendorPath = $this->environment->getVendorPath()->getRealPath();
        $rootDirectory = $this->environment->getRootDirectory()->getRealPath();

        $phpMessDetectorConfig = $this->environment->getPackageDirectory()->getRealPath() . '/config/phpmd/phpmd.xml';

        $this->commands['PHPMDWL'] = 'php ' . $vendorPath . '/bin/phpmd %1$s' .
            ' text ' . $phpMessDetectorConfig . ' --suffixes php';
        $this->commands['PHPMDBL'] = 'php ' . $vendorPath . '/bin/phpmd '
            . $rootDirectory . ' text ' . $phpMessDetectorConfig . ' --suffixes php --exclude %1$s';
    }

    /**
     * {@inheritDoc}
     */
    public function writeViolationsToOutput($targetBranch = ''): int
    {
        $prefix = self::TOOL_SHORT_NAME . ' : ';
        $fullMessage = $prefix . 'Running full check';
        $diffMessage = $prefix . 'Running check on diff';

        $exitCode = $this->runTool($targetBranch, $fullMessage, self::TOOL_SHORT_NAME, $diffMessage);

        return $exitCode;
    }
}
