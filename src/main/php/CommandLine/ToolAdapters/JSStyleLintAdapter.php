<?php

namespace Zooroyal\CodingStandard\CommandLine\ToolAdapters;

use DI\Annotation\Injectable;
use Zooroyal\CodingStandard\CommandLine\Library\Exceptions\TerminalCommandNotFoundException;

/**
 * Class JSStyleLintAdapter
 *
 * @Injectable(lazy=true)
 */
class JSStyleLintAdapter extends AbstractBlackAndWhitelistAdapter implements ToolAdapterInterface, FixerSupportInterface
{
    /** @var string */
    protected $blacklistToken = '.dontSniffLESS';
    /** @var string[] */
    protected $allowedFileEndings = ['/*.{css,scss,sass,less}'];
    /** @var string */
    protected $blacklistPrefix = '--ignore-pattern=';
    /** @var string */
    protected $blacklistGlue = ' ';
    /** @var string */
    protected $whitelistGlue = ' ';
    /** @var bool */
    private $commandNotFound = false;

    /**
     * {@inheritDoc}
     */
    protected function init()
    {
        try {
            $commandPath = $this->terminalCommandFinder->findTerminalCommand('stylelint');
        } catch (TerminalCommandNotFoundException $exception) {
            $this->commandNotFound = true;
            $commandPath = '';
        }

        $stylelintConfig = $this->environment->getPackageDirectory() . '/config/stylelint/.stylelintrc';
        $styleLintBlacklistCommand = $commandPath . ' **' .
            $this->allowedFileEndings[0] . ' --allow-empty-input --config=' . $stylelintConfig . ' %1$s';
        $styleLintWhitelistCommand = $commandPath . ' %1$s --allow-empty-input --config=' . $stylelintConfig;
        $styleLintFixBlacklistCommand = $commandPath . ' **' .
            $this->allowedFileEndings[0] . ' --allow-empty-input --config=' . $stylelintConfig . ' --fix %1$s';
        $styleLintFixWhitelistCommand = $commandPath . ' %1$s --allow-empty-input --config='
            . $stylelintConfig . ' --fix';

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
    public function writeViolationsToOutput($targetBranch = ''): int
    {
        if ($this->commandNotFound) {
            $this->output->write('StyleLint could not be found. ' .
                'To use this sniff please refer to the README.md', true);
            return 0;
        }

        $tool = 'STYLELINT';
        $prefix = $tool . ' : ';
        $fullMessage = $prefix . 'Running full check';
        $diffMessage = $prefix . 'Running check on diff';

        $exitCode = $this->runTool($targetBranch, $fullMessage, $tool, $diffMessage);

        return $exitCode;
    }

    /**
     * {@inheritDoc}
     */
    public function fixViolations($targetBranch = '')
    {
        if ($this->commandNotFound) {
            $this->output->write('StyleLint could not be found. ' .
                'To use this sniff please refer to the README.md', true);
            return 0;
        }

        $tool = 'STYLELINTFIX';
        $prefix = $tool . ' : ';
        $fullMessage = $prefix . 'Fix all Files';
        $diffMessage = $prefix . 'Fix Files in diff';

        $exitCode = $this->runTool($targetBranch, $fullMessage, $tool, $diffMessage);

        return $exitCode;
    }
}
