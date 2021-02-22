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
    /** @var string */
    protected $blacklistToken = '.dontMessDetectPHP';
    /** @var string[] */
    protected $allowedFileEndings = ['.php'];
    /** @var string */
    protected $blacklistGlue = ',';
    /** @var string */
    protected $whitelistGlue = ',';

    /**
     * {@inheritDoc}
     */
    protected function init()
    {
        $phpMessDetectorConfig = $this->environment->getPackageDirectory() . '/config/phpmd/phpmd.xml';

        $vendorPath = $this->environment->getVendorPath();
        $rootDirectory = $this->environment->getRootDirectory();

        $this->commands['PHPMDWL'] = 'php ' . $vendorPath . '/bin/phpmd %1$s' .
            ' text ' . $phpMessDetectorConfig . ' --suffixes php';
        $this->commands['PHPMDBL'] = 'php ' . $vendorPath . '/bin/phpmd '
            . $rootDirectory . ' text ' . $phpMessDetectorConfig . ' --suffixes php --exclude %1$s';
    }

    /**
     * {@inheritDoc}
     */
    public function writeViolationsToOutput($targetBranch = '')
    {
        $toolShortName = 'PHPMD';
        $prefix = $toolShortName . ' : ';
        $fullMessage = $prefix . 'Running full check';
        $diffMessage = $prefix . 'Running check on diff';

        $exitCode = $this->runTool($targetBranch, $fullMessage, $toolShortName, $diffMessage);

        return $exitCode;
    }
}
