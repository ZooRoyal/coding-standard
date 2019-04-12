<?php

namespace Zooroyal\CodingStandard\CommandLine\ToolAdapters;

use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\Library\GenericCommandRunner;

class JSESLintAdapter extends AbstractBlackAndWhitelistAdapter implements ToolAdapterInterface, FixerSupportInterface
{
    /** @var string */
    protected $blacklistToken = '.dontSniffJS';
    /** @var string */
    protected $filter = '.js';
    /** @var string */
    protected $blacklistPrefix = '--ignore-pattern=';
    /** @var string */
    protected $blacklistGlue = ' ';
    /** @var string */
    protected $whitelistGlue = ' ';

    /**
     * JSESLintAdapter constructor.
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

        $esLintConfig = $environment->getPackageDirectory() . '/src/config/eslint/.eslintrc.js';
        $rootDirectory = $environment->getRootDirectory();

        $esLintBlacklistCommand = $environment->getPackageDirectory()
            . '/node_modules/eslint/bin/eslint.js --config=' . $esLintConfig . ' %1$s ' . $rootDirectory;
        $esLintWhitelistCommand = $environment->getPackageDirectory()
            . '/node_modules/eslint/bin/eslint.js --config=' . $esLintConfig . ' %1$s';
        $esLintFixBlacklistCommand = $environment->getPackageDirectory()
            . '/node_modules/eslint/bin/eslint.js --config=' . $esLintConfig . ' --fix %1$s ' . $rootDirectory;
        $esLintFixWhitelistCommand = $environment->getPackageDirectory()
            . '/node_modules/eslint/bin/eslint.js --config=' . $esLintConfig . ' --fix %1$s';

        $this->commands = [
            'ESLINTBL' => $esLintBlacklistCommand,
            'ESLINTWL' => $esLintWhitelistCommand,
            'ESLINTFIXBL' => $esLintFixBlacklistCommand,
            'ESLINTFIXWL' => $esLintFixWhitelistCommand,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function writeViolationsToOutput($targetBranch = '', bool $processIsolation = false)
    {
        $tool = 'ESLINT';
        $prefix = $tool . ' : ';
        $fullMessage = $prefix . 'Running full check';
        $diffMessage = $prefix . 'Running check on diff';

        $exitCode = $this->runTool($targetBranch, $processIsolation, $fullMessage, $tool, $diffMessage);

        return $exitCode;
    }

    /**
     * {@inheritDoc}
     */
    public function fixViolations($targetBranch = '', bool $processIsolation = false)
    {
        $tool = 'ESLINTFIX';
        $prefix = $tool . ' : ';
        $fullMessage = $prefix . 'Fix all Files';
        $diffMessage = $prefix . 'Fix Files in diff';

        $exitCode = $this->runTool($targetBranch, $processIsolation, $fullMessage, $tool, $diffMessage);

        return $exitCode;
    }
}
