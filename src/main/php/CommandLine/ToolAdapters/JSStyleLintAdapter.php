<?php

namespace Zooroyal\CodingStandard\CommandLine\ToolAdapters;

use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\Library\GenericCommandRunner;

class JSStyleLintAdapter extends AbstractBlackAndWhitelistAdapter implements ToolAdapterInterface, FixerSupportInterface
{
    /** @var string */
    protected $blacklistToken = '.dontSniffLESS';
    /** @var string */
    protected $filter = '.less';
    /** @var string */
    protected $blacklistPrefix = '--ignore-pattern=';
    /** @var string */
    protected $blacklistGlue = ' ';
    /** @var string */
    protected $whitelistGlue = ' ';

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

        $stylelintConfig = $environment->getPackageDirectory() . '/config/stylelint/.stylelintrc';
        $rootDirectory = $environment->getRootDirectory();

        $styleLintBlacklistCommand = $environment->getPackageDirectory()
            . '/node_modules/.bin/stylelint --config=' . $stylelintConfig . ' %1$s ' . $rootDirectory
            . '/**' . $this->filter;
        $styleLintWhitelistCommand = $environment->getPackageDirectory()
            . '/node_modules/.bin/stylelint --config=' . $stylelintConfig . ' %1$s';
        $styleLintFixBlacklistCommand = $environment->getPackageDirectory()
            . '/node_modules/.bin/stylelint --config='
            . $stylelintConfig . ' --fix %1$s ' . $rootDirectory . '/**' . $this->filter;
        $styleLintFixWhitelistCommand = $environment->getPackageDirectory()
            . '/node_modules/.bin/stylelint --config=' . $stylelintConfig . ' --fix %1$s';

        $this->commands = [
            'STYLELINTWL' => $styleLintWhitelistCommand,
            'STYLELINTFIXWL' => $styleLintFixWhitelistCommand,
            'STYLELINTBL' => $styleLintBlacklistCommand,
            'STYLELINTFIXBL' => $styleLintFixBlacklistCommand,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function writeViolationsToOutput($targetBranch = '', bool $processIsolation = false): int
    {
        $tool = 'STYLELINT';
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
        $tool = 'STYLELINTFIX';
        $prefix = $tool . ' : ';
        $fullMessage = $prefix . 'Fix all Files';
        $diffMessage = $prefix . 'Fix Files in diff';

        $exitCode = $this->runTool($targetBranch, $processIsolation, $fullMessage, $tool, $diffMessage);

        return $exitCode;
    }
}
