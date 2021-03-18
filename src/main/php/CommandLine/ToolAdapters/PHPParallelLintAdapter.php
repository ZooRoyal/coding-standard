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
    protected string $blacklistToken = '.dontLintPHP';
    /** @var string[] */
    protected array $allowedFileEndings = ['.php'];
    protected string $blacklistPrefix = '--exclude ';
    protected string $blacklistGlue = ' ';
    protected string $whitelistGlue = ' ';
    /** @var string */
    private const TOOL_SHORT_NAME = 'PHPPL';

    /**
     * {@inheritDoc}
     */
    protected function init(): void
    {
        $vendorPath = $this->environment->getVendorPath()->getRealPath();
        $rootDirectory = $this->environment->getRootDirectory()->getRealPath();

        $this->blacklistPrefix = '--exclude ' . $rootDirectory . '/';
        $this->commands['PHPPLWL'] = 'php ' . $vendorPath . '/bin/parallel-lint -j 2 %1$s';
        $this->commands['PHPPLBL'] = 'php ' . $vendorPath . '/bin/parallel-lint -j 2 %1$s ' . $rootDirectory;
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
