<?php

namespace Zooroyal\CodingStandard\CommandLine\ToolAdapters;

use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\Library\GenericCommandRunner;

class PHPParallelLintAdapter extends AbstractBlackAndWhitelistAdapter implements ToolAdapterInterface
{
    /** @var string */
    protected $blacklistToken = '.dontLintPHP';
    /** @var string */
    protected $filter = '.php';
    /** @var string */
    protected $blacklistPrefix = '--exclude ';
    /** @var string */
    protected $blacklistGlue = ' ';
    /** @var string */
    protected $whitelistGlue = ' ';

    /**
     * PHPParallelLintAdapter constructor.
     *
     * @param Environment $environment
     * @param OutputInterface $output
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

        $rootDirectory = $environment->getRootDirectory();

        $this->commands['PHPPLWL'] = 'php ' . $rootDirectory
            . '/vendor/bin/parallel-lint -j 2 %1$s';
        $this->commands['PHPPLBL'] =  'php ' . $rootDirectory
            . '/vendor/bin/parallel-lint -j 2 %1$s ./';
    }


    public function writeViolationsToOutput($targetBranch = '', $processIsolation = false)
    {
        $toolShortName = 'PHPPL';
        $prefix = $toolShortName . ' : ';
        $fullMessage = $prefix . 'Running full check';
        $diffMessage = $prefix . 'Running check on diff';

        $exitCode = $this->runTool($targetBranch, $processIsolation, $fullMessage, $toolShortName, $diffMessage);

        return $exitCode;
    }
}
