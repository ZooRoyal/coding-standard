<?php

namespace Zooroyal\CodingStandard\CommandLine\ToolAdapters;

class PHPParallelLintAdapter extends AbstractBlackAndWhitelistAdapter implements ToolAdapterInterface
{
    /** @var string */
    protected $blacklistToken = '.dontLintPHP';
    /** @var string[] */
    protected $allowedFileEndings = ['.php'];
    /** @var string */
    protected $blacklistPrefix = '--exclude ';
    /** @var string */
    protected $blacklistGlue = ' ';
    /** @var string */
    protected $whitelistGlue = ' ';

    /**
     * {@inheritDoc}
     */
    protected function init()
    {
        $rootDirectory = $this->environment->getRootDirectory();

        $this->commands['PHPPLWL'] = 'php ' . $rootDirectory
            . '/vendor/bin/parallel-lint -j 2 %1$s';
        $this->commands['PHPPLBL'] = 'php ' . $rootDirectory
            . '/vendor/bin/parallel-lint -j 2 %1$s ./';
    }


    /**
     * {@inheritDoc}
     */
    public function writeViolationsToOutput($targetBranch = '', bool $processIsolation = false)
    {
        $toolShortName = 'PHPPL';
        $prefix = $toolShortName . ' : ';
        $fullMessage = $prefix . 'Running full check';
        $diffMessage = $prefix . 'Running check on diff';

        $exitCode = $this->runTool($targetBranch, $processIsolation, $fullMessage, $toolShortName, $diffMessage);

        return $exitCode;
    }
}
