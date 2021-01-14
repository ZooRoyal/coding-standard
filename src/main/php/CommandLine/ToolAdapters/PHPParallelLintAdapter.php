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

    /**
     * {@inheritDoc}
     */
    protected function init(): void
    {
        $rootDirectory = $this->environment->getRootDirectory()->getRealPath();

        $this->commands['PHPPLWL'] = 'php ' . $rootDirectory
            . '/vendor/bin/parallel-lint -j 2 %1$s';
        $this->commands['PHPPLBL'] = 'php ' . $rootDirectory
            . '/vendor/bin/parallel-lint -j 2 %1$s ./';
    }


    /**
     * {@inheritDoc}
     */
    public function writeViolationsToOutput($targetBranch = '', bool $processIsolation = false): ?int
    {
        $toolShortName = 'PHPPL';
        $prefix = $toolShortName . ' : ';
        $fullMessage = $prefix . 'Running full check';
        $diffMessage = $prefix . 'Running check on diff';

        $exitCode = $this->runTool($targetBranch, $processIsolation, $fullMessage, $toolShortName, $diffMessage);

        return $exitCode;
    }
}
