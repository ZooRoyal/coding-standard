<?php
namespace Zooroyal\CodingStandard\CommandLine\ToolAdapters;

use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\Library\GenericCommandRunner;

class JSESLintAdapter implements FixerSupportInterface
{
    /** @var OutputInterface */
    private $output;
    /** @var string[] */
    private $commands;
    /** @var Environment */
    private $environment;
    /** @var GenericCommandRunner */
    private $genericCommandRunner;
    /** @var string */
    private $stopword;
    /** @var string */
    private $filter;

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
        $this->environment          = $environment;
        $this->output               = $output;
        $this->genericCommandRunner = $genericCommandRunner;

        $esLintConfig  = $environment->getPackageDirectory() . '/src/config/eslint/.eslintrc.js';
        $rootDirectory = $environment->getRootDirectory();

        $this->stopword = '.dontSniffJS';
        $this->filter   = '.js';

        $esLintBlacklistCommand    = $environment->getPackageDirectory()
            . '/node_modules/eslint/bin/eslint.js --config=' . $esLintConfig . ' %1$s ' . $rootDirectory;
        $esLintWhitelistCommand    = $environment->getPackageDirectory()
            . '/node_modules/eslint/bin/eslint.js --config=' . $esLintConfig . ' %1$s';
        $esLintFixBlacklistCommand = $environment->getPackageDirectory()
            . '/node_modules/eslint/bin/eslint.js --config=' . $esLintConfig . ' --fix %1$s ' . $rootDirectory;
        $esLintFixWhitelistCommand = $environment->getPackageDirectory()
            . '/node_modules/eslint/bin/eslint.js --config=' . $esLintConfig . ' --fix %1$s';

        $this->commands = [
            'ESLINTBL'    => $esLintBlacklistCommand,
            'ESLINTWL'    => $esLintWhitelistCommand,
            'ESLINTFIXBL' => $esLintFixBlacklistCommand,
            'ESLINTFIXWL' => $esLintFixWhitelistCommand,
        ];
    }

    /**
     * Search for violations by using ESLINT and write finds to screen.
     *
     * @param string $targetBranch
     * @param bool   $processIsolation
     *
     * @return int|null
     */
    public function writeViolationsToOutput($targetBranch = '', $processIsolation = false)
    {
        $fullMessage = 'Running full check.';
        $diffMessage = 'Running check on';
        $tool        = 'ESLINT';

        $exitCode = $this->runTool($targetBranch, $processIsolation, $fullMessage, $tool, $diffMessage);

        return $exitCode;
    }

    /**
     * Tries to fix violations by calling ESLINT in fix mode.
     *
     * @param string $targetBranch
     * @param bool   $processIsolation
     *
     * @return int|null
     */
    public function fixViolations($targetBranch = '', $processIsolation = false)
    {
        $fullMessage = 'Fix all Files.';
        $diffMessage = 'Fix Files in';
        $tool        = 'ESLINTFIX';

        $exitCode = $this->runTool($targetBranch, $processIsolation, $fullMessage, $tool, $diffMessage);

        return $exitCode;
    }

    /**
     * Runs ESLint in normal or fix mode according to settings.
     *
     * @param string $targetBranch
     * @param bool   $processIsolation
     * @param string $fullMessage
     * @param string $tool
     * @param string $diffMessage
     *
     * @return int|null
     */
    private function runTool($targetBranch, $processIsolation, $fullMessage, $tool, $diffMessage)
    {
        if (empty($targetBranch) || $this->environment->isLocalBranchEqualTo('master')) {
            $this->output->writeln($fullMessage, OutputInterface::VERBOSITY_NORMAL);
            $template = $this->commands[$tool . 'BL'];
            $prefix   = '--ignore-pattern=';
            $exitCode = $this->genericCommandRunner->runBlacklistCommand(
                $template,
                $this->stopword,
                $prefix,
                ' '
            );
        } else {
            $this->output->writeln($diffMessage . ' diff to ' . $targetBranch, OutputInterface::VERBOSITY_NORMAL);
            $template = $this->commands[$tool . 'WL'];
            $exitCode  = $this->genericCommandRunner->runWhitelistCommand(
                $template,
                $targetBranch,
                $this->stopword,
                $this->filter,
                $processIsolation,
                ' '
            );
        }

        return $exitCode;
    }

}
