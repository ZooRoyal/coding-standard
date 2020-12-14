<?php

namespace Zooroyal\CodingStandard\CommandLine\ToolAdapters;

use Zooroyal\CodingStandard\CommandLine\Library\Exceptions\TerminalCommandNotFoundException;

class JSESLintAdapter extends AbstractBlackAndWhitelistAdapter implements ToolAdapterInterface, FixerSupportInterface
{
    /** @var string */
    protected $blacklistToken = '.dontSniffJS';
    /** @var string[] */
    protected $allowedFileEndings = ['js', 'ts', 'jsx', 'tsx'];
    /** @var string */
    protected $blacklistPrefix = '--ignore-pattern ';
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
            $commandPath = $this->terminalCommandFinder->findTerminalCommand('eslint');
        } catch (TerminalCommandNotFoundException $exception) {
            $this->commandNotFound = true;
            $commandPath = '';
        }

        $esLintConfig = $this->environment->getPackageDirectory() . '/config/eslint/.eslintrc.js';
        $esLintFilterFlags = '--ext ' . implode(' --ext ', $this->allowedFileEndings);

        $esLintBlacklistCommand = $commandPath . ' --config ' . $esLintConfig
            . ' ' . $esLintFilterFlags . ' %1$s ' . $this->environment->getRootDirectory();
        $esLintWhitelistCommand = $commandPath . ' --config ' . $esLintConfig
            . ' ' . $esLintFilterFlags . ' %1$s';
        $esLintFixBlacklistCommand = $commandPath . ' --config ' . $esLintConfig . ' '
            . $esLintFilterFlags . ' --fix %1$s ' . $this->environment->getRootDirectory();
        $esLintFixWhitelistCommand = $commandPath . ' --config ' . $esLintConfig . ' '
            . $esLintFilterFlags . ' --fix %1$s';

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
        if ($this->commandNotFound) {
            $this->output->write('Eslint could not be found. ' .
                'To use this sniff please refer to the README.md', true);
            return 0;
        }

        $tool = 'ESLINT';
        $prefix = $tool . ' : ';
        $fullMessage = $prefix . 'Running full check';
        $diffMessage = $prefix . 'Running check on diff';

        $exitCode = $this->runTool($targetBranch, $processIsolation, $fullMessage, $tool, $diffMessage);

        // This is because of the god damn stupid behavior change of eslint if no files to lint were found
        if ($exitCode === 2) {
            $exitCode = 0;
            $this->output->write('We ignore this for now!', true);
        }

        return $exitCode;
    }

    /**
     * {@inheritDoc}
     */
    public function fixViolations($targetBranch = '', bool $processIsolation = false)
    {
        if ($this->commandNotFound) {
            $this->output->write('Eslint could not be found. ' .
                'To use this sniff please refer to the README.md', true);
            return 0;
        }

        $tool = 'ESLINTFIX';
        $prefix = $tool . ' : ';
        $fullMessage = $prefix . 'Fix all Files';
        $diffMessage = $prefix . 'Fix Files in diff';

        $exitCode = $this->runTool($targetBranch, $processIsolation, $fullMessage, $tool, $diffMessage);

        // This is because of the god damn stupid behavior change of eslint if no files to lint were found
        if ($exitCode === 2) {
            $exitCode = 0;
            $this->output->write('We ignore this for now!', true);
        }

        return $exitCode;
    }
}
