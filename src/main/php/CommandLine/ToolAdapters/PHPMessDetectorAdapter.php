<?php

namespace Zooroyal\CodingStandard\CommandLine\ToolAdapters;

use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\Library\GenericCommandRunner;

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
     * PHPCodeSnifferAdapter constructor.
     *
     * @param Environment          $environment
     * @param OutputInterface      $output
     * @param GenericCommandRunner $genericCommandRunner
     */
    public function __construct(
        Environment $environment,
        OutputInterface $output,
        GenericCommandRunner $genericCommandRunner
    ) {
        $this->environment = $environment;
        $this->output = $output;
        $this->genericCommandRunner = $genericCommandRunner;

        $phpMessDetectorConfig = $environment->getPackageDirectory() . '/config/phpmd/phpmd.xml';
        $rootDirectory = $environment->getRootDirectory();

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
