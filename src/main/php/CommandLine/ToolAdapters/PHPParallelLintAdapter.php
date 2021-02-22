<?php

namespace Zooroyal\CodingStandard\CommandLine\ToolAdapters;

use DI\Annotation\Injectable;

/**
 * Class PHPParallelLintAdapter
 *
 * @Injectable(lazy=true)
 */
class PHPParallelLintAdapter extends AbstractBlackAndWhitelistAdapter implements ToolAdapterInterface
{
    /** @var string */
    protected $blacklistToken = '.dontLintPHP';
    /** @var string[] */
    protected $allowedFileEndings = ['.php'];
    /** @var string */
    protected $blacklistPrefix = '';
    /** @var string */
    protected $blacklistGlue = ' ';
    /** @var string */
    protected $whitelistGlue = ' ';

    /**
     * {@inheritDoc}
     */
    protected function init()
    {
        $vendorPath = $this->environment->getVendorPath();
        $rootDirectory = $this->environment->getRootDirectory();

        $this->blacklistPrefix = '--exclude ' . $rootDirectory . '/';
        $this->commands['PHPPLWL'] = 'php ' . $vendorPath . '/bin/parallel-lint -j 2 %1$s';
        $this->commands['PHPPLBL'] = 'php ' . $vendorPath . '/bin/parallel-lint -j 2 %1$s ' . $rootDirectory;
    }


    /**
     * {@inheritDoc}
     */
    public function writeViolationsToOutput($targetBranch = '')
    {
        $toolShortName = 'PHPPL';
        $prefix = $toolShortName . ' : ';
        $fullMessage = $prefix . 'Running full check';
        $diffMessage = $prefix . 'Running check on diff';

        $exitCode = $this->runTool($targetBranch, $fullMessage, $toolShortName, $diffMessage);

        return $exitCode;
    }
}
