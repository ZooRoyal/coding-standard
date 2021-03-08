<?php

namespace Zooroyal\CodingStandard\CommandLine\ToolAdapters;

use DI\Annotation\Injectable;
use Zooroyal\CodingStandard\CommandLine\Library\Exceptions\TerminalCommandNotFoundException;

/**
 * Class JSESLintAdapter
 *
 * @Injectable(lazy=true)
 */
class JSESLintAdapter extends AbstractBlackAndWhitelistAdapter implements ToolAdapterInterface, FixerSupportInterface
{
    protected string $blacklistToken = '.dontSniffJS';
    /** @var string[] */
    protected array $allowedFileEndings = ['js', 'ts', 'jsx', 'tsx'];
    protected string $blacklistPrefix = '--ignore-pattern ';
    protected string $blacklistGlue = ' ';
    protected string $whitelistGlue = ' ';
    private bool $commandNotFound = false;

    /**
     * {@inheritDoc}
     */
    protected function init(): void
    {
        try {
            $commandPath = $this->terminalCommandFinder->findTerminalCommand('eslint');
        } catch (TerminalCommandNotFoundException $exception) {
            $this->commandNotFound = true;
            $commandPath = '';
        }
        $esLintConfigPath = $this->environment->getPackageDirectory()->getRealPath() . '/config/eslint/';

        $esLintCliOptions = [
            'ignoreFile' => '--ignore-path ' . $esLintConfigPath . '.eslintignore',
            'noErrorOnUnmatchedPattern' => '--no-error-on-unmatched-pattern',
            'configFile' => '--config ' . $esLintConfigPath . '.eslintrc.js',
            'filterFlags' => '--ext ' . implode(' --ext ', $this->allowedFileEndings),
        ];
        $baseCommand = $commandPath . ' ' . implode(' ', $esLintCliOptions);

        $esLintBlacklistCommand = implode(
            ' ',
            [$baseCommand, '%1$s', $this->environment->getRootDirectory()->getRealPath()]
        );
        $esLintWhitelistCommand = implode(' ', [$baseCommand, '%1$s']);
        $esLintFixBlacklistCommand = implode(
            ' ',
            [$baseCommand, '--fix %1$s', $this->environment->getRootDirectory()->getRealPath()]
        );
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
    public function writeViolationsToOutput($targetBranch = ''): ?int
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

        $exitCode = $this->runTool($targetBranch, $fullMessage, $tool, $diffMessage);

        return $exitCode;
    }

    /**
     * {@inheritDoc}
     */
    public function fixViolations($targetBranch = ''): ?int
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

        $exitCode = $this->runTool($targetBranch, $fullMessage, $tool, $diffMessage);

        return $exitCode;
    }
}
