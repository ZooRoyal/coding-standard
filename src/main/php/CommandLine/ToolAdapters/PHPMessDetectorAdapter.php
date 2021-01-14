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
    /** @var string[] */
    protected array $allowedFileEndings = ['.php'];
    protected string $blacklistGlue = ',';
    protected string $whitelistGlue = ',';

    /**
     * {@inheritDoc}
     */
    protected function init(): void
    {
        $phpMessDetectorConfig = $this->environment->getPackageDirectory()->getRealPath() . '/config/phpmd/phpmd.xml';
        $rootDirectory = $this->environment->getRootDirectory()->getRealPath();

        $this->commands['PHPMDWL'] = 'php ' . $rootDirectory . '/vendor/bin/phpmd %1$s' .
            ' text ' . $phpMessDetectorConfig . ' --suffixes php';
        $this->commands['PHPMDBL'] = 'php ' . $rootDirectory . '/vendor/bin/phpmd '
            . $rootDirectory . ' text ' . $phpMessDetectorConfig . ' --suffixes php --exclude %1$s';
    }

    /**
     * {@inheritDoc}
     */
    public function writeViolationsToOutput($targetBranch = '', bool $processIsolation = false): ?int
    {
        $toolShortName = 'PHPMD';
        $prefix = $toolShortName . ' : ';
        $fullMessage = $prefix . 'Running full check';
        $diffMessage = $prefix . 'Running check on diff';

        $exitCode = $this->runTool($targetBranch, $processIsolation, $fullMessage, $toolShortName, $diffMessage);

        return $exitCode;
    }
}
