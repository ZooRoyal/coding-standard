<?php
namespace Zooroyal\CodingStandard\CommandLine\ToolAdapters;

use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\Library\GenericCommandRunner;

class JSStyleLintAdapter implements FixerSupportInterface
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
        $this->environment          = $environment;
        $this->output               = $output;
        $this->genericCommandRunner = $genericCommandRunner;

        $stylelintConfig = $environment->getPackageDirectory() . '/src/config/stylelint/.stylelintrc';
        $rootDirectory   = $environment->getRootDirectory();

        $this->stopword = '.dontSniffLESS';
        $this->filter   = '.less';

        $styleLintBlacklistCommand    = $environment->getPackageDirectory()
            . '/node_modules/stylelint/bin/stylelint.js --config=' . $stylelintConfig . ' %1$s ' . $rootDirectory
            . '/**' . $this->filter;
        $styleLintWhitelistCommand    = $environment->getPackageDirectory()
            . '/node_modules/stylelint/bin/stylelint.js --config=' . $stylelintConfig . ' %1$s';
        $styleLintFixBlacklistCommand = $environment->getPackageDirectory()
            . '/node_modules/stylelint/bin/stylelint.js --config='
            . $stylelintConfig . ' --fix %1$s ' . $rootDirectory . '/**' . $this->filter;
        $styleLintFixWhitelistCommand = $environment->getPackageDirectory()
            . '/node_modules/stylelint/bin/stylelint.js --config=' . $stylelintConfig . ' --fix %1$s';

        $this->commands = [
            'STYLELINTWL'    => $styleLintWhitelistCommand,
            'STYLELINTFIXWL' => $styleLintFixWhitelistCommand,
            'STYLELINTBL'    => $styleLintBlacklistCommand,
            'STYLELINTFIXBL' => $styleLintFixBlacklistCommand,
        ];
    }

    /**
     * Search for violations by using STYLELINT and write finds to screen.
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
        $tool        = 'STYLELINT';

        $exitCode = $this->runTool($targetBranch, $processIsolation, $fullMessage, $tool, $diffMessage);

        return $exitCode;
    }

    /**
     * Tries to fix violations by calling STYLELINT in fix mode.
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
        $tool        = 'STYLELINTFIX';

        $exitCode = $this->runTool($targetBranch, $processIsolation, $fullMessage, $tool, $diffMessage);

        return $exitCode;
    }

    /**
     * Runs StyleLint in normal or fix mode according to the settings.
     *
     * @param $targetBranch
     * @param $processIsolation
     * @param $fullMessage
     * @param $tool
     * @param $diffMessage
     *
     * @return int|null
     */
    private function runTool($targetBranch, $processIsolation, $fullMessage, $tool, $diffMessage)
    {
        if (empty($targetBranch) || $this->environment->isLocalBranchEqualTo('origin/master')) {
            $this->output->writeln($fullMessage, OutputInterface::VERBOSITY_NORMAL);
            $exitCode = $this->genericCommandRunner->runBlacklistCommand(
                $this->commands[$tool . 'BL'],
                $this->stopword,
                '--ignore-pattern=',
                ' '
            );
        } else {
            $this->output->writeln($diffMessage . ' diff to ' . $targetBranch, OutputInterface::VERBOSITY_NORMAL);
            $exitCode = $this->genericCommandRunner->runWhitelistCommand(
                $this->commands[$tool . 'WL'],
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
