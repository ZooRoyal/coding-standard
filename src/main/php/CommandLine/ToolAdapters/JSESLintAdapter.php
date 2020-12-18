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
        $esLintConfigPath = $this->environment->getPackageDirectory() . '/config/eslint/';

        $esLintCliOptions = [
            'ignoreFile' => '--ignore-path ' . $esLintConfigPath . '.eslintignore',
            'noErrorOnUnmatchedPattern' => '--no-error-on-unmatched-pattern',
            'configFile' => '--config ' . $esLintConfigPath . '.eslintrc.js',
            'filterFlags' => '--ext ' . implode(' --ext ', $this->allowedFileEndings),
        ];
        $baseCommand = $commandPath . ' ' . implode(' ', $esLintCliOptions);

        $esLintBlacklistCommand = implode(' ', [$baseCommand, '%1$s', $this->environment->getRootDirectory()]);
        $esLintWhitelistCommand = implode(' ', [$baseCommand, '%1$s']);
        $esLintFixBlacklistCommand = implode(' ', [$baseCommand, '--fix %1$s', $this->environment->getRootDirectory()]);
        $esLintFixWhitelistCommand =  implode(' ', [$baseCommand, '--fix %1$s']);

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

        // https://eslint.org/docs/user-guide/command-line-interface#exit-codes

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
