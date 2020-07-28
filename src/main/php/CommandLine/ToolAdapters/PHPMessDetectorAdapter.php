<?php

namespace Zooroyal\CodingStandard\CommandLine\ToolAdapters;

class PHPMessDetectorAdapter extends AbstractBlackAndWhitelistAdapter implements ToolAdapterInterface
{
    /** @var string */
    protected $blacklistToken = '.dontMessDetectPHP';
    /** @var string */
    protected $filter = '.php';
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
        $rootDirectory = $this->environment->getRootDirectory();

        $this->commands['PHPMDWL'] = 'php ' . $rootDirectory . '/vendor/bin/phpmd %1$s' .
            ' text ' . $phpMessDetectorConfig . ' --suffixes php';
        $this->commands['PHPMDBL'] = 'php ' . $rootDirectory . '/vendor/bin/phpmd '
            . $rootDirectory . ' text ' . $phpMessDetectorConfig . ' --suffixes php --exclude %1$s';
    }

    /**
     * {@inheritDoc}
     */
    public function writeViolationsToOutput($targetBranch = '', bool $processIsolation = false)
    {
        $toolShortName = 'PHPMD';
        $prefix = $toolShortName . ' : ';
        $fullMessage = $prefix . 'Running full check';
        $diffMessage = $prefix . 'Running check on diff';

        $exitCode = $this->runTool($targetBranch, $processIsolation, $fullMessage, $toolShortName, $diffMessage);

        return $exitCode;
    }
}
